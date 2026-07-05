<?php
class Review {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll() {
        return $this->pdo->query("
            SELECT r.*, p.title AS product_title
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            ORDER BY r.created_at DESC
        ")->fetchAll();
    }

    public function getByProduct($productId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM reviews WHERE product_id = ? AND is_approved = 1 ORDER BY created_at DESC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getAverageRating($productId) {
        $stmt = $this->pdo->prepare("SELECT AVG(rating) FROM reviews WHERE product_id = ? AND is_approved = 1");
        $stmt->execute([$productId]);
        $avg = $stmt->fetchColumn();
        return $avg ? round((float) $avg, 1) : 0;
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO reviews (product_id, user_id, name, email, rating, comment, is_approved)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['product_id'],
            $data['user_id'] ?? null,
            $data['name'],
            $data['email'] ?? null,
            $data['rating'] ?? 5,
            $data['comment'] ?? null,
            $data['is_approved'] ?? 0
        ]);
        return $this->pdo->lastInsertId();
    }

    public function toggleApproval($id) {
        $stmt = $this->pdo->prepare("UPDATE reviews SET is_approved = NOT is_approved WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM reviews WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
