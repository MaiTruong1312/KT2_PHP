<?php
// templates/user/change-password.php
$pageTitle = 'Thay đổi mật khẩu';
require __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center my-5">
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h1 class="h4">Thay đổi mật khẩu</h1>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form action="change-password.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                    <div class="mb-3">
                        <label for="old_password" class="form-label">Mật khẩu hiện tại</label>
                        <input type="password" class="form-control" id="old_password" name="old_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">Mật khẩu mới</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                        <div class="form-text">Mật khẩu phải có ít nhất 8 ký tự.</div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <?php if ($user['is_2fa_enabled']): ?>
                    <hr>
                    <div class="mb-3">
                        <label for="code" class="form-label">Mã xác thực hai yếu tố (2FA)</label>
                        <input type="text" class="form-control" id="code" name="code" 
                               required maxlength="6" pattern="[0-9]*" inputmode="numeric" 
                               autocomplete="one-time-code"
                               placeholder="Nhập mã 6 số từ ứng dụng xác thực">
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary w-100">Cập nhật mật khẩu</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>