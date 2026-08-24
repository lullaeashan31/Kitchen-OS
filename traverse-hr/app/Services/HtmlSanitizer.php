<?php

namespace App\Services;

/**
 * Document bodies are authored by trusted admins, but they render on an
 * unauthenticated public page (the staff locker) and inside generated PDFs.
 * Stripping scripts and event handlers means a compromised or careless
 * admin account cannot turn a policy document into a payload aimed at staff.
 */
class HtmlSanitizer
{
    public static function clean(?string $html): string
    {
        if (! $html) {
            return '';
        }

        // Whole elements that must never survive.
        $html = preg_replace('#<\s*(script|iframe|object|embed|form|style|link|meta)\b.*?<\s*/\s*\1\s*>#is', '', $html);
        $html = preg_replace('#<\s*(script|iframe|object|embed|form|style|link|meta)\b[^>]*/?>#is', '', $html);

        // Inline handlers (onclick=, onerror=…) and javascript:/data: URLs.
        $html = preg_replace('#\son[a-z-]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#is', '', $html);
        $html = preg_replace('#(href|src|xlink:href)\s*=\s*("|\')\s*(javascript|vbscript|data)\s*:[^"\']*\2#is', '$1="#"', $html);

        return $html;
    }
}
