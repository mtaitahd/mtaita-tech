<?php
require_once __DIR__ . '/Course.php';
require_once __DIR__ . '/Enrollment.php';
require_once __DIR__ . '/CourseAccess.php';
require_once __DIR__ . '/Order.php';

class AccessControl {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function hasCourseAccess($userId, $course) {
        if (!$userId) return false;
        $courseId = (int) ($course['id'] ?? $course);
        if (is_array($course)) {
            if (($course['type'] ?? '') === 'free') return true;
            $courseId = (int) $course['id'];
        }

        $courseAccess = new CourseAccess();
        if ($courseAccess->hasAccess($userId, $courseId)) return true;

        $enrollment = new Enrollment();
        $row = $enrollment->getByUserAndCourse($userId, $courseId);
        if ($row && $row['status'] === 'active') return true;

        return false;
    }

    public function canViewLessonContent($userId, $lessonRow) {
        if (!$userId) return false;

        $courseId = (int) ($lessonRow['course_id'] ?? 0);
        $isFree = ($lessonRow['course_type'] ?? '') === 'free';
        $isPaidLesson = !empty($lessonRow['is_paid']);

        if ($isFree) return true;
        if (!$isPaidLesson) return true;

        return $this->hasCourseAccess($userId, ['id' => $courseId, 'type' => 'premium']);
    }
}
