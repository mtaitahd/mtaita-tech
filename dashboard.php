<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db_connect.php';
requirePublicLogin();

require_once __DIR__ . '/lib/Enrollment.php';
require_once __DIR__ . '/lib/Course.php';
require_once __DIR__ . '/lib/Order.php';
require_once __DIR__ . '/lib/CourseAccess.php';
require_once __DIR__ . '/lib/LessonProgress.php';

$user = getPublicUser();
$userId = $user['id'];
$enrollment = new Enrollment();
$courseModel = new Course();
$order = new Order();
$courseAccess = new CourseAccess();

foreach ($courseAccess->getGrantedCourseIds($userId) as $grantCourseId) {
    $enrollment->ensureEnrollment($userId, $grantCourseId);
}

$myCourses = $enrollment->getCoursesForUser($userId);
$purchases = $order->getByUserId($userId);

$totalEnrolled = count($myCourses);
$totalCompletedLessons = 0;
$totalLessonsAvailable = 0;
if ($totalEnrolled > 0) {
    $lp = new LessonProgress();
    $courseIds = array_column($myCourses, 'course_id');
    foreach ($courseIds as $cid) {
        $totalCompletedLessons += $lp->countCompletedInCourse($userId, (int)$cid);
        $totalLessonsAvailable += $courseModel->countLessons((int)$cid);
    }
}
$overallProgress = $totalLessonsAvailable > 0 ? round(($totalCompletedLessons / $totalLessonsAvailable) * 100) : 0;

$page_title = 'Dashboard';
$active_page = 'dashboard';
require_once 'user_header.php';
?>
<div class="row g-3 mb-4">
    <div class="col">
        <div class="stat-card">
            <div class="stat-icon bg-primary"><i class="bi bi-book"></i></div>
            <div class="stat-info">
                <h3><?= $totalEnrolled ?></h3>
                <p>Enrolled Courses</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card">
            <div class="stat-icon bg-success"><i class="bi bi-check-circle"></i></div>
            <div class="stat-info">
                <h3><?= $totalCompletedLessons ?></h3>
                <p>Completed Lessons</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card">
            <div class="stat-icon bg-info"><i class="bi bi-journal"></i></div>
            <div class="stat-info">
                <h3><?= $totalLessonsAvailable ?></h3>
                <p>Total Lessons</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="stat-card">
            <div class="stat-icon bg-warning"><i class="bi bi-graph-up"></i></div>
            <div class="stat-info">
                <h3><?= $overallProgress ?>%</h3>
                <p>Overall Progress</p>
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
                                <th>Progress</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myCourses as $mc): ?>
                            <?php
                            $c = $courseModel->getById((int)$mc['course_id']);
                            $ids = $c ? $courseModel->getLessonIdsOrdered((int)$mc['course_id']) : [];
                            $resume = !empty($ids) ? 'lesson.php?id=' . (int)$ids[0] : 'single-course.php?slug=' . urlencode($mc['slug']);
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
