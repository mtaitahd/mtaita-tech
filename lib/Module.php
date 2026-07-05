<?php
class Module {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM modules WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByCourseId($courseId) {
        $stmt = $this->pdo->prepare("SELECT * FROM modules WHERE course_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function create($courseId, $title, $sortOrder = 0) {
        $stmt = $this->pdo->prepare("INSERT INTO modules (course_id, title, sort_order) VALUES (?, ?, ?)");
        $stmt->execute([$courseId, $title, $sortOrder]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $title, $sortOrder = 0) {
        $stmt = $this->pdo->prepare("UPDATE modules SET title = ?, sort_order = ? WHERE id = ?");
        return $stmt->execute([$title, $sortOrder, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM modules WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
