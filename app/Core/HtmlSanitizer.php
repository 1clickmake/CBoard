<?php

namespace App\Core;

/**
 * Whitelist-based HTML sanitizer for rich-text content.
 * Used on SAVE for posts/comments/pages so stored XSS cannot persist.
 * Allows basic formatting + links + images, strips scripts/event handlers/
 * dangerous protocols and non-whitelisted tags (keeps their text).
 */
class HtmlSanitizer {
    private static $allowedTags = [
        'p' => [],
        'br' => [],
        'hr' => [],
        'b' => [],
        'strong' => [],
        'i' => [],
        'em' => [],
        'u' => [],
        's' => [],
        'strike' => [],
        'blockquote' => [],
        'pre' => [],
        'code' => [],
        'h1' => [],
        'h2' => [],
        'h3' => [],
        'h4' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'a' => ['href', 'title', 'target'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'table' => [],
        'thead' => [],
        'tbody' => [],
        'tr' => [],
        'th' => [],
        'td' => [],
        'span' => [],
        'div' => [],
    ];

    public static function sanitize($html) {
        if ($html === null || $html === '') return '';
        $html = (string)$html;
        // Strip null bytes / control chars
        $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $html);

        $doc = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        // Wrap in body so fragments parse correctly
        $doc->loadHTML('<?xml encoding="utf-8"?><body>' . $html . '</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $body = $doc->getElementsByTagName('body')->item(0);
        if (!$body) return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');

        self::walk($body);

        $out = '';
        foreach ($body->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }
        return $out;
    }

    private static function walk(\DOMNode $node) {
        // Iterate backwards since we may remove/replace nodes
        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $child = $node->childNodes->item($i);
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $tag = strtolower($child->nodeName);
                if (!isset(self::$allowedTags[$tag])) {
                    // Drop script/style/iframe/object/embed entirely (with contents)
                    if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'link', 'meta'], true)) {
                        $node->removeChild($child);
                    } else {
                        // Unwrap: keep children, drop the tag itself
                        while ($child->firstChild) {
                            $node->insertBefore($child->firstChild, $child);
                        }
                        $node->removeChild($child);
                    }
                    continue;
                }
                // Clean attributes
                $allowed = self::$allowedTags[$tag];
                $toRemove = [];
                foreach ($child->attributes as $attr) {
                    $name = strtolower($attr->nodeName);
                    if (!in_array($name, $allowed, true) || stripos($name, 'on') === 0) {
                        $toRemove[] = $attr->nodeName;
                        continue;
                    }
                    $val = $attr->nodeValue;
                    if (in_array($name, ['href', 'src'], true) && !self::safeUrl($val, $tag)) {
                        $toRemove[] = $attr->nodeName;
                    }
                }
                foreach ($toRemove as $a) {
                    $child->removeAttribute($a);
                }
                // Force safe link targets
                if ($tag === 'a') {
                    $child->setAttribute('rel', 'noopener noreferrer nofollow');
                }
                self::walk($child);
            } elseif ($child->nodeType === XML_COMMENT_NODE) {
                $node->removeChild($child);
            }
        }
    }

    private static function safeUrl($url, $tag) {
        $url = trim((string)$url);
        if ($url === '') return false;
        // Block dangerous protocols (allow protocol-relative cautiously: block)
        if (preg_match('/^\s*(javascript|data|vbscript|file|blob)\s*:/i', $url)) return false;
        if ($tag === 'img') {
            // Images: only http(s) or site-relative paths
            return (bool)preg_match('#^(https?://|/[^/])#i', $url);
        }
        // Links: http(s), mailto, tel, or site-relative
        return (bool)preg_match('#^(https?://|mailto:|tel:|/|#)#i', $url);
    }
}
