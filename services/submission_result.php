<?php
require_once __DIR__ . '/../auth_helper.php';
require_once __DIR__ . '/../db_connect.php';

header('Content-Type: application/json; charset=utf-8');

if (!isPublicLoggedIn()) {
    echo json_encode(['error' => 'Not logged in.']);
    exit;
}

$userId = getPublicUserId();
$submissionId = (int)($_GET['submission_id'] ?? 0);

require_once __DIR__ . '/../lib/CodingSubmission.php';
$submissionModel = new CodingSubmission();
$submission = $submissionModel->getById($submissionId);

if (!$submission || (int)$submission['user_id'] !== $userId) {
    echo json_encode(['error' => 'Submission not found.']);
    exit;
}

echo json_encode([
    'submission_id' => (int)$submission['id'],
    'status' => $submission['status'],
    'score' => (int)$submission['score'],
    'total_marks' => (int)$submission['total_marks'],
    'tests_total' => (int)$submission['tests_total'],
    'tests_passed' => (int)$submission['tests_passed'],
    'execution_time' => (float)$submission['execution_time'],
    'passed' => (bool)$submission['passed'],
    'results' => $submissionModel->getResultsForSubmissionSafe($submissionId),
]);
