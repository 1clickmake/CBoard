<?php

namespace Plugins\asamiyaeditor\Controllers;

/**
 * Serves the plugin's static assets (JS/CSS) with proper
 * content types and client-side caching.
 */
class AssetController
{
    private static $map = [
        'js' => ['asamiya-editor.js', 'application/javascript'],
        'css' => ['asamiya-editor.css', 'text/css'],
        'theme' => ['asamiya-editor-theme.css', 'text/css'],
    ];

    public function serve($vars)
    {
        $file = $vars['file'] ?? '';
        if (!isset(self::$map[$file])) {
            http_response_code(404);
            echo 'Not found';
            return;
        }

        [$name, $type] = self::$map[$file];
        $path = __DIR__ . '/../assets/' . $name;
        if (!file_exists($path)) {
            http_response_code(404);
            echo 'Not found';
            return;
        }

        header('Content-Type: ' . $type . '; charset=utf-8');
        header('Cache-Control: public, max-age=604800');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
    }
}
