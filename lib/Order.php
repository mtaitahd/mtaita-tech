<?php
class Order {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll() {
        return $this->pdo->query("
            SELECT o.*, u.name AS user_name, u.email AS user_email, p.title AS product_title, p.type, p.thumbnail, p.youtube_url, p.zip_file
            FROM orders o
            JOIN public_users u ON o.user_id = u.id
            JOIN products p ON o.product_id = p.id
            ORDER BY o.created_at DESC
        ")->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.name AS user_name, u.email AS user_email, p.title AS product_title
            FROM orders o
            JOIN public_users u ON o.user_id = u.id
            JOIN products p ON o.product_id = p.id
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByUserId($userId) {
        $stmt = $this->pdo->prepare("
            SELECT o.*, p.title AS product_title, p.type, p.thumbnail, p.youtube_url, p.zip_file, p.id AS product_id
            FROM orders o
            JOIN products p ON o.product_id = p.id
            WHERE o.user_id = ? AND o.status = 'completed'
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO orders (user_id, product_id, transaction_id, amount, status, snippe_reference)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['user_id'],
            $data['product_id'],
            $data['transaction_id'] ?? null,
            $data['amount'] ?? 0,
            $data['status'] ?? 'pending',
            $data['snippe_reference'] ?? null
        ]);
        return $this->pdo->lastInsertId();
    }

    public function updatePaymentStatus($id, $status, $transactionId = null, $snippeRef = null) {
        $sql = "UPDATE orders SET status = ?";
        $params = [$status];
        if ($transactionId !== null) {
            $sql .= ", transaction_id = ?";
            $params[] = $transactionId;
        }
        if ($snippeRef !== null) {
            $sql .= ", snippe_reference = ?";
            $params[] = $snippeRef;
        }
        $sql .= " WHERE id = ?";
        $params[] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function hasCompletedOrder($userId, $productId) {
        $stmt = $this->pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND product_id = ? AND status = 'completed' LIMIT 1");
        $stmt->execute([$userId, $productId]);
        return (bool) $stmt->fetch();
    }

    public function getByTransactionId($transactionId) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE transaction_id = ?");
        $stmt->execute([$transactionId]);
        return $stmt->fetch();
    }

    public function getStatusLabel($status) {
        $labels = ['pending' => 'Pending', 'completed' => 'Completed', 'failed' => 'Failed', 'refunded' => 'Refunded'];
        return $labels[$status] ?? 'Unknown';
    }

    public function getStatusColor($status) {
        $colors = ['pending' => 'warning', 'completed' => 'success', 'failed' => 'danger', 'refunded' => 'info'];
        return $colors[$status] ?? 'secondary';
    }
}
