<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/lib/OTP.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$type = $input['type'] ?? ($_SESSION['pending_type'] ?? '');
$userId = $_SESSION['pending_user_id'] ?? 0;
$otp_method = $input['otp_method'] ?? ($_SESSION['pending_otp_method'] ?? 'email');

if (empty($type) || empty($userId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

// Clear the success-shown flag so the toast shows again on next page view
unset($_SESSION['otp_success_shown_' . $type]);

$otp = new OTP();
$sent = $otp->sendUserOTP($userId, $type, $otp_method);

if ($sent) {
    echo json_encode(['success' => true, 'message' => 'Code resent.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send code. Please try again.']);
}
