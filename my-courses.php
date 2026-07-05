<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db_connect.php';
requirePublicLogin();

require_once __DIR__ . '/lib/Enrollment.php';
require_once __DIR__ . '/lib/Course.php';
require_once __DIR__ . '/lib/LessonProgress.php';

$user = getPublicUser();
$userId = $user['id'];
$enrollment = new Enrollment();
$courseModel = new Course();

$myCourses = $enrollment->getCoursesForUser($userId);

$page_title = 'My Courses — Mtaita Tech';
$page_desc = 'View all your enrolled courses.';
$page_keywords = 'my courses, enrolled courses, Mtaita Tech';
$hide_navbar = true;
require_once 'header.php';
?>
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
                        <a href="dashboard" class="btn btn-outline-primary btn-sm text-start"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                        <a href="my-courses" class="btn btn-primary btn-sm text-start"><i class="fas fa-book me-2"></i>My Courses</a>
                        <a href="digital_products" class="btn btn-outline-light btn-sm text-start"><i class="fas fa-box me-2"></i>Digital Products</a>
                        <a href="courses" class="btn btn-outline-light btn-sm text-start"><i class="fas fa-search me-2"></i>Browse Courses</a>
                        <a href="logout" class="btn btn-outline-danger btn-sm text-start mt-2"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                    </nav>
                </div>
            </div>
            <div class="col-md-9">
                <div class="mt-dash-hero text-white mb-4" data-aos="fade-up">
                    <h1 class="fw-bold mb-2" style="font-size:1.75rem;">My Courses</h1>
                    <p class="text-secondary mb-0">You are enrolled in <?= count($myCourses) ?> course(s).</p>
                </div>

                <div class="row">
                    <?php if (empty($myCourses)): ?>
                        <div class="col-12">
                            <div class="mt-glass p-4 rounded-4 text-center">
                                <p class="text-secondary mb-3">You are not enrolled in any course yet.</p>
                                <a href="courses" class="btn btn-primary mt-btn-glow px-4">Browse courses</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($myCourses as $mc): ?>
                            <div class="col-md-6 mb-4" data-aos="fade-up">
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
                                        <small class="text-secondary"><?= number_format((float)$mc['progress'], 0) ?>% complete</small>
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
            </div>
        </div>
    </div>
</section>
<?php require_once 'footer.php'; ?>
