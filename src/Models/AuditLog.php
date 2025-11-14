<?php
// src/Models/AuditLog.php
namespace Models;

use Core\Database;
use PDO;

class AuditLog {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * [E] Lấy log bảo mật cho user
     * 
     */
    public function getLogsForUser(int $userId, int $limit = 20) {
        $stmt = $this->db->prepare(
            "SELECT event_type, event_text, INET6_NTOA(ip) as ip_address, user_agent, created_at 
             FROM audit_logs 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT ?"
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
}