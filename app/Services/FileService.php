<?php

namespace App\Services;

use App\Core\Database;

class FileService {
    private $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
    private $allowedEditorExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $maxFileSize = 10485760; // 10MB

    private function ensureUploadProtection($fullPath) {
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
        $ht = rtrim($fullPath, '/\\') . '/.htaccess';
        if (!file_exists($ht)) {
            @file_put_contents($ht, "Options -Indexes\n<FilesMatch \"\\.(php|phtml|phar|pl|py|jsp|asp|sh|cgi)\\$\">\n    Require all denied\n</FilesMatch>\nRemoveHandler .php .phtml\nRemoveType .php .phtml\n");
        }
    }

    private function safeSlug($slug) {
        $slug = (string)$slug;
        $slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $slug);
        return $slug !== '' ? $slug : 'default';
    }
    
    public function handleUploads($postId, $boardSlug, $files) {
        if (empty($files) || !isset($files['name'][0]) || empty($files['name'][0])) {
            return;
        }

        $db = Database::getInstance();
        $postId = (int)$postId;
        $basePublicPath = CM_PUBLIC_PATH;
        $boardSlug = $this->safeSlug($boardSlug);
        $uploadDir = '/data/' . $boardSlug . '/';
        $fullPath = rtrim($basePublicPath, '/\\') . $uploadDir;

        $this->ensureUploadProtection($fullPath);

        foreach ($files['name'] as $key => $name) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                if (($files['size'][$key] ?? 0) > $this->maxFileSize) continue;
                $tmpName = $files['tmp_name'][$key];
                $originalName = basename($name);
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                
                // Whitelist check
                if (!in_array($extension, $this->allowedExtensions, true)) {
                    continue;
                }
                // Verify MIME for images
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    $info = @getimagesize($tmpName);
                    if (!$info) continue;
                }

                $newName = uniqid() . '.' . $extension;
                $targetFile = rtrim($fullPath, '/\\') . '/' . $newName;

                if (move_uploaded_file($tmpName, $targetFile)) {
                    try {
                        $stmt = $db->prepare("INSERT INTO post_files (post_id, filename, original_name, filepath, file_size, file_type) 
                                              VALUES (:post_id, :filename, :original_name, :filepath, :file_size, :file_type)");
                        
                        $fileSize = (int)($files['size'][$key] ?? 0);
                        $cleanOriginalName = mb_substr($originalName, 0, 200, 'UTF-8');
                        $detected = 'application/octet-stream';
                        if (function_exists('finfo_open')) {
                            $f = finfo_open(FILEINFO_MIME_TYPE);
                            if ($f) { $d = finfo_file($f, $tmpName); if ($d) $detected = $d; finfo_close($f); }
                        }
                        $fileType = substr($detected, 0, 90);

                        $stmt->execute([
                            ':post_id' => $postId,
                            ':filename' => $newName,
                            ':original_name' => $cleanOriginalName,
                            ':filepath' => $uploadDir . $newName,
                            ':file_size' => $fileSize,
                            ':file_type' => $fileType
                        ]);

                        // Resize if image
                        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                            $this->resizeImage($targetFile, 1000);
                        }
                    } catch (\Throwable $e) {
                        error_log("File Upload Error: " . $e->getMessage());
                    }
                }
            }
        }
    }

    public function uploadEditorImage($file, $boardSlug) {
        $boardSlug = $this->safeSlug($boardSlug);
        $uploadDir = '/data/' . $boardSlug . '/';
        $fullPath = CM_PUBLIC_PATH . $uploadDir;

        $this->ensureUploadProtection($fullPath);

        if ($file['error'] === UPLOAD_ERR_OK) {
            if (($file['size'] ?? 0) > $this->maxFileSize) return ['error' => 'File too large'];
            $originalName = basename($file['name']);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            
            if (!in_array($extension, $this->allowedEditorExtensions, true)) {
                return ['error' => 'Invalid file type'];
            }
            if (!@getimagesize($file['tmp_name'])) {
                return ['error' => 'Invalid image'];
            }

            $newName = 'editor_' . uniqid() . '.' . $extension;
            $targetFile = $fullPath . $newName;

            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                $this->resizeImage($targetFile, 1000);
                return ['url' => $uploadDir . $newName];
            }
        }
        return ['error' => 'Upload failed'];
    }

    private function resizeImage($filePath, $maxWidth) {
        if (!function_exists('getimagesize') || !function_exists('imagecreatetruecolor')) return;
        
        $info = @getimagesize($filePath);
        if (!$info) return;

        list($width, $height, $type) = $info;
        
        if ($width <= $maxWidth) return;

        $newWidth = $maxWidth;
        $newHeight = floor($height * ($maxWidth / $width));

        $src = null;
        switch ($type) {
            case IMAGETYPE_JPEG: 
                if (function_exists('imagecreatefromjpeg')) $src = imagecreatefromjpeg($filePath); 
                break;
            case IMAGETYPE_PNG: 
                if (function_exists('imagecreatefrompng')) $src = imagecreatefrompng($filePath); 
                break;
            case IMAGETYPE_GIF: 
                if (function_exists('imagecreatefromgif')) $src = imagecreatefromgif($filePath); 
                break;
            case IMAGETYPE_WEBP: 
                if (function_exists('imagecreatefromwebp')) $src = imagecreatefromwebp($filePath); 
                break;
        }

        if (!$src) return;

        $tmp = imagecreatetruecolor($newWidth, $newHeight);
        if (!$tmp) {
            imagedestroy($src);
            return;
        }
        
        // Preserve transparency for PNG and GIF
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagealphablending($tmp, false);
            imagesavealpha($tmp, true);
        }

        imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        switch ($type) {
            case IMAGETYPE_JPEG: imagejpeg($tmp, $filePath, 90); break;
            case IMAGETYPE_PNG: imagepng($tmp, $filePath); break;
            case IMAGETYPE_GIF: imagegif($tmp, $filePath); break;
            case IMAGETYPE_WEBP: imagewebp($tmp, $filePath, 90); break;
        }

        imagedestroy($tmp);
        imagedestroy($src);
    }

    public function deleteFile($filepath) {
        $fullPath = CM_PUBLIC_PATH . $filepath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}
