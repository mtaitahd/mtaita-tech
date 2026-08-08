<?php
class CodingSubmission {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function rateLimitExceeded($userId, $maxPerHour = 30, $windowSeconds = 3600) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM coding_submissions
            WHERE user_id = ? AND created_at >= (NOW() - INTERVAL ? SECOND)
        ");
        $stmt->execute([$userId, (int)$windowSeconds]);
        return (int)$stmt->fetchColumn() >= $maxPerHour;
    }

    public function createSubmission($userId, $challenge, $code, $status, $score, $testsTotal, $testsPassed, $execTime) {
        $stmt = $this->pdo->prepare("
            INSERT INTO coding_submissions
            (challenge_id, user_id, language, code, status, score, total_marks, tests_total, tests_passed, execution_time, passed)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $passed = ($testsTotal > 0 && $testsPassed === $testsTotal && $score >= $challenge['passing_score']) ? 1 : 0;
        $stmt->execute([
            $challenge['id'],
            $userId,
            $challenge['language'],
            $code,
            $status,
            (int)$score,
            (int)$challenge['marks'],
            (int)$testsTotal,
            (int)$testsPassed,
            (float)$execTime,
            $passed
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function saveResult($submissionId, $testCaseId, $passed, $status, $actualOutput, $execTime) {
        $stmt = $this->pdo->prepare("
            INSERT INTO coding_submission_results
            (submission_id, test_case_id, passed, status, actual_output, execution_time)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$submissionId, $testCaseId, $passed ? 1 : 0, $status, $actualOutput, (float)$execTime]);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM coding_submissions WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getResultsForSubmission($submissionId) {
        $stmt = $this->pdo->prepare("
            SELECT r.*, t.input_data, t.expected_output, t.is_visible
            FROM coding_submission_results r
            LEFT JOIN coding_test_cases t ON r.test_case_id = t.id
            WHERE r.submission_id = ?
            ORDER BY r.id ASC
        ");
        $stmt->execute([$submissionId]);
        return $stmt->fetchAll();
    }

    public function getResultsForSubmissionSafe($submissionId) {
        $rows = $this->getResultsForSubmission($submissionId);
        $safe = [];
        foreach ($rows as $r) {
            $safe[] = [
                'passed' => (bool)$r['passed'],
                'status' => $r['status'],
                'actual_output' => $r['actual_output'],
                'execution_time' => (float)$r['execution_time'],
                'visible' => (bool)$r['is_visible'],
            ];
        }
        return $safe;
    }

    public function getHistoryForUser($userId, $limit = 50) {
        $stmt = $this->pdo->prepare("
            SELECT s.*, c.title AS challenge_title, c.marks AS challenge_marks, c.language AS challenge_language
            FROM coding_submissions s
            JOIN coding_challenges c ON s.challenge_id = c.id
            WHERE s.user_id = ?
            ORDER BY s.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getBestPerChallenge($userId) {
        $stmt = $this->pdo->prepare("
            SELECT s.challenge_id, c.title, c.course_id, c.language,
                   MAX(s.score) AS best_score, s.total_marks,
                   MAX(s.tests_passed) AS best_tests, s.tests_total,
                   s.status, s.passed, s.created_at
            FROM coding_submissions s
            JOIN coding_challenges c ON s.challenge_id = c.id
            WHERE s.user_id = ?
            GROUP BY s.challenge_id, c.title, c.course_id, c.language, s.total_marks, s.status, s.passed, s.created_at
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function hasPassed($userId, $challengeId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM coding_submissions WHERE user_id = ? AND challenge_id = ? AND passed = 1");
        $stmt->execute([$userId, $challengeId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function countPassedInCourse($userId, $courseId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT s.challenge_id) FROM coding_submissions s
            JOIN coding_challenges c ON s.challenge_id = c.id
            WHERE s.user_id = ? AND c.course_id = ? AND s.passed = 1
        ");
        $stmt->execute([$userId, $courseId]);
        return (int)$stmt->fetchColumn();
    }

    public function updateCourseProgress($userId, $courseId) {
        require_once __DIR__ . '/Enrollment.php';
        $enrollment = new Enrollment();
        return $enrollment->updateProgressForUserCourse($userId, $courseId);
    }

    public function getStatsForUser($userId) {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*) AS total_submissions,
                COUNT(DISTINCT challenge_id) AS attempted,
                COUNT(DISTINCT CASE WHEN passed = 1 THEN challenge_id END) AS completed,
                SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) AS passed_submissions,
                SUM(score) AS total_xp
            FROM coding_submissions WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function getTotalStats() {
        return $this->pdo->query("
            SELECT
                (SELECT COUNT(*) FROM coding_challenges) AS total_challenges,
                (SELECT COUNT(*) FROM coding_submissions) AS total_submissions,
                (SELECT COUNT(*) FROM coding_submissions WHERE passed = 1) AS passed_submissions,
                (SELECT COUNT(*) FROM coding_submissions WHERE passed = 0) AS failed_submissions,
                (SELECT COUNT(DISTINCT user_id) FROM coding_submissions) AS active_students
        ")->fetch();
    }

    public function getMostAttempted($limit = 5) {
        return $this->pdo->query("
            SELECT c.id, c.title, COUNT(s.id) AS attempts
            FROM coding_challenges c
            JOIN coding_submissions s ON s.challenge_id = c.id
            GROUP BY c.id, c.title
            ORDER BY attempts DESC
            LIMIT " . (int)$limit
        )->fetchAll();
    }

    public function getMostDifficult($limit = 5) {
        return $this->pdo->query("
            SELECT c.id, c.title,
                   COUNT(s.id) AS attempts,
                   SUM(CASE WHEN s.passed = 1 THEN 1 ELSE 0 END) AS passed,
                   ROUND(100 * SUM(CASE WHEN s.passed = 1 THEN 1 ELSE 0 END) / NULLIF(COUNT(s.id), 0), 1) AS pass_rate
            FROM coding_challenges c
            JOIN coding_submissions s ON s.challenge_id = c.id
            GROUP BY c.id, c.title
            HAVING attempts >= 1
            ORDER BY pass_rate ASC, attempts DESC
            LIMIT " . (int)$limit
        )->fetchAll();
    }

    public function getPopularLanguages() {
        return $this->pdo->query("
            SELECT s.language, COUNT(*) AS total,
                   SUM(CASE WHEN s.passed = 1 THEN 1 ELSE 0 END) AS passed
            FROM coding_submissions s
            GROUP BY s.language
            ORDER BY total DESC
        ")->fetchAll();
    }

    public function getStudentPerformance($limit = 10) {
        return $this->pdo->query("
            SELECT u.id, u.name, u.email,
                   COUNT(s.id) AS submissions,
                   COUNT(DISTINCT CASE WHEN s.passed = 1 THEN s.challenge_id END) AS challenges_passed,
                   SUM(s.score) AS total_xp
            FROM coding_submissions s
            JOIN public_users u ON s.user_id = u.id
            GROUP BY u.id, u.name, u.email
            ORDER BY challenges_passed DESC, total_xp DESC
            LIMIT " . (int)$limit
        )->fetchAll();
    }

    public function getSubmissionsAdmin($limit = 100) {
        return $this->pdo->query("
            SELECT s.*, u.name AS user_name, c.title AS challenge_title, c.course_id
            FROM coding_submissions s
            JOIN public_users u ON s.user_id = u.id
            JOIN coding_challenges c ON s.challenge_id = c.id
            ORDER BY s.created_at DESC
            LIMIT " . (int)$limit
        )->fetchAll();
    }

    public function deleteByChallenge($challengeId) {
        $stmt = $this->pdo->prepare("DELETE FROM coding_submissions WHERE challenge_id = ?");
        return $stmt->execute([$challengeId]);
    }

    public function getSubmissionCountForUserChallenge($userId, $challengeId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM coding_submissions WHERE user_id = ? AND challenge_id = ?");
        $stmt->execute([$userId, $challengeId]);
        return (int)$stmt->fetchColumn();
    }
}
