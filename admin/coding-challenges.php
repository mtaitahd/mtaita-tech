<?php
$page_title = 'Coding Challenges';
$active_page = 'coding-challenges';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../auth_helper.php';
require_once __DIR__ . '/../lib/CodingChallenge.php';

$challengeModel = new CodingChallenge();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Invalid security token. Please refresh and try again.';
    } elseif (isset($_POST['delete_challenge'])) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $challengeModel->delete($id);
            $success_msg = 'Challenge deleted.';
        }
    } elseif (isset($_POST['toggle_publish'])) {
        $id = (int)($_POST['id'] ?? 0);
        $published = (int)($_POST['is_published'] ?? 0);
        if ($id > 0) {
            $challengeModel->setPublished($id, $published === 0);
            $success_msg = $published === 0 ? 'Challenge published.' : 'Challenge unpublished.';
        }
    }
}

$challenges = $challengeModel->getAllAdmin();
require_once 'admin_header.php';
?>
<div class="page-header d-flex flex-wrap gap-2">
    <a href="coding-challenge-edit" class="btn btn-cyan"><i class="bi bi-plus-lg"></i> Add Challenge</a>
    <a href="coding-analytics" class="btn btn-outline-cyan"><i class="bi bi-bar-chart"></i> Analytics</a>
</div>

<?php if ($success_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
<?php if ($error_msg): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Course / Module / Lesson</th>
                    <th>Language</th>
                    <th>Difficulty</th>
                    <th>Marks</th>
                    <th>Tests</th>
                    <th>Submissions</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($challenges)): ?>
                <tr>
                    <td colspan="10" class="text-muted text-center py-4">
                        No coding challenges yet. <a href="coding-challenge-edit" class="text-cyan">Add your first challenge</a>.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($challenges as $c): ?>
                    <tr>
                        <td><?= (int)$c['id'] ?></td>
                        <td>
                            <?= htmlspecialchars($c['title']) ?>
                            <?php if (!empty($c['lesson_title'])): ?>
                                <div class="small text-muted"><?= htmlspecialchars($c['lesson_title']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="small">
                            <div class="text-cyan"><?= htmlspecialchars($c['course_title']) ?></div>
                            <?php if (!empty($c['module_title'])): ?>
                                <div class="text-muted"><i class="bi bi-folder"></i> <?= htmlspecialchars($c['module_title']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge" style="background:#164E63;color:#67E8F9;"><?= strtoupper(htmlspecialchars($c['language'])) ?></span></td>
                        <td>
                            <span class="badge bg-<?= $c['difficulty'] === 'easy' ? 'success' : ($c['difficulty'] === 'medium' ? 'warning text-dark' : 'danger') ?>">
                                <?= ucfirst($c['difficulty']) ?>
                            </span>
                        </td>
                        <td><?= (int)$c['marks'] ?></td>
                        <td><?= (int)$c['test_count'] ?></td>
                        <td><?= (int)$c['submission_count'] ?></td>
                        <td>
                            <?php if ($c['is_published']): ?>
                                <span class="badge bg-success">Published</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="coding-challenge-edit?id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-cyan me-1" title="Edit"><i class="bi bi-pencil"></i></a>
                            <a href="../challenge?id=<?= (int)$c['id'] ?>" target="_blank" class="btn btn-sm btn-outline-info me-1" title="View as student"><i class="bi bi-eye"></i></a>
                            <form method="POST" class="d-inline me-1">
                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                <input type="hidden" name="toggle_publish" value="1">
                                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                <input type="hidden" name="is_published" value="<?= (int)$c['is_published'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-warning" title="<?= $c['is_published'] ? 'Unpublish' : 'Publish' ?>">
                                    <i class="bi bi-<?= $c['is_published'] ? 'eye-slash' : 'eye' ?>"></i>
                                </button>
                            </form>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this challenge and all its test cases and submissions?')">
                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                <input type="hidden" name="delete_challenge" value="1">
                                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
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

<?php require_once 'admin_footer.php'; ?>
