<?php

namespace App\Services;

use DOMDocument;
use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlSanitizer
{
    public function clean(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }
        if (! preg_match('/<\/?[a-z][^>]*>/i', $html)) {
            $html = '<p>'.nl2br(htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')).'</p>';
        }
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,strong,em,u,s,h2,h3,h4,ul,ol,li,blockquote,a[href|title],img[src|alt],hr');
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        $config->set('Cache.DefinitionImpl', null);
        $clean = (new HTMLPurifier($config))->purify($html);
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->loadHTML('<?xml encoding="UTF-8"><div id="sanitized-root">'.$clean.'</div>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        foreach (iterator_to_array($document->getElementsByTagName('img')) as $image) {
            if (! $this->allowedImage($image->getAttribute('src'))) {
                $image->parentNode->removeChild($image);
            }
        }
        foreach ($document->getElementsByTagName('a') as $link) {
            $link->setAttribute('rel', 'noopener noreferrer nofollow');
        }
        $result = '';
        foreach ($document->getElementById('sanitized-root')->childNodes as $node) {
            $result .= $document->saveHTML($node);
        }

        return $result;
    }

    private function allowedImage(string $url): bool
    {
        $parts = parse_url($url);
        if (! $parts || isset($parts['user']) || isset($parts['pass']) || isset($parts['query'])) {
            return false;
        }
        // Require an absolute trusted origin; private controller URLs are never rich-text assets.
        foreach ([rtrim(config('app.url'), '/').'/storage', config('filesystems.disks.r2_public.url')] as $base) {
            $trusted = $base ? parse_url($base) : null;
            if (! $trusted || ! isset($parts['host'], $parts['scheme'])
                || ! in_array($parts['scheme'], ['http', 'https'], true)
                || strtolower($parts['host']) !== strtolower($trusted['host'] ?? '')
                || $parts['scheme'] !== ($trusted['scheme'] ?? '')
                || ($parts['port'] ?? null) !== ($trusted['port'] ?? null)) {
                continue;
            }
            $path = rawurldecode($parts['path'] ?? '/');
            if (str_contains($path, '..') || str_contains($path, '\\') || preg_match('#/(media|anggota|pembina)(/|$)#i', $path)) {
                return false;
            }
            $prefix = rtrim($trusted['path'] ?? '', '/').'/';

            return str_starts_with($path, $prefix);
        }

        return false;
    }
}
