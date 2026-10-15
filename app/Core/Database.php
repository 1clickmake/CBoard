<?php

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $host = $_ENV['DB_HOST'] ?? null;
        $db   = $_ENV['DB_NAME'] ?? null;
        $user = $_ENV['DB_USER'] ?? null;
        $pass = $_ENV['DB_PASS'] ?? null;

        if (!$host || !$db || !$user) {
            throw new PDOException("Database configuration is missing. Please check your .env file.");
        }

        try {
            $this->conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('DB connection failed: ' . $e->getMessage());
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (strpos($uri, '/install') === false) {
                die("Database connection failed. Please try again later.");
            }
            throw $e;
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance->conn ?? null;
    }
}
