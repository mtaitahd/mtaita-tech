<?php
$page_title = 'Checkout — Mtaita Tech';
$page_desc = 'Complete your purchase of digital products and courses securely on Mtaita Tech.';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/services/PaymentService.php';

$type = $_GET['type'] ?? '';
$id   = (int)($_GET['id'] ?? 0);

if (!in_array($type, ['course', 'product']) || $id <= 0) {
    header('Location: index');
    exit;
}

// Courses require login (need account for access)
if ($type === 'course') {
    requirePublicLogin();
}

$item = null;
if ($type === 'course') {
    $stmt = $pdo->prepare("SELECT id, title, slug, description, type, price FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
} else {
    $stmt = $pdo->prepare("SELECT id, title, description, type, price, is_paid FROM products WHERE id = ? AND is_visible = 1");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
}

if (!$item) {
    header('Location: index');
    exit;
}

$user = isPublicLoggedIn() ? getPublicUser() : null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        $errors[] = 'Invalid security token. Please try again.';
    }

    $paymentType = $_POST['payment_type'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    // Collect guest info for product purchases
    $guestName = trim($_POST['guest_name'] ?? '');
    $guestEmail = trim($_POST['guest_email'] ?? '');

    if (!isPublicLoggedIn() && $type === 'product') {
        if (empty($guestName)) {
            $errors[] = 'Please enter your name.';
        }
        if (empty($guestEmail) || !filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
    }

    if (!in_array($paymentType, ['mobile'])) {
        $errors[] = 'Please select a payment method.';
    }

    if ($paymentType === 'mobile') {
        $formattedPhone = SnippeApi::formatPhone($phone);
        if (!preg_match('/^255[0-9]{9}$/', $formattedPhone)) {
            $errors[] = 'Please enter a valid Tanzanian phone number (e.g. 0712XXXXXX).';
        }
    }

    $amountError = SnippeApi::validateAmount((int)$item['price']);
    if ($amountError) {
        $errors[] = $amountError;
    }

    if (empty($errors)) {
        // Build customer array for PaymentService
        if (isPublicLoggedIn()) {
            $customer = $user;
        } else {
            $nameParts = explode(' ', $guestName, 2);
            $customer = [
                'name'  => $guestName,
                'email' => $guestEmail,
                'phone' => $phone ?: null,
            ];
        }

        if ($paymentType === 'mobile') {
            $paymentService = new PaymentService($pdo);
            $payment = $paymentService->createPayment(
                $customer,
                $item,
                $paymentType,
                $phone,
                $type
            );

            if ($payment === null) {
                $errors[] = 'Payment failed: ' . ($paymentService->getLastError() ?? 'Unknown error');
            } else {
                header('Location: payment-pending.php?ref=' . urlencode($payment['payment_reference']));
                exit;
            }
        }
    }
}

require_once 'header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Checkout</h1>
        <p>Complete your purchase</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <div class="col-lg-8">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>

                    <!-- Order Summary -->
                    <div class="card payment-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3 flex-wrap">
                                <div class="checkout-item-icon">
                                    <i class="bi bi-<?= $type === 'course' ? 'book' : 'box-seam' ?>"></i>
                                </div>
                                <div class="checkout-item-info">
                                    <span class="badge payment-badge mb-2" style="background:<?= ($item['type'] ?? 'free') === 'free' ? '#10B981' : 'var(--red)' ?>;"><?= htmlspecialchars(ucfirst($item['type'] ?? 'paid')) ?></span>
                                    <h4><?= htmlspecialchars($item['title']) ?></h4>
                                    <p><?= htmlspecialchars(substr(strip_tags($item['description'] ?? ''), 0, 200)) ?></p>
                                </div>
                                <div class="text-end flex-shrink-0 checkout-price-col" style="min-width:100px;">
                                    <div class="checkout-item-price">
                                        TSh <?= number_format((int)$item['price']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- Guest Info Form (for product purchases without login) -->
                <?php if (!isPublicLoggedIn() && $type === 'product'): ?>
                <div class="card payment-card">
                    <div class="card-body">
                        <h5 class="payment-card-title">Your Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="guest_name" form="paymentForm" class="form-control" placeholder="John Doe" value="<?= htmlspecialchars($_POST['guest_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="guest_email" form="paymentForm" class="form-control" placeholder="john@example.com" value="<?= htmlspecialchars($_POST['guest_email'] ?? '') ?>" required>
                                <small class="payment-help-text d-block">We'll send your download link here</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Payment Form -->
                <form method="POST" id="paymentForm">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                    <div class="card payment-card">
                        <div class="card-body">
                            <h5 class="payment-card-title">Payment Method</h5>

                            <div class="form-check mb-2 payment-option" data-type="mobile">
                                <input class="form-check-input" type="radio" name="payment_type" value="mobile" id="methodMobile" checked>
                                <label class="form-check-label w-100" for="methodMobile">
                                    <i class="bi bi-phone"></i> Mobile Money (M-Pesa / Tigo Pesa / Airtel Money)
                                    <small class="d-block text-muted" style="font-weight:400;">Receive USSD push on your phone to authorize payment</small>
                                </label>
                            </div>

                            <div id="phoneField" class="mt-3">
                                <label class="form-label">Phone Number (Mobile Money)</label>
                                <input type="tel" name="phone" class="form-control" placeholder="e.g. 0712XXXXXX" value="<?= htmlspecialchars($_POST['phone'] ?? ($user['phone'] ?? '')) ?>" required>
                                <small class="payment-help-text d-block">Enter your Tanzanian mobile money number</small>
                            </div>
                        </div>
                    </div>

                    <div class="card payment-card">
                        <div class="card-body">
                            <h5 class="payment-card-title">Order Summary</h5>
                            <div class="payment-row">
                                <span class="payment-label"><?= htmlspecialchars($item['title']) ?> (<?= ucfirst($type) ?>)</span>
                                <span class="payment-value">TSh <?= number_format((int)$item['price']) ?></span>
                            </div>
                            <hr>
                            <div class="payment-row">
                                <span class="checkout-summary-total">Total</span>
                                <span class="checkout-summary-amount">TSh <?= number_format((int)$item['price']) ?></span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-red w-100 btn-checkout-pay" id="payBtn">
                        <i class="bi bi-lock-fill me-2"></i> Pay TSh <?= number_format((int)$item['price']) ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('paymentForm').addEventListener('submit', function() {
    var btn = document.getElementById('payBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
});
</script>

<?php require_once 'footer.php'; ?>
