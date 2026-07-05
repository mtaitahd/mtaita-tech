<?php
class StudentShowcase {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll() {
        return $this->pdo->query("
            SELECT s.*, u.name AS user_name
            FROM student_showcase s
            LEFT JOIN public_users u ON s.user_id = u.id
            ORDER BY s.created_at DESC
        ")->fetchAll();
    }

    public function getApproved() {
        return $this->pdo->query("
            SELECT s.*, u.name AS user_name
            FROM student_showcase s
            LEFT JOIN public_users u ON s.user_id = u.id
            WHERE s.is_approved = 1
            ORDER BY s.created_at DESC
        ")->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM student_showcase WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO student_showcase (user_id, title, description, image_path, project_url)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['user_id'] ?? null,
            $data['title'],
            $data['description'] ?? null,
            $data['image_path'] ?? null,
            $data['project_url'] ?? null
        ]);
        return $this->pdo->lastInsertId();
    }

    public function approve($id) {
        $stmt = $this->pdo->prepare("UPDATE student_showcase SET is_approved = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM student_showcase WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
