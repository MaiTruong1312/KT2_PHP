<?php
namespace Core;

use PDO;

class DatabaseSessionHandler implements \SessionHandlerInterface {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function open($savePath, $sessionName): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read($sessionId): string|false {
        $stmt = $this->db->prepare("SELECT session_data FROM sessions WHERE id = ?");
        $stmt->execute([$sessionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['session_data'] : '';
    }

    public function write($sessionId, $data): bool {
        $userId = $_SESSION['user_id'] ?? null;
        $ip = @inet_pton($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $stmt = $this->db->prepare(
            "REPLACE INTO sessions (id, user_id, session_data, ip, user_agent, last_active_at) 
             VALUES (?, ?, ?, ?, ?, NOW())"
        );

        return $stmt->execute([$sessionId, $userId, $data, $ip, $userAgent]);
    }

    public function destroy($sessionId): bool {
        // Xóa cả token "remember me" liên quan nếu có
        // Đây là một logic nâng cao, tạm thời chỉ xóa session
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE id = ?");
        return $stmt->execute([$sessionId]);
    }

    public function gc($maxLifetime): int|false {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE last_active_at < NOW() - INTERVAL ? SECOND");
        $stmt->execute([$maxLifetime]);
        return $stmt->rowCount();
    }

    /**
     * Lấy tất cả các phiên của một người dùng.
     */
    public function getSessionsForUser(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT id, INET6_NTOA(ip) as ip_address, user_agent, last_active_at 
             FROM sessions 
             WHERE user_id = ? 
             ORDER BY last_active_at DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thu hồi (xóa) một phiên cụ thể, đảm bảo nó thuộc về đúng người dùng.
     */
    public function revokeSession(string $sessionId, int $currentUserId): bool {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE id = ? AND user_id = ?");
        return $stmt->execute([$sessionId, $currentUserId]);
    }

    /**
     * Thu hồi tất cả các phiên khác của người dùng, trừ phiên hiện tại.
     */
    public function revokeAllOtherSessions(string $currentSessionId, int $currentUserId): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM sessions WHERE user_id = ? AND id != ?"
        );
        return $stmt->execute([$currentUserId, $currentSessionId]);
    }
}