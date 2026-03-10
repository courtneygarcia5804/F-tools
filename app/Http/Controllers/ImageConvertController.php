<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use ZipArchive;

class ImageConvertController extends Controller
{
    // Configurable constants
    const MAX_FILE_SIZE = 10240; // 10MB in KB
    const MAX_UPLOAD_COUNT = 20;
    const WEBP_QUALITY = 75;
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];
    const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/jpg'];

    /**
     * Display the upload page
     */
    public function index()
    {
        return view('upload');
    }

    /**
     * Handle bulk image upload and conversion
     */
    public function upload(Request $request)
    {
        // Validate request has files
        if (!$request->hasFile('images')) {
            return response()->json([
                'success' => false,
                'message' => 'No images uploaded'
            ], 400);
        }

        $files = $request->file('images');
        
        // Ensure it's an array
        if (!is_array($files)) {
            $files = [$files];
        }

        // Check max upload count
        if (count($files) > self::MAX_UPLOAD_COUNT) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum ' . self::MAX_UPLOAD_COUNT . ' images allowed per upload'
            ], 400);
        }

        // Create unique session directory
        $sessionId = session()->getId() ?: uniqid('guest_');
        $tempDir = 'temp-webp/' . $sessionId;
        
        // Ensure directory exists
        Storage::makeDirectory($tempDir);

        $results = [];
        $errors = [];

        // Initialize ImageManager with GD driver
        $manager = new ImageManager(new Driver());

        foreach ($files as $index => $file) {
            try {
                // Validate file
                $validation = $this->validateFile($file);
                if ($validation !== true) {
                    $errors[] = [
                        'file' => $file->getClientOriginalName(),
                        'error' => $validation
                    ];
                    continue;
                }

                // Get original file info
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $originalSize = $file->getSize();

                // Generate unique filename to handle duplicates
                $webpFilename = $this->generateUniqueFilename($tempDir, $originalName);

                // Read and convert image
                $image = $manager->read($file->getPathname());
                
                // Convert to WebP with quality setting
                $webpPath = Storage::path($tempDir . '/' . $webpFilename);
                $image->toWebp(self::WEBP_QUALITY)->save($webpPath);

                // Get WebP file size
                $webpSize = filesize($webpPath);

                // Calculate reduction percentage
                $reduction = round((1 - ($webpSize / $originalSize)) * 100, 1);

                $results[] = [
                    'original_name' => $file->getClientOriginalName(),
                    'webp_name' => $webpFilename,
                    'original_size' => $originalSize,
                    'original_size_formatted' => $this->formatBytes($originalSize),
                    'webp_size' => $webpSize,
                    'webp_size_formatted' => $this->formatBytes($webpSize),
                    'reduction' => $reduction,
                    'preview_url' => route('image.preview', ['filename' => $webpFilename, 'session' => $sessionId]),
                    'download_url' => route('image.download', ['filename' => $webpFilename, 'session' => $sessionId])
                ];

            } catch (\Exception $e) {
                $errors[] = [
                    'file' => $file->getClientOriginalName(),
                    'error' => 'Failed to process: ' . $e->getMessage()
                ];
            }
        }

        // Store session ID for download-all
        session(['image_session_id' => $sessionId]);

        return response()->json([
            'success' => true,
            'session_id' => $sessionId,
            'results' => $results,
            'errors' => $errors,
            'download_all_url' => route('image.downloadAll')
        ]);
    }

    /**
     * Serve preview image
     */
    public function preview(Request $request, string $filename)
    {
        $sessionId = $request->query('session', session('image_session_id'));
        $path = 'temp-webp/' . $sessionId . '/' . $filename;

        if (!Storage::exists($path)) {
            abort(404, 'Image not found');
        }

        return response()->file(Storage::path($path), [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'no-cache, no-store, must-revalidate'
        ]);
    }

    /**
     * Download single WebP file
     */
    public function download(Request $request, string $filename)
    {
        $sessionId = $request->query('session', session('image_session_id'));
        $path = 'temp-webp/' . $sessionId . '/' . $filename;

        if (!Storage::exists($path)) {
            abort(404, 'File not found');
        }

        $fullPath = Storage::path($path);

        // Return file for download
        return response()->download($fullPath, $filename, [
            'Content-Type' => 'image/webp'
        ])->deleteFileAfterSend(false); // Keep file for potential re-download or ZIP
    }

    /**
     * Download all images as ZIP
     */
    public function downloadAll(Request $request)
    {
        $sessionId = $request->input('session_id', session('image_session_id'));
        $tempDir = 'temp-webp/' . $sessionId;

        if (!Storage::exists($tempDir)) {
            return response()->json([
                'success' => false,
                'message' => 'No images found to download'
            ], 404);
        }

        $files = Storage::files($tempDir);

        if (empty($files)) {
            return response()->json([
                'success' => false,
                'message' => 'No images found to download'
            ], 404);
        }

        // Create ZIP file
        $zipFilename = 'webp-images-' . date('Y-m-d-His') . '.zip';
        $zipPath = Storage::path('temp-webp/' . $zipFilename);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create ZIP file'
            ], 500);
        }

        foreach ($files as $file) {
            $filename = basename($file);
            $zip->addFile(Storage::path($file), $filename);
        }

        $zip->close();

        // Return ZIP for download and cleanup after
        return response()->download($zipPath, $zipFilename, [
            'Content-Type' => 'application/zip'
        ])->deleteFileAfterSend(true);
    }

    /**
     * Clear session files (can be called via AJAX or scheduler)
     */
    public function cleanup(Request $request)
    {
        $sessionId = $request->input('session_id', session('image_session_id'));
        
        if ($sessionId) {
            $tempDir = 'temp-webp/' . $sessionId;
            Storage::deleteDirectory($tempDir);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Validate uploaded file
     */
    private function validateFile($file): bool|string
    {
        // Check if file is valid
        if (!$file->isValid()) {
            return 'File upload failed';
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return 'Invalid file type. Allowed: JPG, JPEG, PNG';
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIMES)) {
            return 'Invalid file type. Only images are allowed';
        }

        // Check file size (in KB)
        $sizeInKB = $file->getSize() / 1024;
        if ($sizeInKB > self::MAX_FILE_SIZE) {
            return 'File too large. Maximum size: ' . (self::MAX_FILE_SIZE / 1024) . 'MB';
        }

        return true;
    }

    /**
     * Generate unique filename to handle duplicates
     */
    private function generateUniqueFilename(string $directory, string $baseName): string
    {
        $filename = $baseName . '.webp';
        $counter = 1;

        while (Storage::exists($directory . '/' . $filename)) {
            $filename = $baseName . '_' . $counter . '.webp';
            $counter++;
        }

        return $filename;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
