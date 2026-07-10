<?php
$active_page = 'image-compressor';
$page_title = 'Image Compressor';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';
require_once 'admin_header.php';

function formatSize($bytes) {
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

function scanImages($dir, $root) {
    $results = [];
    if (!is_dir($dir)) return $results;
    $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($items as $item) {
        if ($item->isDir()) continue;
        $ext = strtolower($item->getExtension());
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) continue;
        $rel = str_replace($root, '', str_replace('\\', '/', $item->getPathname()));
        if ($rel[0] === '/') $rel = substr($rel, 1);
        $webpPath = preg_replace('/\.(jpe?g|png|gif)$/i', '.webp', $item->getPathname());
        $hasWebp = file_exists($webpPath);
        $webpSize = $hasWebp ? filesize($webpPath) : 0;
        $results[] = [
            'path' => $rel,
            'full' => $item->getPathname(),
            'size' => $item->getSize(),
            'ext' => $ext,
            'has_webp' => $hasWebp,
            'webp_size' => $webpSize,
        ];
    }
    return $results;
}

$root = dirname(__DIR__) . '/';
$imgDir = dirname(__DIR__) . '/assets/img';

$allImages = scanImages($imgDir, $root);

$totalOriginal = 0;
$totalWebp = 0;
$count = 0;
$compressedCount = 0;
$pendingCount = 0;
foreach ($allImages as $img) {
    $totalOriginal += $img['size'];
    if ($img['has_webp']) {
        $totalWebp += $img['webp_size'];
        $compressedCount++;
    } else {
        $pendingCount++;
    }
    $count++;
}

$savings = $totalOriginal > 0 && $totalWebp > 0 ? round((1 - $totalWebp / $totalOriginal) * 100) : 0;
$skipFiles = ['jj.png', 'jj.webp'];
?>

<div class="page-header">
    <div>
        <h5 class="mb-0 text-white">Image Compressor</h5>
        <small class="text-white-50">Convert images to WebP and reduce file sizes for faster loading</small>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="admin-card text-center p-3">
            <div class="fw-bold" style="font-size:1.8rem;color:var(--cyan);"><?= $count ?></div>
            <small class="text-muted">Total Images</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-card text-center p-3">
            <div class="fw-bold" style="font-size:1.8rem;color:#F59E0B;"><?= formatSize($totalOriginal) ?></div>
            <small class="text-muted">Original Size</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-card text-center p-3">
            <div class="fw-bold" style="font-size:1.8rem;color:#10B981;"><?= $compressedCount > 0 ? formatSize($totalWebp) : '—' ?></div>
            <small class="text-muted">WebP Size</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-card text-center p-3">
            <div class="fw-bold" style="font-size:1.8rem;color:<?= $savings > 0 ? '#10B981' : 'var(--red)' ?>;">
                <?= $savings > 0 ? $savings . '%' : '—' ?>
            </div>
            <small class="text-muted">Size Saved</small>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="d-flex align-items-center justify-content-between mb-3 px-3 pt-3">
        <div class="d-flex align-items-center gap-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="selectAll">
                <label class="form-check-label text-white" for="selectAll">Select All</label>
            </div>
            <span class="text-muted small" id="selectedCount">0 selected</span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-cyan btn-sm" id="compressSelectedBtn" onclick="compressSelected()" disabled>
                <i class="bi bi-lightning-charge-fill me-1"></i> Compress Selected
            </button>
            <button class="btn btn-outline-cyan btn-sm" id="compressAllBtn" onclick="compressAll()">
                <i class="bi bi-lightning-charge-fill me-1"></i> Compress All (<?= $pendingCount ?> pending)
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th width="40"><input type="checkbox" class="form-check-input" id="selectAllTable"></th>
                    <th>Preview</th>
                    <th>File</th>
                    <th>Format</th>
                    <th>Original</th>
                    <th>WebP</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($allImages)): ?>
                    <tr><td colspan="8" class="text-muted text-center py-4">No images found.</td></tr>
                <?php else: ?>
                    <?php foreach ($allImages as $img):
                        $isLogo = in_array(basename($img['path']), $skipFiles);
                    ?>
                    <tr data-path="<?= htmlspecialchars($img['path']) ?>" class="<?= $img['ext'] === 'webp' || $img['has_webp'] ? 'row-compressed' : '' ?>">
                        <td>
                            <?php if ($img['ext'] !== 'webp' && !$isLogo): ?>
                            <input type="checkbox" class="form-check-input img-checkbox" value="<?= htmlspecialchars($img['path']) ?>" <?= $img['has_webp'] ? 'disabled' : '' ?>>
                            <?php endif; ?>
                        </td>
                        <td>
                            <img src="/<?= htmlspecialchars($img['path']) ?>" alt="" width="60" height="40"
                                style="object-fit:cover;border-radius:4px;background:#1E293B;"
                                onerror="this.style.display='none'">
                        </td>
                        <td>
                            <small class="text-white"><?= basename($img['path']) ?></small><br>
                            <small class="text-muted"><?= dirname($img['path']) ?>/</small>
                        </td>
                        <td><span class="badge bg-dark"><?= strtoupper($img['ext']) ?></span></td>
                        <td><?= formatSize($img['size']) ?></td>
                        <td class="webp-size">
                            <?php if ($img['has_webp']): ?>
                                <span class="text-success"><?= formatSize($img['webp_size']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="status-col">
                            <?php if ($img['has_webp']): ?>
                                <span class="badge" style="background:#10B981;">Compressed</span>
                            <?php elseif ($img['ext'] === 'webp'): ?>
                                <span class="badge bg-secondary">Already WebP</span>
                            <?php else: ?>
                                <span class="badge" style="background:#F59E0B;color:#000;">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isLogo): ?>
                            <span class="text-muted small" title="Site logo - cannot compress"><i class="bi bi-lock"></i></span>
                            <?php elseif ($img['has_webp']): ?>
                            <button class="btn btn-sm btn-outline-warning revert-btn" onclick="revertOne(this, '<?= htmlspecialchars(addslashes($img['path'])) ?>')" title="Revert to original">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                            <?php elseif ($img['ext'] !== 'webp'): ?>
                            <button class="btn btn-sm btn-outline-cyan compress-btn" onclick="compressOne(this, '<?= htmlspecialchars(addslashes($img['path'])) ?>')">
                                <i class="bi bi-lightning-charge"></i>
                            </button>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="compressProgress" class="d-none" style="position:fixed;bottom:24px;right:24px;z-index:9999;">
    <div class="admin-card p-3" style="min-width:300px;box-shadow:0 8px 32px rgba(0,0,0,0.4);">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div class="spinner-border spinner-border-sm text-cyan" role="status"></div>
            <span id="progressText" class="text-white">Compressing...</span>
        </div>
        <div class="progress" style="height:6px;">
            <div id="progressBar" class="progress-bar bg-cyan" style="width:0%;"></div>
        </div>
        <small id="progressDetail" class="text-muted mt-1 d-block"></small>
    </div>
</div>

<script>
function showProgress(text, detail, pct) {
    document.getElementById('compressProgress').classList.remove('d-none');
    document.getElementById('progressText').textContent = text;
    document.getElementById('progressDetail').textContent = detail || '';
    document.getElementById('progressBar').style.width = pct + '%';
}

function updateSelectedCount() {
    var checked = document.querySelectorAll('.img-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = checked + ' selected';
    document.getElementById('compressSelectedBtn').disabled = checked === 0;
}

document.getElementById('selectAll').addEventListener('change', function() {
    var boxes = document.querySelectorAll('.img-checkbox:not(:disabled)');
    boxes.forEach(function(cb) { cb.checked = document.getElementById('selectAll').checked; });
    document.getElementById('selectAllTable').checked = document.getElementById('selectAll').checked;
    updateSelectedCount();
});

document.getElementById('selectAllTable').addEventListener('change', function() {
    var boxes = document.querySelectorAll('.img-checkbox:not(:disabled)');
    boxes.forEach(function(cb) { cb.checked = document.getElementById('selectAllTable').checked; });
    document.getElementById('selectAll').checked = document.getElementById('selectAllTable').checked;
    updateSelectedCount();
});

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('img-checkbox')) updateSelectedCount();
});

function getSelectedPaths() {
    var paths = [];
    document.querySelectorAll('.img-checkbox:checked').forEach(function(cb) {
        var row = cb.closest('tr');
        if (row) paths.push(row.dataset.path);
    });
    return paths;
}

function compressOne(btn, path) {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    showProgress('Compressing 1 image...', path, 50);

    fetch('compress_ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=compress&path=' + encodeURIComponent(path)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var row = btn.closest('tr');
            row.querySelector('.webp-size').innerHTML = '<span class="text-success">' + data.webp_size + '</span>';
            row.querySelector('.status-col').innerHTML = '<span class="badge" style="background:#10B981;">Compressed</span>';
            row.querySelector('.img-checkbox').disabled = true;
            row.querySelector('.img-checkbox').checked = false;
            row.classList.add('row-compressed');
            btn.outerHTML = '<button class="btn btn-sm btn-outline-warning revert-btn" onclick="revertOne(this, \'' + path + '\')" title="Revert to original"><i class="bi bi-arrow-counterclockwise"></i></button>';
            updateSelectedCount();
            showProgress('Done!', data.original + ' → ' + data.webp_size, 100);
            setTimeout(function() { document.getElementById('compressProgress').classList.add('d-none'); }, 2000);
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-lightning-charge"></i>';
            showProgress('Failed', data.error || 'Unknown error', 0);
        }
    })
    .catch(function(e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lightning-charge"></i>';
        showProgress('Error', e.message, 0);
    });
}

function revertOne(btn, path) {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    showProgress('Reverting...', path, 50);

    fetch('compress_ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=revert&path=' + encodeURIComponent(path)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var row = btn.closest('tr');
            row.querySelector('.webp-size').innerHTML = '<span class="text-muted">—</span>';
            row.querySelector('.status-col').innerHTML = '<span class="badge" style="background:#F59E0B;color:#000;">Pending</span>';
            row.classList.remove('row-compressed');
            btn.outerHTML = '<button class="btn btn-sm btn-outline-cyan compress-btn" onclick="compressOne(this, \'' + path + '\')"><i class="bi bi-lightning-charge"></i></button>';
            showProgress('Reverted!', 'WebP deleted. Original restored.', 100);
            setTimeout(function() { document.getElementById('compressProgress').classList.add('d-none'); }, 2000);
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i>';
            showProgress('Failed', data.error || 'Unknown error', 0);
        }
    })
    .catch(function(e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i>';
        showProgress('Error', e.message, 0);
    });
}

function compressSelected() {
    var paths = getSelectedPaths();
    if (paths.length === 0) return;
    compressBatch(paths, 'Compress Selected');
}

function compressAll() {
    var pending = [];
    document.querySelectorAll('.compress-btn').forEach(function(b) {
        var row = b.closest('tr');
        if (row && !row.classList.contains('row-compressed')) {
            pending.push(row.dataset.path);
        }
    });
    if (pending.length === 0) {
        showProgress('Nothing to compress', 'All images are already compressed.', 100);
        return;
    }
    compressBatch(pending, 'Compress All');
}

function compressBatch(paths, label) {
    var total = paths.length;
    var done = 0;
    showProgress(label + ': 0 of ' + total + '...', 'Starting...', 0);

    var btnSel = document.getElementById('compressSelectedBtn');
    var btnAll = document.getElementById('compressAllBtn');
    btnSel.disabled = true;
    btnAll.disabled = true;
    btnSel.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Working...';
    btnAll.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Working...';

    function next(idx) {
        if (idx >= paths.length) {
            showProgress('Done!', total + ' images compressed.', 100);
            btnSel.innerHTML = '<i class="bi bi-check-circle me-1"></i> Done';
            btnAll.innerHTML = '<i class="bi bi-lightning-charge-fill me-1"></i> Compress All';
            btnAll.disabled = false;
            updateSelectedCount();
            setTimeout(function() { location.reload(); }, 1500);
            return;
        }
        var pct = Math.round((idx / total) * 100);
        showProgress(label + ': ' + (idx + 1) + ' of ' + total + '...', paths[idx], pct);

        fetch('compress_ajax.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=compress&path=' + encodeURIComponent(paths[idx])
        })
        .then(function(r) { return r.json(); })
        .then(function() { done++; next(idx + 1); })
        .catch(function() { done++; next(idx + 1); });
    }

    next(0);
}
</script>

<style>
.row-compressed { opacity: 0.5; }
.row-compressed:hover { opacity: 1; }
.img-checkbox { width: 18px; height: 18px; cursor: pointer; }
</style>

<?php require_once 'admin_footer.php'; ?>
