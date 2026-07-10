<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action !== 'compress') {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

$path = $_POST['path'] ?? '';
if (empty($path)) {
    echo json_encode(['success' => false, 'error' => 'No path provided']);
    exit;
}

$root = dirname(__DIR__) . '/';
$fullPath = $root . $path;

if (!file_exists($fullPath)) {
    echo json_encode(['success' => false, 'error' => 'File not found: ' . $path]);
    exit;
}

$ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
    echo json_encode(['success' => false, 'error' => 'Unsupported format: ' . $ext]);
    exit;
}

function createImage($path, $ext) {
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            return imagecreatefromjpeg($path);
        case 'png':
            return imagecreatefrompng($path);
        case 'gif':
            return imagecreatefromgif($path);
        default:
            return false;
    }
}

$img = createImage($fullPath, $ext);
if (!$img) {
    echo json_encode(['success' => false, 'error' => 'Failed to load image. GD may not support this format.']);
    exit;
}

$origW = imagesx($img);
$origH = imagesy($img);
$maxSize = 1920;

if ($origW > $maxSize || $origH > $maxSize) {
    if ($origW >= $origH) {
        $newW = $maxSize;
        $newH = round($origH * ($maxSize / $origW));
    } else {
        $newH = $maxSize;
        $newW = round($origW * ($maxSize / $origH));
    }
    $resized = imagecreatetruecolor($newW, $newH);
    imagealphablending($resized, false);
    imagesavealpha($resized, true);
    imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
    imagedestroy($img);
    $img = $resized;
    $origW = $newW;
    $origH = $newH;
}

$webpPath = preg_replace('/\.(jpe?g|png|gif)$/i', '.webp', $fullPath);
$maxBytes = 500 * 1024;

$quality = 82;
$bestQuality = $quality;
$bestData = null;

for ($q = 85; $q >= 30; $q -= 5) {
    ob_start();
    imagewebp($img, null, $q);
    $data = ob_get_clean();
    if ($data === false) break;
    if (strlen($data) <= $maxBytes) {
        $bestData = $data;
        $bestQuality = $q;
        break;
    }
    if ($bestData === null || strlen($data) < strlen($bestData)) {
        $bestData = $data;
        $bestQuality = $q;
    }
}

if ($bestData === null) {
    ob_start();
    imagewebp($img, null, 30);
    $bestData = ob_get_clean();
    $bestQuality = 30;
}

imagedestroy($img);

$written = file_put_contents($webpPath, $bestData);
if ($written === false) {
    echo json_encode(['success' => false, 'error' => 'Failed to write WebP file. Check directory permissions.']);
    exit;
}

$originalSize = filesize($fullPath);
$webpSize = filesize($webpPath);

function fmtSize($bytes) {
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

echo json_encode([
    'success' => true,
    'original' => fmtSize($originalSize),
    'webp_size' => fmtSize($webpSize),
    'quality' => $bestQuality,
    'dimensions' => $origW . 'x' . $origH,
]);
