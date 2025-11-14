<?php
// src/Services/Token.php
namespace Services;

class Token {
    
    /**
     * Tạo một token an toàn (ví dụ: cho password reset)
     * @return array [string $rawToken, string $hashedToken]
     */
    public static function generateToken(): array {
        $rawToken = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $rawToken);
        return [$rawToken, $hashedToken];
    }

    /**
     * Tạo cặp Selector/Validator cho "Remember Me"
     * @return array [string $selector, string $rawValidator, string $hashedValidator]
     */
    public static function generateRememberMeToken(): array {
        $selector = bin2hex(random_bytes(12)); // 24 chars
        $rawValidator = bin2hex(random_bytes(32)); // 64 chars
        $hashedValidator = hash('sha256', $rawValidator);
        
        return [$selector, $rawValidator, $hashedValidator];
    }
}