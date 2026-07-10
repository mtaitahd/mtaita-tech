<?php
$page_title = 'Page Hero Images';
$active_page = 'page-heroes';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../lib/Settings.php';

$pages = [
    'services'      => ['label' => 'Services',      'key' => 'hero_bg_services'],
    'about'         => ['label' => 'About Us',       'key' => 'hero_bg_about'],
    'portfolio'     => ['label' => 'Portfolio',      'key' => 'hero_bg_portfolio'],
    'courses'       => ['label' => 'Courses',        'key' => 'hero_bg_courses'],
    'digital_products' => ['label' => 'Digital Products', 'key' => 'hero_bg_digital_products'],
    'blog'          => ['label' => 'Blog',           'key' => 'hero_bg_blog'],
    'contact'       => ['label' => 'Contact Us',     'key' => 'hero_bg_contact'],
    'web_development' => ['label' => 'Web Development', 'key' => 'hero_bg_web_development'],
    'graphic_design'  => ['label' => 'Graphic Design',  'key' => 'hero_bg_graphic_design'],
    'mobile_apps'     => ['label' => 'Mobile Apps',     'key' => 'hero_bg_mobile_apps'],
    'seo_digital_marketing' => ['label' => 'SEO & Digital Marketing', 'key' => 'hero_bg_seo_digital_marketing'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_hero'])) {
    $target = $_POST['page_key'] ?? '';
    if (!isset($pages[$target])) {
        $error_msg = 'Invalid page.';
    } elseif (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $file = $_FILES['hero_image'];

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        } else {
            $mime = $file['type'];
        }

        if (!in_array($mime, $allowed)) {
            $error_msg = 'Invalid file type. Only PNG, JPG, and WEBP are allowed.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $error_msg = 'File is too large. Maximum size is 5MB.';
        } else {
            $upload_dir = __DIR__ . '/../assets/img/uploads/page-heroes/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('hero_' . $target . '_', true) . '.' . $ext;
            $dest = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $old = Settings::get($pages[$target]['key'], '');
                if ($old) {
                    $old_path = __DIR__ . '/../' . $old;
                    if (file_exists($old_path) && is_file($old_path)) @unlink($old_path);
                }
                $db_path = 'assets/img/uploads/page-heroes/' . $filename;
                Settings::set($pages[$target]['key'], $db_path);
                $success_msg = $pages[$target]['label'] . ' hero image updated successfully.';
            } else {
                $error_msg = 'Failed to upload file. Check directory permissions.';
            }
        }
    } else {
        $error_msg = 'Please select an image file.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_hero'])) {
    $target = $_POST['page_key'] ?? '';
    if (isset($pages[$target])) {
        $old = Settings::get($pages[$target]['key'], '');
        if ($old) {
            $old_path = __DIR__ . '/../' . $old;
            if (file_exists($old_path) && is_file($old_path)) @unlink($old_path);
        }
        Settings::set($pages[$target]['key'], '');
        $success_msg = $pages[$target]['label'] . ' hero image removed.';
    }
}

foreach ($pages as &$p) {
    $p['image'] = Settings::get($p['key'], '');
}
unset($p);

require_once 'admin_header.php';
?>

<div class="page-header">
    <span class="text-white-50">Manage hero background images for site pages</span>
</div>

<?php if ($success_msg): ?>
<div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div>
<?php endif; ?>
<?php if ($error_msg): ?>
<div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div>
<?php endif; ?>

<div class="row g-4">
    <?php foreach ($pages as $key => $p): ?>
    <div class="col-lg-4 col-md-6">
        <div class="admin-card h-100">
            <div class="card-body">
                <h6 class="text-cyan mb-3"><i class="bi bi-image me-1"></i> <?= htmlspecialchars($p['label']) ?></h6>
                <?php if ($p['image']): ?>
                    <div class="position-relative mb-3">
                        <img src="../<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['label']) ?> Hero" style="width:100%;height:140px;object-fit:cover;border-radius:8px;">
                        <form method="POST" class="position-absolute top-0 end-0 m-1" onsubmit="return confirm('Remove this hero image?');">
                            <input type="hidden" name="remove_hero" value="1">
                            <input type="hidden" name="page_key" value="<?= $key ?>">
                            <button type="submit" class="btn btn-sm btn-danger" title="Remove"><i class="bi bi-x-lg"></i></button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted mb-3" style="height:140px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.05);border-radius:8px;border:2px dashed rgba(255,255,255,0.15);">
                        <div><i class="bi bi-image" style="font-size:2rem;"></i><br><small>No image</small></div>
                    </div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="upload_hero" value="1">
                    <input type="hidden" name="page_key" value="<?= $key ?>">
                    <div class="input-group input-group-sm">
                        <input type="file" name="hero_image" class="form-control" accept="image/png,image/jpeg,image/webp" required>
                        <button type="submit" class="btn btn-cyan"><i class="bi bi-upload"></i></button>
                    </div>
                    <small class="text-muted">PNG, JPG, WEBP. Max 5MB. Recommended: 1920x400</small>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php require_once 'admin_footer.php'; ?>
