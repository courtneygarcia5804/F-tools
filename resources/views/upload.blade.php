<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bulk Image to WebP Converter</title>
    <meta name="description" content="Free online tool to convert and compress images to WebP format. Drag & drop multiple images, preview results, and download optimized files.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --accent: #22d3ee;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg-dark: #0f0f23;
            --bg-card: rgba(30, 30, 60, 0.6);
            --bg-card-hover: rgba(40, 40, 80, 0.8);
            --text-primary: #ffffff;
            --text-secondary: #a1a1aa;
            --text-muted: #71717a;
            --border: rgba(255, 255, 255, 0.1);
            --border-active: rgba(99, 102, 241, 0.5);
            --shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            --glass: rgba(255, 255, 255, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Animated background */
        .bg-pattern {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(34, 211, 238, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(139, 92, 246, 0.1) 0%, transparent 50%);
            z-index: -1;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Header */
        header {
            text-align: center;
            margin-bottom: 3rem;
            animation: fadeInDown 0.6s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .logo-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--text-primary), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-top: 0.5rem;
        }

        /* Upload Zone */
        .upload-zone {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 2px dashed var(--border);
            border-radius: 24px;
            padding: 4rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.6s ease-out 0.2s both;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .upload-zone::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent, rgba(99, 102, 241, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .upload-zone:hover,
        .upload-zone.dragover {
            border-color: var(--primary);
            background: var(--bg-card-hover);
            transform: translateY(-2px);
            box-shadow: var(--shadow), 0 0 40px rgba(99, 102, 241, 0.2);
        }

        .upload-zone:hover::before,
        .upload-zone.dragover::before {
            opacity: 1;
        }

        .upload-zone.dragover {
            border-color: var(--accent);
            box-shadow: var(--shadow), 0 0 60px rgba(34, 211, 238, 0.3);
        }

        .upload-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
            transition: transform 0.3s ease;
        }

        .upload-zone:hover .upload-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .upload-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .upload-subtitle {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }

        .upload-formats {
            display: inline-flex;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--glass);
            border-radius: 100px;
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .upload-formats span {
            padding: 0.25rem 0.75rem;
            background: rgba(99, 102, 241, 0.2);
            border-radius: 100px;
            color: var(--primary-light);
            font-weight: 500;
        }

        #fileInput {
            display: none;
        }

        /* Processing State */
        .processing-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 15, 35, 0.9);
            backdrop-filter: blur(10px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 2rem;
        }

        .processing-overlay.active {
            display: flex;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .processing-text {
            font-size: 1.25rem;
            color: var(--text-secondary);
        }

        /* Results Section */
        .results-section {
            display: none;
            margin-top: 3rem;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .results-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .results-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(99, 102, 241, 0.5);
        }

        .btn-secondary {
            background: var(--glass);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--bg-card-hover);
            border-color: var(--primary);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Stats Summary */
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Results Table */
        .results-table-wrapper {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
        }

        .results-table th {
            background: rgba(99, 102, 241, 0.1);
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .results-table td {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
            vertical-align: middle;
        }

        .results-table tr:hover td {
            background: rgba(99, 102, 241, 0.05);
        }

        .preview-cell {
            width: 80px;
        }

        .preview-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .filename {
            font-weight: 500;
            color: var(--text-primary);
        }

        .file-original {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .size-badge {
            display: inline-flex;
            padding: 0.25rem 0.75rem;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .size-original {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        .size-webp {
            background: rgba(16, 185, 129, 0.2);
            color: #6ee7b7;
        }

        .reduction-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(34, 211, 238, 0.2));
            border-radius: 100px;
            font-weight: 700;
            color: var(--success);
        }

        .btn-download {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
        }

        /* Error Toast */
        .toast-container {
            position: fixed;
            top: 2rem;
            right: 2rem;
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .toast {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--danger);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideIn 0.3s ease-out;
            max-width: 400px;
        }

        .toast.success {
            border-color: var(--success);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .toast-icon {
            font-size: 1.25rem;
        }

        .toast-message {
            flex: 1;
            font-size: 0.875rem;
        }

        .toast-close {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1.25rem;
            padding: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            h1 {
                font-size: 1.75rem;
            }

            .upload-zone {
                padding: 2rem 1rem;
            }

            .results-header {
                flex-direction: column;
                align-items: stretch;
            }

            .results-actions {
                justify-content: center;
            }

            .results-table-wrapper {
                overflow-x: auto;
            }

            .results-table {
                min-width: 600px;
            }

            .stats-summary {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 3rem 0;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        footer a {
            color: var(--primary-light);
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    
    <div class="container">
        <header>
            <div class="logo">
                <div class="logo-icon">🖼️</div>
            </div>
            <h1>Bulk Image to WebP Converter</h1>
            <p class="subtitle">Compress & convert multiple images to WebP format instantly</p>
        </header>

        <div class="upload-zone" id="uploadZone">
            <div class="upload-icon">📁</div>
            <h2 class="upload-title">Drag & Drop Images Here</h2>
            <p class="upload-subtitle">or click to select files</p>
            <div class="upload-formats">
                Supported: <span>JPG</span> <span>JPEG</span> <span>PNG</span>
            </div>
            <p style="margin-top: 1rem; color: var(--text-muted); font-size: 0.875rem;">
                Max 20 images • 10MB each • Quality: 75%
            </p>
            <input type="file" id="fileInput" multiple accept=".jpg,.jpeg,.png,image/jpeg,image/png">
        </div>

        <section class="results-section" id="resultsSection">
            <div class="results-header">
                <h2 class="results-title">✨ Converted Images</h2>
                <div class="results-actions">
                    <button class="btn btn-secondary" id="clearBtn">
                        🗑️ Clear All
                    </button>
                    <button class="btn btn-primary" id="downloadAllBtn">
                        📦 Download All as ZIP
                    </button>
                </div>
            </div>

            <div class="stats-summary" id="statsSummary">
                <div class="stat-card">
                    <div class="stat-value" id="statCount">0</div>
                    <div class="stat-label">Images Converted</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="statOriginal">0 KB</div>
                    <div class="stat-label">Original Size</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="statWebp">0 KB</div>
                    <div class="stat-label">WebP Size</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="statSaved">0%</div>
                    <div class="stat-label">Space Saved</div>
                </div>
            </div>

            <div class="results-table-wrapper">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Preview</th>
                            <th>Filename</th>
                            <th>Original</th>
                            <th>WebP</th>
                            <th>Saved</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="resultsBody">
                    </tbody>
                </table>
            </div>
        </section>

        <footer>
            <p>Built with ❤️ using Laravel & Intervention Image</p>
        </footer>
    </div>

    <div class="processing-overlay" id="processingOverlay">
        <div class="spinner"></div>
        <p class="processing-text">Converting images to WebP...</p>
    </div>

    <div class="toast-container" id="toastContainer"></div>

    <script>
        // DOM Elements
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('fileInput');
        const processingOverlay = document.getElementById('processingOverlay');
        const resultsSection = document.getElementById('resultsSection');
        const resultsBody = document.getElementById('resultsBody');
        const downloadAllBtn = document.getElementById('downloadAllBtn');
        const clearBtn = document.getElementById('clearBtn');
        const toastContainer = document.getElementById('toastContainer');

        // State
        let currentSessionId = null;
        let allResults = [];

        // CSRF Token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Drag & Drop Events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, preventDefaults);
            document.body.addEventListener(eventName, preventDefaults);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => uploadZone.classList.add('dragover'));
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => uploadZone.classList.remove('dragover'));
        });

        uploadZone.addEventListener('drop', handleDrop);
        uploadZone.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', handleFileSelect);

        function handleDrop(e) {
            const files = e.dataTransfer.files;
            handleFiles(files);
        }

        function handleFileSelect(e) {
            const files = e.target.files;
            handleFiles(files);
        }

        async function handleFiles(files) {
            if (files.length === 0) return;

            // Validate file count
            if (files.length > 20) {
                showToast('Maximum 20 images allowed per upload', 'error');
                return;
            }

            // Filter valid files
            const validFiles = [];
            const invalidFiles = [];
            
            for (const file of files) {
                const ext = file.name.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png'].includes(ext)) {
                    if (file.size > 10 * 1024 * 1024) {
                        invalidFiles.push(`${file.name} (too large)`);
                    } else {
                        validFiles.push(file);
                    }
                } else {
                    invalidFiles.push(`${file.name} (invalid type)`);
                }
            }

            if (invalidFiles.length > 0) {
                showToast(`Skipped: ${invalidFiles.join(', ')}`, 'error');
            }

            if (validFiles.length === 0) {
                showToast('No valid images to upload', 'error');
                return;
            }

            // Show processing overlay
            processingOverlay.classList.add('active');

            // Create FormData
            const formData = new FormData();
            validFiles.forEach(file => formData.append('images[]', file));

            try {
                const response = await fetch('{{ route("image.upload") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Upload failed');
                }

                currentSessionId = data.session_id;
                
                // Handle errors
                if (data.errors && data.errors.length > 0) {
                    data.errors.forEach(err => {
                        showToast(`${err.file}: ${err.error}`, 'error');
                    });
                }

                // Handle results
                if (data.results && data.results.length > 0) {
                    allResults = [...allResults, ...data.results];
                    renderResults();
                    updateStats();
                    resultsSection.style.display = 'block';
                    showToast(`Successfully converted ${data.results.length} image(s)`, 'success');
                }

            } catch (error) {
                showToast(error.message, 'error');
            } finally {
                processingOverlay.classList.remove('active');
                fileInput.value = '';
            }
        }

        function renderResults() {
            resultsBody.innerHTML = allResults.map((result, index) => `
                <tr style="animation: slideUp 0.3s ease-out ${index * 0.05}s both">
                    <td class="preview-cell">
                        <img src="${result.preview_url}" alt="${result.webp_name}" class="preview-img" loading="lazy">
                    </td>
                    <td>
                        <div class="filename">${result.webp_name}</div>
                        <div class="file-original">${result.original_name}</div>
                    </td>
                    <td>
                        <span class="size-badge size-original">${result.original_size_formatted}</span>
                    </td>
                    <td>
                        <span class="size-badge size-webp">${result.webp_size_formatted}</span>
                    </td>
                    <td>
                        <span class="reduction-badge">↓ ${result.reduction}%</span>
                    </td>
                    <td>
                        <a href="${result.download_url}" class="btn btn-primary btn-download" download>
                            ⬇️ Download
                        </a>
                    </td>
                </tr>
            `).join('');
        }

        function updateStats() {
            const totalOriginal = allResults.reduce((sum, r) => sum + r.original_size, 0);
            const totalWebp = allResults.reduce((sum, r) => sum + r.webp_size, 0);
            const avgReduction = totalOriginal > 0 ? Math.round((1 - totalWebp / totalOriginal) * 100) : 0;

            document.getElementById('statCount').textContent = allResults.length;
            document.getElementById('statOriginal').textContent = formatBytes(totalOriginal);
            document.getElementById('statWebp').textContent = formatBytes(totalWebp);
            document.getElementById('statSaved').textContent = avgReduction + '%';
        }

        function formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Download All
        downloadAllBtn.addEventListener('click', async () => {
            if (!currentSessionId) {
                showToast('No images to download', 'error');
                return;
            }

            downloadAllBtn.disabled = true;
            downloadAllBtn.innerHTML = '⏳ Creating ZIP...';

            try {
                const formData = new FormData();
                formData.append('session_id', currentSessionId);

                const response = await fetch('{{ route("image.downloadAll") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });

                if (!response.ok) {
                    const data = await response.json();
                    throw new Error(data.message || 'Download failed');
                }

                // Get filename from header
                const contentDisposition = response.headers.get('Content-Disposition');
                let filename = 'webp-images.zip';
                if (contentDisposition) {
                    const match = contentDisposition.match(/filename="?([^"]+)"?/);
                    if (match) filename = match[1];
                }

                // Download blob
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);

                showToast('ZIP downloaded successfully!', 'success');

            } catch (error) {
                showToast(error.message, 'error');
            } finally {
                downloadAllBtn.disabled = false;
                downloadAllBtn.innerHTML = '📦 Download All as ZIP';
            }
        });

        // Clear All
        clearBtn.addEventListener('click', async () => {
            if (currentSessionId) {
                try {
                    const formData = new FormData();
                    formData.append('session_id', currentSessionId);
                    
                    await fetch('{{ route("image.cleanup") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: formData
                    });
                } catch (e) {
                    // Ignore cleanup errors
                }
            }

            allResults = [];
            currentSessionId = null;
            resultsSection.style.display = 'none';
            resultsBody.innerHTML = '';
            showToast('All images cleared', 'success');
        });

        // Toast Notification
        function showToast(message, type = 'error') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <span class="toast-icon">${type === 'success' ? '✅' : '⚠️'}</span>
                <span class="toast-message">${message}</span>
                <button class="toast-close" onclick="this.parentElement.remove()">×</button>
            `;
            toastContainer.appendChild(toast);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>
