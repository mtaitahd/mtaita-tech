<?php
$page_title = 'Payment Successful — Mtaita Tech';
$page_desc = 'Your payment was successful. Access your purchased digital products and courses on Mtaita Tech.';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/auth_helper.php';

$reference = trim($_GET['ref'] ?? '');

$enrollment = null;
$payment = null;
$item = null;
$item_type_label = '';

if (!empty($reference)) {
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE payment_reference = ? LIMIT 1");
    $stmt->execute([$reference]);
    $payment = $stmt->fetch();

    if ($payment) {
        $metadata = json_decode($payment['metadata'] ?? '{}', true);
        $itemType = $metadata['item_type'] ?? '';
        $itemId = (int)($metadata['item_id'] ?? 0);

        // Check enrollment for courses
        $stmt = $pdo->prepare("SELECT e.* FROM enrollments e WHERE e.payment_reference = ? LIMIT 1");
        $stmt->execute([$reference]);
        $enrollment = $stmt->fetch();

        if (!$enrollment && $payment['status'] === 'pending') {
            header('Location: payment-pending.php?ref=' . urlencode($reference));
            exit;
        }

        if (!$enrollment && $payment['status'] === 'completed') {
            require_once __DIR__ . '/services/PaymentService.php';
            $paymentService = new PaymentService($pdo);
            // Activate service directly — don't call verifyPayment() which
            // can revert "completed" to "pending" via a race condition.
            $paymentService->activateService($payment);
            $stmt = $pdo->prepare("SELECT e.* FROM enrollments e WHERE e.payment_reference = ? LIMIT 1");
            $stmt->execute([$reference]);
            $enrollment = $stmt->fetch();
        }

        if ($enrollment) {
            if ($enrollment['status'] === 'pending') {
                header('Location: payment-pending.php?ref=' . urlencode($reference));
                exit;
            }
            if ($enrollment['status'] === 'cancelled') {
                header('Location: cancel');
                exit;
            }
            $item_type_label = 'Course';
            $stmt = $pdo->prepare("SELECT id, title, slug, type FROM courses WHERE id = ?");
            $stmt->execute([$enrollment['item_id']]);
            $item = $stmt->fetch();
        } elseif ($itemType === 'product' && $payment['status'] === 'completed') {
            $item_type_label = 'Product';
            $stmt = $pdo->prepare("SELECT id, title, type, price FROM products WHERE id = ?");
            $stmt->execute([$itemId]);
            $item = $stmt->fetch();
        }
    }
}

require_once 'header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Payment Successful</h1>
        <p>Your transaction has been completed</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">

                <?php if ($payment && $payment['status'] === 'completed' && $item): ?>
                    <div class="payment-icon-wrap payment-icon-success">
                        <i class="bi bi-check-lg"></i>
                    </div>

                    <h3 class="payment-status-text">Thank You for Your Purchase!</h3>
                    <p class="payment-help-text">Your payment was successful.</p>

                    <div class="card payment-card">
                        <div class="card-body">
                            <h5 class="payment-card-title">Transaction Details</h5>
                            <div class="payment-row">
                                <span class="payment-label">Reference</span>
                                <span class="payment-value"><?= htmlspecialchars($payment['payment_reference']) ?></span>
                            </div>
                            <div class="payment-row">
                                <span class="payment-label">Item</span>
                                <span class="payment-value"><?= htmlspecialchars($item['title']) ?></span>
                            </div>
                            <div class="payment-row">
                                <span class="payment-label">Type</span>
                                <span class="payment-value"><?= $item_type_label ?></span>
                            </div>
                            <div class="payment-row">
                                <span class="payment-label">Status</span>
                                <span class="badge payment-badge" style="background:#10B981;">
                                    Completed
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="card payment-card mt-4" style="border:1px solid rgba(250,204,21,0.3);background:rgba(250,204,21,0.05);">
                        <div class="card-body text-center">
                            <div style="font-size:2rem;margin-bottom:0.5rem;">⭐</div>
                            <h5 class="payment-card-title">Love what you got?</h5>
                            <p class="payment-help-text">Your feedback helps us improve. Please take a moment to leave us a review on Google!</p>
                            <a href="https://g.page/r/CV7D8gf6yuhmEBM/review" target="_blank" rel="noopener" class="btn btn-outline-cyan" style="border-color:var(--deep-blue);">
                                <i class="bi bi-google me-1"></i> Write a Review
                            </a>
                        </div>
                    </div>

                    <div class="payment-actions">
                        <?php if ($item_type_label === 'Course' && !empty($item['slug'])): ?>
                            <a href="course/<?= urlencode($item['slug']) ?>" class="btn btn-red">
                                <i class="bi bi-play-circle me-1"></i> Go to Course
                            </a>
                        <?php elseif ($item_type_label === 'Product'): ?>
                            <a href="download-product?id=<?= $item['id'] ?>&ref=<?= urlencode($payment['payment_reference']) ?>" class="btn btn-red">
                                <i class="bi bi-download me-1"></i> Download Now
                            </a>
                        <?php endif; ?>
                        <a href="index" class="btn btn-outline-red">
                            <i class="bi bi-house me-1"></i> Back to Home
                        </a>
                    </div>

                <?php else: ?>
                    <div class="payment-icon-wrap payment-icon-error">
                        <i class="bi bi-question-lg"></i>
                    </div>

                    <h3 class="payment-status-text">Payment Not Found</h3>
                    <p class="payment-help-text">We could not find a completed payment matching this reference. Please check your link or contact support.</p>

                    <div class="payment-actions">
                        <a href="index" class="btn btn-red">
                            <i class="bi bi-house me-1"></i> Back to Home
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
