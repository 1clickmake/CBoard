<?php

namespace App\Core;

/**
 * TOTP (RFC 6238) implementation without external dependencies.
 * Secrets are Base32 strings stored in users.two_factor_secret.
 */
class Totp {
    public static function generateSecret($len = 16) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $s = '';
        for ($i = 0; $i < $len; $i++) {
            $s .= $chars[random_int(0, 31)];
        }
        return $s;
    }

    private static function base32Decode($b32) {
        $b32 = strtoupper(rtrim($b32, '='));
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        foreach (str_split($b32) as $c) {
            $v = strpos($chars, $c);
            if ($v === false) return false;
            $bits .= str_pad(decbin($v), 5, '0', STR_PAD_LEFT);
        }
        $out = '';
        for ($i = 0; $i + 8 <= strlen($bits); $i += 8) {
            $out .= chr(bindec(substr($bits, $i, 8)));
        }
        return $out;
    }

    public static function code($secret, $time = null) {
        $key = self::base32Decode($secret);
        if ($key === false) return false;
        $t = floor(($time ?? time()) / 30);
        $msg = pack('N*', 0) . pack('N*', $t);
        $hash = hash_hmac('sha1', $msg, $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $code = ((ord($hash[$offset]) & 0x7F) << 24)
              | (ord($hash[$offset + 1]) << 16)
              | (ord($hash[$offset + 2]) << 8)
              | ord($hash[$offset + 3]);
        return str_pad((string)($code % 1000000), 6, '0', STR_PAD_LEFT);
    }

    public static function verify($secret, $input, $window = 1) {
        $input = preg_replace('/\D/', '', (string)$input);
        if (strlen($input) !== 6) return false;
        $now = time();
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals((string)self::code($secret, $now + $i * 30), $input)) return true;
        }
        return false;
    }

    public static function provisioningUri($secret, $account, $issuer = 'CMAKE') {
        return 'otpauth://totp/' . rawurlencode($issuer . ':' . $account)
            . '?secret=' . $secret . '&issuer=' . rawurlencode($issuer);
    }
}
