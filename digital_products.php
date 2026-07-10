<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/lib/Product.php';
require_once __DIR__ . '/lib/Settings.php';

$hero_bg = Settings::get('hero_bg_digital_products', '');

$productModel = new Product();
$products = $productModel->getVisible();

$page_title = 'Digital Products | Code & Templates Tanzania';
$page_desc = 'Browse and download digital products including source code, website templates, and design resources from Mtaita Tech.';
$page_keywords = 'digital products, downloads, Mtaita Tech';
require_once 'header.php';
?>

<section class="page-header<?= $hero_bg ? ' page-header-with-bg' : '' ?>"<?php if ($hero_bg): ?> style="background-image:url('/<?= htmlspecialchars(webp_url($hero_bg)) ?>')"<?php endif; ?>>
    <div class="container">
        <h1>Digital Products</h1>
        <p>Source code, video tutorials, and project files</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row g-4">
            <?php if (empty($products)): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-box" style="font-size:4rem;color:#CBD5E1;"></i>
                    <h3 class="mt-4 text-muted">No Products Yet</h3>
                    <p class="text-muted">Check back soon for digital products.</p>
                </div>
            <?php else: ?>
                <?php foreach ($products as $prod): ?>
                <div class="col-md-4">
                    <div class="floating-card h-100">
                        <?php if ($prod['thumbnail']): ?>
                            <img src="<?= htmlspecialchars(webp_url($prod['thumbnail'])) ?>" alt="" style="width:100%;height:200px;object-fit:cover;border-radius:12px 12px 0 0;">
                        <?php else: ?>
                            <div style="height:200px;background:var(--light-gray);border-radius:12px 12px 0 0;display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-box" style="font-size:4rem;color:#CBD5E1;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="p-3">
                            <span class="badge mb-2" style="background:<?= $prod['is_paid'] ? 'var(--red)' : '#10B981' ?>;">
                                <?= $prod['is_paid'] ? 'Premium' : 'Free' ?>
                            </span>
                            <h5 style="color:var(--deep-blue);font-weight:700;"><?= htmlspecialchars($prod['title']) ?></h5>
                            <p class="text-muted small mb-2"><?= htmlspecialchars(substr($prod['description'] ?? '', 0, 120)) ?><?= strlen($prod['description'] ?? '') > 120 ? '...' : '' ?></p>
                            <p class="small text-muted">
                                <i class="bi bi-tag me-1"></i><?= $productModel->getTypeLabel($prod['type']) ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="fw-bold" style="color:var(--deep-blue);">
                                    <?= $prod['is_paid'] ? 'TSh ' . number_format($prod['price']) : 'Free' ?>
                                </span>
                                <a href="product-detail?id=<?= $prod['id'] ?>" class="btn btn-sm btn-red">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
