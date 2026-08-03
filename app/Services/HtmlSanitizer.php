<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlSanitizer
{
    private const ALLOWED_TAGS = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'blockquote', 'h2', 'h3', 'h4', 'a'];

    public function sanitize(string $html): string
    {
        if (trim($html) === '') return '';

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><div id="security-root">'.$html.'</div>', LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('security-root');
        if (! $root) return '';

        foreach (iterator_to_array($root->childNodes) as $child) $this->cleanNode($child);

        $output = '';
        foreach ($root->childNodes as $child) $output .= $dom->saveHTML($child);

        return trim($output);
    }

    private function cleanNode(DOMNode $node): void
    {
        if (! $node instanceof DOMElement) return;

        $tag = strtolower($node->tagName);
        if (! in_array($tag, self::ALLOWED_TAGS, true)) {
            $this->unwrap($node);
            return;
        }

        foreach (iterator_to_array($node->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            if ($tag !== 'a' || ! in_array($name, ['href', 'title'], true)) $node->removeAttribute($attribute->name);
        }

        if ($tag === 'a') {
            $href = trim($node->getAttribute('href'));
            if (! preg_match('/^(?:https:\/\/|mailto:|#)/i', $href)) $node->removeAttribute('href');
            if ($node->hasAttribute('href')) {
                $node->setAttribute('rel', 'noopener noreferrer');
            }
        }

        foreach (iterator_to_array($node->childNodes) as $child) $this->cleanNode($child);
    }

    private function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if (! $parent) return;
        while ($element->firstChild) {
            $child = $element->firstChild;
            $parent->insertBefore($child, $element);
            $this->cleanNode($child);
        }
        $parent->removeChild($element);
    }
}
