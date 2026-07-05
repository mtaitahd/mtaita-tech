<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/google_oauth_config.php';

if (isset($_GET['error'])) {
    header('Location: login.php?error=' . urlencode('Google sign-in was cancelled or denied. Please try again.'));
    exit;
}

$code = $_GET['code'] ?? '';
if (empty($code)) {
    header('Location: login.php?error=' . urlencode('Invalid authorization code. Please try again.'));
    exit;
}

$tokenData = getGoogleAccessToken($code);
if (!$tokenData || !isset($tokenData['access_token'])) {
    header('Location: login.php?error=' . urlencode('Failed to authenticate with Google. Please try again.'));
    exit;
}

$googleUser = getGoogleUserInfo($tokenData['access_token']);
if (!$googleUser || empty($googleUser['email'])) {
    header('Location: login.php?error=' . urlencode('Failed to retrieve your Google profile information. Please try again.'));
    exit;
}

$googleId = $googleUser['id'] ?? '';
$email = $googleUser['email'];
$name = $googleUser['name'] ?? $googleUser['email'];
$picture = $googleUser['picture'] ?? '';

$stmt = $pdo->prepare("SELECT id, google_id FROM public_users WHERE email = ?");
$stmt->execute([$email]);
$existingUser = $stmt->fetch();

if ($existingUser) {
    $stmt = $pdo->prepare("UPDATE public_users SET google_id = ?, profile_picture = ?, name = ? WHERE id = ?");
    $stmt->execute([$googleId, $picture, $name, $existingUser['id']]);
    $_SESSION['public_user_id'] = $existingUser['id'];
} else {
    $stmt = $pdo->prepare(
        "INSERT INTO public_users (google_id, name, email, password, profile_picture, created_at) VALUES (?, ?, ?, NULL, ?, NOW())"
    );
    $stmt->execute([$googleId, $name, $email, $picture]);
    $_SESSION['public_user_id'] = $pdo->lastInsertId();
}

$_SESSION['google_logged_in'] = true;

$redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
unset($_SESSION['redirect_after_login']);
header('Location: ' . $redirect);
exit;
