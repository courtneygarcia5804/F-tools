<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class AiHunterController extends Controller
{
    /**
     * Queries focused on constructor timelapse videos.
     */
    private const SEARCH_QUERIES = [
        'constructor timelapse',
        'constructor build timelapse',
        'construction timelapse',
        'construction simulator timelapse',
        'builder timelapse',
        'city build timelapse',
        'konstruksi timelapse',
        'pembangunan timelapse',
    ];

    /**
     * Fallback queries when strict matching yields zero results.
     */
    private const RELAXED_SEARCH_QUERIES = [
        'constructor build',
        'constructor game build',
        'constructor simulator',
        'timelapse build',
        'construction progress',
        'construction update timelapse',
        'pembangunan progress',
    ];

    private const CONSTRUCTION_KEYWORDS = [
        'constructor',
        'construction',
        'simulator',
        'builder',
        'build',
        'building',
        'architecture',
        'konstruksi',
        'pembangunan',
        'bangun',
        'gedung',
        'rumah',
        'city build',
    ];

    private const TIMELAPSE_KEYWORDS = [
        'timelapse',
        'time-lapse',
        'time lapse',
        'hyperlapse',
    ];

    /**
     * Show AI Hunter UI.
     */
    public function index()
    {
        return view('ai-hunter.index');
    }

    /**
     * Fetch constructor timelapse videos from YouTube Data API v3.
     */
    public function search(Request $request): JsonResponse
    {
        $apiKey = config('services.youtube.api_key');
        $manualKeyword = trim((string) $request->query('keyword', ''));
        $isManualKeyword = $manualKeyword !== '';
        $shortsOnly = $request->boolean('shorts', true);

        $searchQueries = $isManualKeyword
            ? [$manualKeyword]
            : self::SEARCH_QUERIES;

        $relaxedSearchQueries = $isManualKeyword
            ? array_values(array_unique(array_filter([
                $manualKeyword . ' timelapse',
                $manualKeyword . ' build',
                $manualKeyword . ' simulator',
            ])))
            : self::RELAXED_SEARCH_QUERIES;

        // Manual keyword mode should behave like direct YouTube search.
        if ($isManualKeyword) {
            // Prefer yt-dlp first so manual search still works when API quota is exceeded.
            $fallback = $this->searchWithYtDlp($manualKeyword, 25, $shortsOnly);
            if ($fallback['ok']) {
                return response()->json($fallback['videos']);
            }

            if (empty($apiKey)) {
                return response()->json([
                    'message' => 'YOUTUBE_API_KEY is not configured and yt-dlp fallback failed: ' . $fallback['error'],
                ], 500);
            }

            $response = Http::timeout(20)->get('https://www.googleapis.com/youtube/v3/search', [
                'part' => 'snippet',
                'q' => $manualKeyword,
                'type' => 'video',
                'order' => 'relevance',
                'maxResults' => 25,
                'videoDuration' => $shortsOnly ? 'short' : null,
                'key' => $apiKey,
            ]);

            if (! $response->successful()) {
                $apiMessage = (string) data_get($response->json(), 'error.message', '');

                return response()->json([
                    'message' => ($apiMessage !== ''
                        ? 'YouTube API error: ' . $apiMessage
                        : 'Failed to fetch YouTube search results.')
                        . ' | yt-dlp fallback failed: ' . $fallback['error'],
                ], 502);
            }

            $videos = [];
            $seen = [];

            foreach ($response->json('items', []) as $item) {
                $videoId = (string) data_get($item, 'id.videoId', '');
                if ($videoId === '' || isset($seen[$videoId])) {
                    continue;
                }

                $seen[$videoId] = true;
                $videos[] = [
                    'title' => (string) data_get($item, 'snippet.title', ''),
                    'thumbnail' => data_get($item, 'snippet.thumbnails.high.url')
                        ?? data_get($item, 'snippet.thumbnails.medium.url')
                        ?? data_get($item, 'snippet.thumbnails.default.url'),
                    'videoId' => $videoId,
                    'publishedAt' => data_get($item, 'snippet.publishedAt'),
                ];
            }

            return response()->json($videos);
        }

        if (empty($apiKey)) {
            return response()->json([
                'message' => 'YOUTUBE_API_KEY is not configured.',
            ], 500);
        }

        $videosById = [];
        $rawVideosById = [];

        foreach ($searchQueries as $query) {
            $params = [
                'part' => 'snippet',
                'q' => $query,
                'type' => 'video',
                // "Rame" = prioritize popular content.
                'order' => 'viewCount',
                'maxResults' => 10,
                'videoDuration' => $shortsOnly ? 'short' : null,
                'key' => $apiKey,
            ];

            $response = Http::timeout(20)->get('https://www.googleapis.com/youtube/v3/search', $params);

            if (! $response->successful()) {
                // Keep other queries working even if one request fails.
                continue;
            }

            $items = $response->json('items', []);

            foreach ($items as $item) {
                $videoId = data_get($item, 'id.videoId');
                $title = (string) data_get($item, 'snippet.title', '');

                if (! $videoId) {
                    continue;
                }

                $rawVideosById[$videoId] = [
                    'title' => $title,
                    'thumbnail' => data_get($item, 'snippet.thumbnails.high.url')
                        ?? data_get($item, 'snippet.thumbnails.medium.url')
                        ?? data_get($item, 'snippet.thumbnails.default.url'),
                    'videoId' => $videoId,
                    'publishedAt' => data_get($item, 'snippet.publishedAt'),
                    'description' => (string) data_get($item, 'snippet.description', ''),
                    'channelTitle' => (string) data_get($item, 'snippet.channelTitle', ''),
                ];

                if (! $isManualKeyword && ! $this->isConstructionTimelapseVideo($item)) {
                    continue;
                }

                $videosById[$videoId] = [
                    'title' => $title,
                    'thumbnail' => data_get($item, 'snippet.thumbnails.high.url')
                        ?? data_get($item, 'snippet.thumbnails.medium.url')
                        ?? data_get($item, 'snippet.thumbnails.default.url'),
                    'videoId' => $videoId,
                    'publishedAt' => data_get($item, 'snippet.publishedAt'),
                    'description' => (string) data_get($item, 'snippet.description', ''),
                    'channelTitle' => (string) data_get($item, 'snippet.channelTitle', ''),
                ];
            }
        }

        // Fallback: relax timelapse requirement if strict pass returns no items.
        if (empty($videosById)) {
            foreach ($relaxedSearchQueries as $query) {
                $response = Http::timeout(20)->get('https://www.googleapis.com/youtube/v3/search', [
                    'part' => 'snippet',
                    'q' => $query,
                    'type' => 'video',
                    'order' => 'viewCount',
                    'maxResults' => 10,
                    'videoDuration' => $shortsOnly ? 'short' : null,
                    'key' => $apiKey,
                ]);

                if (! $response->successful()) {
                    continue;
                }

                foreach ($response->json('items', []) as $item) {
                    $videoId = data_get($item, 'id.videoId');

                    if (! $videoId) {
                        continue;
                    }

                    $rawVideosById[$videoId] = [
                        'title' => (string) data_get($item, 'snippet.title', ''),
                        'thumbnail' => data_get($item, 'snippet.thumbnails.high.url')
                            ?? data_get($item, 'snippet.thumbnails.medium.url')
                            ?? data_get($item, 'snippet.thumbnails.default.url'),
                        'videoId' => $videoId,
                        'publishedAt' => data_get($item, 'snippet.publishedAt'),
                        'description' => (string) data_get($item, 'snippet.description', ''),
                        'channelTitle' => (string) data_get($item, 'snippet.channelTitle', ''),
                    ];

                    if (! $isManualKeyword && ! $this->isConstructionLikeVideo($item)) {
                        continue;
                    }

                    $videosById[$videoId] = [
                        'title' => (string) data_get($item, 'snippet.title', ''),
                        'thumbnail' => data_get($item, 'snippet.thumbnails.high.url')
                            ?? data_get($item, 'snippet.thumbnails.medium.url')
                            ?? data_get($item, 'snippet.thumbnails.default.url'),
                        'videoId' => $videoId,
                        'publishedAt' => data_get($item, 'snippet.publishedAt'),
                        'description' => (string) data_get($item, 'snippet.description', ''),
                        'channelTitle' => (string) data_get($item, 'snippet.channelTitle', ''),
                    ];
                }
            }
        }

        // Last resort: keep results useful instead of returning empty list.
        if (empty($videosById) && ! empty($rawVideosById)) {
            if ($isManualKeyword) {
                foreach ($rawVideosById as $videoId => $video) {
                    $videosById[$videoId] = $video;

                    if (count($videosById) >= 24) {
                        break;
                    }
                }
            }

            foreach ($rawVideosById as $videoId => $video) {
                if ($isManualKeyword) {
                    break;
                }

                if (! $this->isTimelapseOrConstructorLikeVideo($video)) {
                    continue;
                }

                $videosById[$videoId] = $video;

                if (count($videosById) >= 24) {
                    break;
                }
            }
        }

        // Keep response payload simple for frontend.
        $videos = array_values(array_map(function (array $video): array {
            return [
                'title' => $video['title'],
                'thumbnail' => $video['thumbnail'],
                'videoId' => $video['videoId'],
                'publishedAt' => $video['publishedAt'],
            ];
        }, $videosById));

        usort($videos, function (array $a, array $b): int {
            return strcmp((string) $b['publishedAt'], (string) $a['publishedAt']);
        });

        return response()->json($videos);
    }

    /**
     * Fallback search using yt-dlp when YouTube API is unavailable (e.g. quota exceeded).
     *
     * @return array{ok: bool, videos: array<int, array<string, mixed>>, error: string}
     */
    private function searchWithYtDlp(string $keyword, int $limit = 25, bool $shortsOnly = true): array
    {
        $ytDlpBinary = $this->resolveYtDlpBinary();
        if ($ytDlpBinary === null) {
            return [
                'ok' => false,
                'videos' => [],
                'error' => 'yt-dlp is not installed on this server.',
            ];
        }

        $searchKeyword = $shortsOnly ? trim($keyword . ' shorts') : $keyword;

        $result = Process::timeout(60)->run([
            $ytDlpBinary,
            '--flat-playlist',
            '--dump-single-json',
            '--no-warnings',
            'ytsearch' . $limit . ':' . $searchKeyword,
        ]);

        if ($result->failed()) {
            return [
                'ok' => false,
                'videos' => [],
                'error' => trim($result->errorOutput() ?: $result->output() ?: 'Unknown yt-dlp error.'),
            ];
        }

        $payload = json_decode($result->output(), true);
        if (! is_array($payload)) {
            return [
                'ok' => false,
                'videos' => [],
                'error' => 'Unable to parse yt-dlp search result.',
            ];
        }

        $videos = [];
        $seen = [];
        foreach ((array) data_get($payload, 'entries', []) as $entry) {
            $videoId = (string) data_get($entry, 'id', '');
            if ($videoId === '' || isset($seen[$videoId])) {
                continue;
            }

            $seen[$videoId] = true;
            $thumbnail = data_get($entry, 'thumbnails.0.url');
            if (! is_string($thumbnail) || $thumbnail === '') {
                $thumbnail = 'https://i.ytimg.com/vi/' . $videoId . '/hqdefault.jpg';
            }

            $videos[] = [
                'title' => (string) data_get($entry, 'title', ''),
                'thumbnail' => $thumbnail,
                'videoId' => $videoId,
                'publishedAt' => null,
            ];
        }

        return [
            'ok' => true,
            'videos' => $videos,
            'error' => '',
        ];
    }

    /**
     * Keep only results that look like construction/build timelapse videos.
     */
    private function isConstructionTimelapseVideo(array $item): bool
    {
        $title = strtolower((string) data_get($item, 'snippet.title', ''));
        $description = strtolower((string) data_get($item, 'snippet.description', ''));
        $channel = strtolower((string) data_get($item, 'snippet.channelTitle', ''));
        $haystack = $title . ' ' . $description . ' ' . $channel;

        $hasConstructionTerm = false;
        foreach (self::CONSTRUCTION_KEYWORDS as $keyword) {
            if (str_contains($haystack, $keyword)) {
                $hasConstructionTerm = true;
                break;
            }
        }

        if (! $hasConstructionTerm) {
            return false;
        }

        foreach (self::TIMELAPSE_KEYWORDS as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Relaxed check: construction related without timelapse requirement.
     */
    private function isConstructionLikeVideo(array $item): bool
    {
        $title = strtolower((string) data_get($item, 'snippet.title', ''));
        $description = strtolower((string) data_get($item, 'snippet.description', ''));
        $channel = strtolower((string) data_get($item, 'snippet.channelTitle', ''));
        $haystack = $title . ' ' . $description . ' ' . $channel;

        foreach (self::CONSTRUCTION_KEYWORDS as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Very loose fallback check to avoid empty results.
     */
    private function isTimelapseOrConstructorLikeVideo(array $video): bool
    {
        $title = strtolower((string) ($video['title'] ?? ''));
        $description = strtolower((string) ($video['description'] ?? ''));
        $channel = strtolower((string) ($video['channelTitle'] ?? ''));
        $haystack = $title . ' ' . $description . ' ' . $channel;

        foreach (self::TIMELAPSE_KEYWORDS as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return true;
            }
        }

        foreach (self::CONSTRUCTION_KEYWORDS as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Download a YouTube video via yt-dlp.
     */
    public function download(string $videoId): JsonResponse
    {
        if (! preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid YouTube video ID.',
            ], 422);
        }

        $ytDlpBinary = $this->resolveYtDlpBinary();
        if ($ytDlpBinary === null) {
            return response()->json([
                'success' => false,
                'message' => 'yt-dlp is not installed on this server.',
            ], 500);
        }

        Storage::disk('public')->makeDirectory('downloads');

        $outputTemplate = storage_path('app/public/downloads/%(upload_date)s - %(title)s.%(ext)s');
        $videoUrl = 'https://www.youtube.com/watch?v=' . $videoId;

        $result = Process::timeout(900)->run([
            $ytDlpBinary,
            '--no-progress',
            '-o',
            $outputTemplate,
            $videoUrl,
        ]);

        if ($result->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download video.',
                'error' => trim($result->errorOutput() ?: $result->output()),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Video downloaded successfully.',
            'videoId' => $videoId,
            'downloadPath' => 'storage/app/public/downloads',
        ]);
    }

    /**
     * Resolve yt-dlp binary path for environments with limited PATH.
     */
    private function resolveYtDlpBinary(): ?string
    {
        $commonPaths = [
            '/opt/homebrew/bin/yt-dlp',
            '/usr/local/bin/yt-dlp',
        ];

        foreach ($commonPaths as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }

        $command = Process::run('command -v yt-dlp || which yt-dlp');
        if ($command->successful()) {
            $resolved = trim($command->output());
            if ($resolved !== '' && is_executable($resolved)) {
                return $resolved;
            }
        }

        return null;
    }
}
