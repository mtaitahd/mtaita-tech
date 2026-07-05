<?php
$page_title = 'Manage Services';
$active_page = 'services';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'fa-solid fa-code');
    $list_items = trim($_POST['list_items'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($title && $description) {
        try {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE services SET title = ?, description = ?, icon = ?, list_items = ?, sort_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$title, $description, $icon, $list_items, $sort_order, $is_active, $id]);
                $success_msg = 'Service updated!';
            } else {
                $stmt = $pdo->prepare("INSERT INTO services (title, description, icon, list_items, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $icon, $list_items, $sort_order, $is_active]);
                $success_msg = 'Service added!';
            }
        } catch (Exception $e) {
            error_log('save_service error: ' . $e->getMessage());
            $error_msg = 'Database error.';
        }
    } else {
        $error_msg = 'Title and description are required.';
    }
}

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$id]);
        $success_msg = 'Service deleted.';
    } catch (Exception $e) {
        $error_msg = 'Database error.';
    }
}

// Toggle active
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $pdo->prepare("UPDATE services SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
    $success_msg = 'Service status toggled.';
}

$services = $pdo->query("SELECT * FROM services ORDER BY sort_order ASC")->fetchAll();
require_once 'admin_header.php';
?>
<div class="page-header">
    <button class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#serviceModal"><i class="bi bi-plus-lg"></i> Add Service</button>
</div>

<?php if ($success_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
<?php if ($error_msg): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr><th>Order</th><th>Icon</th><th>Title</th><th>Description</th><th>Features</th><th>Active</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($services)): ?>
                    <tr><td colspan="7" class="text-muted text-center">No services yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($services as $s): ?>
                    <tr>
                        <td><?= $s['sort_order'] ?></td>
                        <td><i class="<?= htmlspecialchars($s['icon']) ?>" style="font-size:1.3rem;color:var(--cyan);"></i></td>
                        <td><?= htmlspecialchars($s['title']) ?></td>
                        <td class="small text-muted" style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($s['description']) ?></td>
                        <td class="small text-muted" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($s['list_items'] ?? '') ?></td>
                        <td><?= $s['is_active'] ? '<span class="text-cyan">Active</span>' : '<span class="text-muted">Inactive</span>' ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-cyan me-1" data-bs-toggle="modal" data-bs-target="#serviceModal"
                                data-id="<?= $s['id'] ?>"
                                data-title="<?= htmlspecialchars($s['title'], ENT_QUOTES) ?>"
                                data-description="<?= htmlspecialchars($s['description'], ENT_QUOTES) ?>"
                                data-icon="<?= htmlspecialchars($s['icon'], ENT_QUOTES) ?>"
                                data-list-items="<?= htmlspecialchars($s['list_items'] ?? '', ENT_QUOTES) ?>"
                                data-sort="<?= $s['sort_order'] ?>"
                                data-active="<?= $s['is_active'] ?>"
                                title="Edit"><i class="bi bi-pencil"></i></button>
                            <a href="?toggle=<?= $s['id'] ?>" class="btn btn-sm btn-outline-warning me-1" title="Toggle active"><i class="bi bi-eye<?= $s['is_active'] ? '-slash' : '' ?>"></i></a>
                            <a href="?delete=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this service?')" title="Delete"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="id" id="svc-id" value="0">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil text-cyan me-2"></i><span id="svc-modal-title">New Service</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="svc-title" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="svc-sort" class="form-control" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="svc-desc" rows="4" class="form-control" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Feature List (one per line)</label>
                            <textarea name="list_items" id="svc-items" rows="4" class="form-control" placeholder="Custom website design&#10;E-commerce solutions&#10;CMS integration"></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Icon (Font Awesome class)</label>
                            <input type="text" name="icon" id="svc-icon" class="form-control" placeholder="fa-solid fa-code">
                            <small class="text-muted">e.g. fa-solid fa-laptop-code, fa-solid fa-robot</small>
                        </div>
                        <div class="col-md-4 d-flex align-items-end pb-2">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" id="svc-active" class="form-check-input" checked>
                                <label class="form-check-label" for="svc-active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cyan"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('serviceModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            var id = btn.dataset.id || '0';
            document.getElementById('svc-id').value = id;
            document.getElementById('svc-title').value = btn.dataset.title || '';
            document.getElementById('svc-desc').value = btn.dataset.description || '';
            document.getElementById('svc-items').value = btn.dataset.listItems || '';
            document.getElementById('svc-icon').value = btn.dataset.icon || 'fa-solid fa-code';
            document.getElementById('svc-sort').value = btn.dataset.sort || '0';
            document.getElementById('svc-active').checked = btn.dataset.active !== '0';
            document.getElementById('svc-modal-title').textContent = id !== '0' ? 'Edit Service' : 'New Service';
        });
    }
});
</script>

<?php require_once 'admin_footer.php'; ?>
