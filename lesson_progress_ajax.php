<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/lib/Lesson.php';
require_once __DIR__ . '/lib/LessonProgress.php';
require_once __DIR__ . '/lib/Enrollment.php';

header('Content-Type: application/json');

if (!isPublicLoggedIn()) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$courseId = (int)($_GET['course_id'] ?? 0);
$lessonIdx = (int)($_GET['lesson_idx'] ?? 0) - 1;

if ($courseId < 1 || $lessonIdx < 0) {
    echo json_encode(['error' => 'Invalid params']);
    exit;
}

$userId = getPublicUserId();

$lessonModel = new Lesson();
$moduleModel = new Module();

// Get all lessons ordered by module/lesson sort
$modules = $moduleModel->getByCourseId($courseId);
$allLessons = [];
foreach ($modules as $mod) {
    $lessons = $lessonModel->getByModuleId($mod['id']);
    $allLessons = array_merge($allLessons, $lessons);
}
if (empty($allLessons)) {
    $allLessons = $lessonModel->getByCourseId($courseId);
}

if (!isset($allLessons[$lessonIdx])) {
    echo json_encode(['error' => 'Lesson not found']);
    exit;
}

$lesson = $allLessons[$lessonIdx];

$progress = new LessonProgress();
$progress->markComplete($userId, $lesson['id']);

$enrollment = new Enrollment();
$enrollment->updateProgressForUserCourse($userId, $courseId);

echo json_encode(['completed' => true, 'lesson_id' => $lesson['id']]);
