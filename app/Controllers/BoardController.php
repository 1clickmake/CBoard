<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Csrf;
use App\Services\FileService;
use PDO;

class BoardController extends BaseController {
    
    public function index($vars) {
        $slug = $vars['slug'];
        $db = Database::getInstance();
        
        // Get board info
        $stmt = $db->prepare("SELECT * FROM boards WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        $board = $stmt->fetch();
        
        if (!$board) {
            $this->notFound('Board not found');
        }

        // Permission Check: List
        if (!check_level($board['level_list'] ?? 1)) {
            alert('You do not have permission to view this board list.');
        }
        
        // Search functionality
        $search = $_GET['search'] ?? '';
        $searchWhere = '';
        $params = ['board_id' => $board['id']];
        
        if ($search) {
            $searchWhere = " AND (p.title LIKE :search OR p.content LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        // Pagination logic
        $limit = $board['page_rows'] ?? 20;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        // Get total count (only parent posts, reply_depth = 0)
        $stmt = $db->prepare("SELECT COUNT(*) FROM posts p WHERE p.board_id = :board_id AND p.reply_depth = 0" . $searchWhere);
        $stmt->execute($params);
        $totalItems = $stmt->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        // Get posts with limit and offset (only parent posts)
        $sql = "SELECT p.*, u.username, 
                (SELECT COUNT(*) FROM posts WHERE parent_id = p.id) as reply_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count,
                (SELECT COUNT(*) FROM post_files WHERE post_id = p.id) as file_count,
                (SELECT filepath FROM post_files WHERE post_id = p.id AND file_type LIKE 'image/%' LIMIT 1) as first_file
                FROM posts p 
                LEFT JOIN users u ON p.user_id = u.user_id 
                WHERE p.board_id = :board_id AND p.reply_depth = 0" . $searchWhere . "
                ORDER BY p.created_at DESC 
                LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll();
        
        // Load skin-specific view
        $skin = $board['skin'] ?? 'basic';
        $viewPath = "board/skins/{$skin}/list";
        
        $this->view($viewPath, [
            'board' => $board, 
            'posts' => $posts, 
            'page' => $page, 
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'limit' => $limit,
            'pageButtons' => $board['page_buttons'] ?? 5,
            'search' => $search
        ]);
    }

    public function show($vars) {
        $id = $vars['id'];
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT p.*, u.username, b.title as board_title, b.slug as board_slug, 
                              b.skin, b.max_replies, b.allow_comments, b.level_view, b.level_comment, b.point_view
                              FROM posts p 
                              LEFT JOIN users u ON p.user_id = u.user_id 
                              JOIN boards b ON p.board_id = b.id 
                              WHERE p.id = :id");
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();
        
        if (!$post) {
            $this->notFound('Post not found');
        }

        // Permission Check: View
        if (!check_level($post['level_view'] ?? 1)) {
            alert('You do not have permission to view this post.');
        }

        $is_member = \App\Services\AuthService::isMember();
        $user = \App\Services\AuthService::user();
        $is_admin = \App\Services\AuthService::isAdmin();

        // Secret post verification
        if (!empty($post['is_secret'])) {
            $canViewSecret = $is_admin
                || ($is_member && !empty($user['user_id']) && $user['user_id'] === $post['user_id'])
                || !empty($_SESSION['unlocked_posts'][(int)$id]);

            if (!$canViewSecret && $_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
                    $this->deny('Invalid security token. Please try again.');
                }
                $inputPw = $_POST['secret_password'] ?? '';
                if (!empty($post['secret_password']) && password_verify($inputPw, $post['secret_password'])) {
                    $_SESSION['unlocked_posts'][(int)$id] = true;
                    $canViewSecret = true;
                } else {
                    $secretError = '비밀번호가 일치하지 않습니다.';
                }
            }

            if (!$canViewSecret) {
                $skin = $post['skin'] ?? 'basic';
                $secretView = "board/skins/{$skin}/secret";
                if (!file_exists(__DIR__ . "/../../views/{$secretView}.php")) {
                    $secretView = "board/skins/sebyeol_contact/secret";
                }
                $this->view($secretView, [
                    'post' => $post,
                    'secretError' => $secretError ?? null
                ]);
                return;
            }
        }

        // Apply View Points (only if logged in and not the author/admin)
        if ($is_member && !$is_admin && $user['user_id'] !== $post['user_id']) {
            $viewPoint = (int)($post['point_view'] ?? 0);
            if ($viewPoint != 0) {
                // To prevent point draining on refresh, only deduct once per session per post
                $viewKey = 'viewed_post_' . $id;
                if (!isset($_SESSION[$viewKey])) {
                    // Check if enough points for deduction
                    if ($viewPoint < 0 && $user['point'] < abs($viewPoint)) {
                        alert('Not enough points to view this post.');
                    }
                    
                    add_point($user['user_id'], $viewPoint, 'Post View: ' . $post['title']);
                    $_SESSION['user']['point'] += $viewPoint; // Update session

                    $_SESSION[$viewKey] = true;
                }
            }
        }
        
        // Increase view count
        $stmt = $db->prepare("UPDATE posts SET views = views + 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $post['views']++; // Update local copy
        
        // Get files
        $stmt = $db->prepare("SELECT * FROM post_files WHERE post_id = :post_id");
        $stmt->execute(['post_id' => $id]);
        $files = $stmt->fetchAll();
        
        // Get replies (답글)
        $stmt = $db->prepare("SELECT r.*, u.username FROM posts r JOIN users u ON r.user_id = u.user_id WHERE r.parent_id = :parent_id ORDER BY r.created_at ASC");
        $stmt->execute(['parent_id' => $id]);
        $replies = $stmt->fetchAll();
        
        // Get comments (댓글) with pagination (10 per page)
        $commentPage = isset($_GET['comment_page']) ? (int)$_GET['comment_page'] : 1;
        if ($commentPage < 1) $commentPage = 1;
        $commentLimit = 10;
        $commentOffset = ($commentPage - 1) * $commentLimit;
        
        // Total comments
        $stmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE post_id = :post_id AND reply_depth = 0");
        $stmt->execute(['post_id' => $id]);
        $totalComments = $stmt->fetchColumn();
        $totalCommentPages = ceil($totalComments / $commentLimit);
        
        // Get parent comments
        $stmt = $db->prepare("SELECT c.*, u.username FROM comments c LEFT JOIN users u ON c.user_id = u.user_id WHERE c.post_id = :post_id AND c.reply_depth = 0 ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':post_id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $commentLimit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $commentOffset, PDO::PARAM_INT);
        $stmt->execute();
        $comments = $stmt->fetchAll();
        
        // For each comment, get sub-comments (대댓글)
        foreach ($comments as &$comment) {
            $stmt = $db->prepare("SELECT c.*, u.username FROM comments c LEFT JOIN users u ON c.user_id = u.user_id WHERE c.parent_comment_id = :parent_id ORDER BY c.created_at ASC");
            $stmt->execute(['parent_id' => $comment['id']]);
            $comment['sub_comments'] = $stmt->fetchAll();
        }
        
        // Load skin-specific view
        $skin = $post['skin'] ?? 'basic';
        $viewPath = "board/skins/{$skin}/view";
        
        // Pass Database instance for comments/replies recursively if view needs it (though bad practice, keeping minimal changes)
        // Better: Fetch everything needed here.
        
        $this->view($viewPath, [
            'post' => $post, 
            'files' => $files,
            'replies' => $replies,
            'comments' => $comments,
            'commentPage' => $commentPage,
            'totalCommentPages' => $totalCommentPages
        ]);
    }

    public function write($vars) {
        $is_member = \App\Services\AuthService::isMember();
        $slug = $vars['slug'];
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT * FROM boards WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        $board = $stmt->fetch();

        if (!$board) {
            $this->notFound('Board not found.');
            return;
        }

        // Permission Check: Write (allow guest when level_write <= 1)
        if (!$is_member && (int)($board['level_write'] ?? 1) > 1) {
            $this->redirect('/login');
        }
        if (!check_level($board['level_write'] ?? 1)) {
            alert('You do not have permission to write here.');
        }

        $this->ensurePostExtraColumns($db);
        $skin = $board['skin'] ?? 'basic';
        $viewPath = "board/skins/{$skin}/write";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::verify($_POST['csrf_token'] ?? '')) $this->deny('Invalid security token. Please try again.');

            $title = mb_substr(trim($_POST['title'] ?? ''), 0, 255);
            $content = \App\Core\HtmlSanitizer::sanitize($_POST['content'] ?? '');
            $editor_mode = $_POST['editor_mode'] ?? 'visual';
            $authorName = mb_substr(trim($_POST['author_name'] ?? ''), 0, 80);
            $isSecret = !empty($_POST['is_secret']) ? 1 : 0;
            $email = mb_substr(trim($_POST['email'] ?? ''), 0, 150);
            $phone = mb_substr(trim($_POST['phone'] ?? ''), 0, 50);
            $pw = $_POST['password'] ?? '';
            $pwConfirm = $_POST['password_confirm'] ?? '';

            $error = null;
            if ($skin === 'sebyeol_contact') {
                if (isset($_POST['captcha_answer']) && !\App\Core\Captcha::verify($_POST['captcha_answer'])) {
                    $error = '스팸방지 코드(Security Code)가 올바르지 않습니다.';
                } elseif (($pw !== '' || $pwConfirm !== '' || $isSecret) && $pw !== $pwConfirm) {
                    $error = '비밀번호와 비밀번호 확인이 일치하지 않습니다.';
                } elseif (!$is_member && $pw === '') {
                    $error = '비밀번호를 입력해 주세요.';
                }
            }

            if ($error !== null) {
                $this->view($viewPath, [
                    'board' => $board,
                    'error' => $error,
                    'captcha_q' => \App\Core\Captcha::generate(),
                    'old' => [
                        'author_name' => $authorName,
                        'is_secret' => $isSecret,
                        'email' => $email,
                        'phone' => $phone,
                        'title' => $title,
                        'content' => $_POST['content'] ?? ''
                    ]
                ]);
                return;
            }

            $secretPassword = $pw !== '' ? password_hash($pw, PASSWORD_DEFAULT) : '';
            $user = \App\Services\AuthService::user();
            $authorId = ($is_member && !empty($user['user_id'])) ? $user['user_id'] : 'guest';
            $stmt = $db->prepare("INSERT INTO posts (group_id, board_id, user_id, author_name, title, content, editor_mode, is_secret, secret_password, email, phone) VALUES (:group_id, :board_id, :user_id, :author_name, :title, :content, :editor_mode, :is_secret, :secret_password, :email, :phone)");
            $stmt->execute([
                'group_id' => $board['group_id'],
                'board_id' => $board['id'],
                'user_id' => $authorId,
                'author_name' => $authorName,
                'title' => $title,
                'content' => $content,
                'editor_mode' => $editor_mode,
                'is_secret' => $isSecret,
                'secret_password' => $secretPassword,
                'email' => $email,
                'phone' => $phone
            ]);
            $postId = $db->lastInsertId();
            
            // Backup logic for hosting environments where lastInsertId might fail
            if ($postId <= 0) {
                 $stmt = $db->prepare("SELECT id FROM posts WHERE user_id = :user_id ORDER BY id DESC LIMIT 1");
                 $stmt->execute(['user_id' => $authorId]);
                 $postId = (int)$stmt->fetchColumn();
            }

            if ($isSecret && $postId > 0) {
                $_SESSION['unlocked_posts'][(int)$postId] = true;
            }

            // Handle file uploads
            if (isset($_FILES['attachments'])) {
                $fileService = new FileService();
                $fileService->handleUploads($postId, $slug, $_FILES['attachments']);
            }
            
            // Add points for posting (members only)
            $writePoint = (int)($board['point_write'] ?? 0);
            if ($is_member && $writePoint != 0 && !empty($user['user_id'])) {
                add_point($user['user_id'], $writePoint, 'Post Write: ' . $title);
                $_SESSION['user']['point'] = ($_SESSION['user']['point'] ?? 0) + $writePoint;
            }
            
            $this->redirect('/board/view/' . $postId);
            return;
        }

        $this->view($viewPath, [
            'board' => $board,
            'captcha_q' => \App\Core\Captcha::generate()
        ]);
    }

    private function ensurePostExtraColumns($db) {
        static $checked = false;
        if ($checked) return;
        $checked = true;
        try {
            $col = $db->query("SHOW COLUMNS FROM posts LIKE 'is_secret'")->fetch();
            if (!$col) {
                $db->exec("ALTER TABLE posts
                    ADD COLUMN author_name VARCHAR(80) NOT NULL DEFAULT '' AFTER user_id,
                    ADD COLUMN is_secret TINYINT(1) NOT NULL DEFAULT 0 AFTER editor_mode,
                    ADD COLUMN secret_password VARCHAR(255) NOT NULL DEFAULT '' AFTER is_secret,
                    ADD COLUMN email VARCHAR(150) NOT NULL DEFAULT '' AFTER secret_password,
                    ADD COLUMN phone VARCHAR(50) NOT NULL DEFAULT '' AFTER email");
            }
        } catch (\Throwable $e) {
            // Ignore if already exists or lacking privilege
        }
    }

    public function edit($vars) {
        $id = $vars['id'];
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT p.*, b.slug as board_slug, b.skin, b.title as board_title 
                              FROM posts p 
                              JOIN boards b ON p.board_id = b.id 
                              WHERE p.id = :id");
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();

        if (!$post) {
            $this->notFound('Post not found');
        }

        $isAdmin = isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin';
        $isMemberAuthor = isset($_SESSION['user']) && ($_SESSION['user']['user_id'] ?? '') === ($post['user_id'] ?? '');
        $isGuestPost = ($post['user_id'] ?? '') === 'guest';
        $isUnlocked = !empty($_SESSION['unlocked_posts'][(int)$id]) || !empty($_SESSION['edit_unlocked_posts'][(int)$id]);

        if (!$isAdmin && !$isMemberAuthor) {
            if (!$isGuestPost || empty($post['secret_password'])) {
                $this->deny();
            }
            if (!$isUnlocked) {
                $secretError = null;
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['secret_password'])) {
                    if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
                        $this->deny('Invalid security token. Please try again.');
                    }
                    if (password_verify($_POST['secret_password'], $post['secret_password'])) {
                        $_SESSION['edit_unlocked_posts'][(int)$id] = true;
                        $this->redirect('/board/edit/' . (int)$id);
                        return;
                    }
                    $secretError = '비밀번호가 일치하지 않습니다.';
                }
                $skin = $post['skin'] ?? 'basic';
                $secretView = "board/skins/{$skin}/secret";
                if (!file_exists(__DIR__ . "/../../views/{$secretView}.php")) {
                    $secretView = "board/skins/sebyeol_contact/secret";
                }
                $this->view($secretView, [
                    'post' => $post,
                    'actionUrl' => '/board/edit/' . (int)$id,
                    'secretTitle' => '게시글 수정 비밀번호 확인',
                    'secretError' => $secretError
                ]);
                return;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::verify($_POST['csrf_token'] ?? '')) $this->deny('Invalid security token. Please try again.');

            $title = mb_substr(trim($_POST['title'] ?? ''), 0, 255);
            $content = \App\Core\HtmlSanitizer::sanitize($_POST['content'] ?? '');
            $editor_mode = $_POST['editor_mode'] ?? 'visual';
            $authorName = isset($_POST['author_name']) ? mb_substr(trim($_POST['author_name']), 0, 80) : ($post['author_name'] ?? '');
            $isSecret = isset($_POST['author_name']) ? (!empty($_POST['is_secret']) ? 1 : 0) : (int)($post['is_secret'] ?? 0);
            $email = isset($_POST['email']) ? mb_substr(trim($_POST['email']), 0, 150) : ($post['email'] ?? '');
            $phone = isset($_POST['phone']) ? mb_substr(trim($_POST['phone']), 0, 50) : ($post['phone'] ?? '');
            $pw = $_POST['password'] ?? '';
            $pwConfirm = $_POST['password_confirm'] ?? '';

            if (($pw !== '' || $pwConfirm !== '') && $pw !== $pwConfirm) {
                $stmtFiles = $db->prepare("SELECT * FROM post_files WHERE post_id = :id");
                $stmtFiles->execute(['id' => $id]);
                $stmtBoard = $db->prepare("SELECT * FROM boards WHERE id = :id");
                $stmtBoard->execute(['id' => $post['board_id']]);
                $boardInfo = $stmtBoard->fetch();
                $skin = $boardInfo['skin'] ?? 'basic';
                $this->view("board/skins/{$skin}/write", [
                    'post' => $post,
                    'files' => $stmtFiles->fetchAll(),
                    'board' => $boardInfo,
                    'error' => '비밀번호와 비밀번호 확인이 일치하지 않습니다.',
                    'old' => [
                        'author_name' => $authorName,
                        'is_secret' => $isSecret,
                        'email' => $email,
                        'phone' => $phone,
                        'title' => $title,
                        'content' => $_POST['content'] ?? ''
                    ]
                ]);
                return;
            }

            $secretPassword = $pw !== '' ? password_hash($pw, PASSWORD_DEFAULT) : ($post['secret_password'] ?? '');
            
            $stmt = $db->prepare("UPDATE posts SET title = :title, content = :content, editor_mode = :editor_mode, author_name = :author_name, is_secret = :is_secret, secret_password = :secret_password, email = :email, phone = :phone WHERE id = :id");
            $stmt->execute([
                'title' => $title,
                'content' => $content,
                'editor_mode' => $editor_mode,
                'author_name' => $authorName,
                'is_secret' => $isSecret,
                'secret_password' => $secretPassword,
                'email' => $email,
                'phone' => $phone,
                'id' => $id
            ]);

            // Handle file deletions
            if (isset($_POST['delete_files'])) {
                $fileService = new FileService();
                foreach ($_POST['delete_files'] as $fileId) {
                    $stmt = $db->prepare("SELECT * FROM post_files WHERE id = :id");
                    $stmt->execute(['id' => $fileId]);
                    $file = $stmt->fetch();
                    if ($file) {
                        $fileService->deleteFile($file['filepath']);
                        $stmt = $db->prepare("DELETE FROM post_files WHERE id = :id");
                        $stmt->execute(['id' => $fileId]);
                    }
                }
            }

            // Get board slug for file path
            $stmt = $db->prepare("SELECT slug FROM boards WHERE id = :id");
            $stmt->execute(['id' => $post['board_id']]);
            $board = $stmt->fetch();

            // Handle new file uploads
            if (isset($_FILES['attachments'])) {
                $fileService = new FileService();
                $fileService->handleUploads($id, $board['slug'], $_FILES['attachments']);
            }
            
            $this->redirect('/board/view/' . $id);
            return;
        }

        // Get existing files
        $stmt = $db->prepare("SELECT * FROM post_files WHERE post_id = :id");
        $stmt->execute(['id' => $id]);
        $files = $stmt->fetchAll();

        // Get board info for skin
        $stmt = $db->prepare("SELECT b.* FROM boards b JOIN posts p ON b.id = p.board_id WHERE p.id = :id");
        $stmt->execute(['id' => $id]);
        $board = $stmt->fetch();

        // Load skin-specific write view (edit uses same form as write)
        $skin = $board['skin'] ?? 'basic';
        $viewPath = "board/skins/{$skin}/write";
        
        $this->view($viewPath, ['post' => $post, 'files' => $files, 'board' => $board]);
    }

    private function removePostRecords($db, $id, $post) {
        // Delete physical files from post_files table
        $stmt = $db->prepare("SELECT * FROM post_files WHERE post_id = :id");
        $stmt->execute(['id' => $id]);
        $files = $stmt->fetchAll();
        $fileService = new FileService();
        foreach ($files as $file) {
             $fileService->deleteFile($file['filepath']);
        }

        // Delete download logs associated with these files
        $stmt = $db->prepare("DELETE FROM file_downloads WHERE file_id IN (SELECT id FROM post_files WHERE post_id = :id)");
        $stmt->execute(['id' => $id]);

        // Remove file records from DB
        $stmt = $db->prepare("DELETE FROM post_files WHERE post_id = :id");
        $stmt->execute(['id' => $id]);

        // Scan content for editor images and delete them
        preg_match_all('/<img[^>]+src="([^">]+)"/i', $post['content'], $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $src) {
                // Only delete if it's a local path in /data/
                if (strpos($src, '/data/') === 0 && strpos($src, '..') === false) {
                    $filePath = realpath(CM_PUBLIC_PATH . $src);
                    $base = realpath(CM_PUBLIC_PATH . '/data');
                    if ($filePath && $base && strpos($filePath, $base) === 0 && is_file($filePath)) unlink($filePath);
                }
            }
        }

        // Delete all comments associated with the post
        $stmt = $db->prepare("DELETE FROM comments WHERE post_id = :id");
        $stmt->execute(['id' => $id]);

        // Delete from DB
        $stmt = $db->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function delete($vars) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !Csrf::verify($_POST['csrf_token'] ?? '')) {
             $this->deny('Invalid security token. Please try again.');
        }
        $id = $vars['id'];
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT p.*, b.slug as board_slug
                              FROM posts p
                              JOIN boards b ON p.board_id = b.id
                              WHERE p.id = :id");
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();

        if (!$post || (!isset($_SESSION['user']) || ($_SESSION['user']['user_id'] != $post['user_id'] && $_SESSION['user']['role'] != 'admin'))) {
            $this->deny();
        }

        try {
            $db->beginTransaction();
            $this->removePostRecords($db, $id, $post);
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('Post delete failed: ' . $e->getMessage());
            $this->fail('Delete failed. Please try again.');
        }

        $this->redirect('/board/' . $post['board_slug']);
    }

    public function bulkDelete() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            $this->deny();
        }

        $postIds = $_POST['post_ids'] ?? [];
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) $this->deny('Invalid security token. Please try again.');

        $slug = $_POST['slug'] ?? '';
        
        if (empty($postIds)) {
            $this->redirect('/board/' . $slug);
            return;
        }

        $db = Database::getInstance();

        try {
            $db->beginTransaction();
            foreach ($postIds as $id) {
                // 1. Get post for file deletion
                $stmt = $db->prepare("SELECT * FROM posts WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $post = $stmt->fetch();

                if ($post) {
                    $this->removePostRecords($db, $id, $post);
                }
            }
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('Bulk delete failed: ' . $e->getMessage());
            $this->fail('Bulk delete failed. Please try again.');
        }

        $this->redirect('/board/' . $slug);
    }

    // --- Reply (답글) Methods ---
    public function writeReply($vars) {
        if (!isset($_SESSION['user'])) {
            $this->redirect('/login');
        }

        $parentId = $vars['id'];
        $db = Database::getInstance();

        // Get parent post
        $stmt = $db->prepare("SELECT p.*, b.max_replies FROM posts p JOIN boards b ON p.board_id = b.id WHERE p.id = :id");
        $stmt->execute(['id' => $parentId]);
        $parent = $stmt->fetch();

        if (!$parent || $parent['reply_depth'] > 0) {
            $this->deny('Cannot reply to a reply.');
        }

        // Check reply limit
        $stmt = $db->prepare("SELECT COUNT(*) FROM posts WHERE parent_id = :parent_id");
        $stmt->execute(['parent_id' => $parentId]);
        $replyCount = $stmt->fetchColumn();

        if ($replyCount >= $parent['max_replies']) {
            $this->deny('Reply limit reached.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::verify($_POST['csrf_token'] ?? '')) $this->deny('Invalid security token. Please try again.');
            
            $content = \App\Core\HtmlSanitizer::sanitize($_POST['content'] ?? '');
            $editor_mode = $_POST['editor_mode'] ?? 'visual';

            $stmt = $db->prepare("INSERT INTO posts (group_id, board_id, user_id, parent_id, title, content, reply_depth, editor_mode) VALUES (:group_id, :board_id, :user_id, :parent_id, :title, :content, 1, :editor_mode)");
            $stmt->execute([
                'group_id' => $parent['group_id'],
                'board_id' => $parent['board_id'],
                'user_id' => $_SESSION['user']['user_id'],
                'parent_id' => $parentId,
                'title' => 'RE: ' . $parent['title'],
                'content' => $content,
                'editor_mode' => $editor_mode
            ]);

            $this->redirect('/board/view/' . $parentId);
        }
    }

    public function deleteReply($vars) {
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) $this->deny('Invalid security token. Please try again.');
        
        $id = $vars['id'];
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $reply = $stmt->fetch();

        if (!$reply || $reply['reply_depth'] == 0 || (!isset($_SESSION['user']) || ($_SESSION['user']['user_id'] != $reply['user_id'] && $_SESSION['user']['role'] != 'admin'))) {
            $this->deny();
        }

        $parentId = $reply['parent_id'];
        $stmt = $db->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $this->redirect('/board/view/' . $parentId);
    }

    // --- Comment (댓글) Methods ---
    public function addComment() {
        $postId = $_POST['post_id'] ?? null;
        $content = trim(strip_tags($_POST['content'] ?? ''));
        $content = mb_substr($content, 0, 2000);
        $parentCommentId = $_POST['parent_comment_id'] ?? null;

        if (!$postId || !$content) {
            return $this->json(['error' => 'Invalid data']);
        }
        
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
             return $this->json(['error' => 'CSRF validation failed']);
        }

        $db = Database::getInstance();

        // Check board allows comments and get settings
        $stmt = $db->prepare("SELECT b.allow_comments, b.max_replies, b.level_comment, b.point_comment FROM posts p JOIN boards b ON p.board_id = b.id WHERE p.id = :id");
        $stmt->execute(['id' => $postId]);
        $board = $stmt->fetch();

        if (!$board || !$board['allow_comments']) {
            return $this->json(['error' => 'Comments disabled']);
        }

        // Permission Check: Comment
        if (!check_level($board['level_comment'] ?? 1)) {
            return $this->json(['error' => 'You do not have permission to write comments.']);
        }

        $replyDepth = 0;

        // If replying to a comment, check depth limit
        if ($parentCommentId) {
            $stmt = $db->prepare("SELECT reply_depth FROM comments WHERE id = :id");
            $stmt->execute(['id' => $parentCommentId]);
            $parentComment = $stmt->fetch();

            if ($parentComment && $parentComment['reply_depth'] > 0) {
                return $this->json(['error' => 'Cannot reply to sub-comment']);
            }

            // Check sub-comment limit (max 3)
            $stmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE parent_comment_id = :parent_id");
            $stmt->execute(['parent_id' => $parentCommentId]);
            $subCount = $stmt->fetchColumn();

            if ($subCount >= ($board['max_replies'] ?? 3)) {
                return $this->json(['error' => 'Sub-comment limit reached']);
            }

            $replyDepth = 1;
        }

        $commentUserId = $_SESSION['user']['user_id'] ?? 'guest';
        $stmt = $db->prepare("INSERT INTO comments (post_id, user_id, parent_comment_id, content, reply_depth) VALUES (:post_id, :user_id, :parent_comment_id, :content, :reply_depth)");
        $stmt->execute([
            'post_id' => $postId,
            'user_id' => $commentUserId,
            'parent_comment_id' => $parentCommentId,
            'content' => $content,
            'reply_depth' => $replyDepth
        ]);

        // Add points for commenting (members only)
        $commentPoint = (int)($board['point_comment'] ?? 0);
        if (!empty($_SESSION['user']['user_id']) && $commentPoint != 0) {
            add_point($_SESSION['user']['user_id'], $commentPoint, 'Comment Write');
            $_SESSION['user']['point'] = ($_SESSION['user']['point'] ?? 0) + $commentPoint; // Sync session
        }

        return $this->json(['success' => true]);
    }

    public function deleteComment() {
        if (!isset($_SESSION['user'])) {
            return $this->json(['error' => 'Unauthorized']);
        }

        $id = $_POST['id'] ?? null;
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
             return $this->json(['error' => 'CSRF validation failed']);
        }
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM comments WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $comment = $stmt->fetch();

        if (!$comment || ($_SESSION['user']['user_id'] != $comment['user_id'] && $_SESSION['user']['role'] != 'admin')) {
            return $this->json(['error' => 'Unauthorized']);
        }

        // If parent comment (depth 0), delete its sub-comments first
        if ($comment['reply_depth'] == 0) {
            $stmt = $db->prepare("DELETE FROM comments WHERE parent_comment_id = :id");
            $stmt->execute(['id' => $id]);
        }

        $stmt = $db->prepare("DELETE FROM comments WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return $this->json(['success' => true]);
    }

    public function uploadEditorImage($vars) {
        if (!isset($_SESSION['user'])) {
            return $this->json(['error' => 'Unauthorized']);
        }
        if (!\App\Core\Csrf::verify($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) {
            return $this->json(['error' => 'CSRF validation failed']);
        }

        $slug = $vars['slug'];
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileService = new FileService();
            $result = $fileService->uploadEditorImage($_FILES['image'], $slug);
            return $this->json($result);
        }

        return $this->json(['error' => 'Upload failed']);
    }

    public function download($vars) {
        $fileId = $vars['id'];
        $db = Database::getInstance();

        // 1. Get file info
        $stmt = $db->prepare("SELECT * FROM post_files WHERE id = :id");
        $stmt->execute(['id' => $fileId]);
        $file = $stmt->fetch();

        if (!$file) {
            $this->notFound('File not found.');
        }

        $filePath = CM_PUBLIC_PATH . $file['filepath'];
        if (!file_exists($filePath)) {
            $this->fail('File is missing on the server.');
        }

        // 2. Logging logic
        $ip = $_SERVER['REMOTE_ADDR'];
        $userId = isset($_SESSION['user']['user_id']) ? $_SESSION['user']['user_id'] : null;

        // Check if log exists for this file + IP (+ user if logged in)
        $sql = "SELECT id FROM file_downloads WHERE file_id = :file_id AND ip_address = :ip";
        $params = ['file_id' => $fileId, 'ip' => $ip];
        
        if ($userId) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $userId;
        } else {
            $sql .= " AND user_id IS NULL";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $log = $stmt->fetch();

        if ($log) {
            // Update exist log (increment count)
            $stmt = $db->prepare("UPDATE file_downloads SET download_count = download_count + 1 WHERE id = :id");
            $stmt->execute(['id' => $log['id']]);
        } else {
            // Insert new log
            $stmt = $db->prepare("INSERT INTO file_downloads (file_id, user_id, ip_address) VALUES (:file_id, :user_id, :ip)");
            $stmt->execute([
                'file_id' => $fileId,
                'user_id' => $userId,
                'ip' => $ip
            ]);
        }

        // 3. Serve file
        $safeName = str_replace(["\r", "\n", '"'], '', $file['original_name']);
        $safeName = mb_substr($safeName, 0, 200);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $safeName . '"; filename*=UTF-8\'\'' . rawurlencode($safeName));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

}
