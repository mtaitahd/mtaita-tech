<?php
$page_title = 'Partners';
$active_page = 'partners';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../lib/Partner.php';

$partnerObj = new Partner();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $website_url = trim($_POST['website_url'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($name === '') {
        $error_msg = 'Partner name is required.';
    } else {
        $logo_path = null;
        if ($edit_id > 0) {
            $existing = $partnerObj->getById($edit_id);
            $logo_path = $existing['logo'] ?? null;
        }

        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
            $file = $_FILES['logo'];

            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            } else {
                $mime = $file['type'];
            }

            if (!in_array($mime, $allowed)) {
                $error_msg = 'Invalid file type. Only PNG, JPG, WEBP, GIF, and SVG are allowed.';
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $error_msg = 'File is too large. Maximum size is 2MB.';
            } else {
                $upload_dir = __DIR__ . '/../assets/img/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('partner_', true) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                    if ($logo_path) {
                        $old_path = __DIR__ . '/../' . $logo_path;
                        if (file_exists($old_path)) @unlink($old_path);
                    }
                    $logo_path = 'assets/img/uploads/' . $filename;
                } else {
                    $error_msg = 'Failed to upload file.';
                }
            }
        }

        if (!$error_msg) {
            try {
                if ($edit_id > 0) {
                    $partnerObj->update($edit_id, [
                        'name' => $name,
                        'logo' => $logo_path,
                        'website_url' => $website_url,
                        'sort_order' => $sort_order,
                        'is_active' => $is_active
                    ]);
                    $success_msg = 'Partner updated successfully!';
                } else {
                    $partnerObj->create([
                        'name' => $name,
                        'logo' => $logo_path,
                        'website_url' => $website_url,
                        'sort_order' => $sort_order,
                        'is_active' => $is_active
                    ]);
                    $success_msg = 'Partner added successfully!';
                }
            } catch (Exception $e) {
                error_log('partner DB error: ' . $e->getMessage());
                $error_msg = 'Database error. Please try again.';
            }
        }
    }
}

$partners = $partnerObj->getAll();
require_once 'admin_header.php';
?>
<div class="page-header">
    <button class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#addPartnerModal"><i class="bi bi-plus-lg"></i> Add Partner</button>
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
                <tr><th>ID</th><th>Logo</th><th>Name</th><th>Website</th><th>Order</th><th>Active</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($partners)): ?>
                    <tr><td colspan="7" class="text-muted text-center">No partners found.</td></tr>
                <?php else: ?>
                    <?php foreach ($partners as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td>
                            <?php if ($p['logo']): ?>
                            <img src="../<?= htmlspecialchars($p['logo']) ?>" alt="" width="60" height="40" style="object-fit:contain;border-radius:4px;" onerror="this.src='https://via.placeholder.com/60x40/0F172A/00E5FF?text=NA'">
                            <?php else: ?>
                            <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= $p['website_url'] ? '<a href="' . htmlspecialchars($p['website_url']) . '" target="_blank" class="text-cyan small">' . htmlspecialchars(parse_url($p['website_url'], PHP_URL_HOST) ?: $p['website_url']) . '</a>' : '<span class="text-muted">&mdash;</span>' ?></td>
                        <td><?= $p['sort_order'] ?></td>
                        <td><?= $p['is_active'] ? '<span class="text-cyan"><i class="bi bi-check-circle-fill"></i></span>' : '<span class="text-muted"><i class="bi bi-x-circle"></i></span>' ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-warning me-1 btn-edit-partner"
                                data-id="<?= $p['id'] ?>"
                                data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>"
                                data-website="<?= htmlspecialchars($p['website_url'] ?? '', ENT_QUOTES) ?>"
                                data-order="<?= $p['sort_order'] ?>"
                                data-logo="<?= htmlspecialchars($p['logo'] ?? '', ENT_QUOTES) ?>"
                                data-active="<?= $p['is_active'] ?>"
                                title="Edit"><i class="bi bi-pencil"></i></button>
                            <a href="toggle_partner?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-cyan me-1" title="Toggle active"><i class="bi bi-arrow-repeat"></i></a>
                            <a href="delete_partner?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" data-type="partner"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Partner Modal -->
<div class="modal fade" id="addPartnerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle text-cyan me-2"></i>Add Partner</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Partner Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Website URL</label>
                            <input type="url" name="website_url" class="form-control" placeholder="https://example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Logo</label>
                            <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml">
                            <small class="text-muted">PNG, JPG, WEBP, GIF, SVG. Max 2MB.</small>
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

<!-- Edit Partner Modal -->
<div class="modal fade" id="editPartnerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_id" id="edit-id" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil text-warning me-2"></i>Edit Partner</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Partner Name</label>
                            <input type="text" name="name" id="edit-name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Website URL</label>
                            <input type="url" name="website_url" id="edit-website" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="edit-order" class="form-control" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Current Logo</label>
                            <div><img id="edit-logo-preview" src="" alt="" height="40" style="border-radius:4px;object-fit:contain;" onerror="this.style.display='none'"></div>
                            <label class="form-label mt-2">New Logo (optional)</label>
                            <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml">
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
    $(document).on('click', '.btn-edit-partner', function () {
        var id = $(this).data('id');
        $('#edit-id').val(id);
        $('#edit-name').val($(this).data('name'));
        $('#edit-website').val($(this).data('website'));
        $('#edit-order').val($(this).data('order'));
        var logo = $(this).data('logo');
        if (logo) {
            $('#edit-logo-preview').attr('src', '../' + logo).show();
        } else {
            $('#edit-logo-preview').hide();
        }
        $('#editIsActive').prop('checked', $(this).data('active') == 1);
        $('#editPartnerModal').modal('show');
    });
});
</script>

<?php require_once 'admin_footer.php'; ?>
