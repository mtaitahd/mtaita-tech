<?php
class Enrollment {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getByUserAndCourse($userId, $courseId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM enrollments
            WHERE user_id = ? AND item_type = 'course' AND item_id = ?
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$userId, $courseId]);
        return $stmt->fetch();
    }

    public function ensureEnrollment($userId, $courseId) {
        $existing = $this->getByUserAndCourse($userId, $courseId);
        if ($existing) return $existing['id'];

        $stmt = $this->pdo->prepare("
            INSERT INTO enrollments (user_id, item_type, item_id, status)
            VALUES (?, 'course', ?, 'active')
        ");
        $stmt->execute([$userId, $courseId]);
        return $this->pdo->lastInsertId();
    }

    public function updateProgressForUserCourse($userId, $courseId) {
        $lessonProgress = new LessonProgress();
        $course = new Course();
        $total = $course->countLessons($courseId);
        $completed = $lessonProgress->countCompletedInCourse($userId, $courseId);
        $progress = $total > 0 ? round(($completed / $total) * 100) : 0;

        $existing = $this->getByUserAndCourse($userId, $courseId);
        if ($existing) {
            $stmt = $this->pdo->prepare("UPDATE enrollments SET progress = ? WHERE id = ?");
            $stmt->execute([$progress, $existing['id']]);
        }
        return $progress;
    }

    public function getCoursesForUser($userId) {
        $stmt = $this->pdo->prepare("
            SELECT e.*, c.id AS course_id, c.title, c.slug, c.thumbnail, c.type, c.price,
                   COALESCE(e.progress, 0) AS progress,
                   e.status AS enrollment_status
            FROM enrollments e
            JOIN courses c ON e.item_id = c.id
            WHERE e.user_id = ? AND e.item_type = 'course' AND e.status = 'active'
            ORDER BY e.updated_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
