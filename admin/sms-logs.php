<?php
$page_title = 'SMS Logs';
$active_page = 'notifications';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';

// Ensure recipient_name column exists
try {
    $cols = $pdo->query("SHOW COLUMNS FROM sms_log LIKE 'recipient_name'")->fetch();
    if (!$cols) {
        $pdo->exec("ALTER TABLE sms_log ADD COLUMN recipient_name VARCHAR(255) DEFAULT '' AFTER recipient");
    }
} catch (Exception $e) {}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_log'])) {
        $logId = (int)($_POST['log_id'] ?? 0);
        try {
            $pdo->prepare("DELETE FROM sms_log WHERE id = ?")->execute([$logId]);
            $success_msg = 'Log entry deleted.';
        } catch (Exception $e) {
            $error_msg = 'Database error.';
        }
    }

    if (isset($_POST['delete_selected'])) {
        $rawIds = trim($_POST['bulk_ids'] ?? '');
        $ids = $rawIds ? array_filter(array_map('intval', explode(',', $rawIds))) : [];
        if (!empty($ids)) {
            try {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $pdo->prepare("DELETE FROM sms_log WHERE id IN ($placeholders)")->execute($ids);
                $success_msg = count($ids) . ' log entry/entries deleted.';
            } catch (Exception $e) {
                $error_msg = 'Database error.';
            }
        } else {
            $error_msg = 'No entries selected.';
        }
    }

    if (isset($_POST['delete_all_logs'])) {
        try {
            $pdo->exec("DELETE FROM sms_log");
            $success_msg = 'All SMS logs deleted.';
        } catch (Exception $e) {
            $error_msg = 'Database error.';
        }
    }
}

// Filters
$filter_status = $_GET['status'] ?? '';
$filter_search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($filter_status && in_array($filter_status, ['sent', 'failed'])) {
    $where[] = "status = ?";
    $params[] = $filter_status;
}
if ($filter_search) {
    $where[] = "(recipient_name LIKE ? OR recipient LIKE ? OR message LIKE ?)";
    $searchTerm = "%$filter_search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM sms_log $whereSQL");
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

$query = "SELECT * FROM sms_log $whereSQL ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

require_once 'admin_header.php';
?>

<?php if ($success_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
<?php if ($error_msg): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>SMS Logs <small class="text-muted">(<?= number_format($totalRows) ?> total)</small></h5>
    <a href="send-notification" class="btn btn-cyan btn-sm"><i class="fas fa-paper-plane me-1"></i>Send Notification</a>
</div>

<div class="admin-card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Name, phone, or message..." value="<?= htmlspecialchars($filter_search) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="sent" <?= $filter_status === 'sent' ? 'selected' : '' ?>>Sent</option>
                    <option value="failed" <?= $filter_status === 'failed' ? 'selected' : '' ?>>Failed</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i>Filter</button>
                <a href="sms-logs" class="btn btn-outline-secondary btn-sm">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Delivery Logs</h6>
        <div class="d-flex gap-2">
            <form method="POST" id="bulkDeleteForm" onsubmit="return confirm('Delete selected entries?')">
                <input type="hidden" name="bulk_ids" id="bulkIdsInput" value="">
                <button type="submit" name="delete_selected" class="btn btn-outline-danger btn-sm" id="bulkDeleteBtn" style="display:none;">
                    <i class="bi bi-trash me-1"></i>Delete Selected (<span id="bulkCount">0</span>)
                </button>
            </form>
            <form method="POST" onsubmit="return confirm('Delete ALL SMS logs? This cannot be undone.')">
                <button type="submit" name="delete_all_logs" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Delete All</button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:30px;"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($logs): foreach ($logs as $log): ?>
                    <tr>
                        <td><input type="checkbox" class="form-check-input log-checkbox" value="<?= $log['id'] ?>"></td>
                        <td class="text-nowrap small"><?= htmlspecialchars($log['created_at'] ?? '') ?></td>
                        <td class="small fw-semibold"><?= htmlspecialchars($log['recipient_name'] ?? '') ?></td>
                        <td class="small"><?= htmlspecialchars($log['recipient'] ?? '') ?></td>
                        <td class="small" title="<?= htmlspecialchars($log['message'] ?? '') ?>"><?= htmlspecialchars(mb_substr($log['message'] ?? '', 0, 50)) ?><?= mb_strlen($log['message'] ?? '') > 50 ? '...' : '' ?></td>
                        <td><span class="badge bg-<?= ($log['status'] ?? '') === 'sent' ? 'success' : 'danger' ?>"><?= ucfirst($log['status'] ?? '') ?></span></td>
                        <td class="text-nowrap">
                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this log entry?')">
                                <input type="hidden" name="log_id" value="<?= $log['id'] ?>">
                                <button type="submit" name="delete_log" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No SMS logs found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center">
        <small class="text-muted">Page <?= $page ?> of <?= $totalPages ?></small>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($i = max(1, $page - 3); $i <= min($totalPages, $page + 3); $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($filter_status) ?>&search=<?= urlencode($filter_search) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('selectAll').addEventListener('change', function() {
    var checked = this.checked;
    document.querySelectorAll('.log-checkbox').forEach(function(cb) { cb.checked = checked; });
    updateBulkActions();
});

document.querySelectorAll('.log-checkbox').forEach(function(cb) {
    cb.addEventListener('change', updateBulkActions);
});

function updateBulkActions() {
    var checked = document.querySelectorAll('.log-checkbox:checked');
    var count = checked.length;
    var btn = document.getElementById('bulkDeleteBtn');
    document.getElementById('bulkCount').textContent = count;
    btn.style.display = count > 0 ? 'inline-block' : 'none';

    var ids = [];
    checked.forEach(function(cb) { ids.push(cb.value); });
    document.getElementById('bulkIdsInput').value = ids.join(',');
}
</script>

<?php require_once 'admin_footer.php'; ?>
