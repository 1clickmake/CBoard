<?php

namespace App\Core;

/**
 * Simple session-based math CAPTCHA (no external dependency).
 * Usage in controller (GET): $data['captcha_q'] = Captcha::generate();
 * Usage in view: <?= $captcha_q ?> + <input name="captcha_answer">
 * Usage in controller (POST): Captcha::verify($_POST['captcha_answer'] ?? '')
 */
class Captcha {
    public static function generate() {
        $a = random_int(2, 9);
        $b = random_int(2, 9);
        $_SESSION['captcha_answer'] = $a + $b;
        $_SESSION['captcha_time'] = time();
        return "What is $a + $b ?";
    }

    public static function verify($input) {
        if (!isset($_SESSION['captcha_answer'])) return false;
        // Expire after 10 minutes
        if (time() - ($_SESSION['captcha_time'] ?? 0) > 600) {
            unset($_SESSION['captcha_answer'], $_SESSION['captcha_time']);
            return false;
        }
        $ok = ((int)$input === (int)$_SESSION['captcha_answer']);
        // One-time use
        unset($_SESSION['captcha_answer'], $_SESSION['captcha_time']);
        return $ok;
    }
}
