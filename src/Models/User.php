<?php
namespace Models;

use Core\Database;
use PDO;

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(string $email, string $password): bool {
        if ($this->findByEmail($email)) {
            return false; // Email đã tồn tại
        }
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        $stmt = $this->db->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
        return $stmt->execute([$email, $passwordHash]);
    }

    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    public function updateLastLogin(int $userId): void {
        $stmt = $this->db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    }

    public function enableTwoFactor(int $userId, string $secret): bool {
        $stmt = $this->db->prepare("UPDATE users SET is_2fa_enabled = 1, totp_secret = ? WHERE id = ?");
        return $stmt->execute([$secret, $userId]);
    }

    public function storeBackupCodes(int $userId, array $hashedCodes): void {
        $this->db->prepare("DELETE FROM backup_codes WHERE user_id = ?")->execute([$userId]);
        $stmt = $this->db->prepare("INSERT INTO backup_codes (user_id, code_hash) VALUES (?, ?)");
        foreach ($hashedCodes as $hash) {
            $stmt->execute([$userId, $hash]);
        }
    }

    public function useBackupCode(int $userId, string $code): bool {
        $stmt = $this->db->prepare("SELECT id, code_hash FROM backup_codes WHERE user_id = ?");
        $stmt->execute([$userId]);
        $backupCodes = $stmt->fetchAll();

        foreach ($backupCodes as $backupCode) {
            if (hash_equals($backupCode['code_hash'], hash('sha256', $code))) {
                $deleteStmt = $this->db->prepare("DELETE FROM backup_codes WHERE id = ?");
                $deleteStmt->execute([$backupCode['id']]);
                return true; // Mã hợp lệ
            }
        }

        return false; 
    }

    public function updatePassword(int $userId, string $newPassword): bool {
        $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$passwordHash, $userId]);
    }

    /**

     *
     * @param int $userId 
     * @return bool 
     */
    public function disable2FA(int $userId): array {
        $this->db->beginTransaction();
        try {
            $stmtUser = $this->db->prepare(
                "UPDATE users SET is_2fa_enabled = 0, totp_secret = NULL WHERE id = ?"
            );
            if (!$stmtUser->execute([$userId])) {
                throw new \PDOException("Không thể cập nhật bảng người dùng.");
            }

            $stmtBackup = $this->db->prepare(
                "DELETE FROM backup_codes WHERE user_id = ?"
            );
            $stmtBackup->execute([$userId]); 

            $this->db->commit();
            return ['success' => true];
        } catch (\PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}