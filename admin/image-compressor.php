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

$allImages = [];
$allImages = array_merge($allImages, scanImages($imgDir, $root));

$totalOriginal = 0;
$totalWebp = 0;
$count = 0;
$compressedCount = 0;
foreach ($allImages as $img) {
    $totalOriginal += $img['size'];
    if ($img['has_webp']) {
        $totalWebp += $img['webp_size'];
        $compressedCount++;
    }
    $count++;
}

$savings = $totalOriginal > 0 && $totalWebp > 0 ? round((1 - $totalWebp / $totalOriginal) * 100) : 0;
?>

<div class="page-header">
    <div class="d-flex align-items-center justify-content-between w-100">
        <div>
            <h5 class="mb-0 text-white">Image Compressor</h5>
            <small class="text-white-50">Convert images to WebP and reduce file sizes for faster loading</small>
        </div>
        <button class="btn btn-cyan" id="compressAllBtn" onclick="compressAll()">
            <i class="bi bi-lightning-charge-fill me-1"></i> Compress All
        </button>
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
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr>
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
                    <tr><td colspan="7" class="text-muted text-center py-4">No images found.</td></tr>
                <?php else: ?>
                    <?php foreach ($allImages as $img): ?>
                    <tr data-path="<?= htmlspecialchars($img['path']) ?>">
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
                            <?php if ($img['ext'] !== 'webp'): ?>
                            <button class="btn btn-sm btn-outline-cyan compress-btn" onclick="compressOne(this, '<?= htmlspecialchars(addslashes($img['path'])) ?>')">
                                <i class="bi bi-lightning-charge"></i> Compress
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
    var el = document.getElementById('compressProgress');
    el.classList.remove('d-none');
    document.getElementById('progressText').textContent = text;
    document.getElementById('progressDetail').textContent = detail || '';
    document.getElementById('progressBar').style.width = pct + '%';
}

function hideProgress() {
    setTimeout(function() {
        document.getElementById('compressProgress').classList.add('d-none');
    }, 2000);
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
            btn.outerHTML = '<span class="text-success small"><i class="bi bi-check-circle"></i> Done</span>';
            showProgress('Done!', data.original + ' → ' + data.webp_size, 100);
            setTimeout(function() { location.reload(); }, 1500);
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-lightning-charge"></i> Retry';
            showProgress('Failed', data.error || 'Unknown error', 0);
        }
    })
    .catch(function(e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lightning-charge"></i> Retry';
        showProgress('Error', e.message, 0);
    });
}

function compressAll() {
    var btn = document.getElementById('compressAllBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Compressing...';

    var pending = document.querySelectorAll('.compress-btn');
    var total = pending.length;
    if (total === 0) {
        showProgress('Nothing to compress', 'All images are already compressed.', 100);
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> All Done';
        return;
    }

    var done = 0;
    showProgress('Compressing ' + total + ' images...', 'Starting...', 0);

    var paths = [];
    pending.forEach(function(b) {
        var row = b.closest('tr');
        paths.push(row.dataset.path);
    });

    function compressNext(idx) {
        if (idx >= paths.length) {
            showProgress('All done!', total + ' images compressed.', 100);
            setTimeout(function() { location.reload(); }, 1500);
            return;
        }
        var pct = Math.round(((idx) / total) * 100);
        showProgress('Compressing ' + (idx + 1) + ' of ' + total + '...', paths[idx], pct);

        fetch('compress_ajax.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=compress&path=' + encodeURIComponent(paths[idx])
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            done++;
            compressNext(idx + 1);
        })
        .catch(function(e) {
            done++;
            compressNext(idx + 1);
        });
    }

    compressNext(0);
}
</script>

<?php require_once 'admin_footer.php'; ?>
