<?php
// src/Services/AuditLogger.php
namespace Services;

use Core\Database;
use PDO;

class AuditLogger {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }
    public function logEvent(?int $userId, string $eventType, ?string $eventText = null) {
        $ip = $this->getClientIpBinary();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO audit_logs (user_id, event_type, event_text, ip, user_agent, created_at) 
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([$userId, $eventType, $eventText, $ip, $userAgent]);
        } catch (\PDOException $e) {
            // Trong thực tế, ghi lỗi này vào file error.log
            error_log("Failed to write to audit_logs: " . $e->getMessage());
        }
    }

    /**
     * Lấy IP của client (giống của Thành viên C)
     */
    private function getClientIpBinary(): string {
        $ipString = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $binaryIp = @inet_pton($ipString);
        
        if ($binaryIp === false) {
            $binaryIp = @inet_pton('::ffff:' . $ipString);
            if ($binaryIp === false) {
                return inet_pton('::1'); // Fallback
            }
        }
        return $binaryIp;
    }
}