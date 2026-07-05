<?php
$page_title = 'News & Updates';
$active_page = 'news';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../lib/News.php';

$newsObj = new News();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    if ($title === '') {
        $error_msg = 'Title is required.';
    } else {
        $image_path = null;
        if ($edit_id > 0) {
            $existing = $newsObj->getById($edit_id);
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
                $filename = uniqid('news_', true) . '.' . $ext;
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
                    $newsObj->update($edit_id, [
                        'title' => $title,
                        'content' => $content,
                        'image' => $image_path,
                        'author' => $author,
                        'is_published' => $is_published
                    ]);
                    $success_msg = 'News updated successfully!';
                } else {
                    $newsObj->create([
                        'title' => $title,
                        'content' => $content,
                        'image' => $image_path,
                        'author' => $author,
                        'is_published' => $is_published
                    ]);
                    $success_msg = 'News added successfully!';
                }
            } catch (Exception $e) {
                error_log('news DB error: ' . $e->getMessage());
                $error_msg = 'Database error. Please try again.';
            }
        }
    }
}

$news_list = $newsObj->getAll();
require_once 'admin_header.php';
?>
<div class="page-header">
    <button class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#addNewsModal"><i class="bi bi-plus-lg"></i> Add News</button>
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
                <tr><th>ID</th><th>Image</th><th>Title</th><th>Author</th><th>Published</th><th>Created</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($news_list)): ?>
                    <tr><td colspan="7" class="text-muted text-center">No news found.</td></tr>
                <?php else: ?>
                    <?php foreach ($news_list as $n): ?>
                    <tr>
                        <td><?= $n['id'] ?></td>
                        <td>
                            <?php if ($n['image']): ?>
                            <img src="../<?= htmlspecialchars($n['image']) ?>" alt="" width="60" height="40" style="object-fit:cover;border-radius:4px;" onerror="this.src='https://via.placeholder.com/60x40/0F172A/00E5FF?text=NA'">
                            <?php else: ?>
                            <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($n['title']) ?></td>
                        <td><?= htmlspecialchars($n['author'] ?? '—') ?></td>
                        <td><?= $n['is_published'] ? '<span class="text-cyan"><i class="bi bi-check-circle-fill"></i></span>' : '<span class="text-muted"><i class="bi bi-x-circle"></i></span>' ?></td>
                        <td class="small text-muted"><?= date('M d, Y', strtotime($n['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-warning me-1 btn-edit-news"
                                data-id="<?= $n['id'] ?>"
                                data-title="<?= htmlspecialchars($n['title'], ENT_QUOTES) ?>"
                                data-content="<?= htmlspecialchars($n['content'] ?? '', ENT_QUOTES) ?>"
                                data-author="<?= htmlspecialchars($n['author'] ?? '', ENT_QUOTES) ?>"
                                data-image="<?= htmlspecialchars($n['image'] ?? '', ENT_QUOTES) ?>"
                                data-published="<?= $n['is_published'] ?>"
                                title="Edit"><i class="bi bi-pencil"></i></button>
                            <a href="toggle_news?id=<?= $n['id'] ?>" class="btn btn-sm btn-outline-cyan me-1" title="Toggle publish"><i class="bi bi-arrow-repeat"></i></a>
                            <a href="delete_news?id=<?= $n['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" data-type="news"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add News Modal -->
<div class="modal fade" id="addNewsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle text-cyan me-2"></i>Add News</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Author</label>
                            <input type="text" name="author" class="form-control" placeholder="Admin">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Image (optional)</label>
                            <input type="file" name="image" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Content</label>
                            <textarea name="content" rows="5" class="form-control"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_published" class="form-check-input" id="addIsPublished" checked>
                                <label class="form-check-label" for="addIsPublished">Published</label>
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

<!-- Edit News Modal -->
<div class="modal fade" id="editNewsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_id" id="edit-id" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil text-warning me-2"></i>Edit News</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="edit-title" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Author</label>
                            <input type="text" name="author" id="edit-author" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Current Image</label>
                            <div><img id="edit-image-preview" src="" alt="" height="60" style="border-radius:4px;object-fit:cover;" onerror="this.style.display='none'"></div>
                            <label class="form-label mt-2">New Image (optional)</label>
                            <input type="file" name="image" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Content</label>
                            <textarea name="content" id="edit-content" rows="5" class="form-control"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_published" class="form-check-input" id="editIsPublished">
                                <label class="form-check-label" for="editIsPublished">Published</label>
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
    $(document).on('click', '.btn-edit-news', function () {
        var id = $(this).data('id');
        $('#edit-id').val(id);
        $('#edit-title').val($(this).data('title'));
        $('#edit-content').val($(this).data('content'));
        $('#edit-author').val($(this).data('author'));
        var img = $(this).data('image');
        if (img) {
            $('#edit-image-preview').attr('src', '../' + img).show();
        } else {
            $('#edit-image-preview').hide();
        }
        $('#editIsPublished').prop('checked', $(this).data('published') == 1);
        $('#editNewsModal').modal('show');
    });
});
</script>

<?php require_once 'admin_footer.php'; ?>
