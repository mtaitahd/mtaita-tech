<?php
$page_title = 'Posters';
$active_page = 'posters';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_poster'])) {
    $errors = [];
    $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

    if (isset($_FILES['poster_image']) && $_FILES['poster_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $file = $_FILES['poster_image'];

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        } else {
            $mime = $file['type'];
        }

        if (!in_array($mime, $allowed)) {
            $errors[] = 'Invalid file type. Only PNG, JPG, and WEBP are allowed.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = 'File is too large. Maximum size is 5MB.';
        } else {
            $upload_dir = __DIR__ . '/../assets/img/uploads/posters/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('poster_', true) . '.' . $ext;
            $dest = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO posters (poster_title, poster_image_path, redirect_link, is_active) VALUES ('', ?, '', ?)");
                    $stmt->execute(['assets/img/uploads/posters/' . $filename, $is_active]);
                    $success_msg = 'Poster uploaded successfully.';
                } catch (Exception $e) {
                    error_log('add_poster DB error: ' . $e->getMessage());
                    $errors[] = 'Database error. Please try again.';
                }
            } else {
                $errors[] = 'Failed to move uploaded file. Please check directory permissions.';
            }
        }
    } elseif (empty($errors)) {
        $errors[] = 'Please select a poster image.';
    }

    if (!$success_msg && !empty($errors)) {
        $error_msg = implode(' ', $errors);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_poster'])) {
    $id = (int)($_POST['poster_id'] ?? 0);
    $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

    if ($id <= 0) {
        $error_msg = 'Invalid poster.';
    } else {
        $stmt = $pdo->prepare("SELECT poster_image_path FROM posters WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $existing = $stmt->fetch();

        if (!$existing) {
            $error_msg = 'Poster not found.';
        } else {
            $image_path = $existing['poster_image_path'];

            if (isset($_FILES['poster_image']) && $_FILES['poster_image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                $file = $_FILES['poster_image'];

                if (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);
                } else {
                    $mime = $file['type'];
                }

                if (!in_array($mime, $allowed)) {
                    $error_msg = 'Invalid file type. Only PNG, JPG, and WEBP.';
                } elseif ($file['size'] > 5 * 1024 * 1024) {
                    $error_msg = 'File too large. Max 5MB.';
                } else {
                    $upload_dir = __DIR__ . '/../assets/img/uploads/posters/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = uniqid('poster_', true) . '.' . $ext;
                    $dest = $upload_dir . $filename;

                    if (move_uploaded_file($file['tmp_name'], $dest)) {
                        $old_file = __DIR__ . '/../' . $existing['poster_image_path'];
                        if (file_exists($old_file) && is_file($old_file)) {
                            unlink($old_file);
                        }
                        $image_path = 'assets/img/uploads/posters/' . $filename;
                    } else {
                        $error_msg = 'Failed to upload image.';
                    }
                }
            }

            if (!$error_msg) {
                try {
                    $stmt = $pdo->prepare("UPDATE posters SET poster_image_path = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$image_path, $is_active, $id]);
                    $success_msg = 'Poster updated successfully.';
                } catch (Exception $e) {
                    error_log('update_poster DB error: ' . $e->getMessage());
                    $error_msg = 'Database error. Please try again.';
                }
            }
        }
    }
}

$posters = $pdo->query("SELECT id, poster_image_path, is_active, created_at FROM posters ORDER BY created_at DESC")->fetchAll();
require_once 'admin_header.php';
?>
<div class="page-header">
    <button class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#addPosterModal"><i class="bi bi-plus-lg"></i> Add Poster</button>
</div>

<?php if ($success_msg): ?>
<div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div>
<?php endif; ?>
<?php if ($error_msg): ?>
<div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div>
<?php endif; ?>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr><th>ID</th><th>Preview</th><th>Status</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($posters)): ?>
                    <tr><td colspan="5" class="text-muted text-center">No posters found.</td></tr>
                <?php else: ?>
                    <?php foreach ($posters as $po): ?>
                    <tr>
                        <td><?= $po['id'] ?></td>
                        <td><img src="../<?= htmlspecialchars($po['poster_image_path']) ?>" alt="" width="80" height="40" style="object-fit:cover;border-radius:4px;" onerror="this.src='https://via.placeholder.com/80x40/0F172A/00E5FF?text=NA'"></td>
                        <td><?= $po['is_active'] ? '<span class="text-cyan">Active</span>' : '<span class="text-muted">Inactive</span>' ?></td>
                        <td class="small text-muted"><?= date('M d, Y', strtotime($po['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-cyan me-1 btn-edit-poster" data-id="<?= $po['id'] ?>" data-active="<?= $po['is_active'] ?>" title="Edit"><i class="bi bi-pencil"></i></button>
                            <a href="toggle_poster?id=<?= $po['id'] ?>" class="btn btn-sm btn-outline-cyan me-1" title="Toggle"><i class="bi bi-arrow-repeat"></i></a>
                            <a href="delete_poster?id=<?= $po['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" data-type="poster"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addPosterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" action="posters">
                <input type="hidden" name="add_poster" value="1">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle text-cyan me-2"></i>Add New Poster</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Poster Image</label>
                            <input type="file" name="poster_image" class="form-control" accept="image/png,image/jpeg,image/webp" required>
                            <small class="text-muted">PNG, JPG, WEBP. Max 5MB.</small><br>
                            <small class="text-info"><i class="bi bi-info-circle"></i> Recommended: <strong>1920 × 1080</strong> px (16:9 landscape). This fits the hero slider perfectly.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cyan"><i class="bi bi-upload"></i> Upload Poster</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editPosterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" action="posters">
                <input type="hidden" name="update_poster" value="1">
                <input type="hidden" name="poster_id" id="edit_poster_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square text-cyan me-2"></i>Edit Poster</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Poster Image <small class="text-muted">(leave empty to keep existing)</small></label>
                            <input type="file" name="poster_image" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <small class="text-muted">PNG, JPG, WEBP. Max 5MB.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Status</label>
                            <select name="is_active" id="edit_poster_active" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cyan"><i class="bi bi-save"></i> Update Poster</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.btn-edit-poster').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('edit_poster_id').value = this.dataset.id;
        document.getElementById('edit_poster_active').value = this.dataset.active;
        var modal = new bootstrap.Modal(document.getElementById('editPosterModal'));
        modal.show();
    });
});
</script>

<?php require_once 'admin_footer.php'; ?>
