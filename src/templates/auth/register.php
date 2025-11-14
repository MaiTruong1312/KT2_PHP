<?php
// templates/auth/register.php
$pageTitle = 'Đăng ký';
require __DIR__ . '/../layouts/header.php';
?>

<div class="row d-flex justify-content-center align-items-center form-container">
    <div class="col-12 col-md-8 col-lg-6 col-xl-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
                <h2 class="card-title text-center mb-4">Đăng ký</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <input type="hidden" name="csrf_token" 
                           value="<?php echo htmlspecialchars($csrfToken); ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control" 
                               id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu (tối thiểu 8 ký tự):</label>
                        <input type="password" class="form-control" 
                               id="password" name="password" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Đăng ký</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center py-3">
                <small>Đã có tài khoản? <a href="login.php">Đăng nhập</a></small>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>