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

$page_title = 'My Courses';
$active_page = 'my-courses';
require_once 'user_header.php';
?>
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-book text-cyan me-2"></i>My Courses</h5>
                <a href="courses" class="btn btn-cyan btn-sm"><i class="bi bi-plus-lg me-1"></i>Browse Courses</a>
            </div>
            <?php if (empty($myCourses)): ?>
                <div class="text-center py-4">
                    <p class="text-muted mb-3">You are not enrolled in any course yet.</p>
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
                                            <img src="<?= htmlspecialchars($mc['thumbnail']) ?>" style="width:45px;height:45px;object-fit:cover;border-radius:6px;" alt="">
                                        <?php else: ?>
                                            <div style="width:45px;height:45px;background:#1e293b;border-radius:6px;display:flex;align-items:center;justify-content:center;"><i class="bi bi-book text-muted"></i></div>
                                        <?php endif; ?>
                                        <span class="fw-semibold"><?= htmlspecialchars($mc['title']) ?></span>
                                    </div>
                                </td>
                                <td style="width:220px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1 mb-0" style="height:8px;">
                                            <div class="progress-bar bg-cyan" style="width:<?= min(100, (float)$mc['progress']) ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?= number_format((float)$mc['progress'], 0) ?>%</small>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <a href="<?= $resume ?>" class="btn btn-cyan btn-sm me-1">Resume</a>
                                    <a href="single-course?slug=<?= urlencode($mc['slug']) ?>" class="btn btn-outline-cyan btn-sm">Outline</a>
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
