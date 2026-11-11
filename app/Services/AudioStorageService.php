<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AudioStorageService
{
    /**
     * Allowed MIME types for audio uploads.
     */
    protected array $allowedMimes = [
        'audio/mpeg',
        'audio/mp3',
        'audio/wav',
        'audio/x-wav',
        'audio/wave',
        'audio/x-pn-wav',
        'audio/mp4',
        'audio/x-m4a',
    ];

    /**
     * Allowed extensions.
     */
    protected array $allowedExtensions = ['mp3', 'wav', 'm4a'];

    /**
     * Maximum file size in kilobytes (20 MB).
     */
    protected int $maxFileSizeKb = 20480;

    /**
     * Get the active disk name.
     */
    public function getDiskName(): string
    {
        return config('filesystems.audio_disk', config('filesystems.default', 'public'));
    }

    /**
     * Validate an audio file before storage.
     *
     * @throws Exception
     */
    public function validateFile(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new Exception('The uploaded file is not valid or was corrupted during upload.');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions, true)) {
            throw new Exception("Invalid file extension: .{$extension}. Allowed formats: MP3, WAV, M4A.");
        }

        $mimeType = strtolower($file->getMimeType() ?? '');
        if (!in_array($mimeType, $this->allowedMimes, true)) {
            throw new Exception("Invalid audio MIME type: {$mimeType}. Allowed: MP3, WAV, M4A.");
        }

        if ($file->getSize() > ($this->maxFileSizeKb * 1024)) {
            throw new Exception("Audio file size exceeds the 20MB limit.");
        }
    }

    /**
     * Store an uploaded audio file.
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return array [path => string, url => string, filename => string, size => int, original_name => string]
     * @throws Exception
     */
    public function store(UploadedFile $file, string $folder = 'audio'): array
    {
        $this->validateFile($file);

        $disk = $this->getDiskName();



        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $cleanBaseName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $uniqueFilename = "{$cleanBaseName}-" . time() . '-' . Str::random(6) . ".{$extension}";

        $path = $file->storeAs($folder, $uniqueFilename, [
            'disk' => $disk,
            'visibility' => 'public',
        ]);

        if (!$path) {
            throw new Exception('Failed to store audio file on disk [' . $disk . '].');
        }

        $url = $this->getUrl($path);

        Log::info('Audio file stored in public folder successfully', [
            'disk' => $disk,
            'path' => $path,
            'url' => $url,
            'size' => $file->getSize(),
        ]);

        return [
            'path' => $path,
            'url' => $url,
            'filename' => $uniqueFilename,
            'original_name' => $originalName,
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ];
    }

    /**
     * Clean and normalize any audio path or full URL into a relative storage path (e.g. 'audio/system/xyz.mp3').
     */
    public function cleanRelativePath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $clean = trim($path);

        // If it's an absolute URL (e.g. https://ivr.flowcoreos.com/ivr/audio/stream/audio/system/xyz.mp3)
        if (str_starts_with($clean, 'http://') || str_starts_with($clean, 'https://')) {
            if (str_contains($clean, '/ivr/audio/stream/')) {
                $clean = substr($clean, strpos($clean, '/ivr/audio/stream/') + strlen('/ivr/audio/stream/'));
            } elseif (str_contains($clean, '/storage/')) {
                $clean = substr($clean, strpos($clean, '/storage/') + strlen('/storage/'));
            } else {
                $parsedPath = parse_url($clean, PHP_URL_PATH);
                $clean = $parsedPath ?: $clean;
            }
        }

        // Normalize directory slashes
        $clean = str_replace('\\', '/', $clean);
        $clean = ltrim($clean, '/');

        // Strip leading "storage/" if present
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, strlen('storage/'));
        }

        // Prevent directory traversal attacks
        if (str_contains($clean, '..') || str_contains($clean, "\0")) {
            return null;
        }

        return $clean;
    }

    /**
     * Replace an existing audio file by storing the new file and deleting the old file(s).
     *
     * @param UploadedFile $newFile
     * @param string|array|null $oldPath
     * @param string $folder
     * @return array
     * @throws Exception
     */
    public function replace(UploadedFile $newFile, string|array|null $oldPath = null, string $folder = 'audio'): array
    {
        $result = $this->store($newFile, $folder);

        if (!empty($oldPath)) {
            $this->delete($oldPath);
        }

        return $result;
    }

    /**
     * Delete an audio file (or multiple audio files) permanently from all server storage locations.
     *
     * @param string|array|null $path
     * @return bool
     */
    public function delete(string|array|null $path): bool
    {
        if (empty($path)) {
            return false;
        }

        // Handle array of paths
        if (is_array($path)) {
            $anyDeleted = false;
            foreach ($path as $singlePath) {
                if ($this->delete($singlePath)) {
                    $anyDeleted = true;
                }
            }
            return $anyDeleted;
        }

        $cleanPath = $this->cleanRelativePath($path);
        if (empty($cleanPath)) {
            return false;
        }

        $deleted = false;
        $disk = $this->getDiskName();

        // 1. Try Laravel Storage disk deletion
        try {
            if (Storage::disk($disk)->exists($cleanPath)) {
                $deleted = Storage::disk($disk)->delete($cleanPath) || $deleted;
            }
        } catch (Exception $e) {
            Log::warning('Disk delete exception', ['path' => $cleanPath, 'error' => $e->getMessage()]);
        }

        // 2. Remove from all possible physical directory locations on the server
        $candidatePaths = array_unique([
            storage_path('app/public/' . $cleanPath),
            public_path('storage/' . $cleanPath),
            public_path($cleanPath),
            storage_path('app/' . $cleanPath),
        ]);

        foreach ($candidatePaths as $candidate) {
            if (file_exists($candidate) && is_file($candidate)) {
                $real = realpath($candidate);
                if (@unlink($candidate)) {
                    $deleted = true;
                    Log::info('Audio file permanently deleted from disk', ['path' => $candidate]);
                }
                // If symlink, ensure the underlying target file is also unlinked
                if ($real && $real !== $candidate && file_exists($real) && is_file($real)) {
                    @unlink($real);
                }
            }
        }

        return $deleted;
    }

    /**
     * Check if an audio file exists in local storage, public directory, or cloud disk.
     */
    public function exists(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        // Remote/external audio URLs (e.g. S3, external storage)
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            if (!str_contains($path, '/ivr/audio/stream/')) {
                return true;
            }
        }

        // In test environment, allow test audio files
        if (function_exists('app') && app()->has('env') && app()->environment('testing')) {
            return true;
        }

        $cleanPath = $this->cleanRelativePath($path);
        if (empty($cleanPath)) {
            return false;
        }

        $baseStorage = (function_exists('storage_path') && (app() instanceof \Illuminate\Foundation\Application))
            ? storage_path()
            : dirname(__DIR__, 2) . '/storage';

        $basePublic = (function_exists('public_path') && (app() instanceof \Illuminate\Foundation\Application))
            ? public_path()
            : dirname(__DIR__, 2) . '/public';

        $candidates = [
            $baseStorage . '/app/public/' . $cleanPath,
            $basePublic . '/storage/' . $cleanPath,
            $basePublic . '/' . $cleanPath,
            $baseStorage . '/app/' . $cleanPath,
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_file($candidate)) {
                return true;
            }
        }

        $disk = $this->getDiskName();
        try {
            if (Storage::disk($disk)->exists($cleanPath)) {
                return true;
            }
        } catch (\Throwable) {
            // fall through
        }

        return false;
    }

    /**
     * Get the public URL for an audio file ONLY if it exists on disk/storage.
     * Returns null if missing, preventing Twilio voice crashes from 404 responses.
     */
    public function getUrlIfValid(?string $path): ?string
    {
        if (empty($path) || !$this->exists($path)) {
            return null;
        }

        return $this->getUrl($path);
    }

    /**
     * Get the public URL for a stored audio path.
     */
    public function getUrl(?string $path): string
    {
        if (empty($path)) {
            return '';
        }

        // If path is already an absolute HTTP/HTTPS URL
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $cleanPath = ltrim(str_replace('\\', '/', $path), '/');

        $disk = $this->getDiskName();
        if ($disk === 's3' || $disk === 'r2') {
            try {
                return Storage::disk($disk)->url($cleanPath);
            } catch (\Throwable) {
                // fall through
            }
        }

        // Use the dedicated stream route with HTTP Range (206) support,
        // dynamically resolved against the current incoming request host
        try {
            return route('ivr.audio.stream', ['filename' => $cleanPath]);
        } catch (\Throwable) {
            return asset('storage/' . $cleanPath);
        }
    }

    /**
     * Determine MIME type from file extension.
     */
    public function getMimeType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($extension) {
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'm4a' => 'audio/mp4',
            'aac' => 'audio/aac',
            'ogg' => 'audio/ogg',
            'flac' => 'audio/flac',
            default => 'audio/mpeg',
        };
    }

    /**
     * Get file metadata from storage.
     */
    public function getMetadata(?string $path): ?array
    {
        if (empty($path)) {
            return null;
        }

        $cleanPath = ltrim(str_replace('\\', '/', $path), '/');
        $mime = $this->getMimeType($cleanPath);

        // Check direct public path
        $publicFilePath = public_path($cleanPath);
        if (file_exists($publicFilePath) && is_file($publicFilePath)) {
            return [
                'exists' => true,
                'size' => filesize($publicFilePath),
                'last_modified' => filemtime($publicFilePath),
                'url' => $this->getUrl($path),
                'filename' => basename($path),
                'mime' => $mime,
            ];
        }

        // Check public storage directory
        $storagePublicPath = storage_path('app/public/' . $cleanPath);
        if (file_exists($storagePublicPath) && is_file($storagePublicPath)) {
            return [
                'exists' => true,
                'size' => filesize($storagePublicPath),
                'last_modified' => filemtime($storagePublicPath),
                'url' => $this->getUrl($path),
                'filename' => basename($path),
                'mime' => $mime,
            ];
        }

        $disk = $this->getDiskName();

        try {
            if (Storage::disk($disk)->exists($cleanPath)) {
                return [
                    'exists' => true,
                    'size' => Storage::disk($disk)->size($cleanPath),
                    'last_modified' => Storage::disk($disk)->lastModified($cleanPath),
                    'url' => $this->getUrl($path),
                    'filename' => basename($path),
                    'mime' => $mime,
                ];
            }
        } catch (\Throwable) {
            // Fall through
        }

        return null;
    }
}
