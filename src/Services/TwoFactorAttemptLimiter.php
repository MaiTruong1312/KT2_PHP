<?php
namespace Services;

use Core\Database;
use PDO;

class TwoFactorAttemptLimiter {
    private PDO $db;
    private int $userId;

    // Cấu hình: Khóa sau 5 lần thử sai trong vòng 10 phút.
    const MAX_ATTEMPTS = 5;
    const TIME_FRAME_MINUTES = 10;

    public function __construct(int $userId) {
        $this->db = Database::getInstance();
        $this->userId = $userId;
    }

    /**
     * Ghi lại một lần thử mã 2FA thất bại.
     */
    public function logFailure(): void {
        $stmt = $this->db->prepare(
            "INSERT INTO tfa_attempts (user_id, ip_address, user_agent, attempt_at) 
             VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute([
            $this->userId, 
            $this->getClientIpBinary(), 
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }

    /**
     * Kiểm tra xem người dùng có bị khóa tạm thời không.
     * @return bool True nếu bị khóa, False nếu không.
     */
    public function isBlocked(): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as failed_attempts 
             FROM tfa_attempts 
             WHERE user_id = ? AND attempt_at > (NOW() - INTERVAL ? MINUTE)"
        );
        $stmt->execute([$this->userId, self::TIME_FRAME_MINUTES]);
        $result = $stmt->fetch();

        return ($result && $result['failed_attempts'] >= self::MAX_ATTEMPTS);
    }

    /**
     * Kiểm tra xem số lần thử thất bại có sắp đạt đến ngưỡng khóa hay không.
     * Điều này dùng để kích hoạt thông báo email chỉ một lần.
     * @return bool True nếu số lần thử là MAX_ATTEMPTS - 1.
     */
    public function isThresholdReached(): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as failed_attempts 
             FROM tfa_attempts 
             WHERE user_id = ? AND attempt_at > (NOW() - INTERVAL ? MINUTE)"
        );
        $stmt->execute([$this->userId, self::TIME_FRAME_MINUTES]);
        $result = $stmt->fetch();

        return ($result && $result['failed_attempts'] === (self::MAX_ATTEMPTS - 1));
    }

    /**
     * Lấy IP của client dưới dạng binary (hỗ trợ IPv4/IPv6).
     */
    private function getClientIpBinary(): string {
        $ipString = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $binaryIp = @inet_pton($ipString);
        
        if ($binaryIp === false) {
            return inet_pton('::1'); // Fallback an toàn
        }
        return $binaryIp;
    }
}