<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Stores product images safely:
 *  - decodes the upload with GD and re-encodes it as WebP, which drops EXIF/GPS
 *    metadata and any polyglot payload (e.g. PHP or HTML appended to a JPEG);
 *  - uses a random file name, so the user never controls the path or extension.
 */
class ProductImageStorage
{
    private const MAX_DIMENSION = 1200;

    public function store(UploadedFile $file): string
    {
        $contents = $file->get();
        $image = $contents !== false ? @imagecreatefromstring($contents) : false;

        if ($image === false) {
            throw new RuntimeException('Arquivo de imagem inválido.');
        }

        $image = $this->downscale($image);

        ob_start();
        imagewebp($image, null, 82);
        $webp = (string) ob_get_clean();
        imagedestroy($image);

        $path = 'products/'.Str::uuid().'.webp';
        Storage::disk('public')->put($path, $webp);

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function downscale(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $ratio = min(1, self::MAX_DIMENSION / max($width, $height));

        if ($ratio === 1) {
            return $image;
        }

        $resized = imagescale($image, (int) round($width * $ratio), (int) round($height * $ratio));
        imagedestroy($image);

        return $resized ?: throw new RuntimeException('Falha ao processar a imagem.');
    }
}
