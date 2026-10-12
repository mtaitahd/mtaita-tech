<?php
$page_title = 'Build Service Page';
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
$csrf = generateCsrfToken();

$UPLOAD_DIR = 'assets/img/uploads/services';
$ITEM_TYPES = ['benefits', 'service_grid', 'type_cards', 'faq', 'steps', 'tech', 'city_links'];

$serviceId = (int)($_GET['id'] ?? $_POST['service_id'] ?? 0);
$service = $serviceId > 0 ? $serviceObj->getById($serviceId) : null;

if (!$service) {
    header('Location: manage-services?msg=' . urlencode('Please choose a service first.'));
    exit;
}

// -------------------------------------------------------------------------
// POST actions
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Your session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        try {
            if ($action === 'add_section') {
                $data = [
                    'section_type' => in_array($_POST['section_type'] ?? '', array_keys(Service::sectionTypes()), true) ? $_POST['section_type'] : 'content',
                    'column_side' => in_array($_POST['column_side'] ?? '', ['left', 'right', 'full'], true) ? $_POST['column_side'] : 'left',
                    'heading' => trim($_POST['heading'] ?? '') ?: null,
                    'subheading' => trim($_POST['subheading'] ?? '') ?: null,
                    'body' => trim($_POST['body'] ?? '') ?: null,
                    'button_text' => trim($_POST['button_text'] ?? '') ?: null,
                    'button_url' => trim($_POST['button_url'] ?? '') ?: null,
                    'anchor' => trim($_POST['anchor'] ?? '') ?: null,
                    'note' => trim($_POST['note'] ?? '') ?: null,
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'sort_order' => $serviceObj->nextSectionOrder($serviceId),
                ];
                $newId = (int)$serviceObj->createSection($serviceId, $data);
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $up = Uploader::handle($_FILES['image'], $UPLOAD_DIR, null, 'section');
                    if ($up['ok'] && $up['path'] !== null) {
                        $serviceObj->updateSection($newId, array_merge($data, ['image' => $up['path'], 'image_alt' => trim($_POST['image_alt'] ?? '') ?: null]));
                    } elseif (!$up['ok']) {
                        $error_msg = $up['error'];
                    }
                }
                if (!$error_msg) $success_msg = 'Section added.';
            }
        } catch (Exception $e) {
            error_log('service section add: ' . $e->getMessage());
            $error_msg = 'Database error.';
        }

        // Save an existing section
        if (!empty($_POST['save_section_id'])) {
            try {
                $sid = (int)$_POST['save_section_id'];
                $current = $serviceObj->getSection($sid);
                if ($current && (int)$current['service_id'] === $serviceId) {
                    $data = [
                        'section_type' => in_array($_POST['section_type'] ?? '', array_keys(Service::sectionTypes()), true) ? $_POST['section_type'] : 'content',
                        'column_side' => in_array($_POST['column_side'] ?? '', ['left', 'right', 'full'], true) ? $_POST['column_side'] : 'left',
                        'heading' => trim($_POST['heading'] ?? '') ?: null,
                        'subheading' => trim($_POST['subheading'] ?? '') ?: null,
                        'body' => trim($_POST['body'] ?? '') ?: null,
                        'image' => $current['image'],
                        'image_alt' => trim($_POST['image_alt'] ?? '') ?: null,
                        'button_text' => trim($_POST['button_text'] ?? '') ?: null,
                        'button_url' => trim($_POST['button_url'] ?? '') ?: null,
                        'anchor' => trim($_POST['anchor'] ?? '') ?: null,
                        'note' => trim($_POST['note'] ?? '') ?: null,
                        'sort_order' => (int)$current['sort_order'],
                        'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    ];
                    if (!empty($_POST['remove_image'])) {
                        Uploader::delete($current['image']);
                        $data['image'] = null;
                    }
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $up = Uploader::handle($_FILES['image'], $UPLOAD_DIR, $current['image'], 'section');
                        if ($up['ok'] && $up['path'] !== null) {
                            $data['image'] = $up['path'];
                        } elseif (!$up['ok']) {
                            $error_msg = $up['error'];
                        }
                    }
                    if (!$error_msg) {
                        $serviceObj->updateSection($sid, $data);
                        $success_msg = 'Section updated.';
                    }
                }
            } catch (Exception $e) {
                error_log('service section save: ' . $e->getMessage());
                $error_msg = 'Database error.';
            }
        }

        // Add an item to a section
        if (!empty($_POST['add_item_section_id'])) {
            try {
                $sid = (int)$_POST['add_item_section_id'];
                $sec = $serviceObj->getSection($sid);
                if ($sec && (int)$sec['service_id'] === $serviceId) {
                    $serviceObj->createItem($sid, [
                        'item_title' => trim($_POST['item_title'] ?? '') ?: null,
                        'item_text' => trim($_POST['item_text'] ?? '') ?: null,
                        'item_icon' => trim($_POST['item_icon'] ?? '') ?: null,
                        'item_link' => trim($_POST['item_link'] ?? '') ?: null,
                        'is_active' => isset($_POST['item_active']) ? 1 : 0,
                        'sort_order' => $serviceObj->nextItemOrder($sid),
                    ]);
                    if (!$error_msg) $success_msg = 'Item added.';
                }
            } catch (Exception $e) {
                error_log('service item add: ' . $e->getMessage());
                $error_msg = 'Database error.';
            }
        }

        // Save an existing item
        if (!empty($_POST['save_item_id'])) {
            try {
                $iid = (int)$_POST['save_item_id'];
                $item = $serviceObj->getItem($iid);
                if ($item) {
                    $sec = $serviceObj->getSection($item['section_id']);
                    if ($sec && (int)$sec['service_id'] === $serviceId) {
                        $serviceObj->updateItem($iid, [
                            'item_title' => trim($_POST['item_title'] ?? '') ?: null,
                            'item_text' => trim($_POST['item_text'] ?? '') ?: null,
                            'item_icon' => trim($_POST['item_icon'] ?? '') ?: null,
                            'item_link' => trim($_POST['item_link'] ?? '') ?: null,
                            'is_active' => isset($_POST['item_active']) ? 1 : 0,
                            'sort_order' => (int)$item['sort_order'],
                        ]);
                        if (!$error_msg) $success_msg = 'Item updated.';
                    }
                }
            } catch (Exception $e) {
                error_log('service item save: ' . $e->getMessage());
                $error_msg = 'Database error.';
            }
        }
    }
}

// ---------------------------------------------------------------------------
// Lightweight GET actions (guarded with a CSRF token in the query string)
// ---------------------------------------------------------------------------
if (!empty($_GET['action']) && isset($_GET['csrf']) && hash_equals($csrf, (string)$_GET['csrf'])) {
    try {
        $action = $_GET['action'];
        if ($action === 'del_section' && !empty($_GET['sid'])) {
            $sec = $serviceObj->getSection((int)$_GET['sid']);
            if ($sec && (int)$sec['service_id'] === $serviceId) $serviceObj->deleteSection((int)$_GET['sid']);
            $success_msg = 'Section deleted.';
        } elseif ($action === 'toggle_section' && !empty($_GET['sid'])) {
            $sec = $serviceObj->getSection((int)$_GET['sid']);
            if ($sec && (int)$sec['service_id'] === $serviceId) $serviceObj->toggleSection((int)$_GET['sid']);
            $success_msg = 'Section visibility updated.';
        } elseif ($action === 'up_section' || $action === 'down_section') {
            $rows = $serviceObj->getSections($serviceId, false);
            $sid = (int)$_GET['sid'];
            $dir = $action === 'up_section' ? -1 : 1;
            foreach ($rows as $i => $r) {
                if ((int)$r['id'] === $sid) {
                    $j = $i + $dir;
                    if ($j >= 0 && $j < count($rows)) {
                        $serviceObj->updateSection($r['id'], array_merge($r, ['sort_order' => $rows[$j]['sort_order']]));
                        $serviceObj->updateSection($rows[$j]['id'], array_merge($rows[$j], ['sort_order' => $r['sort_order']]));
                    }
                    break;
                }
            }
            $success_msg = 'Order updated.';
        } elseif ($action === 'del_item' && !empty($_GET['iid'])) {
            $item = $serviceObj->getItem((int)$_GET['iid']);
            if ($item) {
                $sec = $serviceObj->getSection($item['section_id']);
                if ($sec && (int)$sec['service_id'] === $serviceId) $serviceObj->deleteItem((int)$_GET['iid']);
            }
            $success_msg = 'Item deleted.';
        } elseif ($action === 'toggle_item' && !empty($_GET['iid'])) {
            $item = $serviceObj->getItem((int)$_GET['iid']);
            if ($item) {
                $sec = $serviceObj->getSection($item['section_id']);
                if ($sec && (int)$sec['service_id'] === $serviceId) $serviceObj->toggleItem((int)$_GET['iid']);
            }
            $success_msg = 'Item visibility updated.';
        } elseif ($action === 'up_item' || $action === 'down_item') {
            $item = $serviceObj->getItem((int)$_GET['iid']);
            if ($item) {
                $sec = $serviceObj->getSection($item['section_id']);
                if ($sec && (int)$sec['service_id'] === $serviceId) {
                    $rows = $serviceObj->getItems($item['section_id'], false);
                    $dir = $action === 'up_item' ? -1 : 1;
                    foreach ($rows as $i => $r) {
                        if ((int)$r['id'] === (int)$item['id']) {
                            $j = $i + $dir;
                            if ($j >= 0 && $j < count($rows)) {
                                $serviceObj->updateItem($r['id'], array_merge($r, ['sort_order' => $rows[$j]['sort_order']]));
                                $serviceObj->updateItem($rows[$j]['id'], array_merge($rows[$j], ['sort_order' => $r['sort_order']]));
                            }
                            break;
                        }
                    }
                }
            }
            $success_msg = 'Order updated.';
        }
    } catch (Exception $e) {
        error_log('service page action: ' . $e->getMessage());
        $error_msg = 'Database error.';
    }
}

$sections = $serviceObj->getSections($serviceId, false);
$itemsBySection = $serviceObj->getItemsByService($serviceId, false);
$types = Service::sectionTypes();

require_once 'admin_header.php';
?>
<div class="page-header d-flex flex-wrap gap-2 align-items-center">
    <a href="manage-services" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Services</a>
    <div class="ms-2">
        <strong><?= htmlspecialchars($service['title']) ?></strong>
        <code class="text-cyan ms-2">/<?= htmlspecialchars($service['slug'] ?? '') ?></code>
    </div>
    <a href="../<?= htmlspecialchars($service['slug'] ?? '') ?>" target="_blank" class="btn btn-outline-cyan ms-auto"><i class="bi bi-box-arrow-up-right"></i> View Page</a>
</div>

<?php if ($success_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
<?php if ($error_msg): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

<p class="text-muted small">Sections appear on the page in the order below. Left / Right build the two-column body; Full sections render below it. Items inside a section are the repeatable rows (benefits, cards, FAQ, etc.).</p>

<!-- Add section -->
<div class="admin-card mb-4">
    <h6 class="text-cyan mb-3"><i class="bi bi-plus-circle"></i> Add Section</h6>
    <form method="POST" enctype="multipart/form-data" class="row g-2">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="service_id" value="<?= $serviceId ?>">
        <input type="hidden" name="action" value="add_section">
        <div class="col-md-4">
            <label class="form-label small">Type</label>
            <select name="section_type" class="form-select form-select-sm" id="addType">
                <?php foreach ($types as $key => $label): ?>
                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Column</label>
            <select name="column_side" class="form-select form-select-sm">
                <option value="left">Left</option>
                <option value="right">Right</option>
                <option value="full">Full width</option>
            </select>
        </div>
        <div class="col-md-3"><label class="form-label small">Heading</label><input name="heading" class="form-control form-control-sm"></div>
        <div class="col-md-3"><label class="form-label small">Anchor (id)</label><input name="anchor" class="form-control form-control-sm" placeholder="wd-overview"></div>
        <div class="col-12"><label class="form-label small">Subheading / text</label><textarea name="subheading" rows="2" class="form-control form-control-sm"></textarea></div>
        <div class="col-md-6"><label class="form-label small">Image (optional)</label><input type="file" name="image" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp,image/gif"></div>
        <div class="col-md-6"><label class="form-label small">Image alt</label><input name="image_alt" class="form-control form-control-sm"></div>
        <div class="col-md-3"><label class="form-label small">Button text</label><input name="button_text" class="form-control form-control-sm"></div>
        <div class="col-md-3"><label class="form-label small">Button URL</label><input name="button_url" class="form-control form-control-sm"></div>
        <div class="col-md-6"><label class="form-label small">Note (small print)</label><input name="note" class="form-control form-control-sm"></div>
        <div class="col-12 d-flex align-items-center gap-3">
            <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" id="addActive" checked><label class="form-check-label" for="addActive">Active</label></div>
            <button class="btn btn-cyan btn-sm ms-auto"><i class="bi bi-plus-lg"></i> Add Section</button>
        </div>
    </form>
</div>

<?php if (empty($sections)): ?>
    <div class="alert alert-info">No sections yet. Add the first section above — items can be added once a section exists.</div>
<?php endif; ?>

<?php foreach ($sections as $idx => $sec): ?>
<?php $sid = (int)$sec['id']; $items = $itemsBySection[$sid] ?? []; ?>
<div class="admin-card mb-3">
    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
        <span class="badge bg-secondary"><?= htmlspecialchars($sec['section_type']) ?></span>
        <span class="badge bg-dark text-uppercase"><?= htmlspecialchars($sec['column_side']) ?></span>
        <strong><?= htmlspecialchars($sec['heading'] ?: '(no heading)') ?></strong>
        <?php if (!$sec['is_active']): ?><span class="badge bg-warning text-dark">Hidden</span><?php endif; ?>
        <span class="ms-auto d-flex gap-1">
            <a class="btn btn-sm btn-outline-light" href="?id=<?= $serviceId ?>&action=up_section&sid=<?= $sid ?>&csrf=<?= urlencode($csrf) ?>" title="Move up"><i class="bi bi-arrow-up"></i></a>
            <a class="btn btn-sm btn-outline-light" href="?id=<?= $serviceId ?>&action=down_section&sid=<?= $sid ?>&csrf=<?= urlencode($csrf) ?>" title="Move down"><i class="bi bi-arrow-down"></i></a>
            <a class="btn btn-sm btn-outline-warning" href="?id=<?= $serviceId ?>&action=toggle_section&sid=<?= $sid ?>&csrf=<?= urlencode($csrf) ?>" title="Toggle visibility"><i class="bi bi-eye<?= $sec['is_active'] ? '' : '-slash' ?>"></i></a>
            <a class="btn btn-sm btn-outline-danger btn-confirm" href="?id=<?= $serviceId ?>&action=del_section&sid=<?= $sid ?>&csrf=<?= urlencode($csrf) ?>" data-confirm="Delete this section and all its items?" title="Delete"><i class="bi bi-trash"></i></a>
        </span>
    </div>

    <div class="accordion">
        <div class="accordion-item bg-transparent">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed bg-dark text-light" type="button" data-bs-toggle="collapse" data-bs-target="#secEdit<?= $sid ?>">Edit section settings</button>
            </h2>
            <div id="secEdit<?= $sid ?>" class="accordion-collapse collapse" data-bs-parent="">
                <div class="accordion-body">
                    <form method="POST" enctype="multipart/form-data" class="row g-2">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="service_id" value="<?= $serviceId ?>">
                        <input type="hidden" name="save_section_id" value="<?= $sid ?>">
                        <div class="col-md-4">
                            <label class="form-label small">Type</label>
                            <select name="section_type" class="form-select form-select-sm">
                                <?php foreach ($types as $key => $label): ?>
                                <option value="<?= htmlspecialchars($key) ?>" <?= $sec['section_type'] === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Column</label>
                            <select name="column_side" class="form-select form-select-sm">
                                <?php foreach (['left' => 'Left', 'right' => 'Right', 'full' => 'Full'] as $k => $v): ?>
                                <option value="<?= $k ?>" <?= $sec['column_side'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label small">Heading</label><input name="heading" class="form-control form-control-sm" value="<?= htmlspecialchars($sec['heading'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label small">Anchor (id)</label><input name="anchor" class="form-control form-control-sm" value="<?= htmlspecialchars($sec['anchor'] ?? '') ?>"></div>
                        <div class="col-12"><label class="form-label small">Subheading / text</label><textarea name="subheading" rows="2" class="form-control form-control-sm"><?= htmlspecialchars($sec['subheading'] ?? '') ?></textarea></div>
                        <div class="col-12"><label class="form-label small">Body (optional long text)</label><textarea name="body" rows="2" class="form-control form-control-sm"><?= htmlspecialchars($sec['body'] ?? '') ?></textarea></div>
                        <div class="col-md-6">
                            <label class="form-label small">Image</label>
                            <?php if (!empty($sec['image'])): ?><div class="mb-1"><img src="../<?= htmlspecialchars(ltrim($sec['image'], '/')) ?>" alt="" height="48" style="border-radius:4px;object-fit:cover;"></div><?php endif; ?>
                            <input type="file" name="image" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp,image/gif">
                            <div class="form-check mt-1"><input class="form-check-input" type="checkbox" name="remove_image" value="1"><label class="form-check-label small">Remove current image</label></div>
                        </div>
                        <div class="col-md-6"><label class="form-label small">Image alt</label><input name="image_alt" class="form-control form-control-sm" value="<?= htmlspecialchars($sec['image_alt'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label small">Button text</label><input name="button_text" class="form-control form-control-sm" value="<?= htmlspecialchars($sec['button_text'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label small">Button URL</label><input name="button_url" class="form-control form-control-sm" value="<?= htmlspecialchars($sec['button_url'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label small">Note</label><input name="note" class="form-control form-control-sm" value="<?= htmlspecialchars($sec['note'] ?? '') ?>"></div>
                        <div class="col-12 d-flex align-items-center gap-3">
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" <?= $sec['is_active'] ? 'checked' : '' ?>><label class="form-check-label">Active</label></div>
                            <button class="btn btn-cyan btn-sm ms-auto"><i class="bi bi-save"></i> Save Section</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Items -->
    <div class="mt-3">
        <h6 class="text-muted small text-uppercase">Items (<?= count($items) ?>)</h6>
        <?php foreach ($items as $it): ?>
        <?php $iid = (int)$it['id']; ?>
        <form method="POST" class="row g-2 align-items-center mb-2 border-bottom border-secondary pb-2">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="service_id" value="<?= $serviceId ?>">
            <input type="hidden" name="save_item_id" value="<?= $iid ?>">
            <div class="col-md-3"><input name="item_title" class="form-control form-control-sm" value="<?= htmlspecialchars($it['item_title'] ?? '') ?>" placeholder="Title"></div>
            <div class="col-md-4"><textarea name="item_text" rows="1" class="form-control form-control-sm" placeholder="Text / answer"><?= htmlspecialchars($it['item_text'] ?? '') ?></textarea></div>
            <div class="col-md-2"><input name="item_link" class="form-control form-control-sm" value="<?= htmlspecialchars($it['item_link'] ?? '') ?>" placeholder="Link"></div>
            <div class="col-md-1"><input name="item_icon" class="form-control form-control-sm" value="<?= htmlspecialchars($it['item_icon'] ?? '') ?>" placeholder="Icon"></div>
            <div class="col-auto form-check">
                <input class="form-check-input" type="checkbox" name="item_active" <?= $it['is_active'] ? 'checked' : '' ?> title="Visible">
            </div>
            <div class="col-auto d-flex gap-1">
                <button class="btn btn-sm btn-cyan" title="Save"><i class="bi bi-save"></i></button>
                <a class="btn btn-sm btn-outline-light" href="?id=<?= $serviceId ?>&action=up_item&iid=<?= $iid ?>&csrf=<?= urlencode($csrf) ?>"><i class="bi bi-arrow-up"></i></a>
                <a class="btn btn-sm btn-outline-light" href="?id=<?= $serviceId ?>&action=down_item&iid=<?= $iid ?>&csrf=<?= urlencode($csrf) ?>"><i class="bi bi-arrow-down"></i></a>
                <a class="btn btn-sm btn-outline-warning" href="?id=<?= $serviceId ?>&action=toggle_item&iid=<?= $iid ?>&csrf=<?= urlencode($csrf) ?>"><i class="bi bi-eye<?= $it['is_active'] ? '' : '-slash' ?>"></i></a>
                <a class="btn btn-sm btn-outline-danger btn-confirm" href="?id=<?= $serviceId ?>&action=del_item&iid=<?= $iid ?>&csrf=<?= urlencode($csrf) ?>" data-confirm="Delete this item?"><i class="bi bi-trash"></i></a>
            </div>
        </form>
        <?php endforeach; ?>

        <?php if (in_array($sec['section_type'], $ITEM_TYPES, true)): ?>
        <form method="POST" class="row g-2 align-items-center mt-2">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="service_id" value="<?= $serviceId ?>">
            <input type="hidden" name="add_item_section_id" value="<?= $sid ?>">
            <div class="col-md-3"><input name="item_title" class="form-control form-control-sm" placeholder="Title / question"></div>
            <div class="col-md-4"><textarea name="item_text" rows="1" class="form-control form-control-sm" placeholder="Text / answer"></textarea></div>
            <div class="col-md-2"><input name="item_link" class="form-control form-control-sm" placeholder="Link"></div>
            <div class="col-md-1"><input name="item_icon" class="form-control form-control-sm" placeholder="Icon"></div>
            <div class="col-auto form-check"><input class="form-check-input" type="checkbox" name="item_active" checked></div>
            <div class="col-auto"><button class="btn btn-sm btn-outline-cyan"><i class="bi bi-plus-lg"></i> Add item</button></div>
        </form>
        <?php else: ?>
            <p class="text-muted small mb-0">This section type does not use items.</p>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-confirm').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) { e.preventDefault(); }
        });
    });
});
</script>

<?php require_once 'admin_footer.php'; ?>
