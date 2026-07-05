<?php
class Lesson {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM lessons WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByIdWithCourse($id) {
        $stmt = $this->pdo->prepare("
            SELECT l.*, m.title AS module_title, c.title AS course_title, c.status AS course_status,
                   c.type AS course_type, c.price AS course_price, c.id AS course_id
            FROM lessons l
            LEFT JOIN modules m ON l.module_id = m.id
            JOIN courses c ON l.course_id = c.id
            WHERE l.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByModuleId($moduleId) {
        $stmt = $this->pdo->prepare("SELECT * FROM lessons WHERE module_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$moduleId]);
        return $stmt->fetchAll();
    }

    public function getByCourseId($courseId) {
        $stmt = $this->pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO lessons (course_id, module_id, title, youtube_url, video_url, thumbnail, code_content, zip_file, sort_order, is_paid)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['course_id'],
            $data['module_id'] ?? null,
            $data['title'],
            $data['youtube_url'] ?? '',
            $data['video_url'] ?? null,
            $data['thumbnail'] ?? null,
            $data['code_content'] ?? null,
            $data['zip_file'] ?? null,
            $data['sort_order'] ?? 0,
            $data['is_paid'] ?? 0
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE lessons SET course_id = ?, module_id = ?, title = ?, youtube_url = ?, video_url = ?, thumbnail = ?, code_content = ?, zip_file = ?, sort_order = ?, is_paid = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['course_id'],
            $data['module_id'] ?? null,
            $data['title'],
            $data['youtube_url'] ?? '',
            $data['video_url'] ?? null,
            $data['thumbnail'] ?? null,
            $data['code_content'] ?? null,
            $data['zip_file'] ?? null,
            $data['sort_order'] ?? 0,
            $data['is_paid'] ?? 0,
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM lessons WHERE id = ?");
        return $stmt->execute([$id]);
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

    public function isLocalVideo($url) {
        return $url && !preg_match('#^https?://(www\.)?(youtube|youtu\.be)#i', $url);
    }
}
