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
        <div class="mt-dash-hero text-white mb-4" data-aos="fade-up">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                <div>
                    <p class="text-secondary small text-uppercase mb-1 fw-semibold">Learner workspace</p>
                    <h1 class="fw-bold mb-2" style="font-size:1.75rem;">Welcome, <?= htmlspecialchars($user['name']) ?>!</h1>
                    <p class="text-secondary mb-0">Track courses, resume lessons, and manage digital purchases.</p>
                </div>
                <div class="mt-quick-actions d-flex flex-wrap gap-2">
                    <a href="courses" class="btn btn-primary mt-btn-glow"><i class="fas fa-graduation-cap me-2"></i>Browse courses</a>
                    <a href="digital_products" class="btn btn-outline-light"><i class="fas fa-box me-2"></i>Digital products</a>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-5">
            <div class="col-md-4" data-aos="fade-up">
                <a href="courses" class="text-decoration-none">
                    <div class="mt-stat-card">
                        <div class="icon-wrap"><i class="fas fa-book-open"></i></div>
                        <h3 class="h4 text-white mb-1"><?= count($myCourses) ?></h3>
                        <p class="text-secondary small mb-0">My courses</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="60">
                <div class="mt-stat-card">
                    <div class="icon-wrap"><i class="fas fa-shopping-bag"></i></div>
                    <h3 class="h4 text-white mb-1"><?= count($purchases) ?></h3>
                    <p class="text-secondary small mb-0">Product purchases</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="120">
                <div class="mt-stat-card">
                    <div class="icon-wrap"><i class="fas fa-bell"></i></div>
                    <h3 class="h4 text-white mb-1">Updates</h3>
                    <p class="text-secondary small mb-0">New modules appear here as your instructors publish them.</p>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-8" data-aos="fade-right">
                <div class="mt-glass p-4 rounded-4 mb-4">
                    <h6 class="text-white fw-bold mb-3"><i class="fas fa-chart-line me-2 text-primary"></i>Learning analytics</h6>
                    <div class="row g-3">
                        <div class="col-6 col-md-3 text-center">
                            <div class="text-primary h3 fw-bold mb-0"><?= $totalEnrolled ?></div>
                            <small class="text-secondary">Courses enrolled</small>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="text-primary h3 fw-bold mb-0"><?= $totalCompletedLessons ?></div>
                            <small class="text-secondary">Lessons done</small>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="text-primary h3 fw-bold mb-0"><?= $totalLessonsAvailable ?></div>
                            <small class="text-secondary">Total lessons</small>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="text-primary h3 fw-bold mb-0"><?= $overallProgress ?>%</div>
                            <small class="text-secondary">Overall progress</small>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height:10px;">
                        <div class="progress-bar bg-primary" style="width:<?= $overallProgress ?>%"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-left">
                <div class="mt-glass p-4 rounded-4 h-100">
                    <h6 class="text-white fw-bold mb-3"><i class="fas fa-bolt me-2 text-primary"></i>Quick actions</h6>
                    <div class="d-grid gap-2 mt-quick-actions">
                        <a href="courses" class="btn btn-outline-primary btn-sm">Find a new course</a>
                        <a href="courses" class="btn btn-outline-light btn-sm">My enrolled courses</a>
                        <a href="contact" class="btn btn-outline-secondary btn-sm">Contact support</a>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mb-3 text-white" data-aos="fade-up"><i class="fas fa-graduation-cap me-2 text-primary"></i>My courses</h3>
        <div class="row mb-5">
            <?php if (empty($myCourses)): ?>
                <div class="col-12">
                    <div class="card border-0 text-white mt-glass">
                        <div class="card-body text-center py-5">
                            <p class="text-secondary mb-3">You are not enrolled in any course yet.</p>
                            <a href="courses" class="btn btn-primary mt-btn-glow px-4">Browse courses</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($myCourses as $mc): ?>
                    <div class="col-md-4 mb-4" data-aos="fade-up">
                        <div class="card border-0 text-white h-100 mt-glass mt-card-hover">
                            <?php if (!empty($mc['thumbnail'])): ?>
                                <img src="<?= htmlspecialchars($mc['thumbnail']) ?>" class="card-img-top" style="height:140px;object-fit:cover;" alt="" loading="lazy">
                            <?php else: ?>
                                <div class="card-img-top bg-dark d-flex align-items-center justify-content-center" style="height:140px;">
                                    <i class="fas fa-book fa-3x text-secondary"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5><?= htmlspecialchars($mc['title']) ?></h5>
                                <div class="progress mb-2" style="height:8px;">
                                    <div class="progress-bar bg-primary" style="width:<?= min(100, (float)$mc['progress']) ?>%"></div>
                                </div>
                                <small class="text-secondary"><?= number_format((float)$mc['progress'], 0) ?>% enrolled</small>
                                <?php
                                $c = $courseModel->getById((int)$mc['course_id']);
                                $ids = $c ? $courseModel->getLessonIdsOrdered((int)$mc['course_id']) : [];
                                $resume = !empty($ids) ? 'lesson.php?id=' . (int)$ids[0] : 'single-course.php?slug=' . urlencode($mc['slug']);
                                ?>
                                <div class="mt-3">
                                    <a href="<?= $resume ?>" class="btn btn-sm btn-primary">Resume</a>
                                    <a href="single-course?slug=<?= urlencode($mc['slug']) ?>" class="btn btn-sm btn-outline-light">Outline</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <h3 class="mb-3 text-white" data-aos="fade-up"><i class="fas fa-shopping-bag me-2 text-primary"></i>Digital product purchases</h3>
        <div class="row mb-4">
            <div class="col-12">
                <?php if (empty($purchases)): ?>
                    <div class="text-center py-4 mt-glass rounded-4">
                        <p class="text-secondary mb-3">No product purchases yet</p>
                        <a href="digital_products" class="btn btn-outline-primary mt-btn-glow">Browse digital products</a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($purchases as $p): ?>
                            <div class="col-md-4 mb-4" data-aos="fade-up">
                                <div class="card border-0 text-white h-100 mt-glass mt-card-hover">
                                    <?php if ($p['thumbnail']): ?>
                                        <img src="<?= htmlspecialchars($p['thumbnail']) ?>" class="card-img-top" style="height:200px;object-fit:cover;" alt="">
                                    <?php else: ?>
                                        <div class="card-img-top d-flex align-items-center justify-content-center bg-dark" style="height:200px;">
                                            <i class="fas fa-box fa-4x text-secondary"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5><?= htmlspecialchars($p['product_title']) ?></h5>
                                        <p class="small text-secondary">
                                            <i class="fas fa-tag me-2"></i><?= htmlspecialchars($p['type'] ?? 'digital') ?>
                                        </p>
                                        <p class="small text-secondary">
                                            Purchased: <?= date('M d, Y', strtotime($p['created_at'])) ?>
                                        </p>
                                        <div class="mt-3">
                                            <?php if ($p['youtube_url']): ?>
                                                <a href="product-detail?id=<?= $p['product_id'] ?>" class="btn btn-sm btn-info me-2">
                                                    <i class="fas fa-play me-2"></i>Watch Video
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($p['zip_file']): ?>
                                                <a href="download-product?id=<?= $p['product_id'] ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-download me-2"></i>Download
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </div>
</div>
</section>

<?php require_once 'footer.php'; ?>
