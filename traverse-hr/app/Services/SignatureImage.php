<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

/**
 * Normalises a signature image, whether it came off the signature pad
 * (a base64 PNG from the capture canvas) or was uploaded as a file.
 *
 * Trimming matters: a canvas is mostly empty space, and an untrimmed
 * capture places a postage-stamp-sized squiggle in the middle of a large
 * transparent box on the document. Trimming to the ink means the signature
 * sits on the signature line at a sensible size.
 */
class SignatureImage
{
    private const MAX_BYTES = 4 * 1024 * 1024;

    public static function fromRequest(?string $dataUrl, ?UploadedFile $file): ?string
    {
        $binary = null;

        if (is_string($dataUrl) && str_starts_with($dataUrl, 'data:image/')) {
            [$meta, $payload] = array_pad(explode(',', $dataUrl, 2), 2, '');
            if (str_contains($meta, 'base64') && $payload !== '') {
                $decoded = base64_decode($payload, true);
                if ($decoded !== false && strlen($decoded) <= self::MAX_BYTES) {
                    $binary = $decoded;
                }
            }
        }

        if ($binary === null && $file && $file->isValid() && $file->getSize() <= self::MAX_BYTES) {
            $binary = file_get_contents($file->getRealPath());
        }

        if ($binary === null || @getimagesizefromstring($binary) === false) {
            return null;
        }

        $trimmed = self::trim($binary);

        // An untouched canvas is not a signature. This must be reported as
        // "no signature" rather than silently passed through, otherwise a
        // document gets recorded as signed with a blank box on it.
        if ($trimmed === false) {
            return null;
        }

        // null means the image could not be processed at all (no GD, or a
        // format GD cannot read) — keep the original rather than lose it.
        return $trimmed ?? $binary;
    }

    /**
     * Crop away the empty margin around the ink, keeping transparency.
     *
     * @return string|false|null the cropped PNG; false when the canvas
     *                           holds no ink; null when it could not be read
     */
    private static function trim(string $binary): string|false|null
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $src = @imagecreatefromstring($binary);
        if ($src === false) {
            return null;
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $minX = $w;
        $minY = $h;
        $maxX = -1;
        $maxY = -1;

        // Sample rather than inspect every pixel — a pad capture can be large
        // and this runs inside a request on modest shared hosting.
        $step = max(1, (int) floor(min($w, $h) / 400));

        for ($y = 0; $y < $h; $y += $step) {
            for ($x = 0; $x < $w; $x += $step) {
                $c = imagecolorat($src, $x, $y);
                $alpha = ($c >> 24) & 0x7F;
                if ($alpha > 100) {
                    continue; // effectively transparent
                }
                $r = ($c >> 16) & 0xFF;
                $g = ($c >> 8) & 0xFF;
                $b = $c & 0xFF;
                if ($r > 245 && $g > 245 && $b > 245) {
                    continue; // effectively white paper
                }
                $minX = min($minX, $x);
                $maxX = max($maxX, $x);
                $minY = min($minY, $y);
                $maxY = max($maxY, $y);
            }
        }

        if ($maxX < 0 || $maxY < 0) {
            imagedestroy($src);

            return false; // nothing drawn
        }

        $pad = 8;
        $minX = max(0, $minX - $pad);
        $minY = max(0, $minY - $pad);
        $maxX = min($w - 1, $maxX + $pad);
        $maxY = min($h - 1, $maxY + $pad);

        $out = imagecrop($src, [
            'x' => $minX, 'y' => $minY,
            'width' => $maxX - $minX + 1, 'height' => $maxY - $minY + 1,
        ]);
        imagedestroy($src);

        if ($out === false) {
            return null;
        }

        imagesavealpha($out, true);
        ob_start();
        imagepng($out, null, 6);
        $png = ob_get_clean();
        imagedestroy($out);

        return $png ?: null;
    }

    /**
     * True when the capture contains actual ink, not an untouched canvas.
     * If the image cannot be read at all we do not claim it is blank.
     */
    public static function hasInk(?string $binary): bool
    {
        return $binary !== null && self::trim($binary) !== false;
    }
}
