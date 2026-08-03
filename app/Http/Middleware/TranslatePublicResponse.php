<?php

namespace App\Http\Middleware;

use App\Services\DeepLTranslationService;
use Closure;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TranslatePublicResponse
{
    public function __construct(private readonly DeepLTranslationService $translator) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if (app()->getLocale() !== 'en' || $request->is('admin/*') || $request->expectsJson() || !$this->isHtml($response)) return $response;

        $html = $response->getContent();
        if (!$html || !str_contains(strtolower($html), '<html')) return $response;

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new DOMXPath($dom);
        $nodes = [];
        $texts = [];

        foreach ($xpath->query('//text()[normalize-space(.) != ""]') as $node) {
            if (!$this->shouldTranslateNode($node)) continue;
            $value = trim($node->nodeValue);
            if (!$this->shouldTranslateText($value)) continue;
            preg_match('/^(\s*)/u', $node->nodeValue, $leading);
            preg_match('/(\s*)$/u', $node->nodeValue, $trailing);
            $nodes[] = ['type' => 'text', 'node' => $node, 'value' => $value, 'leading' => $leading[1] ?? '', 'trailing' => $trailing[1] ?? ''];
            $texts[] = $value;
        }

        foreach ($xpath->query('//*[@placeholder or @title or @aria-label or @alt]') as $element) {
            if ($this->isExcluded($element)) continue;
            foreach (['placeholder', 'title', 'aria-label', 'alt'] as $attribute) {
                if (!$element->hasAttribute($attribute)) continue;
                $value = trim($element->getAttribute($attribute));
                if (!$this->shouldTranslateText($value)) continue;
                $nodes[] = ['type' => 'attribute', 'node' => $element, 'attribute' => $attribute, 'value' => $value];
                $texts[] = $value;
            }
        }

        foreach ($xpath->query('//meta[@name="description" or @property="og:title" or @property="og:description"]') as $element) {
            $value = trim($element->getAttribute('content'));
            if (!$this->shouldTranslateText($value)) continue;
            $nodes[] = ['type' => 'attribute', 'node' => $element, 'attribute' => 'content', 'value' => $value];
            $texts[] = $value;
        }

        $translations = $this->translator->translateMany($texts);
        foreach ($nodes as $item) {
            $translated = $translations[$item['value']] ?? $item['value'];
            if ($item['type'] === 'text') {
                $item['node']->nodeValue = ($item['leading'] ?? '').$translated.($item['trailing'] ?? '');
            } else {
                $item['node']->setAttribute($item['attribute'], $translated);
            }
        }

        $dom->documentElement->setAttribute('lang', 'en');
        $translatedHtml = $dom->saveHTML();
        $translatedHtml = preg_replace('/^<\\?xml encoding="UTF-8"\\?>/i', '', $translatedHtml);
        $response->setContent($translatedHtml);
        $response->headers->set('Content-Length', (string) strlen($translatedHtml));

        return $response;
    }

    private function isHtml(Response $response): bool
    {
        return $response->isSuccessful() && str_contains(strtolower($response->headers->get('Content-Type', 'text/html')), 'text/html');
    }

    private function shouldTranslateNode(DOMNode $node): bool
    {
        $parent = $node->parentNode;
        if (!$parent instanceof DOMElement) return false;
        return !$this->isExcluded($parent);
    }

    private function isExcluded(DOMElement $element): bool
    {
        for ($current = $element; $current instanceof DOMElement; $current = $current->parentNode) {
            if (in_array(strtolower($current->tagName), ['script', 'style', 'svg', 'code', 'pre', 'noscript'], true)) return true;
            if ($current->hasAttribute('data-no-translate')) return true;
        }
        return false;
    }

    private function shouldTranslateText(string $text): bool
    {
        if (mb_strlen($text) < 2 || !preg_match('/\\p{L}/u', $text)) return false;
        if (filter_var($text, FILTER_VALIDATE_EMAIL) || filter_var($text, FILTER_VALIDATE_URL)) return false;
        if (preg_match('/^(?:\\+?\\d[\\d\\s()\\/.\\-]+|[A-Z0-9_.\\/-]{2,})$/u', $text)) return false;
        return true;
    }
}
