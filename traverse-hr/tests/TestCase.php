<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * A signature-pad capture, as the browser posts it: a base64 PNG data
     * URL. Tests that exercise signing need real ink on the canvas, because
     * an all-transparent capture is rejected as an empty pad.
     */
    protected function signatureCapture(bool $withInk = true): string
    {
        $im = imagecreatetruecolor(400, 160);
        imagesavealpha($im, true);
        // Blending off, so the transparent fill replaces the default opaque
        // black rather than compositing over it — an untouched pad really
        // must come out empty.
        imagealphablending($im, false);
        imagefilledrectangle($im, 0, 0, 399, 159, imagecolorallocatealpha($im, 255, 255, 255, 127));
        imagealphablending($im, true);

        if ($withInk) {
            $ink = imagecolorallocate($im, 20, 25, 36);
            imagesetthickness($im, 4);
            // A short scrawl roughly where a person signs.
            imagearc($im, 120, 80, 90, 60, 0, 300, $ink);
            imageline($im, 150, 100, 260, 50, $ink);
            imageline($im, 260, 50, 300, 95, $ink);
        }

        ob_start();
        imagepng($im);
        $png = ob_get_clean();
        imagedestroy($im);

        return 'data:image/png;base64,'.base64_encode($png);
    }

    /** The stroke vectors the pad records alongside the image. */
    protected function signatureStrokes(int $count = 3): string
    {
        $strokes = [];
        for ($s = 0; $s < $count; $s++) {
            $strokes[] = ['points' => [
                ['x' => 10 + $s, 'y' => 20, 't' => 0, 'p' => null],
                ['x' => 40 + $s, 'y' => 55, 't' => 30, 'p' => null],
            ]];
        }

        // Same shape the signature pad posts (resources/views/partials/signature-pad).
        return json_encode(['strokes' => $strokes, 'capturedAt' => now()->toIso8601String()]);
    }

    /** The full payload an in-person signing session posts. */
    protected function signingPayload(string $name, bool $withInk = true): array
    {
        return [
            'signer_typed_name' => $name,
            'signature_capture' => $this->signatureCapture($withInk),
            'signature_strokes' => $this->signatureStrokes(),
        ];
    }
}
