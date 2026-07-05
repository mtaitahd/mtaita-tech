<?php
$page_title = 'Orders';
$active_page = 'orders';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../lib/Order.php';

$orderModel = new Order();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $allowed = ['pending', 'completed', 'failed', 'refunded'];
    if ($id > 0 && in_array($status, $allowed)) {
        $orderModel->updatePaymentStatus($id, $status);
        $success_msg = 'Order status updated.';
    }
}

$orders = $orderModel->getAll();
require_once 'admin_header.php';
?>
<div class="page-header">
    <h4 style="margin:0;"><i class="bi bi-receipt me-2 text-cyan"></i>All Orders</h4>
</div>

<?php if ($success_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
<?php if ($error_msg): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Amount</th>
                    <th>Transaction</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="8" class="text-muted text-center">No orders yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td><?= $o['id'] ?></td>
                        <td>
                            <div class="fw-medium"><?= htmlspecialchars($o['user_name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($o['user_email']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($o['product_title']) ?></td>
                        <td>TZS <?= number_format($o['amount']) ?></td>
                        <td class="small text-muted" style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= htmlspecialchars($o['transaction_id'] ?: '—') ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $orderModel->getStatusColor($o['status']) ?>">
                                <?= $orderModel->getStatusLabel($o['status']) ?>
                            </span>
                        </td>
                        <td class="small text-muted"><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></td>
                        <td>
                            <form method="POST" class="d-flex gap-1">
                                <input type="hidden" name="update_status" value="1">
                                <input type="hidden" name="id" value="<?= $o['id'] ?>">
                                <select name="status" class="form-select form-select-sm" style="width:auto;">
                                    <option value="pending" <?= $o['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="completed" <?= $o['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="failed" <?= $o['status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                                    <option value="refunded" <?= $o['status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-cyan"><i class="bi bi-check"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>
