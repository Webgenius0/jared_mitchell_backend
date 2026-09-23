<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class FileHandle
{
    /**
     * Upload file to S3 (production) or local public disk (local env).
     * Returns the full public URL of the uploaded file.
     */
    public static function fileUpload($file, string $folder): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        // Generate unique filename
        $fileName = time() . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/' . trim($folder, '/') . '/' . $fileName;

        $disk = config('filesystems.default') === 's3' ? 's3' : 'public';

        if ($disk === 's3') {
            // Upload to S3 with public visibility
            Storage::disk('s3')->put($path, file_get_contents($file), 'public');
            // Return full S3 URL
            return Storage::disk('s3')->url($path);
        }

        // Local: store in storage/app/public
        $stored = $file->storeAs(
            'uploads/' . trim($folder, '/'),
            $fileName,
            'public'
        );

        return 'storage/' . $stored;
    }

    /**
     * Delete file from S3 or local public disk.
     */
    public static function fileDelete(?string $path): void
    {
        if (!$path) {
            return;
        }

        // If it's a full URL (S3), extract the key and delete from S3
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $awsUrl = env('AWS_URL', '');
            if ($awsUrl && str_starts_with($path, $awsUrl)) {
                $key = ltrim(str_replace($awsUrl, '', $path), '/');
                Storage::disk('s3')->delete($key);
            }
            return;
        }

        // Local: delete from public disk
        $localPath = str_replace('storage/', '', $path);
        if (Storage::disk('public')->exists($localPath)) {
            Storage::disk('public')->delete($localPath);
        }
    }

    /**
     * Generate a random alphanumeric string.
     */
    public static function randomAlphaNum($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Generate slug for user profile
     */
    public static function generateSlug(string $firstName): string
    {
        return strtolower($firstName) . self::randomAlphaNum(8);
    }

    /**
     * Generate username for user profile
     */
    public static function generateUsername(string $firstName): string
    {
        $cleanName = preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $firstName));
        return '@' . strtolower($cleanName ?: 'user') . self::randomAlphaNum(8);
    }
}
