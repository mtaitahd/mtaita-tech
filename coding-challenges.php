<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db_connect.php';
requirePublicLogin();

require_once __DIR__ . '/lib/CodingChallenge.php';
require_once __DIR__ . '/lib/CodingSubmission.php';
require_once __DIR__ . '/lib/AccessControl.php';

$user = getPublicUser();
$userId = $user['id'];

$challengeModel = new CodingChallenge();
$submissionModel = new CodingSubmission();
$accessControl = new AccessControl();

$challenges = $challengeModel->getAllPublished();

$passedIds = [];
$attemptedIds = [];
if ($userId) {
    foreach ($submissionModel->getBestPerChallenge($userId) as $bs) {
        $attemptedIds[(int)$bs['challenge_id']] = true;
        if ((int)$bs['passed'] === 1) $passedIds[(int)$bs['challenge_id']] = true;
    }
}

$page_title = 'Coding Challenges';
$active_page = 'coding-challenges';
require_once 'user_header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="mb-0"><i class="bi bi-code-square text-cyan me-2"></i>Coding Challenges</h5>
                <span class="text-muted small"><?= count($challenges) ?> challenge<?= count($challenges) === 1 ? '' : 's' ?> available</span>
            </div>
            <?php if (empty($challenges)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-code-slash" style="font-size:3rem;color:#334155;"></i>
                    <p class="text-muted mt-3 mb-0">No coding challenges available yet. Check back soon!</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Challenge</th>
                                <th>Course</th>
                                <th>Language</th>
                                <th>Difficulty</th>
                                <th>Marks</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($challenges as $cc): ?>
                            <?php
                            $cid = (int)$cc['id'];
                            $isPassed = !empty($passedIds[$cid]);
                            $isAttempted = !empty($attemptedIds[$cid]);
                            $canOpen = $accessControl->hasCourseAccess($userId, ['id' => (int)$cc['course_id'], 'type' => $cc['course_type']]);
                            $link = $canOpen ? 'challenge?id=' . $cid : 'single-course?slug=' . urlencode($cc['course_slug']);
                            ?>
                            <tr>
                                <td>
                                    <span class="fw-semibold"><?= htmlspecialchars($cc['title']) ?></span>
                                    <?php if (!empty($cc['lesson_title'])): ?>
                                        <div class="small text-muted"><?= htmlspecialchars($cc['lesson_title']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="single-course?slug=<?= urlencode($cc['course_slug']) ?>" class="text-cyan text-decoration-none"><?= htmlspecialchars($cc['course_title']) ?></a>
                                </td>
                                <td><span class="badge" style="background:#164E63;color:#67E8F9;"><?= strtoupper(htmlspecialchars($cc['language'])) ?></span></td>
                                <td>
                                    <span class="badge bg-<?= $cc['difficulty'] === 'easy' ? 'success' : ($cc['difficulty'] === 'medium' ? 'warning text-dark' : 'danger') ?>">
                                        <?= ucfirst($cc['difficulty']) ?>
                                    </span>
                                </td>
                                <td><?= (int)$cc['marks'] ?></td>
                                <td>
                                    <?php if ($isPassed): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Completed</span>
                                    <?php elseif ($isAttempted): ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Attempted</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Not started</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if (!$canOpen): ?>
                                        <a href="<?= $link ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-lock me-1"></i>Enroll</a>
                                    <?php else: ?>
                                        <a href="<?= $link ?>" class="btn btn-sm btn-cyan"><i class="bi bi-play me-1"></i>Solve</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'user_footer.php'; ?>
