<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db_connect.php';
requirePublicLogin();

require_once __DIR__ . '/lib/CodingChallenge.php';
require_once __DIR__ . '/lib/CodingSubmission.php';

$user = getPublicUser();
$userId = (int)$user['id'];

$submissionId = (int)($_GET['submission_id'] ?? 0);
$submissionModel = new CodingSubmission();
$submission = $submissionId ? $submissionModel->getById($submissionId) : null;

if (!$submission || (int)$submission['user_id'] !== $userId) {
    header('Location: coding-submissions');
    exit;
}

$challengeModel = new CodingChallenge();
$challenge = $challengeModel->getByIdWithCourse((int)$submission['challenge_id']);
if (!$challenge) {
    $challenge = ['id' => (int)$submission['challenge_id'], 'title' => 'Coding Challenge', 'course_slug' => null];
}

$results = $submissionModel->getResultsForSubmission((int)$submissionId);

$total = (int)$submission['tests_total'];
$passedCount = (int)$submission['tests_passed'];
$failedCount = max(0, $total - $passedCount);
$scorePct = $submission['total_marks'] > 0 ? (int)round(100 * (int)$submission['score'] / (int)$submission['total_marks']) : 0;
$isPassed = (bool)$submission['passed'];
$status = (string)$submission['status'];

$page_title = 'Submission Result — ' . htmlspecialchars($challenge['title']);
$active_page = 'coding-submissions';
require_once 'user_header.php';
?>

<style>
    .sr-verdict { border-radius: 14px; padding: 32px; text-align: center; }
    .sr-verdict.passed { background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(16,185,129,0.04)); border: 1px solid rgba(16,185,129,0.45); }
    .sr-verdict.failed { background: linear-gradient(135deg, rgba(239,68,68,0.15), rgba(239,68,68,0.04)); border: 1px solid rgba(239,68,68,0.45); }
    .sr-verdict .sr-icon { font-size: 3.2rem; line-height: 1; }
    .sr-verdict h3 { font-weight: 800; letter-spacing: 0.05em; margin: 10px 0 4px; }
    .sr-test { background: #0f172a; border: 1px solid #1e293b; border-radius: 10px; padding: 14px; }
    .sr-test pre { background: #0b1220; border: 1px solid #1e293b; border-radius: 6px; padding: 8px; font-size: 0.8rem; color: #e2e8f0; white-space: pre-wrap; word-break: break-word; }
</style>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="admin-card sr-verdict <?= $isPassed ? 'passed' : 'failed' ?>">
            <div class="sr-icon"><?= $isPassed ? '🎉' : '❌' ?></div>
            <h3 class="text-<?= $isPassed ? 'success' : 'danger' ?>"><?= $isPassed ? 'PASSED' : 'FAILED' ?></h3>
            <p class="text-muted mb-1 fw-semibold"><?= htmlspecialchars($challenge['title']) ?></p>
            <p class="text-muted mb-0 small">
                Status: <b class="text-white"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $status))) ?></b>
                &middot; Submitted <?= date('M d, Y H:i', strtotime($submission['created_at'])) ?>
            </p>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg">
        <div class="stat-card">
            <div class="stat-icon <?= $scorePct >= (int)$challenge['passing_score'] && $isPassed ? 'bg-success' : 'bg-primary' ?>"><i class="bi bi-award"></i></div>
            <div class="stat-info">
                <h3><?= $scorePct ?>%</h3>
                <p>Score (<?= (int)$submission['score'] ?>/<?= (int)$submission['total_marks'] ?> marks)</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg">
        <div class="stat-card">
            <div class="stat-icon bg-success"><i class="bi bi-check-circle"></i></div>
            <div class="stat-info">
                <h3><?= $passedCount ?>/<?= $total ?></h3>
                <p>Tests Passed</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg">
        <div class="stat-card">
            <div class="stat-icon bg-danger"><i class="bi bi-x-circle"></i></div>
            <div class="stat-info">
                <h3><?= $failedCount ?></h3>
                <p>Tests Failed</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg">
        <div class="stat-card">
            <div class="stat-icon bg-info"><i class="bi bi-stopwatch"></i></div>
            <div class="stat-info">
                <h3><?= round((float)$submission['execution_time'], 2) ?>s</h3>
                <p>Execution Time</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="admin-card">
            <h5 class="mb-3"><i class="bi bi-clipboard-check text-cyan me-2"></i>Test Case Details</h5>
            <?php if (empty($results)): ?>
                <p class="text-muted mb-0">No test results recorded for this submission.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($results as $i => $r): ?>
                        <?php
                        $st = (string)$r['status'];
                        $ok = (bool)$r['passed'];
                        $badgeCls = 'bg-success';
                        $badgeText = 'Passed';
                        if (!$ok) {
                            if ($st === 'timeout') { $badgeCls = 'bg-warning text-dark'; $badgeText = 'Timeout'; }
                            elseif ($st === 'compile_error') { $badgeCls = 'bg-danger'; $badgeText = 'Compile Error'; }
                            elseif ($st === 'skipped') { $badgeCls = 'bg-secondary'; $badgeText = 'Skipped'; }
                            else { $badgeCls = 'bg-danger'; $badgeText = $st === 'error' ? 'Runtime Error' : 'Failed'; }
                        }
                        ?>
                        <div class="sr-test">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold text-white small">
                                    Test #<?= $i + 1 ?> <?= !empty($r['is_visible']) ? '<span class="text-muted" style="font-weight:400;">(visible)</span>' : '<span class="text-muted" style="font-weight:400;">(hidden)</span>' ?>
                                </span>
                                <span class="badge <?= $badgeCls ?>"><?= $badgeText ?></span>
                            </div>
                            <?php if ($r['actual_output'] !== null && $r['actual_output'] !== ''): ?>
                                <div class="small text-muted mb-1">Your output:</div>
                                <pre><?= htmlspecialchars((string)$r['actual_output']) ?></pre>
                            <?php endif; ?>
                            <?php if (!empty($r['is_visible'])): ?>
                                <?php if (($r['input_data'] ?? '') !== ''): ?>
                                    <div class="small text-muted mb-1">Input:</div>
                                    <pre><?= htmlspecialchars((string)$r['input_data']) ?></pre>
                                <?php endif; ?>
                                <?php if (($r['expected_output'] ?? '') !== ''): ?>
                                    <div class="small text-muted mb-1">Expected output:</div>
                                    <pre><?= htmlspecialchars((string)$r['expected_output']) ?></pre>
                                <?php endif; ?>
                            <?php endif; ?>
                            <div class="small text-muted mt-1">Executed in <?= round((float)$r['execution_time'], 4) ?>s</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="dashboard" class="btn btn-cyan px-4"><i class="bi bi-speedometer2 me-1"></i>Return to Dashboard</a>
            <a href="challenge?id=<?= (int)$challenge['id'] ?>" class="btn btn-outline-secondary px-4"><i class="bi bi-arrow-clockwise me-1"></i>Try Again</a>
            <a href="coding-submissions" class="btn btn-outline-secondary px-4"><i class="bi bi-clock-history me-1"></i>My Submissions</a>
        </div>
    </div>
</div>

<?php require_once 'user_footer.php'; ?>
