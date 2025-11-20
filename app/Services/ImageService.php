<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Cek apakah environment mendukung konversi WebP dengan GD.
     */
    public static function supportsWebp(): bool
    {
        return extension_loaded('gd') && function_exists('imagewebp') && (imagetypes() & IMG_WEBP) === IMG_WEBP;
    }

    /**
     * Konversi file image di disk ke WebP.
     *
     * - Hanya memproses JPEG/PNG.
     * - Menghasilkan file .webp di direktori yang sama.
     * - Opsional mengganti path asli dengan yang .webp dan menghapus file asli.
     *
     * @param string $relativePath Path relatif pada disk
     * @param string $disk Nama disk penyimpanan (default 'public')
     * @param int    $quality Kualitas WebP (0-100)
     * @param bool   $replace Jika true, hapus file asli dan kembalikan path WebP
     * @return string Path relatif yang digunakan (WebP jika berhasil)
     */
    public static function convertToWebp(string $relativePath, string $disk = 'public', int $quality = 85, bool $replace = true): string
    {
        try {
            // Pastikan dukungan WebP tersedia
            if (!self::supportsWebp()) {
                Log::warning('ImageService: GD/WebP tidak tersedia, lewati konversi', [
                    'path' => $relativePath,
                ]);
                return $relativePath;
            }

            // Jika sudah .webp, kembalikan apa adanya
            if (strtolower(pathinfo($relativePath, PATHINFO_EXTENSION)) === 'webp') {
                return $relativePath;
            }

            // Dapatkan absolute path
            $absolutePath = Storage::disk($disk)->path($relativePath);

            // Validasi file ada
            if (!file_exists($absolutePath)) {
                Log::warning('ImageService: file tidak ditemukan untuk konversi', ['path' => $relativePath]);
                return $relativePath;
            }

            // Deteksi mime
            $mime = Storage::disk($disk)->mimeType($relativePath);
            if (!in_array($mime, ['image/jpeg', 'image/png'])) {
                Log::info('ImageService: format bukan JPEG/PNG, lewati konversi', ['path' => $relativePath, 'mime' => $mime]);
                return $relativePath;
            }

            // Buat resource image dari file
            if ($mime === 'image/jpeg') {
                $image = imagecreatefromjpeg($absolutePath);
            } else { // image/png
                $image = imagecreatefrompng($absolutePath);
                if (function_exists('imagepalettetotruecolor')) {
                    imagepalettetotruecolor($image);
                }
                if (function_exists('imagesavealpha')) {
                    imagesavealpha($image, true);
                }
            }

            if (!$image) {
                Log::error('ImageService: gagal membuat resource image', ['path' => $relativePath]);
                return $relativePath;
            }

            // Tentukan path webp baru
            $dir = dirname($relativePath);
            $filename = pathinfo($relativePath, PATHINFO_FILENAME);
            $webpRelative = trim($dir, '/').'/'.$filename.'.webp';
            $webpAbsolute = Storage::disk($disk)->path($webpRelative);

            // Pastikan direktori ada
            $webpDir = dirname($webpAbsolute);
            if (!is_dir($webpDir)) {
                @mkdir($webpDir, 0775, true);
            }

            // Tulis WebP
            $success = imagewebp($image, $webpAbsolute, max(0, min(100, $quality)));
            imagedestroy($image);

            if (!$success || !file_exists($webpAbsolute)) {
                Log::error('ImageService: gagal menulis file WebP', ['path' => $relativePath, 'target' => $webpRelative]);
                return $relativePath;
            }

            // Hapus file lama jika perlu
            if ($replace) {
                try {
                    Storage::disk($disk)->delete($relativePath);
                } catch (\Throwable $e) {
                    Log::warning('ImageService: gagal menghapus file asli setelah konversi', [
                        'path' => $relativePath,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('ImageService: konversi ke WebP berhasil', [
                'source' => $relativePath,
                'target' => $webpRelative,
                'disk' => $disk,
                'quality' => $quality,
            ]);

            return $webpRelative;
        } catch (\Throwable $e) {
            Log::error('ImageService: exception saat konversi WebP', [
                'path' => $relativePath,
                'error' => $e->getMessage(),
            ]);
            return $relativePath;
        }
    }
}

