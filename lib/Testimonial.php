<?php
class Testimonial {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll() {
        return $this->pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC")->fetchAll();
    }

    public function getApproved() {
        return $this->pdo->query("SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC")->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM testimonials WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO testimonials (name, position, company, avatar, content, rating, is_approved) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['position'] ?? null, $data['company'] ?? null, $data['avatar'] ?? null, $data['content'], $data['rating'] ?? 5, $data['is_approved'] ?? 1]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE testimonials SET name = ?, position = ?, company = ?, avatar = ?, content = ?, rating = ?, is_approved = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$data['name'], $data['position'] ?? null, $data['company'] ?? null, $data['avatar'] ?? null, $data['content'], $data['rating'] ?? 5, $data['is_approved'] ?? 1, $id]);
    }

    public function delete($id) {
        $row = $this->getById($id);
        if ($row && $row['avatar']) {
            $path = __DIR__ . '/../' . $row['avatar'];
            if (file_exists($path)) @unlink($path);
        }
        $stmt = $this->pdo->prepare("DELETE FROM testimonials WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function toggleApproval($id) {
        $stmt = $this->pdo->prepare("UPDATE testimonials SET is_approved = NOT is_approved WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
