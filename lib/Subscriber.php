<?php
class Subscriber {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll() {
        return $this->pdo->query("SELECT * FROM subscribers ORDER BY created_at DESC")->fetchAll();
    }

    public function getActive() {
        return $this->pdo->query("SELECT * FROM subscribers WHERE is_active = 1 ORDER BY created_at DESC")->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM subscribers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM subscribers WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO subscribers (name, email) VALUES (?, ?)");
        $stmt->execute([$data['name'] ?? null, $data['email']]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE subscribers SET";
        $fields = [];
        $params = [];
        if (isset($data['name'])) {
            $fields[] = " name = ?";
            $params[] = $data['name'];
        }
        if (isset($data['is_active'])) {
            $fields[] = " is_active = ?";
            $params[] = $data['is_active'];
        }
        if (empty($fields)) return false;
        $sql .= implode(',', $fields) . " WHERE id = ?";
        $params[] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function toggleStatus($id) {
        $stmt = $this->pdo->prepare("UPDATE subscribers SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM subscribers WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
