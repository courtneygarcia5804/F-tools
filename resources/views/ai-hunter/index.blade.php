<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AI Hunter</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">AI Hunter 🔥</h1>
                <p class="mt-2 text-sm text-slate-600">Focused on YouTube Shorts search results.</p>
            </div>

            <div class="flex flex-col items-stretch gap-3 sm:items-end">
                <input
                    id="keyword-input"
                    type="text"
                    placeholder="Contoh: ai, constructor, timelapse"
                    value="ai"
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-800 outline-none ring-0 focus:border-slate-500 sm:w-80"
                >
                <button
                    id="fetch-btn"
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                >
                    Search Shorts
                </button>
            </div>
        </div>

        <div id="status" class="mb-6 rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
            Click "Fetch Videos" to load content.
        </div>

        <section id="videos-grid" class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4"></section>
    </main>

    <script>
        const fetchButton = document.getElementById('fetch-btn');
        const keywordInput = document.getElementById('keyword-input');
        const videosGrid = document.getElementById('videos-grid');
        const statusBox = document.getElementById('status');

        function escapeHtml(value) {
            return value
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            if (Number.isNaN(date.getTime())) {
                return 'Unknown date';
            }

            return date.toLocaleString();
        }

        function renderVideos(videos) {
            videosGrid.innerHTML = videos.map((video) => {
                const safeTitle = escapeHtml(video.title || 'Untitled');
                const safeThumbnail = escapeHtml(video.thumbnail || '');
                const safeVideoId = escapeHtml(video.videoId || '');
                const publishedAt = formatDate(video.publishedAt);

                return `
                    <article class="flex h-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <img
                            src="${safeThumbnail}"
                            alt="${safeTitle} thumbnail"
                            class="h-44 w-full object-cover"
                            loading="lazy"
                        >

                        <div class="flex flex-1 flex-col p-4">
                            <h2 class="line-clamp-2 text-sm font-semibold leading-6 text-slate-900">${safeTitle}</h2>
                            <p class="mt-2 text-xs text-slate-500">Published: ${publishedAt}</p>

                            <div class="mt-4 flex gap-2">
                                <a
                                    href="https://www.youtube.com/watch?v=${safeVideoId}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex flex-1 items-center justify-center rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                >
                                    Watch
                                </a>

                                <button
                                    type="button"
                                    data-video-id="${safeVideoId}"
                                    class="download-btn inline-flex flex-1 items-center justify-center rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-500"
                                >
                                    Download
                                </button>
                            </div>
                        </div>
                    </article>
                `;
            }).join('');
        }

        async function fetchVideos() {
            const keyword = (keywordInput?.value || '').trim();
            if (!keyword) {
                statusBox.textContent = 'Keyword wajib diisi.';
                return;
            }

            fetchButton.disabled = true;
            videosGrid.innerHTML = '';
            statusBox.textContent = `Loading Shorts for keyword: ${keyword}...`;

            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 25000);
                const response = await fetch(`/ai-hunter/search?keyword=${encodeURIComponent(keyword)}&shorts=1`, {
                    signal: controller.signal,
                });
                clearTimeout(timeoutId);

                if (!response.ok) {
                    let errorMessage = 'Failed to fetch videos.';
                    try {
                        const payload = await response.json();
                        if (payload && typeof payload.message === 'string' && payload.message.trim() !== '') {
                            errorMessage = payload.message;
                        }
                    } catch (_) {
                        // Keep fallback message when response is not JSON.
                    }

                    throw new Error(errorMessage);
                }

                const videos = await response.json();

                if (!Array.isArray(videos) || videos.length === 0) {
                    statusBox.textContent = `No Shorts found for keyword: ${keyword}. Try another keyword.`;
                    return;
                }

                renderVideos(videos);
                statusBox.textContent = `Loaded ${videos.length} Shorts for keyword: ${keyword}.`;
            } catch (error) {
                if (error?.name === 'AbortError') {
                    statusBox.textContent = 'Request timeout. Coba keyword lain atau ulangi beberapa detik lagi.';
                } else {
                    statusBox.textContent = error.message || 'Something went wrong while fetching videos.';
                }
            } finally {
                fetchButton.disabled = false;
            }
        }

        async function downloadVideo(videoId, button) {
            const defaultText = button.textContent;
            button.disabled = true;
            button.textContent = 'Downloading...';

            try {
                const response = await fetch(`/ai-hunter/download/${encodeURIComponent(videoId)}`);
                const payload = await response.json();

                if (!response.ok || !payload.success) {
                    throw new Error(payload.message || 'Download failed.');
                }

                statusBox.textContent = `Downloaded video ${videoId} to storage/app/public/downloads.`;
                button.textContent = 'Done';
            } catch (error) {
                statusBox.textContent = error.message || 'Download failed.';
                button.textContent = 'Retry';
            } finally {
                setTimeout(() => {
                    button.disabled = false;
                    button.textContent = defaultText;
                }, 1200);
            }
        }

        fetchButton.addEventListener('click', fetchVideos);

        keywordInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                fetchVideos();
            }
        });

        videosGrid.addEventListener('click', (event) => {
            const button = event.target.closest('.download-btn');
            if (!button) {
                return;
            }

            const videoId = button.getAttribute('data-video-id');
            if (!videoId) {
                return;
            }

            downloadVideo(videoId, button);
        });
    </script>
</body>
</html>
