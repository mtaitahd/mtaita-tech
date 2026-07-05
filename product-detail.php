<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/lib/Product.php';
require_once __DIR__ . '/lib/Order.php';
require_once __DIR__ . '/lib/Review.php';
require_once __DIR__ . '/lib/Subscriber.php';

$productModel = new Product();
$order = new Order();
$review = new Review();
$subscriber = new Subscriber();

$productId = (int)($_GET['id'] ?? 0);
$prod = $productId ? $productModel->getById($productId) : null;

if (!$prod || !$prod['is_visible']) {
    header('Location: digital_products');
    exit;
}

$hasAccess = false;
$isLoggedIn = isPublicLoggedIn();
$userId = $isLoggedIn ? getPublicUserId() : 0;

if (!$prod['is_paid']) {
    $hasAccess = true;
} elseif ($isLoggedIn && $productModel->hasUserPurchased($userId, $productId)) {
    $hasAccess = true;
}



$embedUrl = $productModel->getYoutubeEmbedUrl($prod['youtube_url']);

$error_msg = '';
$success_msg = '';

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'submit_review') {
        $name = trim($_POST['name'] ?? '');
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($prod['is_paid'] && !$hasAccess) {
            $error_msg = 'You need access to this product before leaving a review.';
        } elseif ($name === '' || $comment === '') {
            $error_msg = 'Please add your name and a review comment.';
        } elseif ($rating < 1 || $rating > 5) {
            $error_msg = 'Please choose a rating between 1 and 5 stars.';
        } else {
            $review->create([
                'product_id' => $productId,
                'user_id' => $isLoggedIn ? $userId : null,
                'name' => $name,
                'email' => $isLoggedIn ? null : null,
                'rating' => $rating,
                'comment' => $comment,
                'is_approved' => 0
            ]);
            $success_msg = 'Thanks for your review! It will appear after approval.';
        }
    }
}

$approvedReviews = $review->getByProduct($productId);
$averageRating = $review->getAverageRating($productId);
$reviewCount = count($approvedReviews);

$page_title = htmlspecialchars($prod['title']) . ' — Mtaita Tech';
$page_desc = htmlspecialchars(mb_strimwidth(strip_tags($prod['description'] ?? 'Digital product from Mtaita Tech'), 0, 155, ''));
require_once 'header.php';
?>

<section class="section-padding">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <?php if ($embedUrl && $hasAccess): ?>
                <div class="ratio ratio-16x9 mb-4">
                    <iframe src="<?= htmlspecialchars($embedUrl) ?>" allowfullscreen></iframe>
                </div>
                <?php elseif ($prod['thumbnail']): ?>
                <img src="<?= htmlspecialchars($prod['thumbnail']) ?>" alt="" class="w-100 rounded-3 mb-4" style="max-height:400px;object-fit:cover;">
                <?php endif; ?>

                <div class="floating-card mb-4">
                    <h1 style="color:var(--deep-blue);font-weight:700;"><?= htmlspecialchars($prod['title']) ?></h1>
                    <span class="badge mb-3" style="background:<?= $prod['is_paid'] ? 'var(--red)' : '#10B981' ?>;">
                        <?= $prod['is_paid'] ? 'TSh ' . number_format($prod['price']) : 'Free' ?>
                    </span>
                    <span class="badge bg-secondary ms-2"><?= $productModel->getTypeLabel($prod['type']) ?></span>
                    <p class="mt-3"><?= nl2br(htmlspecialchars($prod['description'] ?? '')) ?></p>
                </div>

                <?php if ($hasAccess && ($prod['zip_file'] || $prod['youtube_url'])): ?>
                <div class="floating-card mb-4">
                    <h5 style="color:var(--deep-blue);font-weight:700;">Downloads</h5>
                    <div class="d-flex gap-3 mt-3">
                        <?php if ($prod['zip_file']): ?>
                            <a href="download-product?id=<?= $prod['id'] ?>" class="btn btn-red"><i class="bi bi-download me-2"></i>Download ZIP</a>
                        <?php endif; ?>
                        <?php if ($prod['youtube_url']): ?>
                            <a href="<?= htmlspecialchars($prod['youtube_url']) ?>" target="_blank" class="btn btn-outline-red"><i class="bi bi-youtube me-2"></i>Watch on YouTube</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php elseif ($prod['is_paid'] && !$hasAccess): ?>
                <div class="floating-card text-center py-4 mb-4" style="border:2px solid var(--red);">
                    <i class="bi bi-lock-fill" style="font-size:2.5rem;color:var(--red);"></i>
                    <h4 class="mt-3" style="color:var(--deep-blue);">Premium Content</h4>
                    <p class="text-muted">Purchase this product to access downloads.</p>
                    <a href="checkout?type=product&id=<?= $prod['id'] ?>" class="btn btn-red btn-lg">Buy Now — TSh <?= number_format($prod['price']) ?></a>
                </div>
                <?php endif; ?>

                <!-- Reviews -->
                <div class="floating-card">
                    <h5 style="color:var(--deep-blue);font-weight:700;">Reviews <?= $reviewCount > 0 ? '(' . $reviewCount . ')' : '' ?></h5>
                    <?php if ($averageRating > 0): ?>
                    <div class="mb-3">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?= $i <= round($averageRating) ? '-fill' : '' ?>" style="color:#F59E0B;font-size:1.2rem;"></i>
                        <?php endfor; ?>
                        <span class="ms-2 fw-bold"><?= $averageRating ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (empty($approvedReviews)): ?>
                        <p class="text-muted">No reviews yet.</p>
                    <?php else: ?>
                        <?php foreach ($approvedReviews as $r): ?>
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between">
                                <strong><?= htmlspecialchars($r['name']) ?></strong>
                                <small class="text-muted"><?= date('M d, Y', strtotime($r['created_at'])) ?></small>
                            </div>
                            <div class="mb-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?= $i <= $r['rating'] ? '-fill' : '' ?>" style="color:#F59E0B;font-size:0.85rem;"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="mb-0"><?= htmlspecialchars($r['comment']) ?></p>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <h6 class="mt-4" style="color:var(--deep-blue);font-weight:700;">Leave a Review</h6>
                    <?php if ($error_msg): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>
                    <?php if ($success_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="submit_review">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <div class="mb-2">
                            <input type="text" name="name" class="form-control" placeholder="Your name" required>
                        </div>
                        <div class="mb-2">
                            <select name="rating" class="form-select" required>
                                <option value="">Rating</option>
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <option value="<?= $i ?>"><?= str_repeat('★', $i) ?> <?= $i ?>/5</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <textarea name="comment" class="form-control" rows="3" placeholder="Your review" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-red btn-sm">Submit Review</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="floating-card">
                    <h5 style="color:var(--deep-blue);font-weight:700;">Product Info</h5>
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tr><td class="text-muted ps-0">Type</td><td class="fw-bold"><?= $productModel->getTypeLabel($prod['type']) ?></td></tr>
                            <tr><td class="text-muted ps-0">Price</td><td class="fw-bold"><?= $prod['is_paid'] ? 'TSh ' . number_format($prod['price']) : 'Free' ?></td></tr>
                            <tr><td class="text-muted ps-0">Downloads</td><td class="fw-bold"><?= number_format($prod['download_count']) ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
