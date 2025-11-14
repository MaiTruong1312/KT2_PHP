<?php
namespace Services;

class RecaptchaVerifier {
    private string $secretKey;

    public function __construct() {
        $config = require __DIR__ . '/../../config/recaptcha.php';
        $this->secretKey = $config['secret_key'];
    }

    /**
     * Xác thực mã reCAPTCHA với Google.
     * @param string $responseToken Mã từ $_POST['g-recaptcha-response']
     * @return bool True nếu hợp lệ, False nếu không.
     */
    public function verify(string $responseToken): bool {
        if (empty($responseToken)) {
            return false;
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret'   => $this->secretKey,
            'response' => $responseToken,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ];

        $options = ['http' => ['method' => 'POST', 'content' => http_build_query($data)]];
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        $response = json_decode($result);
        return $response && $response->success;
    }
}