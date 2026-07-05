<?php
class LessonProgress {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function isCompleted($userId, $lessonId) {
        $stmt = $this->pdo->prepare("SELECT completed FROM lesson_progress WHERE user_id = ? AND lesson_id = ?");
        $stmt->execute([$userId, $lessonId]);
        $row = $stmt->fetch();
        return $row ? (bool) $row['completed'] : false;
    }

    public function markComplete($userId, $lessonId) {
        $stmt = $this->pdo->prepare("
            INSERT INTO lesson_progress (user_id, lesson_id, completed, completed_at)
            VALUES (?, ?, 1, NOW())
            ON DUPLICATE KEY UPDATE completed = 1, completed_at = NOW()
        ");
        return $stmt->execute([$userId, $lessonId]);
    }

    public function countCompletedInCourse($userId, $courseId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM lesson_progress lp
            JOIN lessons l ON lp.lesson_id = l.id
            WHERE lp.user_id = ? AND l.course_id = ? AND lp.completed = 1
        ");
        $stmt->execute([$userId, $courseId]);
        return (int) $stmt->fetchColumn();
    }
}
