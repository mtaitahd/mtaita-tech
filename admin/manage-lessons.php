<?php
$page_title = 'Manage Lessons';
$active_page = 'lessons';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';

$course_id_filter = (int)($_GET['course_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_lesson'])) {
    $id = (int)($_POST['id'] ?? 0);
    $course_id = (int)($_POST['course_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $youtube_url = trim($_POST['youtube_url'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if ($title && $youtube_url && $course_id > 0) {
        $thumbnail = $_POST['existing_thumbnail'] ?? '';

        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            $file = $_FILES['thumbnail'];
            $mime = function_exists('finfo_open') ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file['tmp_name']) : $file['type'];
            if (in_array($mime, $allowed) && $file['size'] <= 2 * 1024 * 1024) {
                $upload_dir = __DIR__ . '/../assets/img/uploads/lessons/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'lesson_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                    if ($thumbnail) @unlink(__DIR__ . '/../' . $thumbnail);
                    $thumbnail = 'assets/img/uploads/lessons/' . $filename;
                }
            }
        }

        try {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE lessons SET course_id = ?, title = ?, youtube_url = ?, thumbnail = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([$course_id, $title, $youtube_url, $thumbnail, $sort_order, $id]);
                $success_msg = 'Lesson updated!';
            } else {
                $stmt = $pdo->prepare("INSERT INTO lessons (course_id, title, youtube_url, thumbnail, sort_order) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$course_id, $title, $youtube_url, $thumbnail, $sort_order]);
                $success_msg = 'Lesson added!';
            }
        } catch (Exception $e) {
            error_log('save_lesson error: ' . $e->getMessage());
            $error_msg = 'Database error.';
        }
    } else {
        $error_msg = 'Title, YouTube URL, and course are required.';
    }
}

if (isset($_POST['delete_lesson'])) {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT thumbnail FROM lessons WHERE id = ?");
    $stmt->execute([$id]);
    $del_lesson = $stmt->fetch();
    try {
        if ($del_lesson && $del_lesson['thumbnail']) @unlink(__DIR__ . '/../' . $del_lesson['thumbnail']);
        $pdo->prepare("DELETE FROM lessons WHERE id = ?")->execute([$id]);
        $success_msg = 'Lesson deleted.';
    } catch (Exception $e) {
        $error_msg = 'Database error.';
    }
}

$courses_list = $pdo->query("SELECT id, title FROM courses ORDER BY title ASC")->fetchAll();

$course_name = '';
if ($course_id_filter > 0) {
    $stmt = $pdo->prepare("SELECT title FROM courses WHERE id = ?");
    $stmt->execute([$course_id_filter]);
    $course_row = $stmt->fetch();
    $course_name = $course_row ? $course_row['title'] : '';
}

if ($course_id_filter > 0) {
    $stmt = $pdo->prepare("SELECT l.*, c.title AS course_title FROM lessons l JOIN courses c ON l.course_id = c.id WHERE l.course_id = ? ORDER BY l.sort_order ASC");
    $stmt->execute([$course_id_filter]);
    $lessons = $stmt->fetchAll();
} else {
    $lessons = $pdo->query("SELECT l.*, c.title AS course_title FROM lessons l JOIN courses c ON l.course_id = c.id ORDER BY c.title ASC, l.sort_order ASC")->fetchAll();
}
require_once 'admin_header.php';
?>
<div class="page-header">
    <button class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#lessonModal"><i class="bi bi-plus-lg"></i> Add Lesson</button>
</div>

<?php if ($success_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
<?php if ($error_msg): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

<div class="admin-card mb-3">
    <form method="GET" class="row g-2 align-items-end p-3">
        <div class="col-auto">
            <label class="form-label text-muted small mb-1">Filter by Course</label>
            <select name="course_id" class="form-select" onchange="this.form.submit()">
                <option value="">All Courses</option>
                <?php foreach ($courses_list as $cr): ?>
                    <option value="<?= $cr['id'] ?>" <?= $course_id_filter === (int)$cr['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cr['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($course_id_filter > 0): ?>
        <div class="col-auto">
            <a href="manage-lessons" class="btn btn-outline-secondary">Clear Filter</a>
        </div>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Thumbnail</th>
                    <th>Title</th>
                    <th>YouTube URL</th>
                    <?php if (!$course_id_filter): ?><th>Course</th><?php endif; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lessons)): ?>
                    <tr><td colspan="<?= $course_id_filter ? 5 : 6 ?>" class="text-muted text-center">No lessons found.</td></tr>
                <?php else: ?>
                    <?php foreach ($lessons as $l): ?>
                    <tr>
                        <td><?= $l['sort_order'] ?></td>
                        <td>
                            <?php if ($l['thumbnail']): ?>
                                <img src="../<?= htmlspecialchars($l['thumbnail']) ?>" alt="" width="60" height="34" style="object-fit:cover;">
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted" style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <a href="<?= htmlspecialchars($l['youtube_url']) ?>" target="_blank" class="text-cyan"><?= htmlspecialchars($l['youtube_url']) ?></a>
                        </td>
                        <?php if (!$course_id_filter): ?><td><?= htmlspecialchars($l['course_title']) ?></td><?php endif; ?>
                        <td>
                            <button class="btn btn-sm btn-outline-cyan me-1" data-bs-toggle="modal" data-bs-target="#lessonModal"
                                data-id="<?= $l['id'] ?>"
                                data-course-id="<?= $l['course_id'] ?>"
                                data-title="<?= htmlspecialchars($l['title'], ENT_QUOTES) ?>"
                                data-youtube="<?= htmlspecialchars($l['youtube_url'], ENT_QUOTES) ?>"
                                data-sort="<?= $l['sort_order'] ?>"
                                data-thumbnail="<?= htmlspecialchars($l['thumbnail'] ?? '', ENT_QUOTES) ?>"
                                title="Edit"><i class="bi bi-pencil"></i></button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this lesson?')">
                                <input type="hidden" name="delete_lesson" value="1">
                                <input type="hidden" name="id" value="<?= $l['id'] ?>">
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

<div class="modal fade" id="lessonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="save_lesson" value="1">
                <input type="hidden" name="id" id="lesson-id" value="0">
                <input type="hidden" name="existing_thumbnail" id="lesson-existing-thumbnail" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil text-cyan me-2"></i><span id="lesson-modal-title">New Lesson</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Course</label>
                            <select name="course_id" id="lesson-course-id" class="form-select" required>
                                <option value="">— Select Course —</option>
                                <?php foreach ($courses_list as $cr): ?>
                                    <option value="<?= $cr['id'] ?>"><?= htmlspecialchars($cr['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="lesson-title" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">YouTube URL</label>
                            <input type="url" name="youtube_url" id="lesson-youtube" class="form-control" required placeholder="https://www.youtube.com/watch?v=XXX">
                            <small class="text-muted">Full YouTube video URL</small>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Thumbnail Image (optional)</label>
                            <input type="file" name="thumbnail" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <small class="text-muted">Max 2MB. Recommended 16:9 ratio.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="lesson-sort" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-12" id="lesson-thumbnail-preview-wrap" style="display:none;">
                            <label class="form-label">Current Thumbnail</label>
                            <div><img id="lesson-thumbnail-preview" src="" alt="" height="56" style="object-fit:cover;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cyan"><i class="bi bi-save"></i> Save Lesson</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('lessonModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            var id = btn.dataset.id || '0';
            document.getElementById('lesson-id').value = id;
            document.getElementById('lesson-course-id').value = btn.dataset.courseId || '<?= $course_id_filter > 0 ? $course_id_filter : '' ?>';
            document.getElementById('lesson-title').value = btn.dataset.title || '';
            document.getElementById('lesson-youtube').value = btn.dataset.youtube || '';
            document.getElementById('lesson-sort').value = btn.dataset.sort || '0';
            document.getElementById('lesson-existing-thumbnail').value = btn.dataset.thumbnail || '';
            document.getElementById('lesson-modal-title').textContent = id !== '0' ? 'Edit Lesson' : 'New Lesson';
            var preview = document.getElementById('lesson-thumbnail-preview');
            var wrap = document.getElementById('lesson-thumbnail-preview-wrap');
            if (btn.dataset.thumbnail) {
                preview.src = '../' + btn.dataset.thumbnail;
                wrap.style.display = '';
            } else {
                wrap.style.display = 'none';
            }
        });
    }
});
</script>

<?php require_once 'admin_footer.php'; ?>
