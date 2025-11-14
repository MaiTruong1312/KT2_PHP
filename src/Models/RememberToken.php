<?php
// src/Models/RememberToken.php
namespace Models;

use Core\Database;
use PDO;

class RememberToken {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Lưu token "Remember Me"
     */
    public function storeToken(int $userId, string $selector, string $hashedValidator, int $expiryDays = 30): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO remember_tokens (user_id, selector, validator_hash, expires_at) 
             VALUES (?, ?, ?, NOW() + INTERVAL ? DAY)"
        );
        return $stmt->execute([$userId, $selector, $hashedValidator, $expiryDays]);
    }

    /**
     * Tìm token hợp lệ bằng selector
     */
    public function findValidTokenBySelector(string $selector) {
        $stmt = $this->db->prepare(
            "SELECT * FROM remember_tokens 
             WHERE selector = ? AND expires_at > NOW() 
             LIMIT 1"
        );
        $stmt->execute([$selector]);
        return $stmt->fetch();
    }

    /**
     * Xóa token (khi logout)
     */
    public function deleteTokenBySelector(string $selector): bool {
        $stmt = $this->db->prepare("DELETE FROM remember_tokens WHERE selector = ?");
        return $stmt->execute([$selector]);
    }

    /**
     * Xóa tất cả token của user (khi đổi mật khẩu)
     */
    public function deleteAllTokensForUser(int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
}