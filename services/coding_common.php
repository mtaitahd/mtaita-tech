<?php
function coding_shutdown_handler() {
    $err = error_get_last();
    if (!$err) return;
    if (in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
        if (function_exists('coding_json_response')) {
            if (ob_get_level() > 0) {
                while (ob_get_level() > 0) { ob_end_clean(); }
            }
            coding_json_response(['error' => 'Server error: ' . $err['message'] . ' (in ' . $err['file'] . ':' . $err['line'] . ')'], 500);
        }
    }
}
register_shutdown_function('coding_shutdown_handler');

function coding_require_auth() {
    global $pdo;
    require_once __DIR__ . '/../auth_helper.php';
    require_once __DIR__ . '/../db_connect.php';
    if (!isPublicLoggedIn()) {
        coding_json_response(['error' => 'You must be logged in.'], 401);
    }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        coding_json_response(['error' => 'Invalid request method.'], 405);
    }
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        coding_json_response(['error' => 'Invalid security token. Please refresh the page.'], 403);
    }
    return getPublicUserId();
}

function coding_load_challenge($challengeId) {
    global $pdo;
    require_once __DIR__ . '/../lib/CodingChallenge.php';
    require_once __DIR__ . '/../lib/AccessControl.php';
    $challengeModel = new CodingChallenge();
    $challenge = $challengeModel->getByIdWithCourse((int)$challengeId);
    if (!$challenge || $challenge['course_status'] !== 'published' || !$challenge['is_published']) {
        coding_json_response(['error' => 'Challenge not found.'], 404);
    }
    $userId = getPublicUserId();
    $accessControl = new AccessControl();
    if (!$accessControl->hasCourseAccess($userId, ['id' => (int)$challenge['course_id'], 'type' => $challenge['course_type']])) {
        coding_json_response(['error' => 'You are not enrolled in this course.'], 403);
    }
    return $challenge;
}

function coding_normalize_output($text) {
    $lines = preg_split('/\r\n|\r|\n/', (string)$text);
    $lines = array_map('rtrim', $lines);
    while (!empty($lines) && end($lines) === '') {
        array_pop($lines);
    }
    return implode("\n", $lines);
}

function coding_json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}
