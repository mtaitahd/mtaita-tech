<?php
$page_title = 'Coding Analytics';
$active_page = 'coding-analytics';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../lib/CodingSubmission.php';

$submissionModel = new CodingSubmission();
$stats = $submissionModel->getTotalStats();
$mostAttempted = $submissionModel->getMostAttempted(5);
$mostDifficult = $submissionModel->getMostDifficult(5);
$languages = $submissionModel->getPopularLanguages();
$students = $submissionModel->getStudentPerformance(10);

$daily = $pdo->query("
    SELECT DATE(created_at) AS d, COUNT(*) AS total, SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) AS passed
    FROM coding_submissions
    WHERE created_at >= (NOW() - INTERVAL 14 DAY)
    GROUP BY DATE(created_at)
    ORDER BY d ASC
")->fetchAll();

require_once 'admin_header.php';
?>

<div class="row row-cols-2 row-cols-md-3 row-cols-xl-6 g-3 mb-4">
    <div class="col">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-code-slash"></i></div>
            <div class="stat-info">
                <h3><?= (int)($stats['total_challenges'] ?? 0) ?></h3>
                <p>Total Challenges</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-send"></i></div>
            <div class="stat-info">
                <h3><?= (int)($stats['total_submissions'] ?? 0) ?></h3>
                <p>Total Submissions</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-info">
                <h3><?= (int)($stats['passed_submissions'] ?? 0) ?></h3>
                <p>Passed</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
            <div class="stat-info">
                <h3><?= (int)($stats['failed_submissions'] ?? 0) ?></h3>
                <p>Failed</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div class="stat-info">
                <h3><?= (int)($stats['active_students'] ?? 0) ?></h3>
                <p>Active Coders</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-bar-chart"></i></div>
            <div class="stat-info">
                <h3><?= ($stats['total_submissions'] ?? 0) > 0 ? round((($stats['passed_submissions'] ?? 0) / $stats['total_submissions']) * 100) : 0 ?>%</h3>
                <p>Pass Rate</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="admin-card">
            <h5><i class="bi bi-activity text-cyan me-2"></i>Submissions — Last 14 Days</h5>
            <canvas id="dailyChart" height="110"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="admin-card">
            <h5><i class="bi bi-bar-chart text-cyan me-2"></i>Popular Languages</h5>
            <canvas id="langChart" height="200"></canvas>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-6">
        <div class="admin-card">
            <h5><i class="bi bi-fire text-cyan me-2"></i>Most Attempted Challenges</h5>
            <canvas id="attemptedChart" height="180"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="admin-card">
            <h5><i class="bi bi-graph-down text-cyan me-2"></i>Most Difficult (Lowest Pass Rate)</h5>
            <canvas id="difficultChart" height="180"></canvas>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="admin-card">
            <h5><i class="bi bi-trophy text-cyan me-2"></i>Student Coding Performance</h5>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Submissions</th>
                            <th>Challenges Passed</th>
                            <th>Total XP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                        <tr><td colspan="4" class="text-muted text-center py-4">No student submissions yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($students as $s): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($s['name']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($s['email']) ?></div>
                                </td>
                                <td><?= (int)$s['submissions'] ?></td>
                                <td><span class="badge bg-success"><?= (int)$s['challenges_passed'] ?></span></td>
                                <td><?= (int)$s['total_xp'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    Chart.defaults.global.defaultFontColor = '#94A3B8';
    Chart.defaults.global.legend.labels.boxWidth = 12;

    var dailyLabels = <?= json_encode(array_column($daily, 'd')) ?>;
    var dailyTotal = <?= json_encode(array_map('intval', array_column($daily, 'total'))) ?>;
    var dailyPassed = <?= json_encode(array_map('intval', array_column($daily, 'passed'))) ?>;

    new Chart(document.getElementById('dailyChart'), {
        type: 'bar',
        data: {
            labels: dailyLabels,
            datasets: [
                { label: 'Total', data: dailyTotal, backgroundColor: '#0EA5E9' },
                { label: 'Passed', data: dailyPassed, backgroundColor: '#22C55E' }
            ]
        },
        options: {
            scales: { xAxes: [{ stacked: false }], yAxes: [{ ticks: { beginAtZero: true, stepSize: 1 } }] }
        }
    });

    var langLabels = <?= json_encode(array_map(function ($l) { return strtoupper($l['language']); }, $languages)) ?>;
    var langData = <?= json_encode(array_map(function ($l) { return (int)$l['total']; }, $languages)) ?>;
    var langColors = ['#0EA5E9', '#22C55E', '#F59E0B', '#EF4444', '#8B5CF6', '#14B8A6', '#F97316'];
    new Chart(document.getElementById('langChart'), {
        type: 'pie',
        data: {
            labels: langLabels,
            datasets: [{ data: langData, backgroundColor: langColors }]
        },
        options: { legend: { position: 'bottom' } }
    });

    var attLabels = <?= json_encode(array_column($mostAttempted, 'title')) ?>;
    var attData = <?= json_encode(array_map('intval', array_column($mostAttempted, 'attempts'))) ?>;
    new Chart(document.getElementById('attemptedChart'), {
        type: 'horizontalBar',
        data: {
            labels: attLabels,
            datasets: [{ label: 'Attempts', data: attData, backgroundColor: '#0EA5E9' }]
        },
        options: { scales: { xAxes: [{ ticks: { beginAtZero: true, stepSize: 1 } }] } }
    });

    var diffLabels = <?= json_encode(array_column($mostDifficult, 'title')) ?>;
    var diffData = <?= json_encode(array_map(function ($d) { return (float)$d['pass_rate']; }, $mostDifficult)) ?>;
    new Chart(document.getElementById('difficultChart'), {
        type: 'horizontalBar',
        data: {
            labels: diffLabels,
            datasets: [{ label: 'Pass Rate (%)', data: diffData, backgroundColor: '#EF4444' }]
        },
        options: { scales: { xAxes: [{ ticks: { beginAtZero: true, max: 100 } }] } }
    });
});
</script>

<?php require_once 'admin_footer.php'; ?>
