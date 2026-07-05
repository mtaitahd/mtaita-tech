<?php
$page_title = 'Payment Cancelled — Mtaita Tech';
$page_desc = 'Your payment was cancelled. Try again or contact support for help.';
require_once __DIR__ . '/auth_helper.php';
require_once 'header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Payment Cancelled</h1>
        <p>Your transaction was not completed</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">

                <div class="payment-icon-wrap payment-icon-error">
                    <i class="bi bi-x-lg"></i>
                </div>

                <h3 class="payment-status-text">Payment Cancelled</h3>
                <p class="payment-help-text">
                    Your payment was not processed. No charges have been made.
                </p>

                <div class="payment-actions">
                    <a href="javascript:history.back()" class="btn btn-red">
                        <i class="bi bi-arrow-left me-1"></i> Try Again
                    </a>
                    <a href="courses" class="btn btn-outline-red">
                        <i class="bi bi-book me-1"></i> Browse Courses
                    </a>
                </div>

                <p class="payment-help-text">
                    If you experienced any issues, please <a href="contact" class="fw-semibold" style="color:var(--red);">contact us</a>.
                </p>

            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
