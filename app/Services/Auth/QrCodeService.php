<?php

namespace App\Services\Auth;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeService
{
    /**
     * Generate an inline SVG string for the given text (e.g. otpauth:// URL).
     */
    public function renderSvg(string $text, int $size = 220, int $margin = 2): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, $margin),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);
        $svg = $writer->writeString($text);

        // Strip xml declaration if present so it embeds cleanly in HTML
        return trim(preg_replace('/<\?xml.*\?>/i', '', $svg));
    }
}
