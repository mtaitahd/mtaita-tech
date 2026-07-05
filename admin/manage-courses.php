<?php
$page_title = 'Manage Courses';
$active_page = 'courses';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_course'])) {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = $_POST['type'] === 'premium' ? 'premium' : 'free';
    $price = $type === 'premium' ? (int)($_POST['price'] ?? 0) : 0;
    $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived']) ? $_POST['status'] : 'published';
    $featured = isset($_POST['featured']) ? 1 : 0;

    $thumbnail = $_POST['existing_thumbnail'] ?? '';

    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $file = $_FILES['thumbnail'];
        $mime = function_exists('finfo_open') ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file['tmp_name']) : $file['type'];
        if (in_array($mime, $allowed) && $file['size'] <= 2 * 1024 * 1024) {
            $upload_dir = __DIR__ . '/../assets/img/uploads/courses/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'course_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                if ($thumbnail) @unlink(__DIR__ . '/../' . $thumbnail);
                $thumbnail = 'assets/img/uploads/courses/' . $filename;
            }
        }
    }

    if ($title && $slug && $description) {
        $slug = preg_replace('/[^a-z0-9_-]/i', '-', strtolower($slug));
        $slug = trim($slug, '-');
        $wasPublished = false;
        try {
            if ($id > 0) {
                $oldStmt = $pdo->prepare("SELECT status FROM courses WHERE id = ?");
                $oldStmt->execute([$id]);
                $oldCourse = $oldStmt->fetch();
                $wasPublished = $oldCourse && $oldCourse['status'] === 'published';
                $stmt = $pdo->prepare("UPDATE courses SET title = ?, slug = ?, description = ?, type = ?, price = ?, thumbnail = ?, status = ?, featured = ? WHERE id = ?");
                $stmt->execute([$title, $slug, $description, $type, $price, $thumbnail, $status, $featured, $id]);
                $success_msg = 'Course updated!';
            } else {
                $stmt = $pdo->prepare("INSERT INTO courses (title, slug, description, type, price, thumbnail, status, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $slug, $description, $type, $price, $thumbnail, $status, $featured]);
                $success_msg = 'Course added!';
            }
        } catch (Exception $e) {
            error_log('save_course error: ' . $e->getMessage());
            $error_msg = 'Database error. Slug might already exist.';
        }

        if ($status === 'published' && !$wasPublished && isset($_POST['notify_users'])) {
            require_once __DIR__ . '/../lib/SMS.php';
            try {
                $phoneStmt = $pdo->query("SELECT phone FROM public_users WHERE phone IS NOT NULL AND phone != ''");
                $phones = $phoneStmt->fetchAll(PDO::FETCH_COLUMN);
                if (!empty($phones)) {
                    $sms = new SMS();
                    $siteName = defined('SITE_NAME') ? SITE_NAME : 'Mtaita Tech';
                    $courseUrl = rtrim(defined('SITE_URL') ? SITE_URL : 'https://mtaitatech.online', '/') . '/courses.php';
                    $msg = "$siteName: New course available! \"$title\" is now published. Visit $courseUrl to enroll.";
                    $sms->sendBulk($phones, $msg);
                    try {
                        $pdo->prepare("INSERT INTO sms_log (type, recipient, message, status) VALUES ('course_published', ?, ?, 'sent')")->execute([implode(', ', $phones), $msg]);
                    } catch (Exception $e2) {}
                }
            } catch (Exception $e) {
                error_log('SMS notification error: ' . $e->getMessage());
            }
        }
    } else {
        $error_msg = 'Title, slug, and description are required.';
    }
}

if (isset($_POST['delete_course'])) {
    $id = (int)($_POST['id'] ?? 0);
    try {
        $stmt = $pdo->prepare("SELECT thumbnail FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        $c = $stmt->fetch();
        if ($c && $c['thumbnail']) @unlink(__DIR__ . '/../' . $c['thumbnail']);
        $pdo->prepare("DELETE FROM courses WHERE id = ?")->execute([$id]);
        $success_msg = 'Course deleted.';
    } catch (Exception $e) {
        $error_msg = 'Database error.';
    }
}

$courses = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) AS lesson_count FROM courses c ORDER BY c.created_at DESC")->fetchAll();
require_once 'admin_header.php';
?>
<div class="page-header">
    <button class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#courseModal"><i class="bi bi-plus-lg"></i> Add Course</button>
    <a href="course_builder" class="btn btn-outline-cyan"><i class="bi bi-diagram-3"></i> Course Builder</a>
</div>

<?php if ($success_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
<?php if ($error_msg): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Thumb</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Price (TZS)</th>
                    <th>Lessons</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($courses)): ?>
                    <tr><td colspan="8" class="text-muted text-center">No courses yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($courses as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td>
                            <?php if ($c['thumbnail']): ?>
                                <img src="../<?= htmlspecialchars($c['thumbnail']) ?>" alt="" width="60" height="34" style="object-fit:cover;border-radius:4px;">
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($c['title']) ?>
                            <?php if ($c['featured']): ?><span class="badge bg-warning text-dark ms-1">Featured</span><?php endif; ?>
                        </td>
                        <td><?= $c['type'] === 'premium' ? '<span class="badge bg-warning text-dark">Premium</span>' : '<span class="badge bg-success">Free</span>' ?></td>
                        <td>
                            <span class="badge bg-<?= $c['status'] === 'published' ? 'success' : ($c['status'] === 'draft' ? 'secondary' : 'danger') ?>">
                                <?= ucfirst($c['status']) ?>
                            </span>
                        </td>
                        <td><?= $c['type'] === 'premium' ? number_format($c['price']) : '<span class="text-muted">—</span>' ?></td>
                        <td><?= $c['lesson_count'] ?></td>
                        <td>
                            <a href="course_builder?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-info me-1" title="Build"><i class="bi bi-diagram-3"></i></a>
                            <button class="btn btn-sm btn-outline-cyan me-1" data-bs-toggle="modal" data-bs-target="#courseModal"
                                data-id="<?= $c['id'] ?>"
                                data-title="<?= htmlspecialchars($c['title'], ENT_QUOTES) ?>"
                                data-slug="<?= htmlspecialchars($c['slug'], ENT_QUOTES) ?>"
                                data-description="<?= htmlspecialchars($c['description'], ENT_QUOTES) ?>"
                                data-type="<?= $c['type'] ?>"
                                data-price="<?= $c['price'] ?>"
                                data-status="<?= $c['status'] ?>"
                                data-featured="<?= $c['featured'] ?>"
                                data-thumbnail="<?= htmlspecialchars($c['thumbnail'] ?? '', ENT_QUOTES) ?>"
                                title="Edit"><i class="bi bi-pencil"></i></button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this course and all its lessons?')">
                                <input type="hidden" name="delete_course" value="1">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="courseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="save_course" value="1">
                <input type="hidden" name="id" id="course-id" value="0">
                <input type="hidden" name="existing_thumbnail" id="course-existing-thumbnail" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil text-cyan me-2"></i><span id="course-modal-title">New Course</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="course-title" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="course-slug" class="form-control" required placeholder="course-slug">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="course-desc" rows="5" class="form-control" required></textarea>
                        </div>
                        <div class="col-md-4 col-6">
                            <label class="form-label">Type</label>
                            <select name="type" id="course-type" class="form-select">
                                <option value="free">Free</option>
                                <option value="premium">Premium</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-6">
                            <label class="form-label">Status</label>
                            <select name="status" id="course-status" class="form-select">
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-12" id="course-price-wrap">
                            <label class="form-label">Price (TZS)</label>
                            <input type="number" name="price" id="course-price" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Thumbnail Image</label>
                            <input type="file" name="thumbnail" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <small class="text-muted">Max 2MB. Recommended 16:9 ratio.</small>
                        </div>
                        <div class="col-md-4 col-12 d-flex align-items-center">
                            <div class="form-check">
                                <input type="checkbox" name="featured" id="course-featured" class="form-check-input" value="1">
                                <label class="form-check-label" for="course-featured">Featured</label>
                            </div>
                        </div>
                        <div class="col-md-4 col-12 d-flex align-items-center">
                            <div class="form-check">
                                <input type="checkbox" name="notify_users" id="course-notify" class="form-check-input" value="1" checked>
                                <label class="form-check-label" for="course-notify">Notify users via SMS</label>
                            </div>
                        </div>
                        <div class="col-12" id="course-thumbnail-preview-wrap" style="display:none;">
                            <label class="form-label">Current Thumbnail</label>
                            <div><img id="course-thumbnail-preview" src="" alt="" height="56" style="object-fit:cover;border-radius:4px;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cyan"><i class="bi bi-save"></i> Save Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var titleInput = document.getElementById('course-title');
    var slugInput = document.getElementById('course-slug');
    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function () {
            if (document.getElementById('course-id').value === '0') {
                slugInput.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            }
        });
    }

    function togglePrice() {
        var wrap = document.getElementById('course-price-wrap');
        wrap.style.display = document.getElementById('course-type').value === 'premium' ? '' : 'none';
    }
    document.getElementById('course-type').addEventListener('change', togglePrice);
    togglePrice();

    var modal = document.getElementById('courseModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            var id = btn.dataset.id || '0';
            document.getElementById('course-id').value = id;
            document.getElementById('course-title').value = btn.dataset.title || '';
            document.getElementById('course-slug').value = btn.dataset.slug || '';
            document.getElementById('course-desc').value = btn.dataset.description || '';
            document.getElementById('course-type').value = btn.dataset.type || 'free';
            document.getElementById('course-status').value = btn.dataset.status || 'published';
            document.getElementById('course-price').value = btn.dataset.price || '0';
            document.getElementById('course-featured').checked = btn.dataset.featured === '1';
            document.getElementById('course-modal-title').textContent = id !== '0' ? 'Edit Course' : 'New Course';

            var thumb = btn.dataset.thumbnail || '';
            document.getElementById('course-existing-thumbnail').value = thumb;
            var preview = document.getElementById('course-thumbnail-preview');
            var wrap = document.getElementById('course-thumbnail-preview-wrap');
            if (thumb) {
                preview.src = '../' + thumb;
                wrap.style.display = '';
            } else {
                wrap.style.display = 'none';
            }

            togglePrice();
        });
    }
});
</script>

<?php require_once 'admin_footer.php'; ?>
