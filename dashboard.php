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

// Ensure granted access creates enrollment rows
foreach ($courseAccess->getGrantedCourseIds($userId) as $grantCourseId) {
    $enrollment->ensureEnrollment($userId, $grantCourseId);
}

$myCourses = $enrollment->getCoursesForUser($userId);
$purchases = $order->getByUserId($userId);

// Learning analytics
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

$get_msg = $_GET['msg'] ?? '';

$page_title = 'Dashboard — Mtaita Tech';
$page_desc = 'Your learning dashboard on Mtaita Tech.';
$page_keywords = 'dashboard, my courses, Mtaita Tech';
$hide_navbar = true;
require_once 'header.php';
?>
<?php if ($get_msg): ?><div class="d-none swal-msg" data-type="success"><?= htmlspecialchars($get_msg) ?></div><?php endif; ?>

<section class="dashboard-section section-padding">
    <div class="container">
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="mt-glass p-3 rounded-4">
                    <div class="text-center mb-3">
                        <div style="width:60px;height:60px;border-radius:50%;background:#dc2626;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:24px;font-weight:700;color:#fff;"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                        <h6 class="text-white mt-2 mb-0"><?= htmlspecialchars($user['name']) ?></h6>
                        <small class="text-secondary"><?= htmlspecialchars($user['email']) ?></small>
                    </div>
                    <hr class="border-secondary">
                    <nav class="d-flex flex-column gap-1">
                        <a href="dashboard" class="btn btn-primary btn-sm text-start"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                        <a href="my-courses" class="btn btn-outline-light btn-sm text-start"><i class="fas fa-book me-2"></i>My Courses</a>
                        <a href="profile" class="btn btn-outline-light btn-sm text-start"><i class="fas fa-user me-2"></i>My Profile</a>
                        <a href="digital_products" class="btn btn-outline-light btn-sm text-start"><i class="fas fa-box me-2"></i>Digital Products</a>
                        <a href="courses" class="btn btn-outline-light btn-sm text-start"><i class="fas fa-search me-2"></i>Browse Courses</a>
                        <a href="logout" class="btn btn-outline-danger btn-sm text-start mt-2"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                    </nav>
                </div>
            </div>
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="text-white fw-bold mb-1">Dashboard</h4>
                        <p class="text-secondary mb-0 small">Welcome back, <?= htmlspecialchars($user['name']) ?></p>
                    </div>
                    <div>
                        <a href="courses" class="btn btn-primary btn-sm"><i class="fas fa-graduation-cap me-1"></i>Browse Courses</a>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="mt-glass p-3 rounded-4 text-center">
                            <div class="text-primary h3 fw-bold mb-0"><?= $totalEnrolled ?></div>
                            <small class="text-secondary">Enrolled</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="mt-glass p-3 rounded-4 text-center">
                            <div class="text-primary h3 fw-bold mb-0"><?= $totalCompletedLessons ?></div>
                            <small class="text-secondary">Completed</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="mt-glass p-3 rounded-4 text-center">
                            <div class="text-primary h3 fw-bold mb-0"><?= $totalLessonsAvailable ?></div>
                            <small class="text-secondary">Lessons</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="mt-glass p-3 rounded-4 text-center">
                            <div class="text-primary h3 fw-bold mb-0"><?= $overallProgress ?>%</div>
                            <small class="text-secondary">Progress</small>
                        </div>
                    </div>
                </div>

                <div class="mt-glass p-3 rounded-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-white fw-bold mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Overall Progress</h6>
                        <span class="text-primary fw-bold small"><?= $overallProgress ?>%</span>
                    </div>
                    <div class="progress" style="height:10px;">
                        <div class="progress-bar bg-primary" style="width:<?= $overallProgress ?>%"></div>
                    </div>
                </div>

                <div class="mt-glass rounded-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom border-secondary">
                        <h6 class="text-white fw-bold mb-0"><i class="fas fa-book me-2 text-primary"></i>My Courses</h6>
                        <a href="my-courses" class="btn btn-outline-primary btn-sm">View All</a>
                    </div>
                    <?php if (empty($myCourses)): ?>
                        <div class="text-center py-4">
                            <p class="text-secondary mb-3">Not enrolled in any course yet.</p>
                            <a href="courses" class="btn btn-primary btn-sm">Browse courses</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-borderless mb-0">
                                <thead><tr><th>Course</th><th>Progress</th><th></th></tr></thead>
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
                                                    <div style="width:40px;height:40px;background:#1e293b;border-radius:6px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-book text-secondary"></i></div>
                                                <?php endif; ?>
                                                <span class="text-white fw-semibold small"><?= htmlspecialchars($mc['title']) ?></span>
                                            </div>
                                        </td>
                                        <td style="width:200px;">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height:6px;">
                                                    <div class="progress-bar bg-primary" style="width:<?= min(100, (float)$mc['progress']) ?>%"></div>
                                                </div>
                                                <small class="text-secondary"><?= number_format((float)$mc['progress'], 0) ?>%</small>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= $resume ?>" class="btn btn-sm btn-primary">Resume</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-glass rounded-4">
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom border-secondary">
                        <h6 class="text-white fw-bold mb-0"><i class="fas fa-shopping-bag me-2 text-primary"></i>Purchases</h6>
                        <a href="digital_products" class="btn btn-outline-primary btn-sm">Browse Products</a>
                    </div>
                    <?php if (empty($purchases)): ?>
                        <div class="text-center py-4">
                            <p class="text-secondary mb-0">No purchases yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-borderless mb-0">
                                <thead><tr><th>Product</th><th>Date</th><th></th></tr></thead>
                                <tbody>
                                    <?php foreach ($purchases as $p): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <?php if ($p['thumbnail']): ?>
                                                    <img src="<?= htmlspecialchars($p['thumbnail']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px;" alt="">
                                                <?php else: ?>
                                                    <div style="width:40px;height:40px;background:#1e293b;border-radius:6px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-box text-secondary"></i></div>
                                                <?php endif; ?>
                                                <span class="text-white fw-semibold small"><?= htmlspecialchars($p['product_title']) ?></span>
                                            </div>
                                        </td>
                                        <td><small class="text-secondary"><?= date('M d, Y', strtotime($p['created_at'])) ?></small></td>
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
    </div>
</section>

<?php require_once 'footer.php'; ?>
