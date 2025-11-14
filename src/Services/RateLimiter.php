<?php
// src/Services/RateLimiter.php
namespace Services;

use Core\Database;
use PDO;

class RateLimiter {
    private PDO $db;
    private string $ip;
    const MAX_ATTEMPTS = 5;
    const TIME_FRAME_MINUTES = 15;
    const RECAPTCHA_THRESHOLD = 3;


    public function __construct() {
        $this->db = Database::getInstance();
        $this->ip = $this->getClientIpBinary();
    }

    public function logAttempt(string $email, bool $success) {
        $stmt = $this->db->prepare(
            "INSERT INTO login_attempts (ip, email, attempt_at, success, user_agent) 
             VALUES (?, ?, NOW(), ?, ?)"
        );
        $stmt->execute([
            $this->ip, 
            $email, 
            $success ? 1 : 0, 
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }

    /**
     * Kiểm tra xem IP có bị tạm khóa không
     */
    public function isIpBlocked(): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as failed_attempts 
             FROM login_attempts 
             WHERE ip = ? AND success = 0 AND attempt_at > (NOW() - INTERVAL ? MINUTE)"
        );
        $stmt->execute([$this->ip, self::TIME_FRAME_MINUTES]);
        $result = $stmt->fetch();

        return ($result && $result['failed_attempts'] >= self::MAX_ATTEMPTS);
    }

    /**
     * Kiểm tra xem có cần hiển thị reCAPTCHA không.
     */
    public function isRecaptchaRequired(): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as failed_attempts 
             FROM login_attempts 
             WHERE ip = ? AND success = 0 AND attempt_at > (NOW() - INTERVAL ? MINUTE)"
        );
        $stmt->execute([$this->ip, self::TIME_FRAME_MINUTES]);
        $result = $stmt->fetch();

        // Hiển thị reCAPTCHA nếu số lần thử sai >= ngưỡng và IP chưa bị khóa hoàn toàn.
        return ($result && $result['failed_attempts'] >= self::RECAPTCHA_THRESHOLD && !$this->isIpBlocked());
    }

    /**
     * Lấy IP của client dưới dạng binary (hỗ trợ IPv4/IPv6)
     */
    private function getClientIpBinary(): string {
        $ipString = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        // INET6_ATON() là hàm của MySQL, PHP dùng inet_pton
        $binaryIp = @inet_pton($ipString);
        
        // Fallback cho IPv4 nếu inet_pton thất bại (ví dụ: IPv6 không được hỗ trợ)
        if ($binaryIp === false) {
            $binaryIp = @inet_pton('::ffff:' . $ipString); // Map IPv4 to IPv6
            if ($binaryIp === false) {
                return inet_pton('::1'); // Fallback an toàn
            }
        }
        return $binaryIp;
    }
}