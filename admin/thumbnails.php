<?php
$page_title = 'Thumbnails';
$active_page = 'thumbnails';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_thumbnail'])) {
    $title = trim($_POST['thumbnail_title'] ?? '');
    $category = trim($_POST['video_category'] ?? '');

    $errors = [];
    if ($title === '') $errors[] = 'Thumbnail title is required.';

    if (empty($errors) && isset($_FILES['thumbnail_image']) && $_FILES['thumbnail_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $file = $_FILES['thumbnail_image'];

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
            $upload_dir = __DIR__ . '/../assets/img/uploads/thumbnails/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('thumb_', true) . '.' . $ext;
            $dest = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO thumbnails (thumbnail_title, thumbnail_image_path, video_category) VALUES (?, ?, ?)");
                    $stmt->execute([$title, 'assets/img/uploads/thumbnails/' . $filename, $category ?: null]);
                    $success_msg = 'Thumbnail uploaded successfully.';
                } catch (Exception $e) {
                    error_log('add_thumbnail DB error: ' . $e->getMessage());
                    $errors[] = 'Database error. Please try again.';
                }
            } else {
                $errors[] = 'Failed to move uploaded file. Please check directory permissions.';
            }
        }
    } elseif (empty($errors)) {
        $errors[] = 'Please select a thumbnail image.';
    }

    if (!$success_msg && !empty($errors)) {
        $error_msg = implode(' ', $errors);
    }
}

$thumbnails = $pdo->query("SELECT id, thumbnail_title, thumbnail_image_path, video_category, created_at FROM thumbnails ORDER BY created_at DESC")->fetchAll();
require_once 'admin_header.php';
?>
<div class="page-header">
    <button class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#addThumbnailModal"><i class="bi bi-plus-lg"></i> Add Thumbnail</button>
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
                <tr><th>ID</th><th>Preview</th><th>Title</th><th>Category</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($thumbnails)): ?>
                    <tr><td colspan="6" class="text-muted text-center">No thumbnails found.</td></tr>
                <?php else: ?>
                    <?php foreach ($thumbnails as $th): ?>
                    <tr>
                        <td><?= $th['id'] ?></td>
                        <td><img src="../<?= htmlspecialchars($th['thumbnail_image_path']) ?>" alt="" width="80" height="45" style="object-fit:cover;border-radius:4px;" onerror="this.src='https://via.placeholder.com/80x45/0F172A/00E5FF?text=NA'"></td>
                        <td><?= htmlspecialchars($th['thumbnail_title']) ?></td>
                        <td class="small text-muted"><?= htmlspecialchars($th['video_category'] ?? '-') ?></td>
                        <td class="small text-muted"><?= date('M d, Y', strtotime($th['created_at'])) ?></td>
                        <td>
                            <a href="delete_thumbnail?id=<?= $th['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" data-type="thumbnail"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addThumbnailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_thumbnail" value="1">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle text-cyan me-2"></i>Add New Thumbnail</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Thumbnail Title</label>
                            <input type="text" name="thumbnail_title" class="form-control" placeholder="e.g. Web Dev Tutorial #12" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Category (optional)</label>
                            <input type="text" name="video_category" class="form-control" placeholder="e.g. Web Development, Design Tips">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Thumbnail Image (16:9)</label>
                            <input type="file" name="thumbnail_image" class="form-control" accept="image/png,image/jpeg,image/webp" required>
                            <small class="text-muted">Recommended: 1280x720px. PNG, JPG, WEBP. Max 5MB.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cyan"><i class="bi bi-upload"></i> Upload Thumbnail</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>
