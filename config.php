<?php
// Load environment variables from .env
require_once __DIR__ . '/DotEnv.php';
try {
    DotEnv::load(__DIR__ . '/.env');
} catch (RuntimeException $e) {
    // Fall back to existing defaults if .env is missing
}

$app_env = env('APP_ENV', 'production');
$debug = filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN);

error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');

// Site configuration
define('SITE_NAME', env('SITE_NAME', 'Mtaita Tech'));
define('SITE_TAGLINE', env('SITE_TAGLINE', 'Software Development Company in Kilimanjaro & Arusha'));
define('SITE_URL', env('SITE_URL', 'https://mtaitatech.online'));
define('ADMIN_EMAIL', env('ADMIN_EMAIL', 'mtaitajohnson7@gmail.com'));
define('UPLOAD_DIR', __DIR__ . '/assets/img/uploads/');
define('UPLOAD_URL', 'assets/img/uploads/');

// Database
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'mtaita_tech_db'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

// SMTP
define('SMTP_HOST', env('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', (int)env('SMTP_PORT', 587));
define('SMTP_ENCRYPTION', env('SMTP_ENCRYPTION', 'ssl'));
define('SMTP_USER', env('SMTP_USER', ''));
define('SMTP_PASS', env('SMTP_PASS', ''));
define('FROM_EMAIL', env('FROM_EMAIL', SMTP_USER));
define('FROM_NAME', env('FROM_NAME', SITE_NAME));

// Language
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$lang_code = $_SESSION['lang'] ?? 'en';
$lang_dir = __DIR__ . '/lang';
$lang_file = "$lang_dir/$lang_code.php";
if (!file_exists($lang_file)) {
    $lang_code = 'en';
    $lang_file = "$lang_dir/en.php";
}
$_SESSION['lang'] = $lang_code;
$lang = require $lang_file;

function __($key) {
    global $lang;
    return $lang[$key] ?? $key;
}

function env(string $key, mixed $default = null): mixed
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }
    $lower = strtolower($value);
    if ($lower === 'true') return true;
    if ($lower === 'false') return false;
    if ($lower === 'null') return null;
    return $value;
}

// Snippe Payments
define('SNIPPE_API_KEY', env('SNIPPE_API_KEY', ''));
define('SNIPPE_WEBHOOK_SECRET', env('SNIPPE_WEBHOOK_SECRET', ''));
define('SNIPPE_API_URL', env('SNIPPE_API_URL', 'https://api.snippe.sh'));
define('SNIPPE_API_VERSION', env('SNIPPE_API_VERSION', '2026-01-25'));
define('CURRENCY', 'TZS');
define('CURRENCY_MIN_AMOUNT', 1000);

// Meseji SMS
define('MESEJI_API_KEY', env('MESEJI_API_KEY', ''));
define('MESEJI_SENDER_ID', env('MESEJI_SENDER_ID', 'MTAITATEC'));

// Default SEO meta
$page_title = $page_title ?? SITE_NAME . ' — ' . SITE_TAGLINE;
$page_desc = $page_desc ?? 'Software & web development company in Kilimanjaro and Arusha, Tanzania. We build websites, mobile apps, POS systems, inventory software, and custom business solutions.';
$page_keywords = $page_keywords ?? 'software company Tanzania, software company Kilimanjaro, website development Tanzania, web design Tanzania, IT company Tanzania, POS system Tanzania, mobile app development Tanzania';
$page_heading = $page_heading ?? '';
$og_image = $og_image ?? SITE_URL . '/assets/img/og-default.jpg';

date_default_timezone_set('Africa/Nairobi');
