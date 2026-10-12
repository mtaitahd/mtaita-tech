<?php
$page_title = 'Manage Services';
$active_page = 'services';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../auth_helper.php';
require_once __DIR__ . '/../lib/Service.php';
require_once __DIR__ . '/../lib/Uploader.php';

$serviceObj = new Service();

$UPLOAD_DIR = 'assets/img/uploads/services';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Your session expired. Please try again.';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? 'save';

        // ---- Delete --------------------------------------------------
        if ($action === 'delete') {
            if ($id > 0) {
                try {
                    $serviceObj->delete($id);
                    $success_msg = 'Service deleted.';
                } catch (Exception $e) {
                    error_log('service delete: ' . $e->getMessage());
                    $error_msg = 'Database error while deleting.';
                }
            }
        } else {
            // ---- Create / update -------------------------------------
            $existing = $id > 0 ? $serviceObj->getById($id) : null;

            $title = trim($_POST['title'] ?? '');
            $slugInput = trim($_POST['slug'] ?? '');
            $slug = $slugInput !== '' ? Service::slugifyTitle($slugInput) : Service::slugifyTitle($title);
            if ($slug !== '') {
                $slug = $serviceObj->uniqueSlug($slug, $id);
            }
            if ($slug === '') $slug = null;

            $data = [
                'slug' => $slug,
                'title' => $title,
                'card_title' => trim($_POST['card_title'] ?? '') ?: null,
                'description' => trim($_POST['description'] ?? ''),
                'short_description' => trim($_POST['short_description'] ?? '') ?: null,
                'full_description' => trim($_POST['full_description'] ?? '') ?: null,
                'image_alt' => trim($_POST['image_alt'] ?? '') ?: null,
                'hero_subtitle' => trim($_POST['hero_subtitle'] ?? '') ?: null,
                'seo_title' => trim($_POST['seo_title'] ?? '') ?: null,
                'seo_description' => trim($_POST['seo_description'] ?? '') ?: null,
                'seo_keywords' => trim($_POST['seo_keywords'] ?? '') ?: null,
                'service_category' => trim($_POST['service_category'] ?? '') ?: null,
                'card_link' => trim($_POST['card_link'] ?? '') ?: null,
                'icon' => trim($_POST['icon'] ?? '') ?: null,
                'list_items' => trim($_POST['list_items'] ?? '') ?: null,
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'show_homepage' => isset($_POST['show_homepage']) ? 1 : 0,
                'show_listing' => isset($_POST['show_listing']) ? 1 : 0,
            ];

            $oldImage = $existing['image'] ?? null;
            $data['image'] = $oldImage;

            // Remove image checkbox
            if (!empty($_POST['remove_image']) && $id > 0) {
                Uploader::delete($existing['image'] ?? null);
                $data['image'] = null;
                $existing['image'] = null;
            }

            // Upload card image
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $up = Uploader::handle($_FILES['image'], $UPLOAD_DIR, $existing['image'] ?? null, 'service');
                if (!$up['ok']) {
                    $error_msg = $up['error'];
                } elseif ($up['path'] !== null) {
                    $data['image'] = $up['path'];
                }
            }

            // Upload hero image
            $existingHero = $existing['hero_image'] ?? null;
            if (!empty($_POST['remove_hero']) && $id > 0) {
                Uploader::delete($existingHero);
                $existingHero = null;
            }
            $data['hero_image'] = $existingHero;
            if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
                $hu = Uploader::handle($_FILES['hero_image'], $UPLOAD_DIR, $existingHero, 'hero');
                if (!$hu['ok']) {
                    $error_msg = $error_msg ?: $hu['error'];
                } elseif ($hu['path'] !== null) {
                    $data['hero_image'] = $hu['path'];
                }
            }

            if (!$error_msg) {
                if ($title === '') {
                    $error_msg = 'Title is required.';
                } else {
                    try {
                        if ($id > 0) {
                            $serviceObj->update($id, $data);
                            $success_msg = 'Service updated.';
                        } else {
                            $newId = $serviceObj->create($data);
                            $success_msg = 'Service added! You can now build its page content.';
                            $id = (int)$newId;
                        }
                    } catch (Exception $e) {
                        error_log('save_service error: ' . $e->getMessage());
                        $error_msg = 'Database error. Please check your details and try again.';
                    }
                }
            }
        }
    }
}

// GET toggles (kept lightweight; state changes only, no destructive action)
if (!empty($_GET['toggle'])) {
    try { $serviceObj->toggleActive((int)$_GET['toggle']); } catch (Exception $e) {}
    header('Location: manage-services?msg=' . urlencode('Service status updated.'));
    exit;
}
if (!empty($_GET['toggle_home'])) {
    try { $serviceObj->toggleHomepage((int)$_GET['toggle_home']); } catch (Exception $e) {}
    header('Location: manage-services?msg=' . urlencode('Homepage visibility updated.'));
    exit;
}
if (!empty($_GET['toggle_list'])) {
    try { $serviceObj->toggleListing((int)$_GET['toggle_list']); } catch (Exception $e) {}
    header('Location: manage-services?msg=' . urlencode('Listing visibility updated.'));
    exit;
}
if (!empty($_GET['msg'])) {
    $success_msg = $_GET['msg'];
}

$services = $serviceObj->getAll();
$csrf = generateCsrfToken();
require_once 'admin_header.php';
?>
<div class="page-header d-flex flex-wrap gap-2">
    <button class="btn btn-cyan" data-bs-toggle="modal" data-bs-target="#serviceModal" id="btnAddService"><i class="bi bi-plus-lg"></i> Add Service</button>
    <a href="solutions" class="btn btn-outline-secondary"><i class="bi bi-grid"></i> Solutions</a>
</div>

<?php if ($success_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
<?php if ($error_msg): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr><th>Image</th><th>Order</th><th>Title</th><th>Slug</th><th>Home</th><th>Listing</th><th>Active</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($services)): ?>
                    <tr><td colspan="8" class="text-muted text-center">No services yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($services as $s): ?>
                    <tr>
                        <td>
                            <?php if (!empty($s['image'])): ?>
                            <img src="../<?= htmlspecialchars(ltrim($s['image'], '/')) ?>" alt="" width="56" height="40" style="object-fit:cover;border-radius:4px;">
                            <?php else: ?><span class="text-muted">&mdash;</span><?php endif; ?>
                        </td>
                        <td><?= (int)$s['sort_order'] ?></td>
                        <td>
                            <div><?= htmlspecialchars($s['title']) ?></div>
                            <?php if (!empty($s['card_title']) && $s['card_title'] !== $s['title']): ?>
                            <small class="text-muted">Card: <?= htmlspecialchars($s['card_title']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><code class="text-cyan"><?= htmlspecialchars($s['slug'] ?? '') ?></code></td>
                        <td><a href="?toggle_home=<?= (int)$s['id'] ?>" title="Toggle homepage"><?= !empty($s['show_homepage']) ? '<i class="bi bi-check-circle-fill text-cyan"></i>' : '<i class="bi bi-circle text-muted"></i>' ?></a></td>
                        <td><a href="?toggle_list=<?= (int)$s['id'] ?>" title="Toggle listing"><?= !empty($s['show_listing']) ? '<i class="bi bi-check-circle-fill text-cyan"></i>' : '<i class="bi bi-circle text-muted"></i>' ?></a></td>
                        <td><a href="?toggle=<?= (int)$s['id'] ?>" title="Toggle active"><?= $s['is_active'] ? '<span class="text-cyan">Active</span>' : '<span class="text-muted">Inactive</span>' ?></a></td>
                        <td class="text-nowrap">
                            <button class="btn btn-sm btn-outline-cyan me-1 btn-edit-service" type="button"
                                data-service="<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>" title="Edit"><i class="bi bi-pencil"></i></button>
                            <a href="manage-service-page?id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-warning me-1" title="Manage page content"><i class="bi bi-layout-text-window-reverse"></i></a>
                            <?php if (!empty($s['slug'])): ?>
                            <a href="../<?= htmlspecialchars($s['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary me-1" title="View page"><i class="bi bi-box-arrow-up-right"></i></a>
                            <?php endif; ?>
                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this service and all its page content?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" id="svc-id" value="0">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil text-cyan me-2"></i><span id="svc-modal-title">Add Service</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="svc-title" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="svc-sort" class="form-control" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Slug (URL)</label>
                            <input type="text" name="slug" id="svc-slug" class="form-control" placeholder="web-development">
                            <small class="text-muted">Leave blank to generate from the title.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Card Title (homepage)</label>
                            <input type="text" name="card_title" id="svc-card-title" class="form-control" placeholder="Optional — defaults to title">
                        </div>

                        <div class="col-12"><hr class="border-secondary my-1"></div>
                        <div class="col-12">
                            <label class="form-label">Card / Listing Image</label>
                            <div class="mb-2"><img id="svc-image-preview" src="" alt="" height="64" style="border-radius:4px;object-fit:cover;display:none;"></div>
                            <input type="file" name="image" id="svc-image" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif">
                            <small class="text-muted">PNG, JPG, WEBP, GIF. Max 5MB. Recommended square.</small>
                            <div class="form-check mt-1">
                                <input type="checkbox" name="remove_image" id="svc-remove-image" class="form-check-input" value="1">
                                <label class="form-check-label" for="svc-remove-image">Remove current image</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Image Alt Text</label>
                            <input type="text" name="image_alt" id="svc-image-alt" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Card / Short Description</label>
                            <textarea name="description" id="svc-desc" rows="3" class="form-control" required></textarea>
                            <small class="text-muted">Shown on the homepage card.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Short Description (services listing)</label>
                            <textarea name="short_description" id="svc-short-desc" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Full Description (optional, HTML-safe text)</label>
                            <textarea name="full_description" id="svc-full-desc" rows="3" class="form-control"></textarea>
                        </div>

                        <div class="col-12"><hr class="border-secondary my-1"></div>
                        <div class="col-md-6">
                            <label class="form-label">Hero Image</label>
                            <input type="file" name="hero_image" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif">
                            <small class="text-muted">Page header background. Falls back to global hero.</small>
                            <div class="form-check mt-1">
                                <input type="checkbox" name="remove_hero" class="form-check-input" value="1">
                                <label class="form-check-label">Remove hero image</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hero Subtitle</label>
                            <input type="text" name="hero_subtitle" id="svc-hero-subtitle" class="form-control">
                        </div>

                        <div class="col-12"><hr class="border-secondary my-1"></div>
                        <div class="col-md-6">
                            <label class="form-label">Portfolio Category (projects carousel)</label>
                            <input type="text" name="service_category" id="svc-category" class="form-control" placeholder="Web Development">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Card Link</label>
                            <input type="text" name="card_link" id="svc-card-link" class="form-control" placeholder="/web-development">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Icon (Font Awesome class)</label>
                            <input type="text" name="icon" id="svc-icon" class="form-control" placeholder="fa-solid fa-code">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Legacy Feature List (one per line)</label>
                            <textarea name="list_items" id="svc-items" rows="2" class="form-control"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">SEO Title</label>
                            <input type="text" name="seo_title" id="svc-seo-title" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SEO Keywords</label>
                            <input type="text" name="seo_keywords" id="svc-seo-keywords" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">SEO Description</label>
                            <textarea name="seo_description" id="svc-seo-desc" rows="2" class="form-control"></textarea>
                        </div>

                        <div class="col-md-4 d-flex align-items-end pb-2">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" id="svc-active" class="form-check-input" checked>
                                <label class="form-check-label" for="svc-active">Active</label>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end pb-2">
                            <div class="form-check">
                                <input type="checkbox" name="show_homepage" id="svc-home" class="form-check-input">
                                <label class="form-check-label" for="svc-home">Show on homepage</label>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end pb-2">
                            <div class="form-check">
                                <input type="checkbox" name="show_listing" id="svc-listing" class="form-check-input" checked>
                                <label class="form-check-label" for="svc-listing">Show in listing</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" id="svc-page-link" class="btn btn-outline-cyan me-auto" style="display:none;"><i class="bi bi-layout-text-window-reverse"></i> Manage Page Content</a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-cyan"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('serviceModal');
    function resetForm() {
        document.getElementById('svc-id').value = '0';
        ['svc-title','svc-sort','svc-slug','svc-card-title','svc-image-alt','svc-desc','svc-short-desc','svc-full-desc','svc-hero-subtitle','svc-category','svc-card-link','svc-icon','svc-items','svc-seo-title','svc-seo-keywords','svc-seo-desc'].forEach(function (id) {
            var el = document.getElementById(id); if (el) el.value = '';
        });
        document.getElementById('svc-sort').value = '0';
        document.getElementById('svc-active').checked = true;
        document.getElementById('svc-home').checked = false;
        document.getElementById('svc-listing').checked = true;
        var p = document.getElementById('svc-image-preview'); p.style.display = 'none'; p.src = '';
        var link = document.getElementById('svc-page-link');
        link.style.display = 'none';
        document.getElementById('svc-modal-title').textContent = 'Add Service';
    }

    document.getElementById('btnAddService').addEventListener('click', resetForm);

    document.querySelectorAll('.btn-edit-service').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var s = JSON.parse(btn.dataset.service);
            document.getElementById('svc-id').value = s.id;
            document.getElementById('svc-title').value = s.title || '';
            document.getElementById('svc-sort').value = s.sort_order || '0';
            document.getElementById('svc-slug').value = s.slug || '';
            document.getElementById('svc-card-title').value = s.card_title || '';
            document.getElementById('svc-image-alt').value = s.image_alt || '';
            document.getElementById('svc-desc').value = s.description || '';
            document.getElementById('svc-short-desc').value = s.short_description || '';
            document.getElementById('svc-full-desc').value = s.full_description || '';
            document.getElementById('svc-hero-subtitle').value = s.hero_subtitle || '';
            document.getElementById('svc-category').value = s.service_category || '';
            document.getElementById('svc-card-link').value = s.card_link || '';
            document.getElementById('svc-icon').value = s.icon || '';
            document.getElementById('svc-items').value = s.list_items || '';
            document.getElementById('svc-seo-title').value = s.seo_title || '';
            document.getElementById('svc-seo-keywords').value = s.seo_keywords || '';
            document.getElementById('svc-seo-desc').value = s.seo_description || '';
            document.getElementById('svc-active').checked = String(s.is_active) === '1';
            document.getElementById('svc-home').checked = String(s.show_homepage) === '1';
            document.getElementById('svc-listing').checked = String(s.show_listing) === '1';
            var p = document.getElementById('svc-image-preview');
            if (s.image) { p.src = '../' + s.image.replace(/^\//, ''); p.style.display = 'block'; } else { p.style.display = 'none'; }
            var link = document.getElementById('svc-page-link');
            link.href = 'manage-service-page?id=' + s.id;
            link.style.display = 'inline-block';
            document.getElementById('svc-modal-title').textContent = 'Edit Service';
            new bootstrap.Modal(modal).show();
        });
    });
});
</script>

<?php require_once 'admin_footer.php'; ?>
