<?php
$page_title = 'Solutions';
$active_page = 'solutions';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../lib/Solution.php';

$solutionObj = new Solution();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($title === '') {
        $error_msg = 'Title is required.';
    } else {
        $image_path = null;
        if ($edit_id > 0) {
            $existing = $solutionObj->getById($edit_id);
            $image_path = $existing['image'] ?? null;
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file = $_FILES['image'];

            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            } else {
                $mime = $file['type'];
            }

            if (!in_array($mime, $allowed)) {
                $error_msg = 'Invalid file type. Only PNG, JPG, WEBP, and GIF are allowed.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $error_msg = 'File is too large. Maximum size is 5MB.';
            } else {
                $upload_dir = __DIR__ . '/../assets/img/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('solution_', true) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                    if ($image_path) {
                        $old_path = __DIR__ . '/../' . $image_path;
                        if (file_exists($old_path)) @unlink($old_path);
                    }
                    $image_path = 'assets/img/uploads/' . $filename;
                } else {
                    $error_msg = 'Failed to upload file.';
                }
            }
        }

        if (!$error_msg) {
            try {
                if ($edit_id > 0) {
                    $solutionObj->update($edit_id, [
                        'title' => $title,
                        'description' => $description,
                        'icon' => $icon,
                        'image' => $image_path,
                        'link' => $link,
                        'sort_order' => $sort_order,
                        'is_active' => $is_active
                    ]);
                    $success_msg = 'Solution updated successfully!';
                } else {
                    $solutionObj->create([
                        'title' => $title,
                        'description' => $description,
                        'icon' => $icon,
                        'image' => $image_path,
                        'link' => $link,
                        'sort_order' => $sort_order,
                        'is_active' => $is_active
                    ]);
                    $success_msg = 'Solution added successfully!';
                }
            } catch (Exception $e) {
                error_log('solution DB error: ' . $e->getMessage());
                $error_msg = 'Database error. Please try again.';
            }
        }
    }
}

$solutions = $solutionObj->getAll();
require_once 'admin_header.php';
?>
<div class="page-header">
    <button class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#addSolutionModal"><i class="bi bi-plus-lg"></i> Add Solution</button>
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
                <tr><th>ID</th><th>Image</th><th>Title</th><th>Icon</th><th>Order</th><th>Active</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($solutions)): ?>
                    <tr><td colspan="7" class="text-muted text-center">No solutions found.</td></tr>
                <?php else: ?>
                    <?php foreach ($solutions as $s): ?>
                    <tr>
                        <td><?= $s['id'] ?></td>
                        <td>
                            <?php if ($s['image']): ?>
                            <img src="../<?= htmlspecialchars($s['image']) ?>" alt="" width="60" height="40" style="object-fit:cover;border-radius:4px;" onerror="this.src='https://via.placeholder.com/60x40/0F172A/00E5FF?text=NA'">
                            <?php else: ?>
                            <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($s['title']) ?></td>
                        <td><?= $s['icon'] ? '<i class="bi bi-' . htmlspecialchars($s['icon']) . '"></i>' : '<span class="text-muted">&mdash;</span>' ?></td>
                        <td><?= $s['sort_order'] ?></td>
                        <td><?= $s['is_active'] ? '<span class="text-cyan"><i class="bi bi-check-circle-fill"></i></span>' : '<span class="text-muted"><i class="bi bi-x-circle"></i></span>' ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-warning me-1 btn-edit-solution"
                                data-id="<?= $s['id'] ?>"
                                data-title="<?= htmlspecialchars($s['title'], ENT_QUOTES) ?>"
                                data-desc="<?= htmlspecialchars($s['description'] ?? '', ENT_QUOTES) ?>"
                                data-icon="<?= htmlspecialchars($s['icon'] ?? '', ENT_QUOTES) ?>"
                                data-link="<?= htmlspecialchars($s['link'] ?? '', ENT_QUOTES) ?>"
                                data-order="<?= $s['sort_order'] ?>"
                                data-image="<?= htmlspecialchars($s['image'] ?? '', ENT_QUOTES) ?>"
                                data-active="<?= $s['is_active'] ?>"
                                title="Edit"><i class="bi bi-pencil"></i></button>
                            <a href="toggle_solution?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-cyan me-1" title="Toggle active"><i class="bi bi-arrow-repeat"></i></a>
                            <a href="delete_solution?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" data-type="solution"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Solution Modal -->
<div class="modal fade" id="addSolutionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle text-cyan me-2"></i>Add Solution</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bootstrap Icon class (e.g. 'gear', 'laptop')</label>
                            <input type="text" name="icon" class="form-control" placeholder="gear">
                            <small class="text-muted">bi bi-<strong>icon-name</strong></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Link (optional)</label>
                            <input type="url" name="link" class="form-control" placeholder="https://example.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="4" class="form-control"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Image (optional)</label>
                            <input type="file" name="image" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif">
                            <small class="text-muted">PNG, JPG, WEBP, GIF. Max 5MB.</small>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="addIsActive" checked>
                                <label class="form-check-label" for="addIsActive">Active</label>
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

<!-- Edit Solution Modal -->
<div class="modal fade" id="editSolutionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_id" id="edit-id" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil text-warning me-2"></i>Edit Solution</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="edit-title" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="edit-order" class="form-control" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bootstrap Icon</label>
                            <input type="text" name="icon" id="edit-icon" class="form-control" placeholder="gear">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Link</label>
                            <input type="url" name="link" id="edit-link" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit-desc" rows="4" class="form-control"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Current Image</label>
                            <div><img id="edit-image-preview" src="" alt="" height="60" style="border-radius:4px;object-fit:cover;" onerror="this.style.display='none'"></div>
                            <label class="form-label mt-2">New Image (optional)</label>
                            <input type="file" name="image" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="editIsActive">
                                <label class="form-check-label" for="editIsActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-check-lg"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    $(document).on('click', '.btn-edit-solution', function () {
        var id = $(this).data('id');
        $('#edit-id').val(id);
        $('#edit-title').val($(this).data('title'));
        $('#edit-desc').val($(this).data('desc'));
        $('#edit-icon').val($(this).data('icon'));
        $('#edit-link').val($(this).data('link'));
        $('#edit-order').val($(this).data('order'));
        var img = $(this).data('image');
        if (img) {
            $('#edit-image-preview').attr('src', '../' + img).show();
        } else {
            $('#edit-image-preview').hide();
        }
        $('#editIsActive').prop('checked', $(this).data('active') == 1);
        $('#editSolutionModal').modal('show');
    });
});
</script>

<?php require_once 'admin_footer.php'; ?>
