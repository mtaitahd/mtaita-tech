<?php
$page_title = 'Course Builder';
$active_page = 'courses';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../lib/Course.php';
require_once __DIR__ . '/../lib/Module.php';
require_once __DIR__ . '/../lib/Lesson.php';

$courseModel = new Course();
$moduleModel = new Module();
$lessonModel = new Lesson();

$courseId = (int)($_GET['id'] ?? ($_POST['course_id'] ?? 0));
$course = $courseId ? $courseModel->getById($courseId) : null;

// Handle module add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_module'])) {
        $moduleId = (int)($_POST['module_id'] ?? 0);
        $title = trim($_POST['module_title'] ?? '');
        $sortOrder = (int)($_POST['module_sort'] ?? 0);
        $cid = (int)($_POST['course_id'] ?? 0);

        if ($title && $cid > 0) {
            if ($moduleId > 0) {
                $moduleModel->update($moduleId, $title, $sortOrder);
                $success_msg = 'Module updated.';
            } else {
                $moduleModel->create($cid, $title, $sortOrder);
                $success_msg = 'Module added.';
            }
        } else {
            $error_msg = 'Module title is required.';
        }
    }

    if (isset($_POST['delete_module'])) {
        $moduleId = (int)($_POST['module_id'] ?? 0);
        $moduleModel->delete($moduleId);
        $success_msg = 'Module deleted.';
    }

    // Handle lesson add/edit/delete
    if (isset($_POST['save_lesson'])) {
        $lessonId = (int)($_POST['lesson_id'] ?? 0);
        $cid = (int)($_POST['course_id'] ?? 0);
        $moduleId = (int)($_POST['module_id'] ?? 0);
        $title = trim($_POST['lesson_title'] ?? '');
        $youtubeUrl = trim($_POST['youtube_url'] ?? '');
        $videoUrl = trim($_POST['video_url'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isPaid = isset($_POST['is_paid']) ? 1 : 0;
        $codeContent = $_POST['code_content'] ?? '';

        $thumbnail = $_POST['existing_thumbnail'] ?? '';
        $zipFile = $_POST['existing_zip'] ?? '';

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

        if (isset($_FILES['zip_file']) && $_FILES['zip_file']['error'] === UPLOAD_ERR_OK) {
            $allowed_zip = ['application/zip', 'application/x-zip-compressed'];
            $file = $_FILES['zip_file'];
            $mime = function_exists('finfo_open') ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file['tmp_name']) : $file['type'];
            if (in_array($mime, $allowed_zip) || pathinfo($file['name'], PATHINFO_EXTENSION) === 'zip') {
                $upload_dir = __DIR__ . '/../assets/uploads/lessons/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $filename = 'lesson_' . uniqid() . '.zip';
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                    if ($zipFile) @unlink(__DIR__ . '/../' . $zipFile);
                    $zipFile = 'assets/uploads/lessons/' . $filename;
                }
            }
        }

        if ($title && $cid > 0 && $moduleId > 0) {
            $data = [
                'course_id' => $cid,
                'module_id' => $moduleId,
                'title' => $title,
                'youtube_url' => $youtubeUrl,
                'video_url' => $videoUrl,
                'thumbnail' => $thumbnail,
                'code_content' => $codeContent,
                'zip_file' => $zipFile,
                'sort_order' => $sortOrder,
                'is_paid' => $isPaid
            ];

            if ($lessonId > 0) {
                $lessonModel->update($lessonId, $data);
                $success_msg = 'Lesson updated.';
            } else {
                $lessonModel->create($data);
                $success_msg = 'Lesson added.';
            }
        } else {
            $error_msg = 'Title, module, and course are required.';
        }
    }

    if (isset($_POST['delete_lesson'])) {
        $lessonId = (int)($_POST['lesson_id'] ?? 0);
        $lessonModel->delete($lessonId);
        $success_msg = 'Lesson deleted.';
    }

    // Refresh course after POST
    $course = $courseId ? $courseModel->getById($courseId) : null;
}

if (!$course) {
    // Show course selector
    $courses = $pdo->query("SELECT id, title, status FROM courses ORDER BY title ASC")->fetchAll();
    require_once 'admin_header.php';
?>
<div class="page-header">
    <h4 style="margin:0;"><i class="bi bi-diagram-3 me-2 text-cyan"></i>Course Builder</h4>
</div>
<div class="admin-card">
    <p class="text-muted">Select a course to build:</p>
    <div class="list-group">
        <?php foreach ($courses as $c): ?>
        <a href="course_builder?id=<?= $c['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            <?= htmlspecialchars($c['title']) ?>
            <span class="badge bg-<?= $c['status'] === 'published' ? 'success' : 'secondary' ?>"><?= ucfirst($c['status']) ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php
    require_once 'admin_footer.php';
    exit;
}

$modules = $moduleModel->getByCourseId($courseId);
$lessonsByModule = [];
$allLessons = [];
foreach ($modules as $mod) {
    $lessons = $lessonModel->getByModuleId($mod['id']);
    $lessonsByModule[$mod['id']] = $lessons;
    $allLessons = array_merge($allLessons, $lessons);
}

require_once 'admin_header.php';
?>

<div class="page-header d-flex flex-wrap gap-2">
    <a href="manage-courses" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Courses</a>
    <button class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#moduleModal"><i class="bi bi-folder-plus"></i> Add Module</button>
    <button class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#lessonModal"><i class="bi bi-file-plus"></i> Add Lesson</button>
</div>

<?php if ($success_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
<?php if ($error_msg): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

<!-- Course Info -->
<div class="admin-card mb-4">
    <div class="d-flex flex-wrap align-items-center gap-3">
        <?php if ($course['thumbnail']): ?>
            <img src="../<?= htmlspecialchars($course['thumbnail']) ?>" alt="" height="56" style="object-fit:cover;border-radius:8px;">
        <?php endif; ?>
        <div>
            <h4 class="mb-1"><?= htmlspecialchars($course['title']) ?></h4>
            <div class="d-flex gap-2">
                <span class="badge bg-<?= $course['type'] === 'premium' ? 'warning' : 'success' ?>"><?= ucfirst($course['type']) ?></span>
                <span class="badge bg-<?= $course['status'] === 'published' ? 'success' : 'secondary' ?>"><?= ucfirst($course['status']) ?></span>
                <span class="text-muted small"><?= count($modules) ?> modules, <?= count($allLessons) ?> lessons</span>
            </div>
        </div>
        <div class="ms-auto">
            <a href="../single-course?slug=<?= urlencode($course['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-cyan"><i class="bi bi-eye"></i> View</a>
        </div>
    </div>
</div>

<!-- Curriculum Tree -->
<?php if (empty($modules)): ?>
<div class="admin-card text-center py-5">
    <i class="bi bi-diagram-3" style="font-size:3rem;color:#4B5563;"></i>
    <p class="text-muted mt-3 mb-3">No modules yet. Start by adding a module.</p>
    <button class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#moduleModal"><i class="bi bi-plus-lg"></i> Add First Module</button>
</div>
<?php else: ?>
    <?php foreach ($modules as $mod): ?>
    <div class="admin-card mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-folder text-cyan"></i>
                <?= htmlspecialchars($mod['title']) ?>
                <small class="text-muted fw-normal">(order: <?= $mod['sort_order'] ?>)</small>
            </h5>
            <div class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-cyan" data-bs-toggle="modal" data-bs-target="#moduleModal"
                    data-id="<?= $mod['id'] ?>"
                    data-title="<?= htmlspecialchars($mod['title'], ENT_QUOTES) ?>"
                    data-sort="<?= $mod['sort_order'] ?>"
                    title="Edit Module"><i class="bi bi-pencil"></i></button>
                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this module and its lessons?')">
                    <input type="hidden" name="delete_module" value="1">
                    <input type="hidden" name="module_id" value="<?= $mod['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </div>

        <?php if (empty($lessonsByModule[$mod['id']])): ?>
            <p class="text-muted small mb-0 ms-4">No lessons in this module.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0" style="font-size:0.9rem;">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Title</th>
                            <th>Video</th>
                            <th>Paid</th>
                            <th>Files</th>
                            <th style="width:100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lessonsByModule[$mod['id']] as $l): ?>
                        <tr>
                            <td><?= $l['sort_order'] ?></td>
                            <td>
                                <?php if ($l['thumbnail']): ?>
                                    <img src="../<?= htmlspecialchars($l['thumbnail']) ?>" alt="" width="40" height="22" style="object-fit:cover;border-radius:3px;" class="me-2">
                                <?php endif; ?>
                                <?= htmlspecialchars($l['title']) ?>
                            </td>
                            <td class="small text-muted" style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <?= htmlspecialchars($l['youtube_url'] ?: $l['video_url'] ?: '—') ?>
                            </td>
                            <td>
                                <?= $l['is_paid'] ? '<span class="badge bg-warning text-dark">Premium</span>' : '<span class="badge bg-success">Free</span>' ?>
                            </td>
                            <td class="small">
                                <?= $l['zip_file'] ? '<i class="bi bi-file-zip text-info"></i> ' : '' ?>
                                <?= $l['code_content'] ? '<i class="bi bi-code-slash text-success"></i>' : '' ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-cyan" data-bs-toggle="modal" data-bs-target="#lessonModal"
                                    data-id="<?= $l['id'] ?>"
                                    data-course-id="<?= $courseId ?>"
                                    data-module-id="<?= $mod['id'] ?>"
                                    data-title="<?= htmlspecialchars($l['title'], ENT_QUOTES) ?>"
                                    data-youtube="<?= htmlspecialchars($l['youtube_url'] ?? '', ENT_QUOTES) ?>"
                                    data-video="<?= htmlspecialchars($l['video_url'] ?? '', ENT_QUOTES) ?>"
                                    data-sort="<?= $l['sort_order'] ?>"
                                    data-thumbnail="<?= htmlspecialchars($l['thumbnail'] ?? '', ENT_QUOTES) ?>"
                                    data-code="<?= htmlspecialchars($l['code_content'] ?? '', ENT_QUOTES) ?>"
                                    data-zip="<?= htmlspecialchars($l['zip_file'] ?? '', ENT_QUOTES) ?>"
                                    data-is-paid="<?= $l['is_paid'] ?>"
                                    title="Edit"><i class="bi bi-pencil"></i></button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this lesson?')">
                                    <input type="hidden" name="delete_lesson" value="1">
                                    <input type="hidden" name="lesson_id" value="<?= $l['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Module Modal -->
<div class="modal fade" id="moduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="save_module" value="1">
                <input type="hidden" name="module_id" id="module-id" value="0">
                <input type="hidden" name="course_id" value="<?= $courseId ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-folder text-cyan me-2"></i><span id="module-modal-title">New Module</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Module Title</label>
                        <input type="text" name="module_title" id="module-title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="module_sort" id="module-sort" class="form-control" value="0" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cyan"><i class="bi bi-save"></i> Save Module</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lesson Modal -->
<div class="modal fade" id="lessonModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="save_lesson" value="1">
                <input type="hidden" name="lesson_id" id="lesson-id" value="0">
                <input type="hidden" name="course_id" value="<?= $courseId ?>">
                <input type="hidden" name="module_id" id="lesson-module-id" value="">
                <input type="hidden" name="existing_thumbnail" id="lesson-existing-thumbnail" value="">
                <input type="hidden" name="existing_zip" id="lesson-existing-zip" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-file text-cyan me-2"></i><span id="lesson-modal-title">New Lesson</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Module</label>
                            <select name="module_id" id="lesson-module-select" class="form-select" required>
                                <option value="">— Select Module —</option>
                                <?php foreach ($modules as $mod): ?>
                                    <option value="<?= $mod['id'] ?>"><?= htmlspecialchars($mod['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Title</label>
                            <input type="text" name="lesson_title" id="lesson-title" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="lesson-sort" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">YouTube URL</label>
                            <input type="url" name="youtube_url" id="lesson-youtube" class="form-control" placeholder="https://youtube.com/watch?v=XXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Video URL (local)</label>
                            <input type="text" name="video_url" id="lesson-video" class="form-control" placeholder="Or path to local video">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Thumbnail</label>
                            <input type="file" name="thumbnail" class="form-control" accept="image/png,image/jpeg,image/webp">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ZIP File (source)</label>
                            <input type="file" name="zip_file" class="form-control" accept=".zip">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Code / Notes</label>
                            <textarea name="code_content" id="lesson-code" class="form-control" rows="4" placeholder="Optional starter code or notes"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_paid" id="lesson-is-paid" class="form-check-input" value="1">
                                <label class="form-check-label" for="lesson-is-paid">Premium lesson (requires course purchase)</label>
                            </div>
                        </div>
                        <div class="col-12" id="lesson-thumbnail-preview-wrap" style="display:none;">
                            <label class="form-label">Current Thumbnail</label>
                            <div><img id="lesson-thumbnail-preview" src="" alt="" height="56" style="object-fit:cover;border-radius:4px;"></div>
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
    // Module modal
    var modModal = document.getElementById('moduleModal');
    if (modModal) {
        modModal.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            var id = btn.dataset.id || '0';
            document.getElementById('module-id').value = id;
            document.getElementById('module-title').value = btn.dataset.title || '';
            document.getElementById('module-sort').value = btn.dataset.sort || '0';
            document.getElementById('module-modal-title').textContent = id !== '0' ? 'Edit Module' : 'New Module';
        });
    }

    // Lesson modal
    var lesModal = document.getElementById('lessonModal');
    if (lesModal) {
        lesModal.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            var id = btn.dataset.id || '0';
            document.getElementById('lesson-id').value = id;
            document.getElementById('lesson-title').value = btn.dataset.title || '';
            document.getElementById('lesson-youtube').value = btn.dataset.youtube || '';
            document.getElementById('lesson-video').value = btn.dataset.video || '';
            document.getElementById('lesson-sort').value = btn.dataset.sort || '0';
            document.getElementById('lesson-code').value = btn.dataset.code || '';
            document.getElementById('lesson-is-paid').checked = btn.dataset.isPaid === '1';
            document.getElementById('lesson-existing-thumbnail').value = btn.dataset.thumbnail || '';
            document.getElementById('lesson-existing-zip').value = btn.dataset.zip || '';
            document.getElementById('lesson-modal-title').textContent = id !== '0' ? 'Edit Lesson' : 'New Lesson';

            // Set module
            var moduleSelect = document.getElementById('lesson-module-select');
            var moduleId = btn.dataset.moduleId || document.getElementById('lesson-module-id').value;
            if (moduleId) moduleSelect.value = moduleId;

            // Thumbnail preview
            var thumb = btn.dataset.thumbnail || '';
            var preview = document.getElementById('lesson-thumbnail-preview');
            var wrap = document.getElementById('lesson-thumbnail-preview-wrap');
            if (thumb) {
                preview.src = '../' + thumb;
                wrap.style.display = '';
            } else {
                wrap.style.display = 'none';
            }
        });
    }
});
</script>

<?php require_once 'admin_footer.php'; ?>
