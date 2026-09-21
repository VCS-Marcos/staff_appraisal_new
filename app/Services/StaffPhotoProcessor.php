<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;

/**
 * Turns an uploaded JPEG/PNG into a small square JPEG. The upload is fully decoded and
 * re-encoded, so whatever the client sent (EXIF/GPS data, comments, appended scripts,
 * a file that merely *claims* to be an image) never reaches storage — only pixels do.
 */
class StaffPhotoProcessor
{
    public const SIZE = 300;

    private const MAX_PIXELS = 16_000_000; // ~ a 12 MP phone photo, guards decode memory
    private const MAX_BYTES = 60_000;      // keeps the stored image inside a BLOB column

    public function process(UploadedFile $file): string
    {
        if (! function_exists('imagecreatefromstring')) {
            throw new RuntimeException('Image processing (GD) is not available on this server.');
        }

        $path = $file->getRealPath();
        $info = @getimagesize($path);

        if ($info === false || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
            throw new RuntimeException('The file is not a valid JPEG or PNG image.');
        }

        [$width, $height] = $info;

        if ($width * $height > self::MAX_PIXELS) {
            throw new RuntimeException('The image is too large in pixel dimensions.');
        }

        $source = @imagecreatefromstring(file_get_contents($path));

        if ($source === false) {
            throw new RuntimeException('The image could not be read.');
        }

        if ($info[2] === IMAGETYPE_JPEG) {
            $source = $this->applyExifOrientation($source, $path);
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $side = min($width, $height);

        $canvas = imagecreatetruecolor(self::SIZE, self::SIZE);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255)); // flatten PNG transparency
        imagecopyresampled(
            $canvas, $source, 0, 0,
            intdiv($width - $side, 2), intdiv($height - $side, 2),
            self::SIZE, self::SIZE, $side, $side,
        );

        for ($quality = 82; $quality >= 50; $quality -= 8) {
            ob_start();
            imagejpeg($canvas, null, $quality);
            $jpeg = ob_get_clean();

            if (strlen($jpeg) <= self::MAX_BYTES) {
                return $jpeg;
            }
        }

        throw new RuntimeException('The image could not be compressed small enough.');
    }

    private function applyExifOrientation(\GdImage $image, string $path): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = is_array($exif) ? ($exif['Orientation'] ?? 1) : 1;

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        return $rotated ?: $image;
    }
}
