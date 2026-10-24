<?php
declare(strict_types=1);

namespace App\Services;

final class UploadService
{
    private const MAX_BYTES = 5 * 1024 * 1024;
    private const TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    /** Validates and stores an uploaded image. Returns the relative path (uploads/...). */
    public static function storeImage(?array $file, string $folder): string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            throw new \RuntimeException('Please choose an image to upload.');
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed (error code ' . $file['error'] . ').');
        }
        if ($file['size'] > self::MAX_BYTES) {
            throw new \RuntimeException('Image must be smaller than 5 MB.');
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($file['tmp_name']);
        if (!isset(self::TYPES[$mime]) || @getimagesize($file['tmp_name']) === false) {
            throw new \RuntimeException('Only JPG, PNG or WebP images are allowed.');
        }
        $folder = preg_replace('/[^a-z0-9_-]/', '', strtolower($folder)) ?: 'misc';
        $dir = UPLOAD_PATH . '/' . $folder;
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new \RuntimeException('Upload directory is not writable.');
        }
        $name = bin2hex(random_bytes(16)) . '.' . self::TYPES[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
            throw new \RuntimeException('Could not save the uploaded file.');
        }
        @chmod($dir . '/' . $name, 0644);
        return 'uploads/' . $folder . '/' . $name;
    }
}
