<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db_connect.php';
requirePublicLogin();

require_once __DIR__ . '/lib/Enrollment.php';
require_once __DIR__ . '/lib/Course.php';
require_once __DIR__ . '/lib/Order.php';
require_once __DIR__ . '/lib/CourseAccess.php';
require_once __DIR__ . '/lib/LessonProgress.php';
require_once __DIR__ . '/lib/CodingChallenge.php';
require_once __DIR__ . '/lib/CodingSubmission.php';
require_once __DIR__ . '/lib/CodingProgress.php';

$user = getPublicUser();
$userId = $user['id'];
$enrollment = new Enrollment();
$courseModel = new Course();
$order = new Order();
$courseAccess = new CourseAccess();
$codingProgress = new CodingProgress();
$codingSubmissionModel = new CodingSubmission();

foreach ($courseAccess->getGrantedCourseIds($userId) as $grantCourseId) {
    $enrollment->ensureEnrollment($userId, $grantCourseId);
}

$myCourses = $enrollment->getCoursesForUser($userId);
$purchases = $order->getByUserId($userId);

$totalEnrolled = count($myCourses);
$totalCompletedLessons = 0;
$totalLessonsAvailable = 0;
$totalCompletedChallenges = 0;
$totalChallengesAvailable = 0;
if ($totalEnrolled > 0) {
    $lp = new LessonProgress();
    $courseIds = array_column($myCourses, 'course_id');
    foreach ($courseIds as $cid) {
        $cid = (int)$cid;
        $totalCompletedLessons += $lp->countCompletedInCourse($userId, $cid);
        $totalLessonsAvailable += $courseModel->countLessons($cid);
        $totalCompletedChallenges += $codingProgress->countCompletedChallengesInCourse($userId, $cid);
        $totalChallengesAvailable += $codingProgress->countTotalChallengesInCourse($cid);
    }
}
$totalItemsAvailable = $totalLessonsAvailable + $totalChallengesAvailable;
$totalItemsCompleted = $totalCompletedLessons + $totalCompletedChallenges;
$overallProgress = $totalItemsAvailable > 0 ? round(($totalItemsCompleted / $totalItemsAvailable) * 100) : 0;
$codingStreak = $codingProgress->getStreakDays($userId);
$codingStats = $codingSubmissionModel->getStatsForUser($userId);

$page_title = 'Dashboard';
$active_page = 'dashboard';
require_once 'user_header.php';
?>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg">
        <div class="stat-card">
            <div class="stat-icon bg-primary"><i class="bi bi-book"></i></div>
            <div class="stat-info">
                <h3><?= $totalEnrolled ?></h3>
                <p>Enrolled Courses</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg">
        <div class="stat-card">
            <div class="stat-icon bg-success"><i class="bi bi-check-circle"></i></div>
            <div class="stat-info">
                <h3><?= $totalCompletedLessons ?></h3>
                <p>Completed Lessons</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg">
        <div class="stat-card">
            <div class="stat-icon bg-info"><i class="bi bi-journal"></i></div>
            <div class="stat-info">
                <h3><?= $totalLessonsAvailable ?></h3>
                <p>Total Lessons</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg">
        <div class="stat-card">
            <div class="stat-icon bg-warning"><i class="bi bi-graph-up"></i></div>
            <div class="stat-info">
                <h3><?= $overallProgress ?>%</h3>
                <p>Overall Progress</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg">
        <div class="stat-card">
            <div class="stat-icon bg-danger"><i class="bi bi-code-slash"></i></div>
            <div class="stat-info">
                <h3><?= $totalCompletedChallenges ?><small class="text-muted fs-6">/<?= $totalChallengesAvailable ?></small></h3>
                <p>Coding Challenges</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="mb-0"><i class="bi bi-code-square text-cyan me-2"></i>Coding Bootcamp</h5>
                <div class="d-flex gap-2">
                    <a href="coding-submissions" class="btn btn-outline-cyan btn-sm"><i class="bi bi-clock-history me-1"></i>My Submissions</a>
                    <a href="coding-challenges" class="btn btn-cyan btn-sm"><i class="bi bi-play me-1"></i>Start Coding</a>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3 p-3 rounded" style="background:rgba(6,182,212,0.08);border:1px solid rgba(6,182,212,0.2);">
                        <i class="bi bi-send fs-3" style="color:#06B6D4;"></i>
                        <div>
                            <div class="fs-4 fw-bold"><?= (int)($codingStats['total_submissions'] ?? 0) ?></div>
                            <div class="small text-muted">Submissions</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3 p-3 rounded" style="background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.2);">
                        <i class="bi bi-trophy fs-3" style="color:#22C55E;"></i>
                        <div>
                            <div class="fs-4 fw-bold"><?= (int)($codingStats['passed_submissions'] ?? 0) ?></div>
                            <div class="small text-muted">Challenges Passed</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3 p-3 rounded" style="background:rgba(250,204,21,0.08);border:1px solid rgba(250,204,21,0.2);">
                        <i class="bi bi-stars fs-3" style="color:#FACC15;"></i>
                        <div>
                            <div class="fs-4 fw-bold"><?= (int)($codingStats['total_xp'] ?? 0) ?></div>
                            <div class="small text-muted">XP Earned</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="d-flex align-items-center gap-3 p-3 rounded" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);">
                        <i class="bi bi-fire fs-3" style="color:#EF4444;"></i>
                        <div>
                            <div class="fs-4 fw-bold"><?= (int)$codingStreak ?></div>
                            <div class="small text-muted">Day Streak</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0"><i class="bi bi-graph-up text-cyan me-2"></i>Overall Progress</h5>
                <span class="text-cyan fw-bold"><?= $overallProgress ?>%</span>
            </div>
            <div class="progress" style="height:10px;">
                <div class="progress-bar bg-cyan" style="width:<?= $overallProgress ?>%"></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-book text-cyan me-2"></i>My Courses</h5>
                <a href="my-courses" class="btn btn-cyan btn-sm">View All</a>
            </div>
            <?php if (empty($myCourses)): ?>
                <div class="text-center py-4">
                    <p class="text-muted mb-3">Not enrolled in any course yet.</p>
                    <a href="courses" class="btn btn-cyan btn-sm">Browse courses</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Lessons</th>
                                <th>Challenges</th>
                                <th>Progress</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myCourses as $mc): ?>
                            <?php
                            $cid = (int)$mc['course_id'];
                            $c = $courseModel->getById($cid);
                            $ids = $c ? $courseModel->getLessonIdsOrdered($cid) : [];
                            $resume = !empty($ids) ? 'lesson.php?id=' . (int)$ids[0] : 'single-course.php?slug=' . urlencode($mc['slug']);
                            $lp = new LessonProgress();
                            $lessonsDone = $lp->countCompletedInCourse($userId, $cid);
                            $lessonsTotal = $courseModel->countLessons($cid);
                            $chalDone = $codingProgress->countCompletedChallengesInCourse($userId, $cid);
                            $chalTotal = $codingProgress->countTotalChallengesInCourse($cid);
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($mc['thumbnail'])): ?>
                                            <img src="<?= htmlspecialchars($mc['thumbnail']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px;" alt="">
                                        <?php else: ?>
                                            <div style="width:40px;height:40px;background:#1e293b;border-radius:6px;display:flex;align-items:center;justify-content:center;"><i class="bi bi-book text-muted"></i></div>
                                        <?php endif; ?>
                                        <span class="fw-semibold"><?= htmlspecialchars($mc['title']) ?></span>
                                    </div>
                                </td>
                                <td><small class="text-muted"><?= $lessonsDone ?>/<?= $lessonsTotal ?></small></td>
                                <td>
                                    <?php if ($chalTotal > 0): ?>
                                        <a href="single-course?slug=<?= urlencode($mc['slug']) ?>" class="text-decoration-none">
                                            <small class="text-cyan"><?= $chalDone ?>/<?= $chalTotal ?> <i class="bi bi-code-slash"></i></small>
                                        </a>
                                    <?php else: ?>
                                        <small class="text-muted">—</small>
                                    <?php endif; ?>
                                </td>
                                <td style="width:200px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1 mb-0" style="height:6px;">
                                            <div class="progress-bar bg-cyan" style="width:<?= min(100, (float)$mc['progress']) ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?= number_format((float)$mc['progress'], 0) ?>%</small>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <a href="<?= $resume ?>" class="btn btn-cyan btn-sm">Resume</a>
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

<div class="row g-3">
    <div class="col-12">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-bag text-cyan me-2"></i>Purchases</h5>
                <a href="digital_products" class="btn btn-outline-cyan btn-sm">Browse Products</a>
            </div>
            <?php if (empty($purchases)): ?>
                <div class="text-center py-4">
                    <p class="text-muted mb-0">No purchases yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($purchases as $p): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if ($p['thumbnail']): ?>
                                            <img src="<?= htmlspecialchars($p['thumbnail']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px;" alt="">
                                        <?php else: ?>
                                            <div style="width:40px;height:40px;background:#1e293b;border-radius:6px;display:flex;align-items:center;justify-content:center;"><i class="bi bi-box text-muted"></i></div>
                                        <?php endif; ?>
                                        <span class="fw-semibold"><?= htmlspecialchars($p['product_title']) ?></span>
                                    </div>
                                </td>
                                <td><small class="text-muted"><?= date('M d, Y', strtotime($p['created_at'])) ?></small></td>
                                <td class="text-end">
                                    <?php if ($p['youtube_url']): ?>
                                        <a href="product-detail?id=<?= $p['product_id'] ?>" class="btn btn-sm btn-outline-info me-1"><i class="fas fa-play"></i></a>
                                    <?php endif; ?>
                                    <?php if ($p['zip_file']): ?>
                                        <a href="download-product?id=<?= $p['product_id'] ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-download"></i></a>
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
