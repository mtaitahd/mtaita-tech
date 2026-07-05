<?php
$page_title = 'Search Results';
$active_page = '';
$q = trim($_GET['q'] ?? '');
require_once 'admin_header.php';

$results = [];

if ($q !== '') {
    $like = '%' . $q . '%';

    // Projects
    $stmt = $pdo->prepare("SELECT id, project_title, category, completion_year FROM portfolio WHERE project_title LIKE ? OR project_desc LIKE ? OR category LIKE ? LIMIT 20");
    $stmt->execute([$like, $like, $like]);
    if ($stmt->rowCount()) $results['Projects'] = $stmt->fetchAll();

    // Posters
    $stmt = $pdo->prepare("SELECT id, poster_title FROM posters WHERE poster_title LIKE ? LIMIT 20");
    $stmt->execute([$like]);
    if ($stmt->rowCount()) $results['Posters'] = $stmt->fetchAll();

    // Thumbnails
    $stmt = $pdo->prepare("SELECT id, title FROM thumbnails WHERE title LIKE ? LIMIT 20");
    $stmt->execute([$like]);
    if ($stmt->rowCount()) $results['Thumbnails'] = $stmt->fetchAll();

    // Announcements
    $stmt = $pdo->prepare("SELECT id, announcement_text FROM announcements WHERE announcement_text LIKE ? LIMIT 20");
    $stmt->execute([$like]);
    if ($stmt->rowCount()) $results['Announcements'] = $stmt->fetchAll();

    // News
    $stmt = $pdo->prepare("SELECT id, title FROM news WHERE title LIKE ? OR content LIKE ? LIMIT 20");
    $stmt->execute([$like, $like]);
    if ($stmt->rowCount()) $results['News'] = $stmt->fetchAll();

    // Partners
    $stmt = $pdo->prepare("SELECT id, name FROM partners WHERE name LIKE ? LIMIT 20");
    $stmt->execute([$like]);
    if ($stmt->rowCount()) $results['Partners'] = $stmt->fetchAll();

    // Testimonials
    $stmt = $pdo->prepare("SELECT id, name, company FROM testimonials WHERE name LIKE ? OR content LIKE ? OR company LIKE ? LIMIT 20");
    $stmt->execute([$like, $like, $like]);
    if ($stmt->rowCount()) $results['Testimonials'] = $stmt->fetchAll();

    // Solutions
    $stmt = $pdo->prepare("SELECT id, title FROM solutions WHERE title LIKE ? OR description LIKE ? LIMIT 20");
    $stmt->execute([$like, $like]);
    if ($stmt->rowCount()) $results['Solutions'] = $stmt->fetchAll();

    // Contacts
    $stmt = $pdo->prepare("SELECT id, name, email, message FROM contacts WHERE name LIKE ? OR email LIKE ? OR message LIKE ? LIMIT 20");
    $stmt->execute([$like, $like, $like]);
    if ($stmt->rowCount()) $results['Messages'] = $stmt->fetchAll();

    // Products
    $stmt = $pdo->prepare("SELECT id, title FROM products WHERE title LIKE ? OR description LIKE ? LIMIT 20");
    $stmt->execute([$like, $like]);
    if ($stmt->rowCount()) $results['Products'] = $stmt->fetchAll();

    // Courses
    $stmt = $pdo->prepare("SELECT id, title FROM courses WHERE title LIKE ? OR description LIKE ? LIMIT 20");
    $stmt->execute([$like, $like]);
    if ($stmt->rowCount()) $results['Courses'] = $stmt->fetchAll();

    // Public Users
    $stmt = $pdo->prepare("SELECT id, name, email FROM public_users WHERE name LIKE ? OR email LIKE ? LIMIT 20");
    $stmt->execute([$like, $like]);
    if ($stmt->rowCount()) $results['Users'] = $stmt->fetchAll();

    // Services
    $stmt = $pdo->prepare("SELECT id, title FROM services WHERE title LIKE ? OR description LIKE ? LIMIT 20");
    $stmt->execute([$like, $like]);
    if ($stmt->rowCount()) $results['Services'] = $stmt->fetchAll();

    // Team
    $stmt = $pdo->prepare("SELECT id, name, role FROM team WHERE name LIKE ? OR role LIKE ? LIMIT 20");
    $stmt->execute([$like, $like]);
    if ($stmt->rowCount()) $results['Team'] = $stmt->fetchAll();
}
?>

<?php if ($q !== ''): ?>
    <div class="mb-3">
        <h5>Search results for "<strong class="text-cyan"><?= htmlspecialchars($q) ?></strong>"</h5>
        <span class="text-muted small"><?= array_sum(array_map('count', $results)) ?> result(s) found</span>
    </div>

    <?php if (empty($results)): ?>
        <div class="admin-card text-center py-5">
            <i class="bi bi-search" style="font-size:3rem;color:var(--text-muted);"></i>
            <p class="text-muted mt-3 mb-0">No results found for "<?= htmlspecialchars($q) ?>".</p>
        </div>
    <?php else: ?>
        <?php $sectionLinks = [
            'Projects' => 'projects',
            'Posters' => 'posters',
            'Thumbnails' => 'thumbnails',
            'Announcements' => 'announcements',
            'News' => 'news',
            'Partners' => 'partners',
            'Testimonials' => 'testimonials',
            'Solutions' => 'solutions',
            'Messages' => 'contacts',
            'Products' => 'products',
            'Courses' => 'manage-courses',
            'Users' => '',
            'Services' => 'manage-services',
            'Team' => '',
        ]; ?>
        <?php foreach ($results as $section => $items): ?>
            <div class="admin-card mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="mb-0"><i class="bi bi-folder2-open text-cyan me-2"></i><?= $section ?> (<?= count($items) ?>)</h6>
                    <?php if (!empty($sectionLinks[$section])): ?>
                        <a href="<?= $sectionLinks[$section] ?>" class="btn btn-sm btn-outline-cyan">View All</a>
                    <?php endif; ?>
                </div>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0 small">
                        <tbody>
                            <?php foreach ($items as $row): ?>
                            <tr>
                                <td>
                                    <?php if ($section === 'Projects'): ?>
                                        <a href="edit_project?id=<?= $row['id'] ?>" class="text-cyan text-decoration-none"><?= htmlspecialchars($row['project_title'] ?? '') ?></a>
                                        <span class="text-muted ms-2"><?= htmlspecialchars($row['category'] ?? '') ?> — <?= htmlspecialchars($row['completion_year'] ?? '') ?></span>
                                    <?php elseif ($section === 'Posters'): ?>
                                        <a href="posters" class="text-cyan text-decoration-none"><?= htmlspecialchars($row['poster_title'] ?? '') ?></a>
                                    <?php elseif ($section === 'Thumbnails'): ?>
                                        <a href="thumbnails" class="text-cyan text-decoration-none"><?= htmlspecialchars($row['title'] ?? '') ?></a>
                                    <?php elseif ($section === 'Announcements'): ?>
                                        <a href="announcements" class="text-cyan text-decoration-none"><?= htmlspecialchars(mb_strimwidth($row['announcement_text'] ?? '', 0, 80, '...')) ?></a>
                                    <?php elseif ($section === 'News'): ?>
                                        <a href="news" class="text-cyan text-decoration-none"><?= htmlspecialchars($row['title'] ?? '') ?></a>
                                    <?php elseif ($section === 'Partners'): ?>
                                        <a href="partners" class="text-cyan text-decoration-none"><?= htmlspecialchars($row['name'] ?? '') ?></a>
                                    <?php elseif ($section === 'Testimonials'): ?>
                                        <a href="testimonials" class="text-cyan text-decoration-none"><?= htmlspecialchars($row['name'] ?? '') ?></a>
                                        <span class="text-muted ms-2"><?= htmlspecialchars($row['company'] ?? '') ?></span>
                                    <?php elseif ($section === 'Solutions'): ?>
                                        <a href="solutions" class="text-cyan text-decoration-none"><?= htmlspecialchars($row['title'] ?? '') ?></a>
                                    <?php elseif ($section === 'Messages'): ?>
                                        <a href="contacts" class="text-cyan text-decoration-none"><?= htmlspecialchars($row['name'] ?? '') ?></a>
                                        <span class="text-muted ms-2"><?= htmlspecialchars($row['email'] ?? '') ?> — <?= htmlspecialchars(mb_strimwidth($row['message'] ?? '', 0, 60, '...')) ?></span>
                                    <?php elseif ($section === 'Products'): ?>
                                        <a href="products" class="text-cyan text-decoration-none"><?= htmlspecialchars($row['title'] ?? '') ?></a>
                                    <?php elseif ($section === 'Courses'): ?>
                                        <a href="manage-courses" class="text-cyan text-decoration-none"><?= htmlspecialchars($row['title'] ?? '') ?></a>
                                    <?php elseif ($section === 'Users'): ?>
                                        <span><?= htmlspecialchars($row['name'] ?? '') ?></span>
                                        <span class="text-muted ms-2"><?= htmlspecialchars($row['email'] ?? '') ?></span>
                                    <?php elseif ($section === 'Services'): ?>
                                        <a href="manage-services" class="text-cyan text-decoration-none"><?= htmlspecialchars($row['title'] ?? '') ?></a>
                                    <?php elseif ($section === 'Team'): ?>
                                        <span><?= htmlspecialchars($row['name'] ?? '') ?></span>
                                        <span class="text-muted ms-2"><?= htmlspecialchars($row['role'] ?? '') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php else: ?>
    <div class="admin-card text-center py-5">
        <i class="bi bi-search" style="font-size:3rem;color:var(--text-muted);"></i>
        <h5 class="text-muted mt-3 mb-1">Search Admin Panel</h5>
        <p class="text-muted small">Type a keyword above to search projects, messages, news, partners, and more.</p>
    </div>
<?php endif; ?>

<?php require_once 'admin_footer.php'; ?>
