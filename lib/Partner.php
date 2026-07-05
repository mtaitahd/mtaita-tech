<?php
class Partner {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll() {
        return $this->pdo->query("SELECT * FROM partners ORDER BY sort_order ASC, created_at DESC")->fetchAll();
    }

    public function getActive() {
        return $this->pdo->query("SELECT * FROM partners WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC")->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM partners WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO partners (name, logo, website_url, sort_order, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['logo'] ?? null, $data['website_url'] ?? null, $data['sort_order'] ?? 0, $data['is_active'] ?? 1]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE partners SET name = ?, logo = ?, website_url = ?, sort_order = ?, is_active = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$data['name'], $data['logo'] ?? null, $data['website_url'] ?? null, $data['sort_order'] ?? 0, $data['is_active'] ?? 1, $id]);
    }

    public function delete($id) {
        $row = $this->getById($id);
        if ($row && $row['logo']) {
            $path = __DIR__ . '/../' . $row['logo'];
            if (file_exists($path)) @unlink($path);
        }
        $stmt = $this->pdo->prepare("DELETE FROM partners WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function toggleActive($id) {
        $stmt = $this->pdo->prepare("UPDATE partners SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
