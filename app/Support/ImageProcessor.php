<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use RuntimeException;

/**
 * Turns an uploaded photograph into the three sizes the site serves.
 *
 * Deliberately built on PHP's GD extension rather than Imagick or an image
 * library: GD is present on effectively every PHP host, including the cheapest
 * shared plans, whereas Imagick frequently is not. That keeps the club free to
 * host the site wherever it likes.
 *
 * Two details matter beyond resizing:
 *
 *  - Every image is re-encoded from decoded pixels rather than having its
 *    original bytes copied. Anything hidden inside the uploaded file — the
 *    classic trick of appending script to a valid image — does not survive the
 *    round trip.
 *
 *  - Metadata is therefore dropped, which also removes the GPS coordinates that
 *    phones embed in photographs. For a club that photographs juniors, quietly
 *    publishing the location a picture was taken would be a genuine problem.
 */
class ImageProcessor
{
    /** Longest edge of the retained "original", in pixels. */
    public const MAX_ORIGINAL = 2400;

    /** Width of the copy used inside post bodies. */
    public const DISPLAY_WIDTH = 1200;

    /** Dimensions of the cropped listing thumbnail. */
    public const THUMB_WIDTH = 600;
    public const THUMB_HEIGHT = 400;

    public const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    /**
     * Reads an upload into a GD image, honouring the orientation flag that
     * phones set instead of physically rotating the pixels. Without this, a
     * photograph taken in portrait appears on its side.
     */
    public function read(UploadedFile $file): \GdImage
    {
        $path = $file->getRealPath();
        $info = @getimagesize($path);

        if ($info === false) {
            throw new RuntimeException('That file does not appear to be an image.');
        }

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            default => null,
        };

        if (! $image instanceof \GdImage) {
            throw new RuntimeException('That image could not be read.');
        }

        return $this->applyExifOrientation($image, $path, $info[2]);
    }

    private function applyExifOrientation(\GdImage $image, string $path, int $type): \GdImage
    {
        if ($type !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = $exif['Orientation'] ?? 1;

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        if ($rotated instanceof \GdImage) {
            imagedestroy($image);

            return $rotated;
        }

        return $image;
    }

    /**
     * Scales an image down to fit within a bounding box, never up: enlarging a
     * small image only makes it blurry.
     */
    public function resizeToFit(\GdImage $source, int $maxWidth, int $maxHeight): \GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);

        $ratio = min($maxWidth / $width, $maxHeight / $height, 1);

        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));

        if ($targetWidth === $width && $targetHeight === $height) {
            return $this->copy($source);
        }

        $target = $this->blankCanvas($targetWidth, $targetHeight);

        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        return $target;
    }

    /**
     * Crops to an exact size from the centre, used for listing thumbnails so
     * that every card in the blog index is the same shape.
     */
    public function cropToFill(\GdImage $source, int $width, int $height): \GdImage
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        $scale = max($width / $sourceWidth, $height / $sourceHeight);

        $scaledWidth = (int) ceil($sourceWidth * $scale);
        $scaledHeight = (int) ceil($sourceHeight * $scale);

        $offsetX = (int) round(($scaledWidth - $width) / 2);
        $offsetY = (int) round(($scaledHeight - $height) / 2);

        $target = $this->blankCanvas($width, $height);

        imagecopyresampled(
            $target, $source,
            -$offsetX, -$offsetY,
            0, 0,
            $scaledWidth, $scaledHeight,
            $sourceWidth, $sourceHeight
        );

        return $target;
    }

    /**
     * Encodes to JPEG, or to PNG when the image has transparency worth keeping.
     */
    public function encode(\GdImage $image, bool $preserveTransparency, int $quality = 82): array
    {
        ob_start();

        if ($preserveTransparency) {
            imagesavealpha($image, true);
            imagepng($image, null, 6);
            $extension = 'png';
            $mime = 'image/png';
        } else {
            // JPEG cannot store transparency, so flatten onto white first,
            // otherwise transparent areas come out black.
            $flattened = $this->flattenOntoWhite($image);
            imagejpeg($flattened, null, $quality);
            imagedestroy($flattened);
            $extension = 'jpg';
            $mime = 'image/jpeg';
        }

        return [ob_get_clean(), $extension, $mime];
    }

    public function hasTransparency(\GdImage $image, string $mime): bool
    {
        if (! in_array($mime, ['image/png', 'image/webp', 'image/gif'], true)) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        // Sampling rather than checking every pixel: a full scan of a large
        // image is slow, and a grid is more than accurate enough to decide
        // between JPEG and PNG.
        $stepX = max(1, (int) ($width / 32));
        $stepY = max(1, (int) ($height / 32));

        for ($x = 0; $x < $width; $x += $stepX) {
            for ($y = 0; $y < $height; $y += $stepY) {
                if (((imagecolorat($image, $x, $y) >> 24) & 0x7F) > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    private function flattenOntoWhite(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $target = imagecreatetruecolor($width, $height);
        imagefill($target, 0, 0, imagecolorallocate($target, 255, 255, 255));
        imagecopy($target, $image, 0, 0, 0, 0, $width, $height);

        return $target;
    }

    private function blankCanvas(int $width, int $height): \GdImage
    {
        $canvas = imagecreatetruecolor($width, $height);

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagealphablending($canvas, true);

        return $canvas;
    }

    private function copy(\GdImage $source): \GdImage
    {
        $copy = $this->blankCanvas(imagesx($source), imagesy($source));
        imagecopy($copy, $source, 0, 0, 0, 0, imagesx($source), imagesy($source));

        return $copy;
    }
}
