<?php
$page_title = 'Coding Challenge';
$active_page = 'coding-challenges';
$success_msg = '';
$error_msg = '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../auth_helper.php';
require_once __DIR__ . '/../lib/CodingChallenge.php';

$challengeModel = new CodingChallenge();

$challengeId = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
$challenge = $challengeId > 0 ? $challengeModel->getById($challengeId) : null;
$testCases = $challenge ? $challengeModel->getTestCases($challengeId) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_challenge'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Invalid security token. Please refresh and try again.';
    } else {
        $cid = (int)($_POST['course_id'] ?? 0);
        $lessonId = (int)($_POST['lesson_id'] ?? 0);
        $moduleId = (int)($_POST['module_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $language = in_array($_POST['language'] ?? '', CodingChallenge::LANGUAGES, true) ? $_POST['language'] : 'cpp';
        $difficulty = in_array($_POST['difficulty'] ?? '', CodingChallenge::DIFFICULTIES, true) ? $_POST['difficulty'] : 'easy';

        if (!$title || $cid < 1) {
            $error_msg = 'Title and course are required.';
        } else {
            if ($lessonId > 0) {
                $moduleId = (int)$challengeModel->getModuleIdForLesson($lessonId);
            }
            if ($slug === '') {
                $slug = preg_replace('/[^a-z0-9_-]/i', '-', strtolower($title));
                $slug = trim($slug, '-');
            }

            $data = [
                'course_id' => $cid,
                'lesson_id' => $lessonId > 0 ? $lessonId : null,
                'module_id' => $moduleId > 0 ? $moduleId : null,
                'title' => $title,
                'slug' => $slug,
                'language' => $language,
                'difficulty' => $difficulty,
                'marks' => max(1, (int)($_POST['marks'] ?? 10)),
                'passing_score' => max(1, min(100, (int)($_POST['passing_score'] ?? 50))),
                'time_limit' => max(1, min(30, (int)($_POST['time_limit'] ?? 5))),
                'memory_limit' => max(32, min(1024, (int)($_POST['memory_limit'] ?? 128))),
                'problem' => trim($_POST['problem'] ?? ''),
                'input_desc' => trim($_POST['input_desc'] ?? ''),
                'output_desc' => trim($_POST['output_desc'] ?? ''),
                'constraints' => trim($_POST['constraints'] ?? ''),
                'sample_input' => trim($_POST['sample_input'] ?? ''),
                'sample_output' => trim($_POST['sample_output'] ?? ''),
                'starter_code' => $_POST['starter_code'] ?? '',
                'is_published' => isset($_POST['is_published']) ? 1 : 0,
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
            ];

            try {
                if ($challengeId > 0) {
                    $challengeModel->update($challengeId, $data);
                } else {
                    $challengeId = (int)$challengeModel->create($data);
                }

                $cases = [];
                $tcInputs = $_POST['tc_input'] ?? [];
                $tcOutputs = $_POST['tc_output'] ?? [];
                $tcVisible = $_POST['tc_visible'] ?? [];
                for ($i = 0; $i < count($tcInputs); $i++) {
                    $input = trim((string)($tcInputs[$i] ?? ''));
                    $output = trim((string)($tcOutputs[$i] ?? ''));
                    if ($input === '' && $output === '') continue;
                    $cases[] = [
                        'input_data' => $input !== '' ? $input : null,
                        'expected_output' => $output !== '' ? $output : null,
                        'is_visible' => !empty($tcVisible[$i]) ? 1 : 0,
                    ];
                }
                $challengeModel->replaceTestCases($challengeId, $cases);

                $challenge = $challengeModel->getById($challengeId);
                $testCases = $challengeModel->getTestCases($challengeId);
                $success_msg = 'Challenge saved!';
            } catch (Exception $e) {
                error_log('save_challenge error: ' . $e->getMessage());
                $error_msg = 'Database error while saving the challenge.';
            }
        }
    }
}

$courses = $pdo->query("SELECT id, title, type, status FROM courses ORDER BY title ASC")->fetchAll();
$selectedModules = [];
$selectedLessons = [];
if ($challenge) {
    if ($challenge['course_id']) {
        $stmt = $pdo->prepare("SELECT id, title FROM modules WHERE course_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$challenge['course_id']]);
        $selectedModules = $stmt->fetchAll();
    }
    if ($challenge['module_id']) {
        $stmt = $pdo->prepare("SELECT id, title FROM lessons WHERE module_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$challenge['module_id']]);
        $selectedLessons = $stmt->fetchAll();
    }
}

require_once 'admin_header.php';
?>

<style>
    .tc-row { background: #0f172a; border: 1px solid #1e293b; border-radius: 8px; padding: 14px; margin-bottom: 12px; }
    .tc-row textarea { font-family: 'Consolas', 'Monaco', monospace; font-size: 0.82rem; }
    .code-ta { font-family: 'Consolas', 'Monaco', monospace; font-size: 0.85rem; }
    .form-label { font-weight: 600; }
</style>

<div class="page-header d-flex flex-wrap gap-2">
    <a href="coding-challenges" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Challenges</a>
    <h4 style="margin:0;" class="align-self-center"><?= $challenge ? 'Edit Challenge' : 'New Challenge' ?></h4>
</div>

<?php if ($success_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
<?php if ($error_msg): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

<form method="POST">
    <input type="hidden" name="save_challenge" value="1">
    <input type="hidden" name="id" value="<?= $challengeId ?>">
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="admin-card">
                <h5><i class="bi bi-code-slash text-cyan me-2"></i>Problem Details</h5>

                <div class="row g-3 mt-1">
                    <div class="col-md-8">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" id="cc-title" class="form-control" required value="<?= htmlspecialchars($challenge['title'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Slug (auto)</label>
                        <input type="text" name="slug" id="cc-slug" class="form-control" value="<?= htmlspecialchars($challenge['slug'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Problem / Question</label>
                        <textarea name="problem" rows="4" class="form-control" required><?= htmlspecialchars($challenge['problem'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Input Description</label>
                        <textarea name="input_desc" rows="2" class="form-control"><?= htmlspecialchars($challenge['input_desc'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Output Description</label>
                        <textarea name="output_desc" rows="2" class="form-control"><?= htmlspecialchars($challenge['output_desc'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Constraints</label>
                        <textarea name="constraints" rows="2" class="form-control"><?= htmlspecialchars($challenge['constraints'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sample Input <span class="text-muted">(HTML/CSS: not used)</span></label>
                        <textarea name="sample_input" rows="3" class="form-control code-ta"><?= htmlspecialchars($challenge['sample_input'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sample Output <span class="text-muted">(HTML/CSS: not used)</span></label>
                        <textarea name="sample_output" rows="3" class="form-control code-ta"><?= htmlspecialchars($challenge['sample_output'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Starter Code (optional template shown to students)</label>
                        <textarea name="starter_code" id="cc-starter" rows="8" class="form-control code-ta"><?= htmlspecialchars($challenge['starter_code'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="admin-card">
                <h5><i class="bi bi-gear text-cyan me-2"></i>Placement & Settings</h5>

                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <label class="form-label">Course</label>
                        <select name="course_id" id="cc-course" class="form-select" required>
                            <option value="">— Select Course —</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= (int)$c['id'] ?>" <?= ($challenge['course_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['title']) ?> (<?= ucfirst($c['type']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Module</label>
                        <select name="module_id" id="cc-module" class="form-select">
                            <option value="">— Optional: Select Module —</option>
                            <?php foreach ($selectedModules as $m): ?>
                                <option value="<?= (int)$m['id'] ?>" <?= ($challenge['module_id'] ?? 0) == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Lesson <span class="text-muted">(optional attachment)</span></label>
                        <select name="lesson_id" id="cc-lesson" class="form-select">
                            <option value="">— Optional: Select Lesson —</option>
                            <?php foreach ($selectedLessons as $l): ?>
                                <option value="<?= (int)$l['id'] ?>" <?= ($challenge['lesson_id'] ?? 0) == $l['id'] ? 'selected' : '' ?>><?= htmlspecialchars($l['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Attach to a lesson to link it inside that lesson page.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Programming Language</label>
                        <select name="language" id="cc-language" class="form-select">
                            <?php foreach (CodingChallenge::LANGUAGES as $lang): ?>
                                <option value="<?= $lang ?>" <?= ($challenge['language'] ?? 'cpp') === $lang ? 'selected' : '' ?>><?= $challengeModel->languageLabel($lang) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Difficulty</label>
                        <select name="difficulty" class="form-select">
                            <?php foreach (CodingChallenge::DIFFICULTIES as $d): ?>
                                <option value="<?= $d ?>" <?= ($challenge['difficulty'] ?? 'easy') === $d ? 'selected' : '' ?>><?= ucfirst($d) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Marks</label>
                        <input type="number" name="marks" class="form-control" value="<?= (int)($challenge['marks'] ?? 10) ?>" min="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Passing Score (%)</label>
                        <input type="number" name="passing_score" class="form-control" value="<?= (int)($challenge['passing_score'] ?? 50) ?>" min="1" max="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= (int)($challenge['sort_order'] ?? 0) ?>" min="0">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Time Limit (seconds)</label>
                        <input type="number" name="time_limit" class="form-control" value="<?= (int)($challenge['time_limit'] ?? 5) ?>" min="1" max="30">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Memory Limit (MB)</label>
                        <input type="number" name="memory_limit" class="form-control" value="<?= (int)($challenge['memory_limit'] ?? 128) ?>" min="32" max="1024">
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="is_published" id="cc-published" class="form-check-input" value="1" <?= !empty($challenge['is_published']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="cc-published">Published (visible to students)</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-cyan w-100"><i class="bi bi-save me-1"></i> Save Challenge</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card mt-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0"><i class="bi bi-check2-square text-cyan me-2"></i>Hidden Test Cases</h5>
            <button type="button" class="btn btn-outline-cyan btn-sm" id="tc-add"><i class="bi bi-plus-lg"></i> Add Test Case</button>
        </div>
        <div id="tc-note" class="alert alert-info py-2 small" style="display:none;">
            For HTML/CSS challenges, the test <b>Input</b> field holds a JSON rule, e.g.
            <code>{"checks":[{"selector":"form"},{"selector":"input","attrs":["type=text","name=username"]},{"selector":"button"}]}</code>
            <br>For CSS: <code>{"checks":[{"css_selector":"form","css_property":"display","css_value":"flex"}]}</code>
        </div>
        <div class="form-text mb-3">Hidden test cases grade the submission. Students never see their inputs or expected outputs. Mark a case "visible" to also show it to students.</div>

        <div id="tc-list">
            <?php foreach ($testCases as $i => $tc): ?>
            <div class="tc-row" data-index="<?= $i ?>">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-semibold text-cyan">Test Case #<?= $i + 1 ?></span>
                    <div>
                        <div class="form-check form-check-inline mb-0">
                            <input type="hidden" name="tc_visible[]" value="<?= $tc['is_visible'] ? 1 : 0 ?>" class="tc-visible-hidden">
                            <input type="checkbox" class="form-check-input tc-visible" <?= $tc['is_visible'] ? 'checked' : '' ?>>
                            <label class="form-check-label">Visible to students</label>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger tc-remove"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small">Input</label>
                        <textarea name="tc_input[]" rows="3" class="form-control"><?= htmlspecialchars($tc['input_data'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Expected Output</label>
                        <textarea name="tc_output[]" rows="3" class="form-control"><?= htmlspecialchars($tc['expected_output'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="text-muted small mt-2 mb-0">Keep at least one hidden test case so the challenge can be graded.</p>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-cyan btn-lg"><i class="bi bi-save me-1"></i> Save Challenge</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var list = document.getElementById('tc-list');
    var counter = list.children.length;

    function rowHtml(index) {
        return '<div class="tc-row" data-index="' + index + '">' +
            '<div class="d-flex justify-content-between align-items-center mb-2">' +
            '<span class="fw-semibold text-cyan">Test Case #' + (index + 1) + '</span>' +
            '<div>' +
            '<div class="form-check form-check-inline mb-0">' +
            '<input type="hidden" name="tc_visible[]" value="0" class="tc-visible-hidden">' +
            '<input type="checkbox" class="form-check-input tc-visible">' +
            '<label class="form-check-label">Visible to students</label></div>' +
            '<button type="button" class="btn btn-sm btn-outline-danger tc-remove"><i class="bi bi-trash"></i></button>' +
            '</div></div>' +
            '<div class="row g-2">' +
            '<div class="col-md-6"><label class="form-label small">Input</label><textarea name="tc_input[]" rows="3" class="form-control"></textarea></div>' +
            '<div class="col-md-6"><label class="form-label small">Expected Output</label><textarea name="tc_output[]" rows="3" class="form-control"></textarea></div>' +
            '</div></div>';
    }

    function renumber() {
        var rows = list.querySelectorAll('.tc-row');
        rows.forEach(function (r, i) {
            r.querySelector('.text-cyan').textContent = 'Test Case #' + (i + 1);
            r.dataset.index = i;
        });
    }

    document.getElementById('tc-add').addEventListener('click', function () {
        list.insertAdjacentHTML('beforeend', rowHtml(counter++));
    });

    list.addEventListener('click', function (e) {
        if (e.target.closest('.tc-remove')) {
            var row = e.target.closest('.tc-row');
            row.remove();
            renumber();
        }
    });

    list.addEventListener('change', function (e) {
        if (e.target.classList.contains('tc-visible')) {
            var hidden = e.target.closest('.tc-row').querySelector('.tc-visible-hidden');
            hidden.value = e.target.checked ? '1' : '0';
        }
    });

    var title = document.getElementById('cc-title');
    var slug = document.getElementById('cc-slug');
    if (title && slug) {
        title.addEventListener('input', function () {
            if (!slug.dataset.touched) {
                slug.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            }
        });
        slug.addEventListener('input', function () { slug.dataset.touched = '1'; });
    }

    var languageSelect = document.getElementById('cc-language');
    var note = document.getElementById('tc-note');
    function updateNote() {
        var v = languageSelect.value;
        note.style.display = (v === 'html' || v === 'css') ? '' : 'none';
    }
    languageSelect.addEventListener('change', updateNote);
    updateNote();

    var courseSel = document.getElementById('cc-course');
    var moduleSel = document.getElementById('cc-module');
    var lessonSel = document.getElementById('cc-lesson');
    var keepModule = moduleSel.value;
    var keepLesson = lessonSel.value;

    courseSel.addEventListener('change', function () {
        var cid = this.value;
        moduleSel.innerHTML = '<option value="">— Optional: Select Module —</option>';
        lessonSel.innerHTML = '<option value="">— Optional: Select Lesson —</option>';
        if (!cid) return;
        fetch('coding_dropdown_ajax.php?type=modules&id=' + cid, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (mods) {
                mods.forEach(function (m) {
                    var opt = document.createElement('option');
                    opt.value = m.id;
                    opt.text = m.title;
                    moduleSel.appendChild(opt);
                });
                if (keepModule) { moduleSel.value = keepModule; keepModule = null; }
            });
    });

    moduleSel.addEventListener('change', function () {
        var mid = this.value;
        lessonSel.innerHTML = '<option value="">— Optional: Select Lesson —</option>';
        if (!mid) return;
        fetch('coding_dropdown_ajax.php?type=lessons&id=' + mid, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (lessons) {
                lessons.forEach(function (l) {
                    var opt = document.createElement('option');
                    opt.value = l.id;
                    opt.text = l.title;
                    lessonSel.appendChild(opt);
                });
                if (keepLesson) { lessonSel.value = keepLesson; keepLesson = null; }
            });
    });
});
</script>

<?php require_once 'admin_footer.php'; ?>
