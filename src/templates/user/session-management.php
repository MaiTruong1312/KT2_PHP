<?php
$pageTitle = 'Quản lý Phiên đăng nhập';
require __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center my-5">
    <div class="col-12 col-lg-10">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h1 class="h4">Quản lý Phiên đăng nhập</h1>
                <p class="text-muted mb-0">Xem và quản lý các thiết bị đang đăng nhập vào tài khoản của bạn.</p>
            </div>

            <div class="card-body">
                <?php
                if (Core\Session::has('flash_success')) {
                    echo '<div class="alert alert-success">' . htmlspecialchars(Core\Session::get('flash_success')) . '</div>';
                    Core\Session::remove('flash_success');
                }
                if (!empty($error)) {
                    echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
                }
                ?>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Thiết bị</th>
                                <th>Địa chỉ IP</th>
                                <th>Hoạt động cuối</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sessions as $session): ?>
                                <?php
                                $isCurrentSession = ($session['id'] === $currentSessionId);
                                ?>
                                <tr class="<?php echo $isCurrentSession ? 'table-success' : ''; ?>">
                                    <td>
                                        <span title="<?php echo htmlspecialchars($session['user_agent']); ?>">
                                            <?php
                                            // Cố gắng phân tích User Agent để hiển thị thân thiện hơn
                                            $userAgent = $session['user_agent'];
                                            if (preg_match('/(Chrome|Firefox|Safari|Edg|Opera)\/([0-9\.]+)/', $userAgent, $matches)) {
                                                echo htmlspecialchars($matches[1]);
                                            } else {
                                                echo "Trình duyệt không xác định";
                                            }

                                            if (preg_match('/(Windows|Macintosh|Linux|Android|iPhone)/', $userAgent, $matches)) {
                                                echo ' trên ' . htmlspecialchars($matches[1]);
                                            }
                                            ?>
                                        </span>
                                        <?php if ($isCurrentSession): ?>
                                            <span class="badge bg-primary ms-2">Thiết bị này</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($session['ip_address']); ?></td>
                                    <td><?php echo htmlspecialchars((new DateTime($session['last_active_at']))->format('H:i d/m/Y')); ?></td>
                                    <td class="text-end">
                                        <?php if (!$isCurrentSession): ?>
                                            <form action="session-management.php" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="revoke">
                                                <input type="hidden" name="session_id" value="<?php echo htmlspecialchars($session['id']); ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn đăng xuất khỏi thiết bị này?');">
                                                    Thu hồi
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <hr>
                <p class="text-muted">Nếu bạn nghi ngờ tài khoản bị truy cập trái phép, bạn có thể đăng xuất khỏi tất cả các thiết bị khác.</p>
                <form action="session-management.php" method="POST">
                    <input type="hidden" name="action" value="revoke_all">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Bạn có chắc muốn đăng xuất khỏi tất cả các thiết bị khác?');">
                        Đăng xuất khỏi tất cả thiết bị khác
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>