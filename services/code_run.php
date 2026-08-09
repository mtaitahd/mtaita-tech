<?php
require_once __DIR__ . '/coding_common.php';

$userId = coding_require_auth();

$challengeId = (int)($_POST['challenge_id'] ?? 0);
$code = (string)($_POST['code'] ?? '');
$language = (string)($_POST['language'] ?? '');
$customInput = (string)($_POST['input'] ?? '');

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
if ($submissionModel->rateLimitExceeded($userId, 60, 3600)) {
    coding_json_response(['error' => 'Too many requests. Please wait a while before running more code.'], 429);
}

if ($language === 'html' || $language === 'css') {
    coding_json_response([
        'status' => 'ok',
        'preview' => true,
        'output' => $code,
        'time' => 0,
    ]);
}

if (!file_exists(__DIR__ . '/CodeRunner.php')) {
    coding_json_response(['error' => 'CodeRunner.php is missing on the server (it may have been quarantined by the host security scanner). Contact the administrator.'], 503);
}
require_once __DIR__ . '/CodeRunner.php';
$runner = new CodeRunner();

if (!$runner->isLanguageSupported($language)) {
    coding_json_response(['error' => 'This language is not available on the server (compiler/interpreter not installed). Contact the administrator.'], 503);
}

$input = $customInput !== '' ? $customInput : (string)($challenge['sample_input'] ?? '');
try {
    $result = $runner->run($language, $code, $input, (int)$challenge['time_limit'], (int)$challenge['memory_limit']);
} catch (\Throwable $e) {
    coding_json_response(['error' => 'Code execution failed on the server: ' . $e->getMessage()], 500);
}

coding_json_response($result);
