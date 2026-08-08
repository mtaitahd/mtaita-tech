<?php
class CodingProgress {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function countTotalChallengesInCourse($courseId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM coding_challenges WHERE course_id = ? AND is_published = 1");
        $stmt->execute([$courseId]);
        return (int)$stmt->fetchColumn();
    }

    public function countCompletedChallengesInCourse($userId, $courseId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT s.challenge_id) FROM coding_submissions s
            JOIN coding_challenges c ON s.challenge_id = c.id
            WHERE s.user_id = ? AND c.course_id = ? AND c.is_published = 1 AND s.passed = 1
        ");
        $stmt->execute([$userId, $courseId]);
        return (int)$stmt->fetchColumn();
    }

    public function countTotalItemsInCourse($courseId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
        $stmt->execute([$courseId]);
        $lessons = (int)$stmt->fetchColumn();
        return $lessons + $this->countTotalChallengesInCourse($courseId);
    }

    public function countCompletedItemsInCourse($userId, $courseId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM lesson_progress lp
            JOIN lessons l ON lp.lesson_id = l.id
            WHERE lp.user_id = ? AND l.course_id = ? AND lp.completed = 1
        ");
        $stmt->execute([$userId, $courseId]);
        $lessons = (int)$stmt->fetchColumn();
        return $lessons + $this->countCompletedChallengesInCourse($userId, $courseId);
    }

    public function overallProgressInCourse($userId, $courseId) {
        $total = $this->countTotalItemsInCourse($courseId);
        if ($total <= 0) return 0;
        $done = $this->countCompletedItemsInCourse($userId, $courseId);
        return (int)round(($done / $total) * 100);
    }

    public function calculateXp($score, $isFirstPass) {
        $xp = (int)$score;
        if ($isFirstPass) $xp += 20;
        return $xp;
    }

    public function getStreakDays($userId) {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT DATE(created_at) AS d
            FROM coding_submissions
            WHERE user_id = ? AND passed = 1
            ORDER BY d DESC
        ");
        $stmt->execute([$userId]);
        $days = array_map(function ($r) { return $r['d']; }, $stmt->fetchAll());
        if (empty($days)) return 0;

        $today = new DateTime(date('Y-m-d'));
        $current = DateTime::createFromFormat('Y-m-d', $days[0]);
        $streak = 0;

        $startIndex = 0;
        $diffToday = (int)$today->diff($current)->format('%a');
        if ($diffToday === 0 || $diffToday === 1) {
            $streak = 1;
        } else {
            return 0;
        }

        for ($i = 1; $i < count($days); $i++) {
            $prev = DateTime::createFromFormat('Y-m-d', $days[$i - 1]);
            $cur = DateTime::createFromFormat('Y-m-d', $days[$i]);
            $gap = (int)$prev->diff($cur)->format('%a');
            if ($gap === 1) {
                $streak++;
            } else {
                break;
            }
        }
        return $streak;
    }

    public function getBadges($userId) {
        $stats = $this->getStatsForUser($userId);
        $completed = (int)($stats['completed'] ?? 0);
        $streak = $this->getStreakDays($userId);
        $languages = $this->getLanguagesPassed($userId);

        $badges = [];
        if ($completed >= 1) {
            $badges[] = ['icon' => '🏆', 'name' => 'First Coding Challenge', 'desc' => 'Passed your first coding challenge'];
        }
        if ($streak >= 3) {
            $badges[] = ['icon' => '🔥', 'name' => $streak . ' Day Coding Streak', 'desc' => 'Kept a coding streak going'];
        }
        if ($completed >= 10) {
            $badges[] = ['icon' => '💪', 'name' => '10 Challenges Completed', 'desc' => 'Passed 10 coding challenges'];
        }
        if ($completed >= 50) {
            $badges[] = ['icon' => '💻', 'name' => '50 Challenges Completed', 'desc' => 'Passed 50 coding challenges'];
        }
        foreach ($languages as $lang) {
            $icons = ['python' => '🐍', 'java' => '☕', 'cpp' => '➕', 'c' => '🅲', 'php' => '🐘', 'html' => '🌐', 'css' => '🎨'];
            if ($lang['count'] >= 1) {
                $badges[] = ['icon' => $icons[$lang['language']] ?? '⭐', 'name' => ucfirst($lang['language']) . ' Beginner', 'desc' => 'Passed your first ' . strtoupper($lang['language']) . ' challenge'];
            }
            if ($lang['count'] >= 5) {
                $badges[] = ['icon' => '🚀', 'name' => ucfirst($lang['language']) . ' Explorer', 'desc' => 'Passed 5 ' . strtoupper($lang['language']) . ' challenges'];
            }
        }
        return array_slice($badges, 0, 12);
    }

    private function getLanguagesPassed($userId) {
        $stmt = $this->pdo->prepare("
            SELECT s.language, COUNT(DISTINCT s.challenge_id) AS count
            FROM coding_submissions s
            WHERE s.user_id = ? AND s.passed = 1
            GROUP BY s.language
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    private function getStatsForUser($userId) {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(DISTINCT challenge_id) AS attempted,
                COUNT(DISTINCT CASE WHEN passed = 1 THEN challenge_id END) AS completed
            FROM coding_submissions WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
}
