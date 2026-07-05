<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/lib/Product.php';

$productId = (int)($_GET['id'] ?? 0);
$productModel = new Product();

$prod = $productModel->getById($productId);

if (!$prod || !$prod['is_visible'] || !$prod['zip_file']) {
    header('Location: digital_products');
    exit;
}

$authorized = false;

if ($prod['is_paid']) {
    // Check payment reference authorization (guest purchase)
    $ref = trim($_GET['ref'] ?? '');
    if (!empty($ref)) {
        $stmt = $pdo->prepare("
            SELECT p.* FROM payments p
            WHERE p.payment_reference = ? AND p.status = 'completed'
            AND JSON_UNQUOTE(JSON_EXTRACT(p.metadata, '$.item_type')) = 'product'
            AND JSON_UNQUOTE(JSON_EXTRACT(p.metadata, '$.item_id')) = ?
            LIMIT 1
        ");
        $stmt->execute([$ref, $productId]);
        if ($stmt->fetch()) {
            $authorized = true;
        }
    }

    // Fall back to login-based access
    if (!$authorized) {
        if (!isPublicLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: login');
            exit;
        }
        $userId = getPublicUserId();
        if (!$productModel->hasUserPurchased($userId, $productId)) {
            header('Location: product-detail.php?id=' . $productId);
            exit;
        }
        $authorized = true;
    }
} else {
    $authorized = true;
}

if (!$authorized) {
    header('Location: product-detail.php?id=' . $productId);
    exit;
}

$filePath = __DIR__ . '/' . $prod['zip_file'];
if (!file_exists($filePath)) {
    header('Location: product-detail.php?id=' . $productId . '&error=' . urlencode('File not found.'));
    exit;
}

$productModel->incrementDownloadCount($productId);

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($prod['zip_file']) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache');
readfile($filePath);
exit;
