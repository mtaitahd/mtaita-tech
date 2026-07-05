<?php
class CourseAccess {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function hasAccess($userId, $courseId) {
        $stmt = $this->pdo->prepare("
            SELECT id FROM course_access
            WHERE user_id = ? AND course_id = ? AND revoked = 0
            AND (expires_at IS NULL OR expires_at > NOW())
            LIMIT 1
        ");
        $stmt->execute([$userId, $courseId]);
        return (bool) $stmt->fetch();
    }

    public function getGrantedCourseIds($userId) {
        $stmt = $this->pdo->prepare("
            SELECT course_id FROM course_access
            WHERE user_id = ? AND revoked = 0
            AND (expires_at IS NULL OR expires_at > NOW())
        ");
        $stmt->execute([$userId]);
        return array_column($stmt->fetchAll(), 'course_id');
    }

    public function grant($userId, $courseId, $expiresAt = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO course_access (user_id, course_id, expires_at)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE revoked = 0, expires_at = VALUES(expires_at)
        ");
        return $stmt->execute([$userId, $courseId, $expiresAt]);
    }
}
