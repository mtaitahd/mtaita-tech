<?php
require_once __DIR__ . '/coding_common.php';

$userId = coding_require_auth();

$challengeId = (int)($_POST['challenge_id'] ?? 0);
$code = (string)($_POST['code'] ?? '');
$language = (string)($_POST['language'] ?? '');

if ($challengeId < 1 || trim($code) === '') {
    coding_json_response(['error' => 'Missing challenge or code.'], 422);
}

$challenge = coding_load_challenge($challengeId);

if (!in_array($language, CodingChallenge::LANGUAGES, true) || $language !== $challenge['language']) {
    coding_json_response(['error' => 'Invalid language for this challenge.'], 422);
}

if (strlen($code) > 51200) {
    coding_json_response(['error' => 'Code exceeds the 50KB limit.'], 422);
}

require_once __DIR__ . '/../lib/CodingSubmission.php';
$submissionModel = new CodingSubmission();

if ($submissionModel->rateLimitExceeded($userId, 30, 3600)) {
    coding_json_response(['error' => 'Submission limit reached. Please try again later.'], 429);
}
if ($submissionModel->getSubmissionCountForUserChallenge($userId, $challengeId) >= 25) {
    coding_json_response(['error' => 'You have used all attempts for this challenge.'], 429);
}

$challengeModel = new CodingChallenge();
$tests = $challengeModel->getTestCases($challengeId);
$total = count($tests);
if ($total === 0) {
    coding_json_response(['error' => 'This challenge has no test cases yet. Contact the administrator.'], 422);
}

$passedCount = 0;
$results = [];
$overallError = null;
$sumTime = 0.0;

$isMarkup = ($language === 'html' || $language === 'css');

if ($isMarkup) {
    require_once __DIR__ . '/HtmlCssValidator.php';
    $validator = new HtmlCssValidator();
}

$runner = null;
if (!$isMarkup) {
    if (!file_exists(__DIR__ . '/CodeRunner.php')) {
        coding_json_response(['error' => 'CodeRunner.php is missing on the server (it may have been quarantined by the host security scanner). Contact the administrator.'], 503);
    }
    require_once __DIR__ . '/CodeRunner.php';
    $runner = new CodeRunner();
    if (!$runner->isLanguageSupported($language)) {
        coding_json_response(['error' => 'This language is not available on the server (compiler/interpreter not installed). Contact the administrator.'], 503);
    }
}

try {
    foreach ($tests as $i => $test) {
    if ($overallError === 'compile_error') {
        $results[] = ['test_case_id' => $test['id'], 'passed' => false, 'status' => 'skipped', 'actual_output' => null, 'time' => 0];
        continue;
    }

    $testInput = (string)($test['input_data'] ?? '');

    if ($isMarkup) {
        $start = microtime(true);
        if ($language === 'css') {
            $res = $validator->validateCss($code, $testInput);
        } else {
            $res = $validator->validate($code, $testInput);
        }
        $elapsed = round(microtime(true) - $start, 4);
        $sumTime += $elapsed;
        if ($res['passed']) {
            $passedCount++;
            $results[] = ['test_case_id' => $test['id'], 'passed' => true, 'status' => 'passed', 'actual_output' => $res['details'] ?? 'OK', 'time' => $elapsed];
        } else {
            $results[] = ['test_case_id' => $test['id'], 'passed' => false, 'status' => 'failed', 'actual_output' => $res['details'] ?? 'Failed', 'time' => $elapsed];
        }
        continue;
    }

    $run = $runner->run($language, $code, $testInput, (int)$challenge['time_limit'], (int)$challenge['memory_limit']);
    $sumTime += (float)$run['time'];

    if ($run['status'] === 'compile_error') {
        $overallError = 'compile_error';
        $results[] = ['test_case_id' => $test['id'], 'passed' => false, 'status' => 'compile_error', 'actual_output' => $run['output'], 'time' => $run['time']];
        continue;
    }
    if ($run['status'] === 'unavailable') {
        coding_json_response(['error' => $run['output']], 503);
    }
    if ($run['status'] === 'timeout') {
        $results[] = ['test_case_id' => $test['id'], 'passed' => false, 'status' => 'timeout', 'actual_output' => null, 'time' => $run['time']];
        continue;
    }
    if ($run['status'] === 'runtime_error') {
        $results[] = ['test_case_id' => $test['id'], 'passed' => false, 'status' => 'error', 'actual_output' => $run['output'], 'time' => $run['time']];
        continue;
    }

    $ok = coding_normalize_output($run['output']) === coding_normalize_output((string)($test['expected_output'] ?? ''));
    if ($ok) $passedCount++;
    $results[] = ['test_case_id' => $test['id'], 'passed' => $ok, 'status' => $ok ? 'passed' : 'failed', 'actual_output' => $ok ? null : $run['output'], 'time' => $run['time']];
    }
} catch (\Throwable $e) {
    coding_json_response(['error' => 'Grading failed on the server: ' . $e->getMessage()], 500);
}

$marks = (int)$challenge['marks'];
$score = $total > 0 ? (int)round($marks * ($passedCount / $total)) : 0;
$scorePct = $total > 0 ? (int)round(100 * $passedCount / $total) : 0;
$passingScore = (int)$challenge['passing_score'];
$passedOverall = ($overallError === null) && $passedCount === $total && $scorePct >= $passingScore;

if ($overallError === 'compile_error') {
    $status = 'compilation_error';
} elseif ($passedOverall) {
    $status = 'passed';
} else {
    $status = 'failed';
}

$submissionId = $submissionModel->createSubmission(
    $userId, $challenge, $code, $status, $score, $total, $passedCount, round($sumTime, 4)
);

foreach ($results as $r) {
    $submissionModel->saveResult($submissionId, $r['test_case_id'], $r['passed'], $r['status'], $r['actual_output'], $r['time']);
}

if ($passedOverall) {
    $submissionModel->updateCourseProgress($userId, (int)$challenge['course_id']);
}

coding_json_response([
    'submission_id' => $submissionId,
    'status' => $status,
    'tests_total' => $total,
    'tests_passed' => $passedCount,
    'tests_failed' => $total - $passedCount,
    'score' => $score,
    'total_marks' => $marks,
    'passing_score' => $passingScore,
    'passed' => $passedOverall,
    'execution_time' => round($sumTime, 4),
]);
