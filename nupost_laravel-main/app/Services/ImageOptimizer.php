<?php

namespace App\Services;

class ImageOptimizer
{
    /**
     * Compress and resize an image.
     *
     * @param string $filePath Full path to the image file.
     * @param int $maxWidth Maximum width of the image.
     * @param int $maxHeight Maximum height of the image.
     * @param int $quality Compression quality (0-100).
     * @return bool True if optimized, false otherwise.
     */
    public static function optimize(string $filePath, int $maxWidth = 1200, int $maxHeight = 1200, int $quality = 75): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $info = @getimagesize($filePath);
        if (!$info) {
            return false;
        }

        $mime = $info['mime'];
        $width = $info[0];
        $height = $info[1];

        // Only optimize supported web image formats
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = @imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($filePath);
                break;
            case 'image/gif':
                $image = @imagecreatefromgif($filePath);
                break;
            case 'image/webp':
                $image = @imagecreatefromwebp($filePath);
                break;
            default:
                return false;
        }

        if (!$image) {
            return false;
        }

        // Resize calculations
        $newWidth = $width;
        $newHeight = $height;

        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = $width / $height;
            if ($ratio > 1) {
                $newWidth = $maxWidth;
                $newHeight = (int) round($maxWidth / $ratio);
            } else {
                $newHeight = $maxHeight;
                $newWidth = (int) round($maxHeight * $ratio);
            }

            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            if (!$newImage) {
                imagedestroy($image);
                return false;
            }

            // Handle transparency for PNG and GIF
            if ($mime === 'image/png' || $mime === 'image/gif') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $newImage;
        }

        // Save back to destination path
        $result = false;
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $result = @imagejpeg($image, $filePath, $quality);
                break;
            case 'image/png':
                // PNG quality scale is 0 (no compression) to 9 (max compression)
                $pngQuality = (int) round((100 - $quality) / 10);
                $pngQuality = max(0, min(9, $pngQuality));
                $result = @imagepng($image, $filePath, $pngQuality);
                break;
            case 'image/gif':
                $result = @imagegif($image, $filePath);
                break;
            case 'image/webp':
                $result = @imagewebp($image, $filePath, $quality);
                break;
        }

        imagedestroy($image);
        return $result;
    }
}
