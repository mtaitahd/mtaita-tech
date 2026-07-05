<?php
class Course {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getBySlug($slug) {
        $stmt = $this->pdo->prepare("SELECT * FROM courses WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public function getPublished() {
        return $this->pdo->query("SELECT * FROM courses WHERE status = 'published' ORDER BY featured DESC, created_at DESC")->fetchAll();
    }

    public function getFeatured() {
        return $this->pdo->query("SELECT * FROM courses WHERE status = 'published' AND featured = 1 ORDER BY created_at DESC")->fetchAll();
    }

    public function getAllAdmin() {
        return $this->pdo->query("SELECT c.*, (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) AS lesson_count FROM courses c ORDER BY c.created_at DESC")->fetchAll();
    }

    public function isFree($course) {
        return ($course['type'] ?? '') === 'free';
    }

    public function countLessons($courseId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
        $stmt->execute([$courseId]);
        return (int) $stmt->fetchColumn();
    }

    public function getLessonIdsOrdered($courseId) {
        $stmt = $this->pdo->prepare("
            SELECT l.id FROM lessons l
            LEFT JOIN modules m ON l.module_id = m.id
            WHERE l.course_id = ?
            ORDER BY COALESCE(m.sort_order, 0), l.sort_order ASC
        ");
        $stmt->execute([$courseId]);
        return array_column($stmt->fetchAll(), 'id');
    }
}
