<?php
$page_title = 'Projects';
$active_page = 'projects';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_project'])) {
    if (empty($_POST)) {
        $error_msg = 'Form data too large. Server limit: ' . ini_get('post_max_size') . '. Try a smaller image or increase post_max_size in php.ini.';
    } else {
    $title = trim($_POST['project_title'] ?? '');
    $desc = trim($_POST['project_desc'] ?? '');
    $link = trim($_POST['project_link'] ?? '');
    $errors = [];

    if ($title === '') $errors[] = 'Project title is required.';
    if ($desc === '') $errors[] = 'Project description is required.';
    if ($link === '' || !filter_var($link, FILTER_VALIDATE_URL)) $errors[] = 'A valid project URL is required.';

    $hasFile = isset($_FILES['project_screenshot']) && $_FILES['project_screenshot']['error'] !== UPLOAD_ERR_NO_FILE;

    if (empty($errors) && $hasFile) {
        if ($_FILES['project_screenshot']['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds server size limit (check upload_max_filesize in php.ini).',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Server failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'Upload stopped by a PHP extension.',
            ];
            $errors[] = $uploadErrors[$_FILES['project_screenshot']['error']] ?? 'Upload error #' . $_FILES['project_screenshot']['error'];
        } else {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file = $_FILES['project_screenshot'];

            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            } else {
                $mime = $file['type'];
            }

            if (!in_array($mime, $allowed)) {
                $errors[] = 'Invalid file type. Only PNG, JPG, WEBP, and GIF are allowed.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = 'File is too large. Maximum size is 5MB.';
            } else {
                $upload_dir = __DIR__ . '/../assets/img/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('proj_', true) . '.' . $ext;
                $dest = $upload_dir . $filename;

                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    try {
                        $category = trim($_POST['category'] ?? 'Websites');
                        $techStack = trim($_POST['tech_stack'] ?? '');
                        $completionYear = trim($_POST['completion_year'] ?? '');
                        $isLive = isset($_POST['is_live']) ? 1 : 0;
                        $stmt = $pdo->prepare("INSERT INTO portfolio (project_title, project_desc, project_link, category, tech_stack, completion_year, is_live, project_screenshot) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$title, $desc, $link, $category, $techStack, $completionYear, $isLive, 'assets/img/uploads/' . $filename]);
                        $success_msg = 'Project uploaded successfully.';
                    } catch (Exception $e) {
                        error_log('add_project DB error: ' . $e->getMessage());
                        $errors[] = 'Database error. Please try again.';
                    }
                } else {
                    $errors[] = 'Failed to move uploaded file. Please check directory permissions.';
                }
            }
        }
    } elseif (empty($errors)) {
        $errors[] = 'Please select a screenshot file.';
    }

    if (!$success_msg && !empty($errors)) {
        $error_msg = implode(' ', $errors);
    }
    }
}

$projects = $pdo->query("SELECT id, project_title, project_desc, project_link, category, tech_stack, completion_year, is_live, project_screenshot, created_at FROM portfolio ORDER BY created_at DESC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_project'])) {
    if (empty($_POST)) {
        $error_msg = 'Form data too large. Server limit: ' . ini_get('post_max_size') . '. Try a smaller image.';
    } else {
        $eid = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['project_title'] ?? '');
        $desc = trim($_POST['project_desc'] ?? '');
        $link = trim($_POST['project_link'] ?? '');

        $errors = [];
        if ($eid < 1) $errors[] = 'Invalid project ID.';
        if ($title === '') $errors[] = 'Title is required.';
        if ($desc === '') $errors[] = 'Description is required.';
        if ($link === '' || !filter_var($link, FILTER_VALIDATE_URL)) $errors[] = 'A valid URL is required.';

        $stmt = $pdo->prepare("SELECT * FROM portfolio WHERE id = ? LIMIT 1");
        $stmt->execute([$eid]);
        $proj = $stmt->fetch();

        if (!$proj) $errors[] = 'Project not found.';

        $screenshot_path = $proj['project_screenshot'] ?? '';

        if (empty($errors) && isset($_FILES['project_screenshot_edit']) && $_FILES['project_screenshot_edit']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['project_screenshot_edit']['error'] !== UPLOAD_ERR_OK) {
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds server size limit.',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit.',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Server missing temporary folder.',
                    UPLOAD_ERR_CANT_WRITE => 'Server failed to write file to disk.',
                    UPLOAD_ERR_EXTENSION => 'Upload stopped by a PHP extension.',
                ];
                $errors[] = $uploadErrors[$_FILES['project_screenshot_edit']['error']] ?? 'Upload error #' . $_FILES['project_screenshot_edit']['error'];
            } else {
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $file = $_FILES['project_screenshot_edit'];

                if (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);
                } else {
                    $mime = $file['type'];
                }

                if (!in_array($mime, $allowed)) {
                    $errors[] = 'Invalid file type.';
                } elseif ($file['size'] > 5 * 1024 * 1024) {
                    $errors[] = 'File is too large (max 5MB).';
                } else {
                    $old_path = __DIR__ . '/../' . $screenshot_path;
                    if (file_exists($old_path) && is_file($old_path)) @unlink($old_path);

                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = uniqid('proj_', true) . '.' . $ext;
                    $dest = __DIR__ . '/../assets/img/uploads/' . $filename;

                    if (move_uploaded_file($file['tmp_name'], $dest)) {
                        $screenshot_path = 'assets/img/uploads/' . $filename;
                    } else {
                        $errors[] = 'Failed to upload new file.';
                    }
                }
            }
        }

        if (empty($errors)) {
            try {
                $category = trim($_POST['category'] ?? 'Websites');
                $techStack = trim($_POST['tech_stack'] ?? '');
                $completionYear = trim($_POST['completion_year'] ?? '');
                $isLive = isset($_POST['is_live']) ? 1 : 0;
                $stmt = $pdo->prepare("UPDATE portfolio SET project_title = ?, project_desc = ?, project_link = ?, category = ?, tech_stack = ?, completion_year = ?, is_live = ?, project_screenshot = ? WHERE id = ?");
                $stmt->execute([$title, $desc, $link, $category, $techStack, $completionYear, $isLive, $screenshot_path, $eid]);
                $success_msg = 'Project updated successfully!';
            } catch (Exception $e) {
                error_log('edit_project DB error: ' . $e->getMessage());
                $errors[] = 'Database error. Please try again.';
            }
        }

        if (!$success_msg && !empty($errors)) {
            $error_msg = implode(' ', $errors);
        }

        $projects = $pdo->query("SELECT id, project_title, project_desc, project_link, category, tech_stack, completion_year, is_live, project_screenshot, created_at FROM portfolio ORDER BY created_at DESC")->fetchAll();
    }
}

require_once 'admin_header.php';
?>
<div class="page-header">
    <button class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#addProjectModal"><i class="bi bi-plus-lg"></i> Add Project</button>
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
                <tr><th>ID</th><th>Thumb</th><th>Title</th><th>Category</th><th>Year</th><th>Live</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($projects)): ?>
                    <tr><td colspan="6" class="text-muted text-center">No projects found.</td></tr>
                <?php else: ?>
                    <?php foreach ($projects as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><img src="../<?= htmlspecialchars($p['project_screenshot']) ?>" alt="" width="60" height="40" style="object-fit:cover;border-radius:4px;" onerror="this.src='https://via.placeholder.com/60x40/0F172A/00E5FF?text=NA'"></td>
                        <td><?= htmlspecialchars($p['project_title']) ?></td>
                        <td><span class="badge bg-dark"><?= htmlspecialchars($p['category'] ?? '') ?></span></td>
                        <td><?= htmlspecialchars($p['completion_year'] ?? '') ?></td>
                        <td><?= !empty($p['is_live']) ? '<span class="text-cyan"><i class="bi bi-check-circle-fill"></i></span>' : '<span class="text-muted"><i class="bi bi-x-circle"></i></span>' ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-cyan me-1" data-bs-toggle="modal" data-bs-target="#editProjectModal"
                                data-id="<?= $p['id'] ?>"
                                data-title="<?= htmlspecialchars($p['project_title'], ENT_QUOTES) ?>"
                                data-desc="<?= htmlspecialchars($p['project_desc'], ENT_QUOTES) ?>"
                                data-link="<?= htmlspecialchars($p['project_link'], ENT_QUOTES) ?>"
                                data-category="<?= htmlspecialchars($p['category'] ?? '', ENT_QUOTES) ?>"
                                data-tech="<?= htmlspecialchars($p['tech_stack'] ?? '', ENT_QUOTES) ?>"
                                data-year="<?= htmlspecialchars($p['completion_year'] ?? '', ENT_QUOTES) ?>"
                                data-live="<?= $p['is_live'] ?? 1 ?>"
                                data-screenshot="<?= htmlspecialchars($p['project_screenshot'], ENT_QUOTES) ?>"
                                title="Edit"><i class="bi bi-pencil"></i></button>
                            <a href="delete_project?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" data-type="project"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Project Modal -->
<div class="modal fade" id="addProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_project" value="1">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle text-cyan me-2"></i>Add New Project</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Project Title</label>
                            <input type="text" name="project_title" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Live Project URL</label>
                            <input type="url" name="project_link" class="form-control" placeholder="https://example.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="project_desc" rows="3" class="form-control" required></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-control" required>
                                <option value="Web Development">Web Development</option>
                                <option value="Graphic Design">Graphic Design</option>
                                <option value="Mobile Apps">Mobile Apps</option>
                                <option value="SEO & Digital Marketing">SEO & Digital Marketing</option>
                                <option value="Websites">Websites</option>
                                <option value="E-Commerce">E-Commerce</option>
                                <option value="Business Systems">Business Systems</option>
                                <option value="POS Systems">POS Systems</option>
                                <option value="School Systems">School Systems</option>
                                <option value="Hospital Systems">Hospital Systems</option>
                                <option value="Branding">Branding</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tech Stack</label>
                            <input type="text" name="tech_stack" class="form-control" placeholder="PHP, MySQL, Bootstrap">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Completion Year</label>
                            <input type="text" name="completion_year" class="form-control" placeholder="2025" maxlength="4">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Screenshot</label>
                            <input type="file" name="project_screenshot" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif" required>
                            <small class="text-muted">PNG, JPG, WEBP, GIF. Max 5MB.</small>
                        </div>
                        <div class="col-md-6 d-flex align-items-end pb-2">
                            <div class="form-check">
                                <input type="checkbox" name="is_live" class="form-check-input" id="addIsLive" checked>
                                <label class="form-check-label" for="addIsLive">Has live demo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cyan"><i class="bi bi-upload"></i> Upload & Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Project Modal -->
<div class="modal fade" id="editProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_project" value="1">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil text-cyan me-2"></i>Edit Project</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Project Title</label>
                            <input type="text" name="project_title" id="edit-title" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Live Project URL</label>
                            <input type="url" name="project_link" id="edit-link" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <select name="category" id="edit-category" class="form-control" required>
                                <option value="Web Development">Web Development</option>
                                <option value="Graphic Design">Graphic Design</option>
                                <option value="Mobile Apps">Mobile Apps</option>
                                <option value="SEO & Digital Marketing">SEO & Digital Marketing</option>
                                <option value="Websites">Websites</option>
                                <option value="E-Commerce">E-Commerce</option>
                                <option value="Business Systems">Business Systems</option>
                                <option value="POS Systems">POS Systems</option>
                                <option value="School Systems">School Systems</option>
                                <option value="Hospital Systems">Hospital Systems</option>
                                <option value="Branding">Branding</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tech Stack</label>
                            <input type="text" name="tech_stack" id="edit-tech" class="form-control" placeholder="PHP, MySQL, Bootstrap">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Completion Year</label>
                            <input type="text" name="completion_year" id="edit-year" class="form-control" placeholder="2025" maxlength="4">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="project_desc" id="edit-desc" rows="3" class="form-control" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Current Screenshot</label>
                            <div><img id="edit-screenshot-preview" src="" alt="" height="80" style="border-radius:6px;object-fit:cover;" onerror="this.style.display='none'"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Screenshot (optional)</label>
                            <input type="file" name="project_screenshot_edit" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif">
                            <small class="text-muted">Leave empty to keep current image.</small>
                        </div>
                        <div class="col-md-6 d-flex align-items-end pb-2">
                            <div class="form-check">
                                <input type="checkbox" name="is_live" class="form-check-input" id="editIsLive">
                                <label class="form-check-label" for="editIsLive">Has live demo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cyan"><i class="bi bi-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var editModal = document.getElementById('editProjectModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            document.getElementById('edit-id').value = btn.dataset.id;
            document.getElementById('edit-title').value = btn.dataset.title;
            document.getElementById('edit-desc').value = btn.dataset.desc;
            document.getElementById('edit-link').value = btn.dataset.link;
            document.getElementById('edit-category').value = btn.dataset.category || 'Websites';
            document.getElementById('edit-tech').value = btn.dataset.tech || '';
            document.getElementById('edit-year').value = btn.dataset.year || '';
            document.getElementById('editIsLive').checked = btn.dataset.live == '1';
            var preview = document.getElementById('edit-screenshot-preview');
            preview.src = '../' + btn.dataset.screenshot;
            preview.style.display = '';
        });
    }
});
</script>

<?php require_once 'admin_footer.php'; ?>
