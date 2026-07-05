<?php
$page_title = 'Payments';
$active_page = 'payments';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../services/PaymentService.php';

$paymentService = new PaymentService($pdo);

// Sync with Snippe
if (isset($_GET['sync']) && !empty($_GET['sync'])) {
    $ref = trim($_GET['sync']);
    $paymentService->verifyPayment($ref);
    $success_msg = 'Payment synced with Snippe.';
}

// Mark as paid
if (isset($_GET['mark_paid']) && !empty($_GET['mark_paid'])) {
    $ref = trim($_GET['mark_paid']);
    if ($paymentService->adminMarkCompleted($ref)) {
        $success_msg = 'Payment marked as completed.';
    } else {
        $error_msg = $paymentService->getLastError() ?? 'Failed to mark payment as completed.';
    }
}

// Delete payment
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $ref = trim($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM payments WHERE payment_reference = ?");
    $stmt->execute([$ref]);
    $success_msg = 'Payment deleted.';
}

// Filter
$filterStatus = $_GET['status'] ?? '';
$allowedFilters = ['', 'pending', 'completed', 'failed', 'voided', 'expired'];
if (!in_array($filterStatus, $allowedFilters)) {
    $filterStatus = '';
}

if ($filterStatus) {
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE status = ? ORDER BY created_at DESC");
    $stmt->execute([$filterStatus]);
} else {
    $stmt = $pdo->query("SELECT * FROM payments ORDER BY created_at DESC");
}
$payments = $stmt->fetchAll();

$countAll = $pdo->query("SELECT COUNT(*) FROM payments")->fetchColumn();
$countPending = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'")->fetchColumn();
$countCancelled = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'voided'")->fetchColumn();
$totalCompleted = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'completed'")->fetchColumn();

require_once 'admin_header.php';
?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 style="margin:0;"><i class="bi bi-credit-card-2-front me-2 text-cyan"></i>Payments</h4>
    <a href="payments" class="btn btn-sm btn-outline-cyan"><i class="bi bi-arrow-clockwise"></i> Refresh</a>
</div>

<?php if ($success_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
<?php if ($error_msg): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="admin-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;border-radius:12px;background:rgba(0,200,83,0.15);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#10B981;">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <div class="text-muted small">Wallet</div>
                    <div class="fw-bold fs-5">TZS <?= number_format($totalCompleted) ?></div>
                    <small class="text-success">Collected from paid</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="row g-2">
            <div class="col">
                <div class="admin-card text-center py-3 h-100">
                    <div style="font-size:1.5rem;color:var(--accent);"><i class="bi bi-credit-card-2-front"></i></div>
                    <div class="fw-bold fs-4"><?= $countAll ?></div>
                    <small class="text-muted">All Payments</small>
                </div>
            </div>
            <div class="col">
                <div class="admin-card text-center py-3 h-100">
                    <div style="font-size:1.5rem;color:#F59E0B;"><i class="bi bi-hourglass-split"></i></div>
                    <div class="fw-bold fs-4"><?= $countPending ?></div>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
            <div class="col">
                <div class="admin-card text-center py-3 h-100">
                    <div style="font-size:1.5rem;color:#6B7280;"><i class="bi bi-x-circle"></i></div>
                    <div class="fw-bold fs-4"><?= $countCancelled ?></div>
                    <small class="text-muted">Cancelled</small>
                </div>
            </div>
        </div>
    </div>
</div>

<ul class="nav nav-pills mb-3 gap-1">
    <li class="nav-item"><a class="nav-link <?= !$filterStatus ? 'active' : '' ?>" href="payments">All Payments</a></li>
    <li class="nav-item"><a class="nav-link <?= $filterStatus === 'pending' ? 'active' : '' ?>" href="payments?status=pending">Pending</a></li>
    <li class="nav-item"><a class="nav-link <?= $filterStatus === 'voided' ? 'active' : '' ?>" href="payments?status=voided">Cancelled</a></li>
</ul>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer Name</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr><td colspan="6" class="text-muted text-center">No payments found.</td></tr>
                <?php else: ?>
                    <?php foreach ($payments as $p): ?>
                    <?php
                        $statusColors = [
                            'pending'   => 'warning text-dark',
                            'completed' => 'success',
                            'failed'    => 'danger',
                            'voided'    => 'secondary',
                            'expired'   => 'dark',
                        ];
                        $color = $statusColors[$p['status']] ?? 'secondary';
                    ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td>
                            <div class="fw-medium"><?= htmlspecialchars($p['customer_name'] ?: 'Guest') ?></div>
                            <small class="text-muted"><?= htmlspecialchars($p['customer_email'] ?: '—') ?></small>
                            <?php if ($p['customer_phone']): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($p['customer_phone']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="fw-semibold">TZS <?= number_format($p['amount']) ?></td>
                        <td><span class="badge bg-<?= $color ?>"><?= ucfirst($p['status']) ?></span></td>
                        <td class="small text-muted"><?= date('d M Y, H:i', strtotime($p['created_at'])) ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <?php if ($p['status'] !== 'completed'): ?>
                                    <a href="payments?mark_paid=<?= urlencode($p['payment_reference']) ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Mark <?= htmlspecialchars($p['customer_name'] ?: 'this') ?> payment as completed?')">Make Paid</a>
                                <?php endif; ?>
                                <a href="payments?delete=<?= urlencode($p['payment_reference']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this payment?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>
