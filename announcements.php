<?php
$page_title = 'Announcements | Mtaita Tech News & Updates Tanzania';
$page_desc = 'Stay updated with the latest announcements, news, and updates from Mtaita Tech — your trusted software development company in Kilimanjaro.';
$page_keywords = 'Mtaita Tech announcements, company news Tanzania, software company updates Kilimanjaro';
$page = 'announcements';
require_once 'header.php';
$all = getActiveAnnouncements($pdo);
?>
<section class="page-header">
    <div class="container">
        <h1><i class="bi bi-megaphone me-2"></i>Announcements</h1>
        <p>Latest news and updates from the Mtaita Tech team</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if (empty($all)): ?>
                    <div class="text-center py-5" style="background:#F8FAFC;border:1px solid var(--border-light);">
                        <i class="bi bi-megaphone" style="font-size:2.5rem;color:#CBD5E1;"></i>
                        <p class="text-muted mt-3 mb-0">No announcements at this time. Check back later!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($all as $a): ?>
                        <div class="announcement-page-item">
                            <div class="announcement-page-icon"><i class="bi bi-megaphone"></i></div>
                            <div class="announcement-page-body">
                                <p style="color:<?= htmlspecialchars($a['text_color'] ?? '#0F172A') ?>;"><?= htmlspecialchars($a['announcement_text']) ?></p>
                                <small class="text-muted"><?= date('F d, Y', strtotime($a['updated_at'] ?? $a['created_at'] ?? 'now')) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="text-center mt-4">
                    <a href="/" class="btn btn-red"><i class="bi bi-house-door me-2"></i>Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once 'footer.php'; ?>
