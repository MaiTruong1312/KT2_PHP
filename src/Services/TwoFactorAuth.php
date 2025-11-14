<?php
namespace Services;

use PragmaRX\Google2FA\Google2FA;
class TwoFactorAuth {
    private Google2FA $google2fa;

    public function __construct() {
        $this->google2fa = new Google2FA();
    }
    public function generateSecretKey(): string {
        return $this->google2fa->generateSecretKey();
    }

    public function getQRCodeUrl(string $appName, string $email, string $secret): string {
    $otpAuthUrl = $this->google2fa->getQRCodeUrl($appName, $email, $secret);
    return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($otpAuthUrl);
    }
    public function verifyCode(string $secret, string $code): bool {
        return $this->google2fa->verifyKey($secret, $code);
    }
    public function generateBackupCodes(int $quantity = 8, int $length = 10): array {
        $codes = [];
        for ($i = 0; $i < $quantity; $i++) {
            $codes[] = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, $length);
        }
        return $codes;
    }
}
