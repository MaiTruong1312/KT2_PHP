<?php
// src/Models/PasswordReset.php
namespace Models;

use Core\Database;
use PDO;

class PasswordReset {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Tạo token reset
     */
    public function createToken(int $userId, string $hashedToken, int $expiryMinutes = 60): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO password_resets (user_id, token_hash, expires_at) 
             VALUES (?, ?, NOW() + INTERVAL ? MINUTE)"
        );
        return $stmt->execute([$userId, $hashedToken, $expiryMinutes]);
    }

    /**
     * Tìm token hợp lệ (chưa dùng, chưa hết hạn)
     */
    public function findValidToken(string $hashedToken) {
        $stmt = $this->db->prepare(
            "SELECT * FROM password_resets 
             WHERE token_hash = ? AND used = 0 AND expires_at > NOW() 
             LIMIT 1"
        );
        $stmt->execute([$hashedToken]);
        return $stmt->fetch();
    }

    /**
     * Đánh dấu token là đã sử dụng
     */
    public function markTokenAsUsed(int $tokenId): bool {
        $stmt = $this->db->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
        return $stmt->execute([$tokenId]);
    }
}