<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/lib/Module.php';
require_once __DIR__ . '/lib/Lesson.php';
require_once __DIR__ . '/lib/LessonProgress.php';
require_once __DIR__ . '/lib/Enrollment.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    header('Location: courses');
    exit;
}

$stmt = $pdo->prepare("SELECT id, title, slug, description, type, price, thumbnail FROM courses WHERE slug = ?");
$stmt->execute([$slug]);
$course = $stmt->fetch();

if (!$course) {
    $page_title = 'Course Not Found — Mtaita Tech';
    require_once 'header.php';
    ?>
    <section class="page-header">
        <div class="container">
            <p class="display-1 fw-bold text-red" style="font-size:clamp(3rem, 15vw, 6rem);">404</p>
            <p>Course not found</p>
        </div>
    </section>
    <section class="section-padding">
        <div class="container text-center">
            <i class="bi bi-book" style="font-size:4rem;color:#CBD5E1;"></i>
            <h3 class="mt-4" style="color: var(--deep-blue);">Course Not Found</h3>
            <p class="text-muted mt-2">The course you're looking for doesn't exist or has been moved.</p>
            <a href="courses" class="btn btn-red mt-3">Browse All Courses</a>
        </div>
    </section>
    <?php
    require_once 'footer.php';
    exit;
}

$page_title = htmlspecialchars($course['title']) . ' — Mtaita Tech';
$page_desc = htmlspecialchars(mb_strimwidth(strip_tags($course['description']), 0, 160, '...'));
$page_keywords = htmlspecialchars($course['title']) . ', course, Mtaita Tech, online learning';

$moduleModel = new Module();
$lessonModel = new Lesson();
$modules = $moduleModel->getByCourseId($course['id']);
$allLessons = [];
$lessonList = []; // flat ordered for backward compat
foreach ($modules as $mod) {
    $lessons = $lessonModel->getByModuleId($mod['id']);
    $mod['lessons'] = $lessons;
    foreach ($lessons as $l) {
        $lessonList[] = $l;
        $allLessons[] = $l;
    }
}
// Fallback: if no modules, get lessons directly (legacy support)
if (empty($allLessons)) {
    $lessonList = $lessonModel->getByCourseId($course['id']);
    $allLessons = $lessonList;
}

$is_enrolled = false;
$is_free = $course['type'] === 'free';

$userId = isPublicLoggedIn() ? getPublicUserId() : null;

if ($is_free) {
    $is_enrolled = true;
    if ($userId) {
        $enrollment = new Enrollment();
        $enrollment->ensureEnrollment($userId, $course['id']);
    }
} elseif ($userId) {
    $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND item_type = 'course' AND item_id = ? AND status = 'active'");
    $stmt->execute([$userId, $course['id']]);
    $is_enrolled = $stmt->rowCount() > 0;
}

$active_lesson_index = isset($_GET['lesson']) ? (int)$_GET['lesson'] - 1 : 0;
if ($active_lesson_index < 0) $active_lesson_index = 0;
if ($active_lesson_index >= count($allLessons)) $active_lesson_index = max(0, count($allLessons) - 1);

$lessonProgress = new LessonProgress();

function extractYoutubeId($url): ?string {
    $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
    if (preg_match($pattern, $url, $matches)) {
        return $matches[1];
    }
    $parsed = parse_url($url);
    if (isset($parsed['query'])) {
        parse_str($parsed['query'], $params);
        return $params['v'] ?? null;
    }
    return null;
}

$lesson_videos = [];
foreach ($allLessons as $lesson) {
    $lesson_videos[$lesson['id']] = extractYoutubeId($lesson['youtube_url']);
}

$active_lesson = $allLessons[$active_lesson_index] ?? null;
$active_video_id = $active_lesson ? ($lesson_videos[$active_lesson['id']] ?? null) : null;

require_once 'header.php';
?>

<section class="page-header">
    <div class="container">
        <h1><?= htmlspecialchars($course['title']) ?></h1>
        <p>
            <span class="badge me-2" style="background: <?= $is_free ? '#10B981' : 'var(--red)' ?>;">
                <?= strtoupper($course['type']) ?>
            </span>
            <?php if (!$is_free): ?>
                <span class="fw-bold">TZS <?= number_format($course['price']) ?></span>
            <?php endif; ?>
            <?php if ($is_enrolled && !$is_free): ?>
                <span class="badge bg-success ms-2">ENROLLED</span>
            <?php endif; ?>
        </p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <?php if ($is_enrolled && !empty($allLessons) && $active_video_id): ?>
                    <div class="ratio ratio-16x9 mb-4" style="background: #000;">
                        <iframe 
                            id="video-player"
                            src="https://www.youtube-nocookie.com/embed/<?= htmlspecialchars($active_video_id) ?>?rel=0"
                            title="<?= htmlspecialchars($active_lesson['title'] ?? 'Lesson Video') ?>"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <h4 class="mb-1" style="color: var(--deep-blue);" id="lesson-title">
                        <?= htmlspecialchars($active_lesson['title'] ?? 'No Title') ?>
                    </h4>
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <span class="text-muted small">Lesson <span id="lesson-number"><?= $active_lesson_index + 1 ?></span> of <?= count($allLessons) ?></span>
                        <?php if ($active_lesson && $active_lesson['youtube_url']): ?>
                        <a href="<?= htmlspecialchars($active_lesson['youtube_url']) ?>" target="_blank" rel="noopener" class="text-decoration-none small" style="color:var(--red);">
                            <i class="bi bi-youtube me-1"></i>Watch on YouTube
                        </a>
                        <?php endif; ?>
                    </div>
                <?php elseif (!$is_enrolled && !$is_free): ?>
                    <div class="floating-card text-center py-5">
                        <i class="bi bi-lock-fill" style="font-size:3rem;color: var(--red);"></i>
                        <h4 class="mt-4" style="color: var(--deep-blue);">This Course is Locked</h4>
                        <p class="text-muted mt-2 mb-4">You need to purchase this course to access all lessons.</p>
                        <div class="mb-3">
                            <span class="display-6 fw-bold" style="color: var(--deep-blue);">TZS <?= number_format($course['price']) ?></span>
                        </div>
                        <?php if (!isPublicLoggedIn()): ?>
                            <div class="d-flex flex-wrap gap-2 justify-content-center">
                                <a href="login" class="btn btn-red">Login to Purchase</a>
                                <a href="register" class="btn btn-outline-red">Register</a>
                            </div>
                        <?php else: ?>
                            <button class="btn btn-red" onclick="purchaseCourse()">Buy Now — TZS <?= number_format($course['price']) ?></button>
                        <?php endif; ?>
                    </div>
                <?php elseif (empty($allLessons)): ?>
                    <div class="floating-card text-center py-5">
                        <i class="bi bi-film" style="font-size:3rem;color:#CBD5E1;"></i>
                        <h4 class="mt-4 text-muted">No Lessons Yet</h4>
                        <p class="text-muted mt-2">This course doesn't have any lessons yet. Check back soon!</p>
                    </div>
                <?php endif; ?>

                <?php if ($course['description']): ?>
                <div class="mt-4 text-center">
                    <button class="btn btn-outline-red px-4" data-bs-toggle="modal" data-bs-target="#aboutCourseModal">
                        <i class="bi bi-info-circle me-2"></i>About This Course
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="floating-card">
                    <h5 style="color: var(--deep-blue);" class="mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-list-ul"></i>Course Content
                        <?php if ($is_enrolled && !empty($allLessons)): ?>
                            <?php
                                $completed_count = 0;
                                if ($userId) {
                                    $completed_count = $lessonProgress->countCompletedInCourse($userId, $course['id']);
                                }
                                $pct = count($allLessons) > 0 ? round(($completed_count / count($allLessons)) * 100) : 0;
                            ?>
                            <small class="text-muted fw-normal ms-auto"><?= $completed_count ?>/<?= count($allLessons) ?></small>
                        <?php endif; ?>
                    </h5>

                    <?php if ($is_enrolled && !empty($allLessons)): ?>
                    <div class="progress mb-3" style="height:4px;">
                        <div class="progress-bar bg-success" style="width:<?= $pct ?>%;"></div>
                    </div>
                    <?php endif; ?>

                    <div class="course-curriculum" style="max-height:600px;overflow-y:auto;">
                        <?php if (empty($allLessons)): ?>
                            <div class="text-center py-4 text-muted">
                                <small>No lessons available</small>
                            </div>
                        <?php else: ?>
                            <?php $globalIndex = 0; ?>
                            <?php if (!empty($modules)): ?>
                                <?php foreach ($modules as $mod): ?>
                                <div class="mb-3">
                                    <div class="d-flex align-items-center gap-2 mb-2" style="padding:0 4px;">
                                        <i class="bi bi-folder2-open text-cyan" style="font-size:0.85rem;"></i>
                                        <span class="fw-semibold small" style="color:var(--deep-blue);">
                                            <?= htmlspecialchars($mod['title']) ?>
                                        </span>
                                        <span class="text-muted" style="font-size:0.7rem;">(<?= count($mod['lessons']) ?>)</span>
                                    </div>
                                    <?php foreach ($mod['lessons'] as $lesson):
                                        $is_active = $globalIndex === $active_lesson_index;
                                        $video_id = $lesson_videos[$lesson['id']] ?? null;
                                        $is_completed = $userId ? $lessonProgress->isCompleted($userId, $lesson['id']) : false;
                                    ?>
                                    <a 
                                        href="single-course?slug=<?= htmlspecialchars($slug) ?>&lesson=<?= $globalIndex + 1 ?>"
                                        class="list-group-item list-group-item-action d-flex align-items-start gap-2 <?= $is_active ? 'active' : '' ?>"
                                        style="border-radius:6px;margin-bottom:4px;border:1px solid var(--border-light);cursor:pointer;padding:8px 10px;font-size:0.85rem;"
                                        <?php if ($is_enrolled && $video_id): ?>
                                        data-video-id="<?= htmlspecialchars($video_id) ?>"
                                        data-lesson-index="<?= $globalIndex ?>"
                                        data-lesson-title="<?= htmlspecialchars($lesson['title']) ?>"
                                        data-youtube-url="<?= htmlspecialchars($lesson['youtube_url']) ?>"
                                        <?php endif; ?>
                                        <?php if (!$is_enrolled && !$is_free): ?>
                                        onclick="event.preventDefault(); showPurchaseAlert();"
                                        <?php endif; ?>
                                    >
                                        <?php if ($is_completed && $is_enrolled): ?>
                                            <i class="bi bi-check-circle-fill" style="color:#10B981;font-size:0.9rem;margin-top:2px;"></i>
                                        <?php elseif (!empty($lesson['thumbnail'])): ?>
                                            <div class="flex-shrink-0" style="width:60px;height:34px;overflow:hidden;border-radius:3px;background:#000;position:relative;">
                                                <img src="<?= htmlspecialchars($lesson['thumbnail']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                                                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:#fff;font-size:0.85rem;opacity:0.8;">
                                                    <i class="bi bi-play-circle-fill"></i>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="flex-shrink-0 d-flex align-items-center justify-content-center" 
                                                 style="width:26px;height:26px;border-radius:50%;margin-top:1px;
                                                        background:<?= $is_active ? 'var(--red)' : 'var(--light-gray)' ?>; 
                                                        color:<?= $is_active ? '#fff' : 'var(--deep-blue)' ?>;font-size:0.8rem;">
                                                <i class="bi <?= $is_active ? 'bi-play-fill' : 'bi-play-circle' ?>"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="text-truncate" style="color:<?= $is_active ? '#fff' : 'var(--deep-blue)' ?>;">
                                                <?= htmlspecialchars($lesson['title']) ?>
                                            </div>
                                        </div>
                                        <?php if (!$is_enrolled && !$is_free): ?>
                                        <i class="bi bi-lock-fill text-muted flex-shrink-0" style="font-size:0.75rem;margin-top:3px;"></i>
                                        <?php endif; ?>
                                    </a>
                                    <?php $globalIndex++; endforeach; ?>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php foreach ($allLessons as $index => $lesson):
                                    $is_active = $index === $active_lesson_index;
                                    $video_id = $lesson_videos[$lesson['id']] ?? null;
                                    $is_completed = $userId ? $lessonProgress->isCompleted($userId, $lesson['id']) : false;
                                ?>
                                <a 
                                    href="single-course?slug=<?= htmlspecialchars($slug) ?>&lesson=<?= $index + 1 ?>"
                                    class="list-group-item list-group-item-action d-flex align-items-start gap-2 <?= $is_active ? 'active' : '' ?>"
                                    style="border-radius:6px;margin-bottom:4px;border:1px solid var(--border-light);cursor:pointer;padding:8px 10px;font-size:0.85rem;"
                                    <?php if ($is_enrolled && $video_id): ?>
                                    data-video-id="<?= htmlspecialchars($video_id) ?>"
                                    data-lesson-index="<?= $index ?>"
                                    data-lesson-title="<?= htmlspecialchars($lesson['title']) ?>"
                                    data-youtube-url="<?= htmlspecialchars($lesson['youtube_url']) ?>"
                                    <?php endif; ?>
                                    <?php if (!$is_enrolled && !$is_free): ?>
                                    onclick="event.preventDefault(); showPurchaseAlert();"
                                    <?php endif; ?>
                                >
                                    <?php if ($is_completed && $is_enrolled): ?>
                                        <i class="bi bi-check-circle-fill" style="color:#10B981;font-size:0.9rem;margin-top:2px;"></i>
                                    <?php elseif (!empty($lesson['thumbnail'])): ?>
                                        <div class="flex-shrink-0" style="width:60px;height:34px;overflow:hidden;border-radius:3px;background:#000;position:relative;">
                                            <img src="<?= htmlspecialchars($lesson['thumbnail']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                                            <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:#fff;font-size:0.85rem;opacity:0.8;">
                                                <i class="bi bi-play-circle-fill"></i>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center" 
                                             style="width:26px;height:26px;border-radius:50%;margin-top:1px;
                                                    background:<?= $is_active ? 'var(--red)' : 'var(--light-gray)' ?>; 
                                                    color:<?= $is_active ? '#fff' : 'var(--deep-blue)' ?>;font-size:0.8rem;">
                                            <i class="bi <?= $is_active ? 'bi-play-fill' : 'bi-play-circle' ?>"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="text-truncate" style="color:<?= $is_active ? '#fff' : 'var(--deep-blue)' ?>;">
                                            <?= htmlspecialchars($lesson['title']) ?>
                                        </div>
                                    </div>
                                    <?php if (!$is_enrolled && !$is_free): ?>
                                    <i class="bi bi-lock-fill text-muted flex-shrink-0" style="font-size:0.75rem;margin-top:3px;"></i>
                                    <?php endif; ?>
                                </a>
                                <?php $globalIndex++; endforeach; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!$is_free && !$is_enrolled): ?>
                <div class="floating-card mt-4 text-center" style="border: 2px solid var(--red);">
                    <div class="mb-2">
                        <span class="badge" style="background: var(--red);">PREMIUM COURSE</span>
                    </div>
                    <div class="display-5 fw-bold mb-1" style="color: var(--deep-blue);">
                        TZS <?= number_format($course['price']) ?>
                    </div>
                    <p class="text-muted mb-4 small">Get lifetime access to all lessons</p>
                    <?php if (!isPublicLoggedIn()): ?>
                        <a href="login" class="btn btn-red w-100">Login to Purchase</a>
                        <p class="mt-2 mb-0"><small><a href="register" class="text-decoration-none" style="color: var(--red);">Don't have an account? Register</a></small></p>
                    <?php else: ?>
                        <button class="btn btn-red w-100" onclick="purchaseCourse()">Buy Now</button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php if ($course['description']): ?>
<div class="modal fade" id="aboutCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:0;">
            <div class="modal-header" style="border-bottom:2px solid var(--red);">
                <h5 class="modal-title fw-bold" style="color:var(--deep-blue);">
                    <i class="bi bi-info-circle me-2" style="color:var(--red);"></i><?= htmlspecialchars($course['title']) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 style="color:var(--deep-blue);font-weight:700;margin-bottom:16px;">About This Course</h6>
                <div style="color:var(--medium-gray);line-height:1.8;font-size:0.95rem;">
                    <?= nl2br(htmlspecialchars($course['description'])) ?>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--border-light);">
                <button type="button" class="btn btn-red" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
<?php if ($is_enrolled && !empty($allLessons)): ?>
document.addEventListener('DOMContentLoaded', function() {
    const videoPlayer = document.getElementById('video-player');
    const lessonItems = document.querySelectorAll('[data-video-id]');

    lessonItems.forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const videoId = this.dataset.videoId;
            const lessonIndex = parseInt(this.dataset.lessonIndex);
            const lessonTitle = this.dataset.lessonTitle;

            if (videoId && videoPlayer) {
                videoPlayer.src = 'https://www.youtube-nocookie.com/embed/' + videoId + '?rel=0';
            }

            const lessonNumberEl = document.getElementById('lesson-number');
            if (lessonNumberEl) lessonNumberEl.textContent = lessonIndex + 1;
            const lessonTitleEl = document.getElementById('lesson-title');
            if (lessonTitleEl) lessonTitleEl.textContent = lessonTitle;

            lessonItems.forEach(function(li) {
                li.classList.remove('active');
            });

            this.classList.add('active');

            const url = new URL(window.location.href);
            url.searchParams.set('lesson', lessonIndex + 1);
            window.history.pushState({lesson: lessonIndex + 1}, '', url.toString());
        });
    });

    // Auto-mark lesson complete after watching
    let progressTimer = null;
    if (videoPlayer) {
        videoPlayer.addEventListener('load', function() {
            if (progressTimer) clearTimeout(progressTimer);
            // Mark complete after 60 seconds of viewing
            progressTimer = setTimeout(function() {
                const params = new URLSearchParams(window.location.search);
                const lessonIdx = parseInt(params.get('lesson')) || 1;
                fetch('lesson_progress_ajax.php?course_id=<?= $course['id'] ?>&lesson_idx=' + lessonIdx)
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        if (data.completed) {
                            location.reload();
                        }
                    });
            }, 60000);
        });
    }
});
<?php endif; ?>

function showPurchaseAlert() {
    Swal.fire({
        icon: 'lock',
        title: 'Course Locked',
        text: 'This is a premium course. Please purchase it to access all lessons.',
        confirmButtonColor: 'var(--red)',
        confirmButtonText: 'Buy Now'
    }).then((result) => {
        if (result.isConfirmed) {
            <?php if (!isPublicLoggedIn()): ?>
            window.location.href = 'login';
            <?php else: ?>
            purchaseCourse();
            <?php endif; ?>
        }
    });
}

function purchaseCourse() {
    window.location.href = 'checkout?type=course&id=<?= $course['id'] ?>';
}
</script>

<?php require_once 'footer.php'; ?>
