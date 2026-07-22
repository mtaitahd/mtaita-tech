<?php
$page_title = 'Processing Payment — Mtaita Tech';
$page_desc = 'Your payment is being processed. Please wait while we confirm your transaction on Mtaita Tech.';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/services/PaymentService.php';

$reference = trim($_GET['ref'] ?? '');
if (empty($reference)) {
    header('Location: index');
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*,
        COALESCE(c.title, pr.title) AS item_title,
        COALESCE(c.slug, pr.id) AS item_slug,
        JSON_UNQUOTE(JSON_EXTRACT(p.metadata, '$.item_type')) AS item_type,
        JSON_UNQUOTE(JSON_EXTRACT(p.metadata, '$.item_id')) AS item_id
    FROM payments p
    LEFT JOIN courses c ON JSON_UNQUOTE(JSON_EXTRACT(p.metadata, '$.item_type')) = 'course' AND JSON_UNQUOTE(JSON_EXTRACT(p.metadata, '$.item_id')) = c.id
    LEFT JOIN products pr ON JSON_UNQUOTE(JSON_EXTRACT(p.metadata, '$.item_type')) = 'product' AND JSON_UNQUOTE(JSON_EXTRACT(p.metadata, '$.item_id')) = pr.id
    WHERE p.payment_reference = ?
    LIMIT 1
");
$stmt->execute([$reference]);
$payment = $stmt->fetch();

if (!$payment) {
    header('Location: index');
    exit;
}

$paymentService = new PaymentService($pdo);

// Auto-verify on page load if payment is still pending and older than 30s
// This catches missed webhooks without relying on any other file
if ($payment['status'] === 'pending') {
    $created = strtotime($payment['created_at'] ?? 'now');
    if (time() - $created > 30) {
        $paymentService->verifyPayment($reference);
        // Re-fetch payment after verification
        $stmt = $pdo->prepare("SELECT p.*,
            COALESCE(c.title, pr.title) AS item_title,
            COALESCE(c.slug, pr.id) AS item_slug,
            JSON_UNQUOTE(JSON_EXTRACT(p.metadata, '$.item_type')) AS item_type,
            JSON_UNQUOTE(JSON_EXTRACT(p.metadata, '$.item_id')) AS item_id
        FROM payments p
        LEFT JOIN courses c ON JSON_UNQUOTE(JSON_EXTRACT(p.metadata, '$.item_type')) = 'course' AND JSON_UNQUOTE(JSON_EXTRACT(p.metadata, '$.item_id')) = c.id
        LEFT JOIN products pr ON JSON_UNQUOTE(JSON_EXTRACT(p.metadata, '$.item_type')) = 'product' AND JSON_UNQUOTE(JSON_EXTRACT(p.metadata, '$.item_id')) = pr.id
        WHERE p.payment_reference = ? LIMIT 1");
        $stmt->execute([$reference]);
        $payment = $stmt->fetch();
    }
}

$completed = $paymentService->isPaymentCompleted($reference);

if ($completed) {
    header('Location: success.php?ref=' . urlencode($reference));
    exit;
}

$statusLabel = 'Awaiting Payment';
$statusColor = '#F59E0B';
if ($payment['status'] === 'failed') {
    $statusLabel = 'Failed';
    $statusColor = 'var(--red)';
} elseif ($payment['status'] === 'expired') {
    $statusLabel = 'Expired';
    $statusColor = 'var(--medium-gray)';
} elseif ($payment['status'] === 'voided') {
    $statusLabel = 'Cancelled';
    $statusColor = 'var(--medium-gray)';
}

$paymentTypeLabel = 'Mobile Money';

require_once 'header.php';
?>
<section class="page-header">
    <div class="container">
        <h1>Processing Payment</h1>
        <p>Please check your phone to complete the payment</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">

                <?php if (!in_array($payment['status'], ['failed', 'expired', 'voided'])): ?>
                <div class="spinner-border payment-spinner" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <?php endif; ?>

                <h4 class="payment-status-text">
                    <?= $payment['status'] === 'pending' ? 'Waiting for Payment Confirmation' : 'Payment ' . ucfirst($payment['status']) ?>
                </h4>

                <div class="card payment-card">
                    <div class="card-body">
                        <h5 class="payment-card-title">Order Details</h5>
                        <div class="payment-row">
                            <span class="payment-label">Item</span>
                            <span class="payment-value"><?= htmlspecialchars($payment['item_title'] ?? 'Unknown') ?></span>
                        </div>
                        <div class="payment-row">
                            <span class="payment-label">Reference</span>
                            <span class="payment-value"><?= htmlspecialchars($payment['payment_reference']) ?></span>
                        </div>
                        <div class="payment-row">
                            <span class="payment-label">Amount</span>
                            <span class="payment-value">TSh <?= number_format((int)$payment['amount']) ?></span>
                        </div>
                        <div class="payment-row">
                            <span class="payment-label">Payment Method</span>
                            <span style="font-weight:600;"><?= $paymentTypeLabel ?></span>
                        </div>
                        <div class="payment-row">
                            <span class="payment-label">Status</span>
                            <span class="badge payment-badge" style="background:<?= $statusColor ?>;">
                                <?= $statusLabel ?>
                            </span>
                        </div>
                    </div>
                </div>

                <?php if ($payment['status'] === 'pending'): ?>
                <p class="payment-help-text">
                    <i class="bi bi-phone me-2"></i>A payment prompt has been sent to your phone.<br>
                    Enter your M-Pesa PIN to confirm the payment.
                </p>

                <div id="payment-error" class="alert alert-danger d-none">
                    <i class="bi bi-exclamation-triangle me-2"></i><span id="error-msg"></span>
                </div>

                <div class="payment-actions">
                    <button id="checkStatusBtn" class="btn btn-red" onclick="checkStatus()">
                        <i class="bi bi-arrow-repeat me-1"></i> I've Confirmed Payment
                    </button>

                    <?php if (isset($_SESSION['admin_logged_in']) || ($app_env ?? 'production') === 'development'): ?>
                    <button class="btn btn-outline-warning" onclick="simulatePayment()">
                        <i class="bi bi-lightning me-1"></i> Test: Simulate Payment
                    </button>
                    <?php endif; ?>

                    <a href="index" class="btn btn-outline-red">
                        <i class="bi bi-house me-1"></i> Cancel
                    </a>
                </div>

                <?php elseif ($payment['status'] === 'failed' || $payment['status'] === 'expired'): ?>
                <p class="payment-help-text">
                    This payment was <?= $payment['status'] ?>. Please try again.
                </p>
                <a href="javascript:history.back()" class="btn btn-red">
                    <i class="bi bi-arrow-left me-1"></i> Try Again
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
var ref = <?= json_encode($reference) ?>;
var checkInterval;

function checkStatus() {
    var btn = document.getElementById('checkStatusBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Checking...';

    fetch('check-payment-status.php?ref=' + encodeURIComponent(ref))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> I\'ve Confirmed Payment';

            if (data.status === 'active') {
                window.location.href = 'success?ref=' + encodeURIComponent(ref);
            } else if (data.status === 'cancelled') {
                window.location.href = 'cancel';
            } else {
                document.getElementById('payment-error').classList.remove('d-none');
                document.getElementById('error-msg').textContent = 'Payment not yet confirmed. Please check your phone and try again.';
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> I\'ve Confirmed Payment';
            document.getElementById('payment-error').classList.remove('d-none');
            document.getElementById('error-msg').textContent = 'Connection error. Please try again.';
        });
}

function simulatePayment() {
    var btn = document.querySelector('.btn-outline-warning');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Simulating...';

    var formData = new FormData();
    formData.append('ref', ref);

    fetch('webhook-test.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.status === 'success') {
                window.location.href = 'success?ref=' + encodeURIComponent(ref);
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-lightning me-1"></i> Test: Simulate Payment';
                document.getElementById('payment-error').classList.remove('d-none');
                document.getElementById('error-msg').textContent = data.message || 'Simulation failed.';
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-lightning me-1"></i> Test: Simulate Payment';
        });
}

checkInterval = setInterval(function() {
    fetch('check-payment-status.php?ref=' + encodeURIComponent(ref))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.status === 'active') {
                clearInterval(checkInterval);
                window.location.href = 'success?ref=' + encodeURIComponent(ref);
            } else if (data.status === 'cancelled') {
                clearInterval(checkInterval);
                window.location.href = 'cancel';
            }
        })
        .catch(function() {});
}, 5000);
</script>

<?php require_once 'footer.php'; ?>
