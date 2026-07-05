<?php
class News {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll() {
        return $this->pdo->query("SELECT * FROM news ORDER BY created_at DESC")->fetchAll();
    }

    public function getPublished() {
        return $this->pdo->query("SELECT * FROM news WHERE is_published = 1 ORDER BY created_at DESC")->fetchAll();
    }

    public function getRecent($limit = 4) {
        $stmt = $this->pdo->prepare("SELECT * FROM news WHERE is_published = 1 ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM news WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO news (title, content, image, author, is_published) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['title'], $data['content'], $data['image'] ?? null, $data['author'] ?? null, $data['is_published'] ?? 1]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE news SET title = ?, content = ?, image = ?, author = ?, is_published = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$data['title'], $data['content'], $data['image'] ?? null, $data['author'] ?? null, $data['is_published'] ?? 1, $id]);
    }

    public function delete($id) {
        $row = $this->getById($id);
        if ($row && $row['image']) {
            $path = __DIR__ . '/../' . $row['image'];
            if (file_exists($path)) @unlink($path);
        }
        $stmt = $this->pdo->prepare("DELETE FROM news WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function togglePublish($id) {
        $stmt = $this->pdo->prepare("UPDATE news SET is_published = NOT is_published WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
