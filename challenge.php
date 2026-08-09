<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/db_connect.php';
requirePublicLogin();

require_once __DIR__ . '/lib/CodingChallenge.php';
require_once __DIR__ . '/lib/CodingSubmission.php';
require_once __DIR__ . '/lib/AccessControl.php';
require_once __DIR__ . '/lib/Enrollment.php';

$challengeModel = new CodingChallenge();
$submissionModel = new CodingSubmission();
$accessControl = new AccessControl();
$enrollment = new Enrollment();

$challengeId = (int)($_GET['id'] ?? 0);
$row = $challengeId ? $challengeModel->getByIdWithCourse($challengeId) : null;

if (!$row || $row['course_status'] !== 'published' || !$row['is_published']) {
    header('Location: courses');
    exit;
}

$userId = getPublicUserId();
$courseId = (int)$row['course_id'];

if (!$accessControl->hasCourseAccess($userId, ['id' => $courseId, 'type' => $row['course_type']])) {
    header('Location: single-course?slug=' . urlencode($row['course_slug']));
    exit;
}

$enrollment->ensureEnrollment($userId, $courseId);

$bestSubmission = $submissionModel->getBestPerChallenge($userId);
$bestPassed = false;
foreach ($bestSubmission as $bs) {
    if ((int)$bs['challenge_id'] === $challengeId && (int)$bs['passed'] === 1) {
        $bestPassed = true;
        break;
    }
}

$csrf = generateCsrfToken();
$langMonaco = $row['language'] === 'cpp' ? 'cpp' : $row['language'];
$isMarkup = in_array($row['language'], ['html', 'css'], true);

$visibleTestCases = $challengeModel->getTestCases($challengeId, true);

$page_title = htmlspecialchars($row['title']) . ' — Coding Challenge — Mtaita Tech';
$page_desc = 'Solve the ' . htmlspecialchars($row['title']) . ' coding challenge on Mtaita Tech.';
$hide_navbar = true;
require_once 'header.php';
?>

<style>
    .cc-wrap { padding-top: 0; }
    .cc-topbar { background: #0A1628; padding: 12px 0; }
    .cc-editor { min-height: 460px; border: 1px solid #334155; border-radius: 8px; overflow: hidden; }
    .cc-output { background: #0B1220; border: 1px solid #334155; border-radius: 8px; padding: 14px; min-height: 120px; font-family: 'Consolas', 'Monaco', monospace; font-size: 0.85rem; white-space: pre-wrap; color: #E2E8F0; }
    .cc-section-title { color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; margin: 16px 0 8px; }
    .cc-problem-card { background: #0F1B33; border: 1px solid #1E293B; border-radius: 8px; padding: 18px; color: #E2E8F0; }
    .cc-problem-card h1 { color: #fff; font-size: 1.35rem; }
    .cc-problem-card .text-muted { color: #94A3B8; }
    .cc-sample { background: #0B1220; border: 1px solid #334155; border-radius: 6px; padding: 10px; font-family: 'Consolas', 'Monaco', monospace; font-size: 0.82rem; color: #A5F3FC; white-space: pre-wrap; }
    .badge-lang { background: #164E63; color: #67E8F9; }
    .badge-easy { background: #14532D; color: #86EFAC; }
    .badge-medium { background: #713F12; color: #FCD34D; }
    .badge-hard { background: #7F1D1D; color: #FCA5A5; }
    .cc-btn { border-radius: 8px; font-weight: 600; }
    .btn-run { background: #0891B2; color: #fff; }
    .btn-run:hover { background: #06B6D4; color: #fff; }
    .btn-submit { background: #16A34A; color: #fff; }
    .btn-submit:hover { background: #22C55E; color: #fff; }
    .btn-submit:disabled, .btn-run:disabled { opacity: 0.6; }
    .cc-result { border-radius: 10px; }
    .cc-result.passed { background: rgba(22,163,74,0.12); border: 1px solid rgba(34,197,94,0.4); }
    .cc-result.failed { background: rgba(220,38,38,0.12); border: 1px solid rgba(248,113,113,0.4); }
    .cc-tab { cursor: pointer; }
</style>

<section style="background:var(--deep-blue);min-height:100vh;">
    <div class="cc-topbar">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <a href="single-course?slug=<?= urlencode($row['course_slug']) ?>" class="text-decoration-none small" style="color:#94A3B8;">
                        <i class="bi bi-arrow-left me-1"></i>Back to <?= htmlspecialchars($row['course_title']) ?>
                    </a>
                    <div class="fw-bold text-white small"><?= htmlspecialchars($row['title']) ?></div>
                    <div style="color:#94A3B8;font-size:0.8rem;">
                        <?= htmlspecialchars($row['module_title'] ?? '') ?> <?= $row['lesson_title'] ? '&middot; ' . htmlspecialchars($row['lesson_title']) : '' ?>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge badge-lang"><?= $challengeModel->languageLabel($row['language']) ?></span>
                    <span class="badge badge-<?= $row['difficulty'] ?>"><?= ucfirst($row['difficulty']) ?></span>
                    <span class="badge bg-secondary"><?= (int)$row['marks'] ?> marks</span>
                    <span class="badge bg-info text-dark">Pass: <?= (int)$row['passing_score'] ?>%</span>
                    <span class="badge bg-warning text-dark"><i class="bi bi-stopwatch me-1"></i><?= (int)$row['time_limit'] ?>s</span>
                    <?php if ($bestPassed): ?>
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Completed</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="cc-problem-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h1 class="mb-0"><?= htmlspecialchars($row['title']) ?></h1>
                        <a href="coding-submissions" class="btn btn-sm btn-outline-light" title="My submissions"><i class="bi bi-clock-history"></i></a>
                    </div>

                    <div class="cc-section-title">Problem</div>
                    <div style="white-space:pre-wrap;"><?= nl2br(htmlspecialchars($row['problem'])) ?></div>

                    <?php if ($row['input_desc']): ?>
                        <div class="cc-section-title">Input</div>
                        <div style="white-space:pre-wrap;"><?= nl2br(htmlspecialchars($row['input_desc'])) ?></div>
                    <?php endif; ?>

                    <?php if ($row['output_desc']): ?>
                        <div class="cc-section-title">Output</div>
                        <div style="white-space:pre-wrap;"><?= nl2br(htmlspecialchars($row['output_desc'])) ?></div>
                    <?php endif; ?>

                    <?php if ($row['constraints']): ?>
                        <div class="cc-section-title">Constraints</div>
                        <div style="white-space:pre-wrap;"><?= nl2br(htmlspecialchars($row['constraints'])) ?></div>
                    <?php endif; ?>

                    <?php if (!$isMarkup && $row['sample_input'] !== null && $row['sample_input'] !== ''): ?>
                        <div class="cc-section-title">Sample Input</div>
                        <div class="cc-sample"><?= htmlspecialchars($row['sample_input']) ?></div>
                    <?php endif; ?>

                    <?php if (!$isMarkup && $row['sample_output'] !== null && $row['sample_output'] !== ''): ?>
                        <div class="cc-section-title">Sample Output</div>
                        <div class="cc-sample"><?= htmlspecialchars($row['sample_output']) ?></div>
                    <?php endif; ?>

                    <div class="cc-section-title">Hints</div>
                    <div style="color:#94A3B8;font-size:0.85rem;">
                        Hidden test cases will be used to grade your submission. Passing at least <?= (int)$row['passing_score'] ?>% of the marks with all tests passing marks this challenge as complete.
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                    <div class="fw-bold text-white"><i class="bi bi-code-slash me-2 text-cyan"></i>Solution — <?= $challengeModel->languageLabel($row['language']) ?></div>
                    <?php if ($isMarkup): ?>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="checkbox" id="cc-auto-preview" checked>
                            <label class="form-check-label" for="cc-auto-preview" style="color:#94A3B8;font-size:0.85rem;">Live preview</label>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="cc-editor" class="cc-editor"></div>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    <?php if (!$isMarkup): ?>
                        <button class="btn btn-run cc-btn px-4" id="cc-run"><i class="bi bi-play-fill me-1"></i>Run Code</button>
                    <?php endif; ?>
                    <button class="btn btn-submit cc-btn px-4" id="cc-submit"><i class="bi bi-send me-1"></i>Submit Code</button>
                    <span id="cc-status" class="align-self-center text-muted small"></span>
                </div>

                <?php if (!$isMarkup): ?>
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-light cc-tab" type="button" data-bs-toggle="collapse" data-bs-target="#cc-custom-input" aria-expanded="false">
                        <i class="bi bi-chevron-down me-1"></i>Custom Input (Run uses this if set, else sample input)
                    </button>
                    <div class="collapse mt-2" id="cc-custom-input">
                        <textarea id="cc-custom-input-value" class="form-control bg-dark text-white" rows="3" style="font-family:Consolas,monospace;font-size:0.85rem;" placeholder="Enter test input here..."></textarea>
                    </div>
                </div>
                <?php endif; ?>

                <div class="cc-section-title">Output</div>
                <div class="cc-output" id="cc-output">
                    <span style="color:#64748B;">Run or submit your code to see results here.</span>
                </div>

                <div class="cc-section-title">Result</div>
                <div id="cc-result"></div>

                <?php if ($isMarkup): ?>
                <div class="cc-section-title">Live Preview</div>
                <iframe id="cc-preview" class="w-100" style="height:320px;background:#fff;border:1px solid #334155;border-radius:8px;"></iframe>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.52.2/min/vs/loader.js"></script>
<script>
(function () {
    var CHALLENGE = {
        id: <?= (int)$challengeId ?>,
        language: <?= json_encode($row['language']) ?>,
        isMarkup: <?= $isMarkup ? 'true' : 'false' ?>,
        sampleInput: <?= json_encode($row['sample_input'] ?? '') ?>,
        testCases: <?= json_encode(array_map(function ($t) {
            return [
                'input' => (string)($t['input_data'] ?? ''),
                'output' => (string)($t['expected_output'] ?? ''),
            ];
        }, $visibleTestCases)) ?>,
        starterCode: <?= json_encode($row['starter_code'] ?? '') ?>,
        csrf: <?= json_encode($csrf) ?>
    };

    var editor = null;
    var running = false;

    var outputEl = document.getElementById('cc-output');
    var resultEl = document.getElementById('cc-result');
    var statusEl = document.getElementById('cc-status');

    function setStatus(msg) {
        statusEl.textContent = msg || '';
    }

    function setOutput(html) {
        outputEl.innerHTML = html;
    }

    function setResult(html, cls) {
        resultEl.innerHTML = html;
        resultEl.className = 'cc-result p-3 mt-2 ' + (cls || '');
    }

    function esc(s) {
        return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function getCode() {
        return editor ? editor.getValue() : '';
    }

    function post(url, data) {
        data.csrf_token = CHALLENGE.csrf;
        var body = new URLSearchParams();
        for (var k in data) body.append(k, data[k]);
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            credentials: 'same-origin',
            body: body.toString()
        }).then(function (r) {
            return r.text().then(function (t) {
                var j = null;
                try { j = JSON.parse(t); } catch (e) {}
                if (j !== null) return { ok: r.ok, body: j };
                var preview = t.length > 400 ? t.slice(0, 400) + '\u2026' : t;
                return { ok: false, body: { error: 'Server returned a non-JSON response (HTTP ' + r.status + '). ' + preview } };
            });
        });
    }

    function languageMonaco() {
        var map = { html: 'html', css: 'css', php: 'php', python: 'python', java: 'java', c: 'c', cpp: 'cpp' };
        return map[CHALLENGE.language] || 'plaintext';
    }

    function buildPreviewHtml() {
        var code = getCode();
        if (CHALLENGE.language === 'css') {
            return '<!DOCTYPE html><html><head><style>' + code + '</style></head><body><p style="color:#888;font-family:sans-serif;padding:8px;">Add HTML in an HTML challenge to preview your CSS against real markup.</p></body></html>';
        }
        return code;
    }

    function updatePreview() {
        var frame = document.getElementById('cc-preview');
        if (frame) frame.srcdoc = buildPreviewHtml();
    }

    function initMonaco() {
        require.config({ paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.52.2/min/vs' } });
        require(['vs/editor/editor.main'], function () {
            editor = monaco.editor.create(document.getElementById('cc-editor'), {
                value: CHALLENGE.starterCode || '',
                language: languageMonaco(),
                theme: 'vs-dark',
                fontSize: 14,
                minimap: { enabled: false },
                automaticLayout: true,
                scrollBeyondLastLine: false,
                tabSize: 4
            });
            if (CHALLENGE.isMarkup && document.getElementById('cc-auto-preview').checked) {
                var debounce = null;
                editor.onDidChangeModelContent(function () {
                    if (debounce) clearTimeout(debounce);
                    debounce = setTimeout(updatePreview, 800);
                });
                updatePreview();
            }
        });
    }

    function runInputSource() {
        if (CHALLENGE.sampleInput) return CHALLENGE.sampleInput;
        if (CHALLENGE.testCases && CHALLENGE.testCases.length) return CHALLENGE.testCases[0].input;
        return '';
    }

    function currentRunInput() {
        var box = document.getElementById('cc-custom-input-value');
        var input = box ? box.value : '';
        if (input !== '') return input;
        return runInputSource();
    }

    document.addEventListener('DOMContentLoaded', function () {
        var box = document.getElementById('cc-custom-input-value');
        if (box && box.value === '') {
            box.value = runInputSource();
        }
    });
    if (document.readyState !== 'loading') {
        var box = document.getElementById('cc-custom-input-value');
        if (box && box.value === '') {
            box.value = runInputSource();
        }
    }

    document.getElementById('cc-run').addEventListener('click', function () {
        if (running) return;
        running = true;
        var btn = this;
        btn.disabled = true;
        setStatus('Running...');
        var input = currentRunInput();
        if (input === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Running with empty input',
                text: 'This challenge has no Sample Input, no visible test cases, and the Custom Input box is empty, so your program receives EMPTY stdin. Any variable read from an empty stream stays uninitialized and prints random values. Type input in the Custom Input box below, or add Sample Input / a visible test case with input in the admin.',
                confirmButtonText: 'Run anyway'
            });
        }
        post('services/code_run.php', {
            challenge_id: CHALLENGE.id,
            code: getCode(),
            language: CHALLENGE.language,
            input: input
        }).then(function (res) {
            if (!res.ok) {
                setOutput('<span style="color:#FCA5A5;">Error: ' + esc(res.body.error || 'Request failed') + '</span>');
                return;
            }
            var r = res.body;
            if (r.preview) {
                updatePreview();
                setOutput('<span style="color:#86EFAC;">✓ HTML/CSS validated for preview.</span>\n' + esc(r.output.slice(0, 4000)));
            } else if (r.status === 'ok') {
                setOutput('<span style="color:#67E8F9;">Compilation: Successful</span>\n\nInput:\n' + esc(input === '' ? '(empty)' : input) + '\n\nOutput:\n' + esc(r.output) + '\n\nExecution Time:\n' + r.time + ' seconds');
            } else if (r.status === 'compile_error') {
                setOutput('<span style="color:#FCA5A5;">Compilation Error</span>\n\n' + esc(r.output));
            } else if (r.status === 'timeout') {
                setOutput('<span style="color:#FCA5A5;">Execution timed out (' + CHALLENGE.language.toUpperCase() + ').</span>');
            } else if (r.status === 'runtime_error') {
                setOutput('<span style="color:#FCA5A5;">Runtime Error</span>\n\n' + esc(r.output));
            } else {
                setOutput('<span style="color:#FCA5A5;">' + esc(r.output || 'Unknown status') + '</span>');
            }
        }).catch(function () {
            setOutput('<span style="color:#FCA5A5;">Network error while running code.</span>');
        }).finally(function () {
            running = false;
            btn.disabled = false;
            setStatus('');
        });
    });

    document.getElementById('cc-submit').addEventListener('click', function () {
        if (running) return;
        running = true;
        var btn = this;
        btn.disabled = true;
        setStatus('Submitting & testing against hidden cases...');
        setResult('');
        post('services/code_submit.php', {
            challenge_id: CHALLENGE.id,
            code: getCode(),
            language: CHALLENGE.language
        }).then(function (res) {
            if (!res.ok) {
                setResult('<div class="text-danger fw-bold"><i class="bi bi-x-circle me-2"></i>' + esc(res.body.error || 'Request failed') + '</div>', 'failed');
                return;
            }
            var r = res.body;
            var failed = r.tests_failed;
            var cls = r.passed ? 'passed' : 'failed';
            var verdict = r.passed ? 'PASSED' : 'FAILED';
            var icon = r.passed ? '🎉' : '❌';
            var html = '<div class="d-flex justify-content-between align-items-center mb-2">' +
                '<span class="fw-bold text-white"><i class="bi ' + (r.passed ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger') + ' me-2"></i>CODING RESULT</span>' +
                '<span class="badge bg-' + (r.passed ? 'success' : 'danger') + '">' + icon + ' ' + verdict + '</span></div>';
            html += '<div class="row text-center g-2 mb-3">' +
                '<div class="col-3"><div class="text-white fw-bold fs-5">' + r.tests_total + '</div><small class="text-muted">Tests</small></div>' +
                '<div class="col-3"><div class="text-success fw-bold fs-5">' + r.tests_passed + '</div><small class="text-muted">Passed</small></div>' +
                '<div class="col-3"><div class="text-danger fw-bold fs-5">' + failed + '</div><small class="text-muted">Failed</small></div>' +
                '<div class="col-3"><div class="text-cyan fw-bold fs-5">' + r.score + '/' + r.total_marks + '</div><small class="text-muted">Score</small></div></div>';
            html += '<div class="small text-muted mb-2">Status: <b class="text-white">' + r.status.replace(/_/g, ' ').toUpperCase() + '</b> · Execution: ' + r.execution_time + 's · Pass mark: ' + r.passing_score + '%</div>';
            if (r.passed) {
                html += '<div class="text-success small"><i class="bi bi-trophy me-1"></i>Congratulations! This challenge is marked as completed in your course progress.</div>';
            }
            setResult(html, cls);
        }).catch(function () {
            setResult('<div class="text-danger fw-bold"><i class="bi bi-x-circle me-2"></i>Network error while submitting.</div>', 'failed');
        }).finally(function () {
            running = false;
            btn.disabled = false;
            setStatus('');
        });
    });

    if (CHALLENGE.isMarkup && document.getElementById('cc-auto-preview')) {
        document.getElementById('cc-auto-preview').addEventListener('change', function () {
            if (this.checked) updatePreview();
        });
    }

    initMonaco();
})();
</script>

<?php require_once 'footer.php'; ?>
