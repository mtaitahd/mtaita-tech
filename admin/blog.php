<?php
$page_title = 'Blog Posts';
$active_page = 'blog';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';

try {
    $cols = $pdo->query("SHOW COLUMNS FROM blogs")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('description', $cols)) $pdo->exec("ALTER TABLE blogs ADD COLUMN description TEXT AFTER slug");
    if (!in_array('author', $cols)) $pdo->exec("ALTER TABLE blogs ADD COLUMN author VARCHAR(100) DEFAULT 'Admin' AFTER feature_image");
    if (!in_array('is_published', $cols)) $pdo->exec("ALTER TABLE blogs ADD COLUMN is_published TINYINT(1) DEFAULT 1 AFTER author");
    if (!in_array('updated_at', $cols)) $pdo->exec("ALTER TABLE blogs ADD COLUMN updated_at DATETIME DEFAULT NULL AFTER created_at");
} catch (Exception $e) {
    error_log('blog migration: ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $content = $_POST['content'] ?? '';
    $author = trim($_POST['author'] ?? 'Admin');
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    if ($title === '') {
        $error_msg = 'Title is required.';
    } else {
        $feature_image = null;
        if ($edit_id > 0) {
            $stmt = $pdo->prepare("SELECT feature_image FROM blogs WHERE id = ?");
            $stmt->execute([$edit_id]);
            $existing = $stmt->fetch();
            $feature_image = $existing['feature_image'] ?? null;
        }

        if (isset($_FILES['feature_image']) && $_FILES['feature_image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file = $_FILES['feature_image'];

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
                $upload_dir = __DIR__ . '/../assets/img/uploads/blog/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('blog_', true) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                    if ($feature_image && strpos($feature_image, 'blog/') !== false) {
                        $old_path = __DIR__ . '/../' . $feature_image;
                        if (file_exists($old_path)) @unlink($old_path);
                    }
                    $feature_image = 'assets/img/uploads/blog/' . $filename;
                } else {
                    $error_msg = 'Failed to upload file.';
                }
            }
        }

        if (!$error_msg) {
            try {
                $slug = $edit_id > 0 ? '' : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

                if ($edit_id > 0) {
                    $stmt = $pdo->prepare("UPDATE blogs SET title = ?, slug = ?, description = ?, content = ?, feature_image = ?, author = ?, is_published = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$title, $slug ?: null, $description, $content, $feature_image, $author, $is_published, $edit_id]);
                    $success_msg = 'Blog post updated successfully!';
                } else {
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
                    $check = $pdo->prepare("SELECT id FROM blogs WHERE slug = ?");
                    $check->execute([$slug]);
                    if ($check->fetch()) {
                        $slug .= '-' . time();
                    }
                    $stmt = $pdo->prepare("INSERT INTO blogs (title, slug, description, content, feature_image, author, is_published) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $slug, $description, $content, $feature_image, $author, $is_published]);
                    $success_msg = 'Blog post added successfully!';
                }
            } catch (Exception $e) {
                error_log('blog DB error: ' . $e->getMessage());
                $error_msg = 'Database error. Please try again.';
            }
        }
    }
}

$posts = $pdo->query("SELECT id, title, slug, description, feature_image, author, is_published, created_at, updated_at FROM blogs ORDER BY created_at DESC")->fetchAll();
require_once 'admin_header.php';
?>
<div class="page-header">
    <button class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#addBlogModal"><i class="bi bi-plus-lg"></i> Add Blog Post</button>
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
                <tr><th>ID</th><th>Thumb</th><th>Title</th><th>Author</th><th>Published</th><th>Created</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                    <tr><td colspan="7" class="text-muted text-center">No blog posts found.</td></tr>
                <?php else: ?>
                    <?php foreach ($posts as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td>
                            <?php if ($p['feature_image']): ?>
                            <img src="../<?= htmlspecialchars(webp_url($p['feature_image'])) ?>" alt="" width="60" height="40" style="object-fit:cover;border-radius:4px;" onerror="this.src='https://via.placeholder.com/60x40/0F172A/00E5FF?text=NA'">
                            <?php else: ?>
                            <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($p['title']) ?>
                            <?php if ($p['description']): ?>
                            <br><small class="text-muted"><?= htmlspecialchars(mb_strimwidth($p['description'], 0, 60, '...')) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['author'] ?? '—') ?></td>
                        <td><?= $p['is_published'] ? '<span class="text-cyan"><i class="bi bi-check-circle-fill"></i></span>' : '<span class="text-muted"><i class="bi bi-x-circle"></i></span>' ?></td>
                        <td class="small text-muted"><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-warning me-1 btn-edit-blog"
                                data-id="<?= $p['id'] ?>"
                                data-title="<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>"
                                data-description="<?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES) ?>"
                                data-content="<?= htmlspecialchars($p['content'] ?? '', ENT_QUOTES) ?>"
                                data-author="<?= htmlspecialchars($p['author'] ?? '', ENT_QUOTES) ?>"
                                data-image="<?= htmlspecialchars($p['feature_image'] ?? '', ENT_QUOTES) ?>"
                                data-published="<?= $p['is_published'] ?>"
                                title="Edit"><i class="bi bi-pencil"></i></button>
                            <a href="delete_blog?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" data-type="blog post"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Blog Modal -->
<div class="modal fade" id="addBlogModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle text-cyan me-2"></i>Add Blog Post</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Author</label>
                            <input type="text" name="author" class="form-control" value="Admin">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Short Description</label>
                            <input type="text" name="description" class="form-control" placeholder="Brief summary of the post (shown in listing)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Feature Image (Thumbnail)</label>
                            <input type="file" name="feature_image" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif">
                            <small class="text-muted">PNG, JPG, WEBP, GIF. Max 5MB. Recommended: 1200x630px</small>
                        </div>
                        <div class="col-md-6 d-flex align-items-end pb-2">
                            <div class="form-check">
                                <input type="checkbox" name="is_published" class="form-check-input" id="addIsPublished" checked>
                                <label class="form-check-label" for="addIsPublished">Published</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Content *</label>
                            <textarea name="content" id="add-content" rows="12" class="form-control" placeholder="Write your blog post content here. HTML is supported."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cyan"><i class="bi bi-save"></i> Publish</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Blog Modal -->
<div class="modal fade" id="editBlogModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_id" id="edit-id" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil text-warning me-2"></i>Edit Blog Post</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" id="edit-title" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Author</label>
                            <input type="text" name="author" id="edit-author" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Short Description</label>
                            <input type="text" name="description" id="edit-description" class="form-control" placeholder="Brief summary of the post">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Current Thumbnail</label>
                            <div><img id="edit-image-preview" src="" alt="" height="80" style="border-radius:6px;object-fit:cover;" onerror="this.style.display='none'"></div>
                            <label class="form-label mt-2">New Thumbnail (optional)</label>
                            <input type="file" name="feature_image" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif">
                            <small class="text-muted">Leave empty to keep current image.</small>
                        </div>
                        <div class="col-md-6 d-flex align-items-end pb-2">
                            <div class="form-check">
                                <input type="checkbox" name="is_published" class="form-check-input" id="editIsPublished">
                                <label class="form-check-label" for="editIsPublished">Published</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Content *</label>
                            <textarea name="content" id="edit-content" rows="12" class="form-control"></textarea>
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
    $(document).on('click', '.btn-edit-blog', function () {
        var btn = $(this);
        $('#edit-id').val(btn.data('id'));
        $('#edit-title').val(btn.data('title'));
        $('#edit-description').val(btn.data('description'));
        $('#edit-content').val(btn.data('content'));
        $('#edit-author').val(btn.data('author'));
        var img = btn.data('image');
        if (img) {
            $('#edit-image-preview').attr('src', '../' + img).show();
        } else {
            $('#edit-image-preview').hide();
        }
        $('#editIsPublished').prop('checked', btn.data('published') == 1);
        $('#editBlogModal').modal('show');
    });
});
</script>

<?php require_once 'admin_footer.php'; ?>
