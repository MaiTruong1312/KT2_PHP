<?php
// src/Services/Mailer.php
namespace Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    public function sendPasswordResetLink(string $email, string $rawToken): bool {
        $config = require __DIR__ . '/../../config/mail.php';
        $appConfig = require __DIR__ . '/../../config/app.php';
        $resetLink = $appConfig['url'] . '/public/reset-password.php?token=' . $rawToken;

        $mail = new PHPMailer(true);

        try {
            // Cấu hình Server
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = $config['encryption'];
            $mail->Port       = $config['port'];
            $mail->CharSet    = 'UTF-8';

            // Người gửi và người nhận
            $mail->setFrom($config['from_address'], $config['from_name']);
            $mail->addAddress($email);

            // Nội dung email (sử dụng HTML)
            $mail->isHTML(true);
            $mail->Subject = 'Yêu cầu đặt lại mật khẩu';
            $mail->Body    = $this->createEmailBody($resetLink);
            $mail->AltBody = "Nhấn vào link sau để đặt lại mật khẩu: " . $resetLink;

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Ghi lại lỗi chi tiết thay vì hiển thị cho người dùng
            error_log("Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    public function sendTwoFactorLockoutWarning(string $email): bool {
        $config = require __DIR__ . '/../../config/mail.php';
        $mail = new PHPMailer(true);

        try {
            // Cấu hình Server
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = $config['encryption'];
            $mail->Port       = $config['port'];
            $mail->CharSet    = 'UTF-8';

            // Người gửi và người nhận
            $mail->setFrom($config['from_address'], $config['from_name']);
            $mail->addAddress($email);

            // Nội dung email
            $mail->isHTML(true);
            $mail->Subject = 'Cảnh báo bảo mật tài khoản';
            $mail->Body    = $this->create2faLockoutBody();
            $mail->AltBody = "Chúng tôi phát hiện nhiều lần đăng nhập thất bại bằng mã xác thực hai yếu tố (2FA) trên tài khoản của bạn. Tài khoản của bạn đã bị tạm khóa đăng nhập trong một khoảng thời gian ngắn để đảm bảo an toàn. Nếu không phải bạn thực hiện, hãy đổi mật khẩu ngay lập tức.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error (2FA Lockout): {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Gửi email thông báo khi mật khẩu được thay đổi thành công.
     */
    public function sendPasswordChangedNotification(string $email): bool {
        $config = require __DIR__ . '/../../config/mail.php';
        $mail = new PHPMailer(true);

        try {
            // Cấu hình Server
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = $config['encryption'];
            $mail->Port       = $config['port'];
            $mail->CharSet    = 'UTF-8';

            // Người gửi và người nhận
            $mail->setFrom($config['from_address'], $config['from_name']);
            $mail->addAddress($email);

            // Nội dung email
            $mail->isHTML(true);
            $mail->Subject = 'Cảnh báo bảo mật: Mật khẩu đã được thay đổi';
            $mail->Body    = $this->createPasswordChangedBody();
            $mail->AltBody = "Mật khẩu cho tài khoản của bạn vừa được thay đổi. Nếu bạn không thực hiện hành động này, vui lòng liên hệ hỗ trợ ngay lập tức.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error (Password Change): {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Gửi email thông báo khi trạng thái 2FA thay đổi.
     */
    public function send2faStatusChangedNotification(string $email, bool $isEnabled): bool {
        $config = require __DIR__ . '/../../config/mail.php';
        $mail = new PHPMailer(true);

        try {
            // Cấu hình Server
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = $config['encryption'];
            $mail->Port       = $config['port'];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($config['from_address'], $config['from_name']);
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Cảnh báo bảo mật: Xác thực hai yếu tố đã ' . ($isEnabled ? 'được bật' : 'bị tắt');
            $mail->Body    = $this->create2faStatusChangedBody($isEnabled);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error (2FA Status Change): {$mail->ErrorInfo}");
            return false;
        }
    }
    private function create2faLockoutBody(): string {
        return "
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h2>Cảnh báo bảo mật tài khoản</h2>
                <p>Chúng tôi phát hiện nhiều lần đăng nhập thất bại bằng mã xác thực hai yếu tố (2FA) trên tài khoản của bạn.</p>
                <p>Để bảo vệ bạn, chúng tôi đã tạm thời khóa chức năng đăng nhập vào tài khoản này. Vui lòng thử lại sau ít phút.</p>
                <p><strong>Nếu không phải bạn thực hiện các hành động này, chúng tôi khuyên bạn nên <a href='#'>đặt lại mật khẩu</a> ngay lập tức.</strong></p>
            </div>
        ";
    }

    private function createEmailBody(string $resetLink): string {
        // Một template email HTML đơn giản
        return "
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h2>Yêu cầu đặt lại mật khẩu</h2>
                <p>Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                <p>Vui lòng nhấp vào nút bên dưới để đặt lại mật khẩu của bạn:</p>
                <p style='margin: 20px 0;'>
                    <a href='{$resetLink}' style='background-color: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Đặt lại mật khẩu</a>
                </p>
                <p>Nếu bạn không yêu cầu điều này, vui lòng bỏ qua email này.</p>
                <p>Link này sẽ hết hạn trong 1 giờ.</p>
            </div>
        ";
    }

    private function createPasswordChangedBody(): string {
        return "
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h2>Cảnh báo: Mật khẩu đã được thay đổi</h2>
                <p>Mật khẩu cho tài khoản của bạn vừa được cập nhật thành công.</p>
                <p><strong>Nếu bạn không thực hiện hành động này, hãy liên hệ với bộ phận hỗ trợ ngay lập tức để bảo vệ tài khoản của bạn.</strong></p>
            </div>
        ";
    }

    private function create2faStatusChangedBody(bool $isEnabled): string {
        $statusText = $isEnabled ? 'bật' : 'tắt';
        $actionText = $isEnabled 
            ? 'Một lớp bảo mật bổ sung đã được thêm vào tài khoản của bạn.' 
            : 'Một lớp bảo mật đã bị gỡ bỏ khỏi tài khoản của bạn.';

        return "
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h2>Cảnh báo: Xác thực hai yếu tố (2FA) đã được {$statusText}</h2>
                <p>Trạng thái xác thực hai yếu tố trên tài khoản của bạn vừa được thay đổi. {$actionText}</p>
                <p><strong>Nếu bạn không thực hiện hành động này, hãy đổi mật khẩu và liên hệ với bộ phận hỗ trợ ngay lập tức.</strong></p>
            </div>
        ";
    }
}