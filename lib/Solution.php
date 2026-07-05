<?php
class Solution {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll() {
        return $this->pdo->query("SELECT * FROM solutions ORDER BY sort_order ASC, created_at DESC")->fetchAll();
    }

    public function getActive() {
        return $this->pdo->query("SELECT * FROM solutions WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC")->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM solutions WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO solutions (title, description, icon, image, link, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['title'], $data['description'] ?? null, $data['icon'] ?? null, $data['image'] ?? null, $data['link'] ?? null, $data['sort_order'] ?? 0, $data['is_active'] ?? 1]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE solutions SET title = ?, description = ?, icon = ?, image = ?, link = ?, sort_order = ?, is_active = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$data['title'], $data['description'] ?? null, $data['icon'] ?? null, $data['image'] ?? null, $data['link'] ?? null, $data['sort_order'] ?? 0, $data['is_active'] ?? 1, $id]);
    }

    public function delete($id) {
        $row = $this->getById($id);
        if ($row && $row['image']) {
            $path = __DIR__ . '/../' . $row['image'];
            if (file_exists($path)) @unlink($path);
        }
        $stmt = $this->pdo->prepare("DELETE FROM solutions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function toggleActive($id) {
        $stmt = $this->pdo->prepare("UPDATE solutions SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
