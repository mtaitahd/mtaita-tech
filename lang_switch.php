<?php
require_once __DIR__ . '/config.php';

$new_lang = $_GET['lang'] ?? 'en';
$allowed = ['en', 'sw'];

if (in_array($new_lang, $allowed)) {
    $_SESSION['lang'] = $new_lang;
}

$referer = $_SERVER['HTTP_REFERER'] ?? SITE_URL;
header("Location: $referer");
exit;
