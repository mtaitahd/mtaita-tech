<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db_connect.php';
requirePublicLogin();

require_once __DIR__ . '/lib/CodingChallenge.php';
require_once __DIR__ . '/lib/CodingSubmission.php';
require_once __DIR__ . '/lib/CodingProgress.php';

$user = getPublicUser();
$userId = $user['id'];

$submissionModel = new CodingSubmission();
$progressModel = new CodingProgress();

$history = $submissionModel->getHistoryForUser($userId, 100);
$stats = $submissionModel->getStatsForUser($userId);
$streak = $progressModel->getStreakDays($userId);
$badges = $progressModel->getBadges($userId);

$page_title = 'My Coding Submissions';
$active_page = 'coding-submissions';
require_once 'user_header.php';
?>

<div class="row g-3 mb-4">
    <div class="col">
        <div class="stat-card">
            <div class="stat-icon bg-primary"><i class="bi bi-flag"></i></div>
            <div class="stat-info">
                <h3><?= (int)($stats['attempted'] ?? 0) ?></h3>
                <p>Challenges Attempted</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card">
            <div class="stat-icon bg-success"><i class="bi bi-trophy"></i></div>
            <div class="stat-info">
                <h3><?= (int)($stats['completed'] ?? 0) ?></h3>
                <p>Challenges Completed</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card">
            <div class="stat-icon bg-warning"><i class="bi bi-fire"></i></div>
            <div class="stat-info">
                <h3><?= (int)$streak ?></h3>
                <p>Day Coding Streak</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card">
            <div class="stat-icon bg-info"><i class="bi bi-star"></i></div>
            <div class="stat-info">
                <h3><?= (int)($stats['total_xp'] ?? 0) ?></h3>
                <p>Total XP</p>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($badges)): ?>
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="admin-card">
            <h5 class="mb-3"><i class="bi bi-award text-cyan me-2"></i>Your Badges</h5>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($badges as $b): ?>
                <div class="badge text-start p-2" style="background:#0f172a;border:1px solid #1e293b;border-radius:8px;">
                    <div style="font-size:1.2rem;"><?= htmlspecialchars($b['icon']) ?></div>
                    <div class="fw-semibold text-white small"><?= htmlspecialchars($b['name']) ?></div>
                    <div class="text-muted" style="font-size:0.7rem;"><?= htmlspecialchars($b['desc']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-12">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-clock-history text-cyan me-2"></i>My Coding Submissions</h5>
                <a href="courses" class="btn btn-cyan btn-sm"><i class="bi bi-plus-lg me-1"></i>Find Challenges</a>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Challenge</th>
                            <th>Language</th>
                            <th>Score</th>
                            <th>Tests</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="7" class="text-muted text-center py-4">
                                No submissions yet. Open a coding challenge from your course and start solving!
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($history as $h): ?>
                            <tr>
                                <td>
                                    <a href="challenge?id=<?= (int)$h['challenge_id'] ?>" class="text-cyan text-decoration-none fw-semibold">
                                        <?= htmlspecialchars($h['challenge_title']) ?>
                                    </a>
                                </td>
                                <td><span class="badge" style="background:#164E63;color:#67E8F9;"><?= htmlspecialchars(strtoupper($h['challenge_language'])) ?></span></td>
                                <td>
                                    <?php $pct = $h['total_marks'] > 0 ? round(($h['score'] / $h['total_marks']) * 100) : 0; ?>
                                    <b class="<?= $pct >= $h['passing_score'] ? 'text-success' : 'text-warning' ?>"><?= (int)$pct ?>%</b>
                                    <small class="text-muted">(<?= (int)$h['score'] ?>/<?= (int)$h['total_marks'] ?>)</small>
                                </td>
                                <td><?= (int)$h['tests_passed'] ?>/<?= (int)$h['tests_total'] ?></td>
                                <td>
                                    <?php if ($h['passed']): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Passed</span>
                                    <?php elseif ($h['status'] === 'compilation_error'): ?>
                                        <span class="badge bg-danger">Compile Error</span>
                                    <?php elseif ($h['status'] === 'timeout'): ?>
                                        <span class="badge bg-warning text-dark">Timeout</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Failed</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= date('M d, Y H:i', strtotime($h['created_at'])) ?></small></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-cyan" data-bs-toggle="modal" data-bs-target="#codeModal"
                                        data-code="<?= htmlspecialchars($h['code'], ENT_QUOTES) ?>"
                                        data-title="<?= htmlspecialchars($h['challenge_title'], ENT_QUOTES) ?>">Code</button>
                                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#resultModal"
                                        data-submission="<?= (int)$h['id'] ?>">Result</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="codeModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="background:#0b1220;">
            <div class="modal-header" style="border-color:#1e293b;">
                <h5 class="modal-title text-white"><i class="bi bi-code-slash text-cyan me-2"></i><span id="codeModal-title"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="codeModal-content" class="p-3 mb-0" style="background:#0b1220;color:#e2e8f0;border:1px solid #1e293b;border-radius:8px;max-height:500px;overflow:auto;font-size:0.82rem;white-space:pre-wrap;"></pre>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="resultModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="background:#0b1220;">
            <div class="modal-header" style="border-color:#1e293b;">
                <h5 class="modal-title text-white"><i class="bi bi-clipboard-check text-cyan me-2"></i>Submission Result</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="resultModal-body">
                <div class="text-muted">Loading...</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var codeModal = document.getElementById('codeModal');
    if (codeModal) {
        codeModal.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            document.getElementById('codeModal-title').textContent = btn.dataset.title || '';
            document.getElementById('codeModal-content').textContent = btn.dataset.code || '';
        });
    }

    var resultModal = document.getElementById('resultModal');
    if (resultModal) {
        resultModal.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            var body = document.getElementById('resultModal-body');
            body.innerHTML = '<div class="text-muted">Loading...</div>';
            fetch('services/submission_result.php?submission_id=' + btn.dataset.submission, { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (j.error) { body.innerHTML = '<div class="text-danger">' + j.error + '</div>'; return; }
                    var html = '<div class="row text-center g-2 mb-3">' +
                        '<div class="col-4"><div class="text-white fw-bold fs-5">' + j.tests_total + '</div><small class="text-muted">Tests</small></div>' +
                        '<div class="col-4"><div class="text-success fw-bold fs-5">' + j.tests_passed + '</div><small class="text-muted">Passed</small></div>' +
                        '<div class="col-4"><div class="text-danger fw-bold fs-5">' + (j.tests_total - j.tests_passed) + '</div><small class="text-muted">Failed</small></div></div>';
                    html += '<div class="small text-muted mb-2">Score: <b class="text-white">' + j.score + '/' + j.total_marks + '</b> · Status: <b class="text-white">' + j.status + '</b> · Execution: ' + j.execution_time + 's</div>';
                    if (j.results && j.results.length) {
                        html += '<div class="list-group">';
                        j.results.forEach(function (r, i) {
                            var ok = r.passed ? 'success' : 'danger';
                            html += '<div class="list-group-item" style="background:#0f172a;border-color:#1e293b;">' +
                                '<div class="d-flex justify-content-between">' +
                                '<span class="text-white small fw-semibold">Test ' + (i + 1) + '</span>' +
                                '<span class="badge bg-' + ok + '">' + (r.passed ? 'Passed' : r.status.replace(/_/g, ' ')) + '</span></div>';
                            if (r.actual_output) {
                                html += '<pre class="mt-2 mb-0 small" style="background:#0b1220;color:#e2e8f0;border:1px solid #1e293b;border-radius:6px;padding:8px;white-space:pre-wrap;">' + esc(r.actual_output) + '</pre>';
                            }
                            html += '</div>';
                        });
                        html += '</div>';
                    }
                    body.innerHTML = html;
                })
                .catch(function () {
                    body.innerHTML = '<div class="text-danger">Could not load result.</div>';
                });
        });
    }
});

function esc(s) {
    return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
</script>

<?php require_once 'user_footer.php'; ?>
