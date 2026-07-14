<?php
$page_title = 'Testimonials';
$active_page = 'testimonials';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../lib/Testimonial.php';

$testimonialObj = new Testimonial();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $read_more_url = trim($_POST['read_more_url'] ?? '');
    $rating = (int)($_POST['rating'] ?? 5);
    $is_approved = isset($_POST['is_approved']) ? 1 : 0;

    if ($name === '' || $content === '') {
        $error_msg = 'Name and content are required.';
    } else {
        $avatar_path = null;
        if ($edit_id > 0) {
            $existing = $testimonialObj->getById($edit_id);
            $avatar_path = $existing['avatar'] ?? null;
        }

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file = $_FILES['avatar'];

            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            } else {
                $mime = $file['type'];
            }

            if (!in_array($mime, $allowed)) {
                $error_msg = 'Invalid file type. Only PNG, JPG, WEBP, and GIF are allowed.';
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $error_msg = 'File is too large. Maximum size is 2MB.';
            } else {
                $upload_dir = __DIR__ . '/../assets/img/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('testimonial_', true) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                    if ($avatar_path) {
                        $old_path = __DIR__ . '/../' . $avatar_path;
                        if (file_exists($old_path)) @unlink($old_path);
                    }
                    $avatar_path = 'assets/img/uploads/' . $filename;
                } else {
                    $error_msg = 'Failed to upload file.';
                }
            }
        }

        if (!$error_msg) {
            try {
                if ($edit_id > 0) {
                    $testimonialObj->update($edit_id, [
                        'name' => $name,
                        'position' => $position,
                        'company' => $company,
                        'avatar' => $avatar_path,
                        'content' => $content,
                        'read_more_url' => $read_more_url,
                        'rating' => $rating,
                        'is_approved' => $is_approved
                    ]);
                    $success_msg = 'Testimonial updated successfully!';
                } else {
                    $testimonialObj->create([
                        'name' => $name,
                        'position' => $position,
                        'company' => $company,
                        'avatar' => $avatar_path,
                        'content' => $content,
                        'read_more_url' => $read_more_url,
                        'rating' => $rating,
                        'is_approved' => $is_approved
                    ]);
                    $success_msg = 'Testimonial added successfully!';
                }
            } catch (Exception $e) {
                error_log('testimonial DB error: ' . $e->getMessage());
                $error_msg = 'Database error. Please try again.';
            }
        }
    }
}

$testimonials = $testimonialObj->getAll();
require_once 'admin_header.php';
?>
<div class="page-header">
    <button class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#addTestimonialModal"><i class="bi bi-plus-lg"></i> Add Testimonial</button>
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
                <tr><th>ID</th><th>Avatar</th><th>Name</th><th>Position</th><th>Company</th><th>Read More URL</th><th>Rating</th><th>Approved</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($testimonials)): ?>
                    <tr><td colspan="9" class="text-muted text-center">No testimonials found.</td></tr>
                <?php else: ?>
                    <?php foreach ($testimonials as $t): ?>
                    <tr>
                        <td><?= $t['id'] ?></td>
                        <td>
                            <?php if ($t['avatar']): ?>
                            <img src="../<?= htmlspecialchars($t['avatar']) ?>" alt="" width="40" height="40" style="object-fit:cover;border-radius:50%;" onerror="this.src='https://via.placeholder.com/40x40/0F172A/00E5FF?text=NA'">
                            <?php else: ?>
                            <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($t['name']) ?></td>
                        <td><?= htmlspecialchars($t['position'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($t['company'] ?? '—') ?></td>
                        <td><?= $t['read_more_url'] ? '<a href="'.htmlspecialchars($t['read_more_url']).'" target="_blank" class="text-cyan text-truncate d-inline-block" style="max-width:180px;" title="'.htmlspecialchars($t['read_more_url']).'">'.htmlspecialchars($t['read_more_url']).'</a>' : '<span class="text-muted">Default</span>' ?></td>
                        <td>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?= $i <= $t['rating'] ? '-fill text-warning' : ' text-muted' ?>"></i>
                            <?php endfor; ?>
                        </td>
                        <td><?= $t['is_approved'] ? '<span class="text-cyan"><i class="bi bi-check-circle-fill"></i></span>' : '<span class="text-muted"><i class="bi bi-x-circle"></i></span>' ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-warning me-1 btn-edit-testimonial"
                                data-id="<?= $t['id'] ?>"
                                data-name="<?= htmlspecialchars($t['name'], ENT_QUOTES) ?>"
                                data-position="<?= htmlspecialchars($t['position'] ?? '', ENT_QUOTES) ?>"
                                data-company="<?= htmlspecialchars($t['company'] ?? '', ENT_QUOTES) ?>"
                                data-content="<?= htmlspecialchars($t['content'], ENT_QUOTES) ?>"
                                data-read-more-url="<?= htmlspecialchars($t['read_more_url'] ?? '', ENT_QUOTES) ?>"
                                data-rating="<?= $t['rating'] ?>"
                                data-avatar="<?= htmlspecialchars($t['avatar'] ?? '', ENT_QUOTES) ?>"
                                data-approved="<?= $t['is_approved'] ?>"
                                title="Edit"><i class="bi bi-pencil"></i></button>
                            <a href="toggle_testimonial?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-cyan me-1" title="Toggle approval"><i class="bi bi-arrow-repeat"></i></a>
                            <a href="delete_testimonial?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" data-type="testimonial"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Testimonial Modal -->
<div class="modal fade" id="addTestimonialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle text-cyan me-2"></i>Add Testimonial</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rating</label>
                            <select name="rating" class="form-control">
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Position</label>
                            <input type="text" name="position" class="form-control" placeholder="e.g. CEO">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company</label>
                            <input type="text" name="company" class="form-control" placeholder="Company Name">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Content</label>
                            <textarea name="content" rows="4" class="form-control" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Read More URL (optional)</label>
                            <input type="url" name="read_more_url" class="form-control" placeholder="https://g.page/r/... or any review link">
                            <small class="text-muted">Leave empty to use the default Google review link.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Avatar (optional)</label>
                            <input type="file" name="avatar" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif">
                            <small class="text-muted">PNG, JPG, WEBP, GIF. Max 2MB.</small>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_approved" class="form-check-input" id="addIsApproved" checked>
                                <label class="form-check-label" for="addIsApproved">Approved</label>
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

<!-- Edit Testimonial Modal -->
<div class="modal fade" id="editTestimonialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_id" id="edit-id" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil text-warning me-2"></i>Edit Testimonial</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="edit-name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rating</label>
                            <select name="rating" id="edit-rating" class="form-control">
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Position</label>
                            <input type="text" name="position" id="edit-position" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company</label>
                            <input type="text" name="company" id="edit-company" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Content</label>
                            <textarea name="content" id="edit-content" rows="4" class="form-control" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Read More URL (optional)</label>
                            <input type="url" name="read_more_url" id="edit-read-more-url" class="form-control" placeholder="https://g.page/r/... or any review link">
                            <small class="text-muted">Leave empty to use the default Google review link.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Current Avatar</label>
                            <div><img id="edit-avatar-preview" src="" alt="" height="50" width="50" style="border-radius:50%;object-fit:cover;" onerror="this.style.display='none'"></div>
                            <label class="form-label mt-2">New Avatar (optional)</label>
                            <input type="file" name="avatar" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_approved" class="form-check-input" id="editIsApproved">
                                <label class="form-check-label" for="editIsApproved">Approved</label>
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
    $(document).on('click', '.btn-edit-testimonial', function () {
        var id = $(this).data('id');
        $('#edit-id').val(id);
        $('#edit-name').val($(this).data('name'));
        $('#edit-position').val($(this).data('position'));
        $('#edit-company').val($(this).data('company'));
        $('#edit-content').val($(this).data('content'));
        $('#edit-read-more-url').val($(this).data('read-more-url'));
        $('#edit-rating').val($(this).data('rating'));
        var avatar = $(this).data('avatar');
        if (avatar) {
            $('#edit-avatar-preview').attr('src', '../' + avatar).show();
        } else {
            $('#edit-avatar-preview').hide();
        }
        $('#editIsApproved').prop('checked', $(this).data('approved') == 1);
        $('#editTestimonialModal').modal('show');
    });
});
</script>

<?php require_once 'admin_footer.php'; ?>
