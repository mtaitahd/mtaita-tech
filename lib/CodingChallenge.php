<?php
class CodingChallenge {
    private $pdo;

    const LANGUAGES = ['html', 'css', 'php', 'python', 'java', 'c', 'cpp'];
    const DIFFICULTIES = ['easy', 'medium', 'hard'];

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function languageLabel($lang) {
        $labels = ['html' => 'HTML', 'css' => 'CSS', 'php' => 'PHP', 'python' => 'Python', 'java' => 'Java', 'c' => 'C', 'cpp' => 'C++'];
        return $labels[$lang] ?? strtoupper($lang);
    }

    public function difficultyLabel($d) {
        return ucfirst($d);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM coding_challenges WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByIdWithCourse($id) {
        $stmt = $this->pdo->prepare("
            SELECT cc.*, c.title AS course_title, c.type AS course_type, c.status AS course_status,
                   c.slug AS course_slug, m.title AS module_title, l.title AS lesson_title
            FROM coding_challenges cc
            JOIN courses c ON cc.course_id = c.id
            LEFT JOIN modules m ON cc.module_id = m.id
            LEFT JOIN lessons l ON cc.lesson_id = l.id
            WHERE cc.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getTestCases($challengeId, $visibleOnly = false) {
        $sql = "SELECT * FROM coding_test_cases WHERE challenge_id = ?";
        if ($visibleOnly) $sql .= " AND is_visible = 1";
        $sql .= " ORDER BY sort_order ASC, id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$challengeId]);
        return $stmt->fetchAll();
    }

    public function getHiddenTestCases($challengeId) {
        return $this->getTestCases($challengeId, false);
    }

    public function getByCourseId($courseId, $publishedOnly = false) {
        $sql = "SELECT * FROM coding_challenges WHERE course_id = ?";
        if ($publishedOnly) $sql .= " AND is_published = 1";
        $sql .= " ORDER BY sort_order ASC, id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function getByModuleId($moduleId, $publishedOnly = false) {
        $sql = "SELECT * FROM coding_challenges WHERE module_id = ?";
        if ($publishedOnly) $sql .= " AND is_published = 1";
        $sql .= " ORDER BY sort_order ASC, id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$moduleId]);
        return $stmt->fetchAll();
    }

    public function getByLessonId($lessonId, $publishedOnly = false) {
        $sql = "SELECT * FROM coding_challenges WHERE lesson_id = ?";
        if ($publishedOnly) $sql .= " AND is_published = 1";
        $sql .= " ORDER BY sort_order ASC, id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$lessonId]);
        return $stmt->fetchAll();
    }

    public function getAllAdmin() {
        return $this->pdo->query("
            SELECT cc.*, c.title AS course_title, m.title AS module_title, l.title AS lesson_title,
                   (SELECT COUNT(*) FROM coding_test_cases WHERE challenge_id = cc.id) AS test_count,
                   (SELECT COUNT(*) FROM coding_submissions WHERE challenge_id = cc.id) AS submission_count
            FROM coding_challenges cc
            JOIN courses c ON cc.course_id = c.id
            LEFT JOIN modules m ON cc.module_id = m.id
            LEFT JOIN lessons l ON cc.lesson_id = l.id
            ORDER BY cc.created_at DESC
        ")->fetchAll();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO coding_challenges
            (course_id, lesson_id, module_id, title, slug, language, difficulty, marks, passing_score,
             time_limit, memory_limit, problem, input_desc, output_desc, constraints,
             sample_input, sample_output, starter_code, is_published, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['course_id'],
            $data['lesson_id'] ?? null,
            $data['module_id'] ?? null,
            $data['title'],
            $data['slug'] ?? null,
            $data['language'],
            $data['difficulty'],
            (int)$data['marks'],
            (int)$data['passing_score'],
            (int)$data['time_limit'],
            (int)$data['memory_limit'],
            $data['problem'],
            $data['input_desc'] ?? null,
            $data['output_desc'] ?? null,
            $data['constraints'] ?? null,
            $data['sample_input'] ?? null,
            $data['sample_output'] ?? null,
            $data['starter_code'] ?? null,
            isset($data['is_published']) ? (int)$data['is_published'] : 0,
            (int)$data['sort_order']
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE coding_challenges SET
                course_id = ?, lesson_id = ?, module_id = ?, title = ?, slug = ?, language = ?,
                difficulty = ?, marks = ?, passing_score = ?, time_limit = ?, memory_limit = ?,
                problem = ?, input_desc = ?, output_desc = ?, constraints = ?,
                sample_input = ?, sample_output = ?, starter_code = ?, is_published = ?, sort_order = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['course_id'],
            $data['lesson_id'] ?? null,
            $data['module_id'] ?? null,
            $data['title'],
            $data['slug'] ?? null,
            $data['language'],
            $data['difficulty'],
            (int)$data['marks'],
            (int)$data['passing_score'],
            (int)$data['time_limit'],
            (int)$data['memory_limit'],
            $data['problem'],
            $data['input_desc'] ?? null,
            $data['output_desc'] ?? null,
            $data['constraints'] ?? null,
            $data['sample_input'] ?? null,
            $data['sample_output'] ?? null,
            $data['starter_code'] ?? null,
            isset($data['is_published']) ? (int)$data['is_published'] : 0,
            (int)$data['sort_order'],
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM coding_challenges WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function setPublished($id, $published) {
        $stmt = $this->pdo->prepare("UPDATE coding_challenges SET is_published = ? WHERE id = ?");
        return $stmt->execute([$published ? 1 : 0, $id]);
    }

    public function replaceTestCases($challengeId, array $cases) {
        $del = $this->pdo->prepare("DELETE FROM coding_test_cases WHERE challenge_id = ?");
        $del->execute([$challengeId]);
        $ins = $this->pdo->prepare("
            INSERT INTO coding_test_cases (challenge_id, input_data, expected_output, is_visible, sort_order)
            VALUES (?, ?, ?, ?, ?)
        ");
        $order = 0;
        foreach ($cases as $case) {
            if ($case['input_data'] === null && $case['expected_output'] === null) continue;
            $ins->execute([
                $challengeId,
                $case['input_data'],
                $case['expected_output'],
                !empty($case['is_visible']) ? 1 : 0,
                $order++
            ]);
        }
    }

    public function countForCourse($courseId, $publishedOnly = false) {
        $sql = "SELECT COUNT(*) FROM coding_challenges WHERE course_id = ?";
        if ($publishedOnly) $sql .= " AND is_published = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$courseId]);
        return (int)$stmt->fetchColumn();
    }

    public function getModuleIdForLesson($lessonId) {
        $stmt = $this->pdo->prepare("SELECT module_id FROM lessons WHERE id = ?");
        $stmt->execute([$lessonId]);
        return $stmt->fetchColumn();
    }
}
