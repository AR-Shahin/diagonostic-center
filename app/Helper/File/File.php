<?php

namespace App\Helper\File;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class File
{
    // Allowed image extensions
    private static array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    // Maximum file size in bytes (5MB)
    private static int $maxFileSize = 5242880;

    /**
     * Upload a file with automatic unique name.
     */
    public static function upload($file, $path): ?string
    {
        if (is_null($file)) {
            return null;
        }

        self::validateFile($file);

        $fileName = self::generateFileName($file);
        $storedPath = Storage::putFileAs("public/$path", $file, $fileName);

        self::setReadOnly($storedPath);

        return "storage/$path/$fileName";
    }

    /**
     * Upload a file with custom name.
     */
    public static function uploadWithName($file, $path, ?string $customName = null): ?string
    {
        if (is_null($file)) {
            return null;
        }

        self::validateFile($file);

        $fileName = $customName ? self::sanitizeFileName($customName).'.'.$file->getClientOriginalExtension()
                                : self::generateFileName($file);

        $storedPath = Storage::putFileAs("public/$path", $file, $fileName);

        self::setReadOnly($storedPath);

        return "storage/$path/$fileName";
    }

    /**
     * Upload Base64 image safely with folder structure: Year/Month/Day
     */
    public static function uploadBase64Image($imageData, $folder, $model = null): ?string
    {
        if (is_null($imageData)) {
            return null;
        }

        [$meta, $data] = explode(',', $imageData);
        $imageData = base64_decode($data);

        $mime = finfo_buffer(finfo_open(), $imageData, FILEINFO_MIME_TYPE);
        $extension = self::getExtensionFromMime($mime);

        $title = $model?->image_caption ?? 'image';
        $title = Str::slug($title);

        $filename = $title.'_'.uniqid().'-'.time().'.'.$extension;
        $year = date('Y');
        $month = strtolower(date('F'));
        $day = date('d');

        $fullPath = "$year/$month/$day/$folder/$filename";

        Storage::disk('public')->put($fullPath, $imageData);

        self::setReadOnly($fullPath);

        return "storage/$fullPath";
    }

    /**
     * Upload Base64 image without folder tree.
     */
    public static function uploadBase64ImageWithoutTreeDirectory($imageData, $path): ?string
    {
        if (is_null($imageData)) {
            return null;
        }

        [$meta, $data] = explode(',', $imageData);
        $imageData = base64_decode($data);

        $mime = finfo_buffer(finfo_open(), $imageData, FILEINFO_MIME_TYPE);
        $extension = self::getExtensionFromMime($mime);

        $filename = time().uniqid().'.'.$extension;
        $fullPath = "$path/$filename";

        Storage::disk('public')->put($fullPath, $imageData);

        self::setReadOnly($fullPath);

        return "storage/$fullPath";
    }

    /**
     * Delete file safely inside storage folder.
     */
    public static function deleteFile($file): void
    {
        if (! $file) {
            return;
        }

        $storagePath = realpath(storage_path());
        $fileRealPath = realpath(base_path($file)) ?: realpath($file);

        if ($fileRealPath && str_starts_with($fileRealPath, $storagePath) && file_exists($fileRealPath)) {
            unlink($fileRealPath);
        }
    }

    /**
     * Upload file organized by Year/Month/Day folder structure.
     */
    public static function uploadYearMonthWise($file, $folder): ?string
    {
        if (is_null($file)) {
            return null;
        }

        self::validateFile($file);

        $year = date('Y');
        $month = strtolower(date('F'));
        $day = date('d');

        $path = "$year/$month/$day/$folder";
        $fileName = self::generateFileName($file);

        $file->storeAs("public/$path", $fileName);

        self::setReadOnly("public/$path/$fileName");

        return "storage/$path/$fileName";
    }

    /**
     * Upload bulk image for district, replacing existing image for the day.
     */
    public static function uploadDistrictBulkImage($file, $folder, $district, $day): ?string
    {
        if (is_null($file)) {
            return null;
        }

        self::validateFile($file);

        $existing = $district->images->first(fn ($image) => str_contains(strtolower($image->image), strtolower($day)));

        if ($existing) {
            self::deleteFile($existing->image);
            $existing->delete();
        }

        $fileName = self::sanitizeFileName($file->getClientOriginalName());
        $storedPath = Storage::putFileAs("public/$folder", $file, $fileName);

        self::setReadOnly($storedPath);

        return "storage/$folder/$fileName";
    }

    /**
     * Validate uploaded file type and size.
     */
    private static function validateFile($file): void
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, self::$allowedExtensions)) {
            throw new \Exception("Invalid file type: $ext");
        }

        if ($file->getSize() > self::$maxFileSize) {
            throw new \Exception('File size exceeds limit of 5MB.');
        }
    }

    /**
     * Generate sanitized unique file name.
     */
    private static function generateFileName($file): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $name = self::sanitizeFileName($name);
        $ext = $file->getClientOriginalExtension();

        return $name.'_'.time().'_'.uniqid().'.'.$ext;
    }

    /**
     * Sanitize filename to remove unsafe characters.
     */
    private static function sanitizeFileName($name): string
    {
        return preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
    }

    /**
     * Map MIME type to file extension.
     */
    private static function getExtensionFromMime($mime): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
        ];

        return $map[$mime] ?? 'jpg';
    }

    /**
     * Set read-only permission (644) for uploaded file.
     */
    private static function setReadOnly($path): void
    {
        $fullPath = Storage::disk('public')->path($path);
        if (file_exists($fullPath)) {
            chmod($fullPath, 0644);
            $dir = dirname($fullPath);
            if (is_dir($dir)) {
                chmod($dir, 0755);
            }
        }
    }
}
