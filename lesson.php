<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db_connect.php';
requirePublicLogin();

require_once __DIR__ . '/lib/Lesson.php';
require_once __DIR__ . '/lib/Course.php';
require_once __DIR__ . '/lib/AccessControl.php';
require_once __DIR__ . '/lib/Enrollment.php';
require_once __DIR__ . '/lib/LessonProgress.php';

$lessonModel = new Lesson();
$courseModel = new Course();
$accessControl = new AccessControl();
$enrollment = new Enrollment();
$lessonProgress = new LessonProgress();

$lessonId = (int)($_GET['id'] ?? 0);
$row = $lessonId ? $lessonModel->getByIdWithCourse($lessonId) : null;

if (!$row || $row['course_status'] !== 'published') {
    header('Location: courses');
    exit;
}

$userId = getPublicUserId();
$courseId = (int) $row['course_id'];
$course = $courseModel->getById($courseId);
$canView = $accessControl->canViewLessonContent($userId, $row);

if ($userId && $canView) {
    $enrollment->ensureEnrollment($userId, $courseId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_complete']) && $userId && $canView) {
    $lessonProgress->markComplete($userId, $lessonId);
    $enrollment->updateProgressForUserCourse($userId, $courseId);
    header('Location: lesson.php?id=' . $lessonId . '&done=1');
    exit;
}

$sequence = $courseModel->getLessonIdsOrdered($courseId);
$idx = array_search($lessonId, $sequence, true);
$prevId = ($idx !== false && $idx > 0) ? $sequence[$idx - 1] : null;
$nextId = ($idx !== false && $idx < count($sequence) - 1) ? $sequence[$idx + 1] : null;

$embedUrl = $lessonModel->getYoutubeEmbedUrl($row['video_url'] ?: $row['youtube_url']);
$completed = $userId ? $lessonProgress->isCompleted($userId, $lessonId) : false;

$page_title = htmlspecialchars($row['title']) . ' — Mtaita Tech';
$page_desc = 'Watch lesson ' . htmlspecialchars($row['title']) . ' from ' . htmlspecialchars($row['course_title']) . ' on Mtaita Tech.';
$hide_navbar = true;
require_once 'header.php';
?>

<section style="background:var(--deep-blue);min-height:100vh;padding-top:0;">
    <div style="background:#0A1628;padding:12px 0;">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <a href="single-course?slug=<?= urlencode($row['course_title']) ?>" class="text-decoration-none small" style="color:#94A3B8;">
                        <i class="bi bi-arrow-left me-1"></i>Back to Course
                    </a>
                    <div class="fw-bold text-white small"><?= htmlspecialchars($row['course_title']) ?></div>
                    <div style="color:#94A3B8;font-size:0.8rem;">
                        <?= htmlspecialchars($row['module_title'] ?? '') ?> &middot; <?= htmlspecialchars($row['title']) ?>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <?php if ($prevId): ?>
                        <a href="lesson?id=<?= $prevId ?>" class="btn btn-sm btn-outline-light"><i class="bi bi-chevron-left"></i> Prev</a>
                    <?php endif; ?>
                    <?php if ($nextId): ?>
                        <a href="lesson?id=<?= $nextId ?>" class="btn btn-sm btn-outline-light">Next <i class="bi bi-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$canView): ?>
        <div class="container py-5">
            <div class="floating-card text-center py-5 mx-3" style="max-width:480px;margin:0 auto;width:100%;">
                <i class="bi bi-lock-fill" style="font-size:3rem;color:var(--red);"></i>
                <h4 class="mt-3" style="color:var(--deep-blue);">Premium Lesson</h4>
                <p class="text-muted">Purchase this course to unlock all premium lessons.</p>
                <a href="checkout?type=course&id=<?= $courseId ?>" class="btn btn-red btn-lg">Unlock Course</a>
                <p class="mt-3 mb-0"><a href="single-course?slug=<?= urlencode($row['course_title']) ?>" style="color:#94A3B8;">Back to course outline</a></p>
            </div>
        </div>
    <?php else: ?>
        <div class="container-fluid px-0">
            <div class="ratio ratio-16x9" style="background:#000;max-height:70vh;">
                <?php if ($embedUrl): ?>
                    <iframe src="<?= htmlspecialchars($embedUrl) ?>" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" style="border:0;"></iframe>
                <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center h-100" style="color:#64748B;">
                        <span><i class="bi bi-camera-video-off me-2"></i>No video for this lesson</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="container py-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h1 class="text-white fw-bold mb-1" style="font-size:1.5rem;"><?= htmlspecialchars($row['title']) ?></h1>
                    <?php if (isset($_GET['done'])): ?>
                        <span class="text-success small"><i class="bi bi-check-circle me-1"></i>Progress saved</span>
                    <?php endif; ?>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <?php if ($userId): ?>
                        <form method="post" class="d-inline">
                            <button type="submit" name="mark_complete" value="1" class="btn btn-sm <?= $completed ? 'btn-outline-success' : 'btn-success' ?>" <?= $completed ? 'disabled' : '' ?>>
                                <?= $completed ? 'Completed' : 'Mark Complete' ?>
                            </button>
                        </form>
                    <?php endif; ?>
                    <?php if ($nextId): ?>
                        <a href="lesson?id=<?= $nextId ?>" class="btn btn-sm btn-red">Next <i class="bi bi-arrow-right ms-1"></i></a>
                    <?php else: ?>
                        <a href="single-course?slug=<?= urlencode($row['course_title']) ?>" class="btn btn-sm btn-outline-light">Finish</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php if ($canView): ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="floating-card text-center" style="border:1px solid rgba(250,204,21,0.3);background:rgba(250,204,21,0.05);">
                <div style="font-size:2rem;margin-bottom:0.5rem;">⭐</div>
                <h5 style="color:var(--deep-blue);">Love what you learned?</h5>
                <p class="text-muted">Your feedback helps us improve. Please take a moment to leave us a review on Google!</p>
                <a href="https://g.page/r/CV7D8gf6yuhmEBM/review" target="_blank" rel="noopener" class="btn btn-outline-cyan" style="border-color:var(--deep-blue);">
                    <i class="bi bi-google me-1"></i> Write a Review
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
