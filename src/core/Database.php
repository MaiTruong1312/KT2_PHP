<?php
// src/Core/Database.php
namespace Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;
    
    private function __construct() {}
    private function __clone() {}

    /**
     * Lấy về một đối tượng PDO duy nhất (Singleton Pattern)
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';
            
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], $options);
            } catch (PDOException $e) {
                // Trong thực tế, bạn nên log lỗi này thay vì in ra
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
        return self::$instance;
    }
}