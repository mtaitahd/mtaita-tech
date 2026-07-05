<?php
$page_title = 'Dashboard';
$active_page = 'dashboard';
require_once 'admin_header.php';

$total_projects = $pdo->query("SELECT COUNT(*) FROM portfolio")->fetchColumn();
$total_posters = $pdo->query("SELECT COUNT(*) FROM posters")->fetchColumn();
$active_posters = $pdo->query("SELECT COUNT(*) FROM posters WHERE is_active = 1")->fetchColumn();
$total_thumbnails = $pdo->query("SELECT COUNT(*) FROM thumbnails")->fetchColumn();
$total_announcements = $pdo->query("SELECT COUNT(*) FROM announcements")->fetchColumn();
$active_announcements = $pdo->query("SELECT COUNT(*) FROM announcements WHERE is_active = 1")->fetchColumn();
$unread_messages = $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();
$total_messages = $pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();

$total_enrollments = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
$active_enrollments = $pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'active'")->fetchColumn();
$pending_enrollments = $pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'pending'")->fetchColumn();

?>
<div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-6 g-3 mb-4">
    <div class="col">
        <a href="projects" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-folder"></i></div>
                <div class="stat-info">
                    <h3><?= $total_projects ?></h3>
                    <p>Total Projects</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="posters" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-images"></i></div>
                <div class="stat-info">
                    <h3><?= $active_posters ?><small class="text-muted fs-6">/<?= $total_posters ?></small></h3>
                    <p>Posters (Active/Total)</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="thumbnails" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-film"></i></div>
                <div class="stat-info">
                    <h3><?= $total_thumbnails ?></h3>
                    <p>Total Thumbnails</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="announcements" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-megaphone"></i></div>
                <div class="stat-info">
                    <h3><?= $active_announcements ?><small class="text-muted fs-6">/<?= $total_announcements ?></small></h3>
                    <p>Announcements (Active/Total)</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="news" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-newspaper"></i></div>
                <div class="stat-info">
                    <h3><?= $pdo->query("SELECT COUNT(*) FROM news WHERE is_published = 1")->fetchColumn() ?><small class="text-muted fs-6">/<?= $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn() ?></small></h3>
                    <p>News (Published/Total)</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="partners" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-handshake"></i></div>
                <div class="stat-info">
                    <h3><?= $pdo->query("SELECT COUNT(*) FROM partners")->fetchColumn() ?></h3>
                    <p>Partners</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="testimonials" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-star"></i></div>
                <div class="stat-info">
                    <h3><?= $pdo->query("SELECT COUNT(*) FROM testimonials")->fetchColumn() ?></h3>
                    <p>Testimonials</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="solutions" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-cubes"></i></div>
                <div class="stat-info">
                    <h3><?= $pdo->query("SELECT COUNT(*) FROM solutions")->fetchColumn() ?></h3>
                    <p>Solutions</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="contacts" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-envelope"></i></div>
                <div class="stat-info">
                    <h3><?= $unread_messages ?><small class="text-muted fs-6">/<?= $total_messages ?></small></h3>
                    <p>Messages (Unread/Total)</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="manage-enrollments" class="text-decoration-none">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <div class="stat-info">
                    <h3><?= $active_enrollments ?><small class="text-muted fs-6">/<?= $total_enrollments ?></small></h3>
                    <p>Enrollments (Active/Total)</p>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="admin-card">
            <h5><i class="bi bi-lightning-charge text-cyan me-2"></i>Quick Actions</h5>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <a href="projects" class="btn btn-cyan btn-sm"><i class="bi bi-plus-lg"></i> Add Project</a>
                <a href="posters" class="btn btn-outline-cyan btn-sm"><i class="bi bi-plus-lg"></i> Add Poster</a>
                <a href="thumbnails" class="btn btn-outline-cyan btn-sm"><i class="bi bi-plus-lg"></i> Add Thumbnail</a>
                <a href="announcements" class="btn btn-outline-cyan btn-sm"><i class="bi bi-plus-lg"></i> Add Announcement</a>
                <a href="news" class="btn btn-outline-cyan btn-sm"><i class="bi bi-plus-lg"></i> Add News</a>
                <a href="partners" class="btn btn-outline-cyan btn-sm"><i class="bi bi-plus-lg"></i> Add Partner</a>
                <a href="testimonials" class="btn btn-outline-cyan btn-sm"><i class="bi bi-plus-lg"></i> Add Testimonial</a>
                <a href="solutions" class="btn btn-outline-cyan btn-sm"><i class="bi bi-plus-lg"></i> Add Solution</a>
                <a href="contacts" class="btn btn-outline-cyan btn-sm"><i class="bi bi-envelope"></i> View Messages</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>
