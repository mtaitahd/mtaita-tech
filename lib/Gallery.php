<?php
class Gallery {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll() {
        return $this->pdo->query("SELECT * FROM gallery_images ORDER BY sort_order ASC, created_at DESC")->fetchAll();
    }

    public function getActive() {
        return $this->pdo->query("SELECT * FROM gallery_images WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC")->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM gallery_images WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO gallery_images (title, image_path, alt_text, sort_order) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['title'] ?? null, $data['image_path'], $data['alt_text'] ?? null, $data['sort_order'] ?? 0]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE gallery_images SET title = ?, alt_text = ?, sort_order = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$data['title'] ?? null, $data['alt_text'] ?? null, $data['sort_order'] ?? 0, $id]);
    }

    public function toggleStatus($id) {
        $stmt = $this->pdo->prepare("UPDATE gallery_images SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function delete($id) {
        $row = $this->getById($id);
        if ($row && $row['image_path']) {
            $path = __DIR__ . '/../' . $row['image_path'];
            if (file_exists($path)) @unlink($path);
        }
        $stmt = $this->pdo->prepare("DELETE FROM gallery_images WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
