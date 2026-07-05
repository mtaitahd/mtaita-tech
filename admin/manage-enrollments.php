<?php
$page_title = 'Manage Enrollments';
$active_page = 'enrollments';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';

if (isset($_GET['set_status'])) {
    $id = (int)$_GET['set_status'];
    $new_status = $_GET['to'] ?? '';
    if (in_array($new_status, ['active', 'pending', 'cancelled'])) {
        try {
            $pdo->prepare("UPDATE enrollments SET status = ?, updated_at = NOW() WHERE id = ?")->execute([$new_status, $id]);
            $success_msg = 'Enrollment status updated to ' . $new_status . '.';

            // Sync payments table when admin changes enrollment status
            $enrollStmt = $pdo->prepare("SELECT payment_reference FROM enrollments WHERE id = ?");
            $enrollStmt->execute([$id]);
            $enroll = $enrollStmt->fetch();
            if ($enroll && !empty($enroll['payment_reference'])) {
                $paymentStatus = match ($new_status) {
                    'active'    => 'completed',
                    'cancelled' => 'voided',
                    default     => 'pending',
                };
                $completedAt = $new_status === 'active' ? date('Y-m-d H:i:s') : null;
                $pdo->prepare("UPDATE payments SET status = ?, completed_at = ? WHERE payment_reference = ?")
                    ->execute([$paymentStatus, $completedAt, $enroll['payment_reference']]);
            }
        } catch (Exception $e) {
            $error_msg = 'Database error.';
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        // Also void the payment record
        $enrollStmt = $pdo->prepare("SELECT payment_reference FROM enrollments WHERE id = ?");
        $enrollStmt->execute([$id]);
        $enroll = $enrollStmt->fetch();
        if ($enroll && !empty($enroll['payment_reference'])) {
            $pdo->prepare("UPDATE payments SET status = 'voided' WHERE payment_reference = ?")
                ->execute([$enroll['payment_reference']]);
        }
        $pdo->prepare("DELETE FROM enrollments WHERE id = ?")->execute([$id]);
        $success_msg = 'Enrollment deleted.';
    } catch (Exception $e) {
        $error_msg = 'Database error.';
    }
}

$status_filter = $_GET['status'] ?? '';
if (!in_array($status_filter, ['pending', 'active', 'cancelled', ''])) {
    $status_filter = '';
}

$sql = "SELECT e.*, 
    c.title AS item_title,
    u.name AS user_name, u.email AS user_email
    FROM enrollments e
    LEFT JOIN courses c ON e.item_type = 'course' AND e.item_id = c.id
    LEFT JOIN public_users u ON e.user_id = u.id";

$params = [];
if ($status_filter !== '') {
    $sql .= " WHERE e.status = ?";
    $params[] = $status_filter;
}
$sql .= " ORDER BY e.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$enrollments = $stmt->fetchAll();
require_once 'admin_header.php';
?>
<div class="page-header">
</div>

<?php if ($success_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
<?php if ($error_msg): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

<div class="admin-card mb-3">
    <form method="GET" class="row g-2 align-items-end p-3">
        <div class="col-auto">
            <label class="form-label text-muted small mb-1">Filter by Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Paid</option>
                <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
        <?php if ($status_filter !== ''): ?>
        <div class="col-auto">
            <a href="manage-enrollments" class="btn btn-outline-secondary">Clear Filter</a>
        </div>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr><th>ID</th><th>User</th><th>Item Type</th><th>Item</th><th>Payment Ref</th><th>Status</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($enrollments)): ?>
                    <tr><td colspan="8" class="text-muted text-center">No enrollments found.</td></tr>
                <?php else: ?>
                    <?php foreach ($enrollments as $e): ?>
                    <tr>
                        <td><?= $e['id'] ?></td>
                        <td>
                            <div><?= htmlspecialchars($e['user_name'] ?? '—') ?></div>
                            <small class="text-muted"><?= htmlspecialchars($e['user_email'] ?? '') ?></small>
                        </td>
                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($e['item_type']) ?></span></td>
                        <td><?= htmlspecialchars($e['item_title'] ?? '—') ?></td>
                        <td class="small text-muted"><?= htmlspecialchars($e['payment_reference'] ?? '—') ?></td>
                        <td>
                            <?php if ($e['status'] === 'active'): ?>
                                <span class="badge bg-success">Paid</span>
                            <?php elseif ($e['status'] === 'cancelled'): ?>
                                <span class="badge bg-secondary">Cancelled</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted"><?= date('M d, Y', strtotime($e['created_at'])) ?></td>
                        <td>
                            <?php if ($e['status'] === 'pending'): ?>
                                <a href="?set_status=<?= $e['id'] ?>&to=active<?= $status_filter !== '' ? '&status=' . urlencode($status_filter) : '' ?>" class="btn btn-sm btn-outline-success me-1" title="Mark as Paid"><i class="bi bi-check-lg"></i></a>
                                <a href="?set_status=<?= $e['id'] ?>&to=cancelled<?= $status_filter !== '' ? '&status=' . urlencode($status_filter) : '' ?>" class="btn btn-sm btn-outline-warning me-1" title="Cancel"><i class="bi bi-x-lg"></i></a>
                            <?php elseif ($e['status'] === 'active'): ?>
                                <a href="?set_status=<?= $e['id'] ?>&to=cancelled<?= $status_filter !== '' ? '&status=' . urlencode($status_filter) : '' ?>" class="btn btn-sm btn-outline-warning me-1" title="Cancel"><i class="bi bi-x-lg"></i></a>
                            <?php elseif ($e['status'] === 'cancelled'): ?>
                                <a href="?set_status=<?= $e['id'] ?>&to=active<?= $status_filter !== '' ? '&status=' . urlencode($status_filter) : '' ?>" class="btn btn-sm btn-outline-success me-1" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></a>
                            <?php endif; ?>
                            <a href="?delete=<?= $e['id'] ?><?= $status_filter !== '' ? '&status=' . urlencode($status_filter) : '' ?>" class="btn btn-sm btn-outline-danger btn-delete" data-type="enrollment" title="Delete"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>
