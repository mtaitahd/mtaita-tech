<?php
class Product {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll() {
        return $this->pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
    }

    public function getVisible() {
        return $this->pdo->query("SELECT * FROM products WHERE is_visible = 1 ORDER BY created_at DESC")->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO products (title, description, type, price, is_paid, is_visible, youtube_url, zip_file, thumbnail)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['type'] ?? 'code',
            $data['price'] ?? 0,
            $data['is_paid'] ?? 0,
            $data['is_visible'] ?? 1,
            $data['youtube_url'] ?? null,
            $data['zip_file'] ?? null,
            $data['thumbnail'] ?? null
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE products SET title = ?, description = ?, type = ?, price = ?, is_paid = ?, is_visible = ?, youtube_url = ?, zip_file = ?, thumbnail = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['type'] ?? 'code',
            $data['price'] ?? 0,
            $data['is_paid'] ?? 0,
            $data['is_visible'] ?? 1,
            $data['youtube_url'] ?? null,
            $data['zip_file'] ?? null,
            $data['thumbnail'] ?? null,
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function toggleVisibility($id) {
        $stmt = $this->pdo->prepare("UPDATE products SET is_visible = NOT is_visible WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function incrementDownloadCount($id) {
        $stmt = $this->pdo->prepare("UPDATE products SET download_count = download_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function hasUserPurchased($userId, $productId) {
        $stmt = $this->pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND product_id = ? AND status = 'completed' LIMIT 1");
        $stmt->execute([$userId, $productId]);
        return (bool) $stmt->fetch();
    }

    public function getTypeLabel($type) {
        $labels = ['code' => 'Source Code', 'video' => 'Video Tutorial', 'both' => 'Code + Video'];
        return $labels[$type] ?? 'Unknown';
    }

    public function getYoutubeEmbedUrl($url) {
        if (empty($url)) return null;
        $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
        if (preg_match($pattern, $url, $matches)) {
            return 'https://www.youtube-nocookie.com/embed/' . $matches[1] . '?rel=0';
        }
        $parsed = parse_url($url);
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $params);
            if (!empty($params['v'])) {
                return 'https://www.youtube-nocookie.com/embed/' . $params['v'] . '?rel=0';
            }
        }
        return null;
    }
}
