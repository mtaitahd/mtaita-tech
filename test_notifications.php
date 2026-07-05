<?php
// Diagnostic script for notifications
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/lib/Settings.php';

echo "<h2>Diagnostic: checking Settings</h2>";
echo 'Admin Email: ' . Settings::get('admin_email', 'FALLBACK') . "<br>";
echo 'Admin Phone: ' . Settings::get('admin_phone', 'FALLBACK') . "<br>";
echo 'SMTP User: ' . Settings::get('smtp_user', 'FALLBACK') . "<br>";
echo 'SMTP Host: ' . Settings::get('smtp_host', 'FALLBACK') . "<br>";
echo 'SMTP Port: ' . Settings::get('smtp_port', 'FALLBACK') . "<br>";
echo 'Meseji Key: ' . substr(Settings::get('meseji_api_key', ''), 0, 10) . '...<br>';

echo "<h2>Diagnostic: testing Mailer</h2>";
require_once __DIR__ . '/mailer.php';
$mailer = new Mailer();
echo "Mailer created<br>";
echo "From: {$mailer->fromEmail}<br>";
$result = @$mailer->send(['mtaitajohnson7@gmail.com'], 'Test from Mtaita Tech', 'This is a test email from the diagnostic script.');
echo "Mailer send result: " . ($result ? 'SUCCESS' : 'FAILED') . "<br>";

echo "<h2>Diagnostic: testing SMS</h2>";
require_once __DIR__ . '/lib/SMS.php';
$sms = new SMS();
$fmtPhone = $sms->formatPhone('+255 616 591 639');
echo "Formatted phone: $fmtPhone<br>";
$result = $sms->send('+255 616 591 639', 'Test SMS from Mtaita Tech diagnostic.');
echo "SMS send result: " . ($result ? 'SUCCESS' : 'FAILED') . "<br>";

echo "<h2>PHP Info</h2>";
echo "curl: " . (function_exists('curl_version') ? 'enabled' : 'NOT enabled') . "<br>";
echo "openssl: " . (extension_loaded('openssl') ? 'enabled' : 'NOT enabled') . "<br>";
echo "sockets: " . (extension_loaded('sockets') ? (function_exists('stream_socket_client') ? 'enabled' : 'partial') : 'NOT enabled') . "<br>";
