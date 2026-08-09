<?php
/**
 * Execution Engine Verification Suite
 * -----------------------------------
 * Verifies stdin/stdout/stderr handling, compilation, runtime errors, timeouts,
 * exit codes, input integrity and HTML/CSS validation for every supported
 * language through the real CodeRunner / HtmlCssValidator classes.
 *
 * Usage (CLI only):
 *   php services/tests/execution_engine_test.php
 *   php services/tests/execution_engine_test.php --insecure        # skip TLS verify (local dev)
 *   php services/tests/execution_engine_test.php --lang=python     # only one language
 *   php services/tests/execution_engine_test.php --skip-timeout    # skip the ~10s infinite-loop test
 *
 * Exit code 0 = all tests passed, 1 = failures.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

$argv = $_SERVER['argv'] ?? [];
$insecure = in_array('--insecure', $argv, true);
$skipTimeout = in_array('--skip-timeout', $argv, true);
$langFilter = null;
foreach ($argv as $arg) {
    if (strpos($arg, '--lang=') === 0) {
        $langFilter = substr($arg, 7);
    }
}

require_once __DIR__ . '/../CodeRunner.php';
require_once __DIR__ . '/../HtmlCssValidator.php';

$runner = new CodeRunner();
if ($insecure) {
    $runner->setSslVerify(false);
}
$validator = new HtmlCssValidator();

$passed = 0;
$failed = 0;
$failures = [];

function t($label, $ok, $detail = '') {
    global $passed, $failed, $failures;
    if ($ok) {
        $passed++;
        echo "  PASS  $label\n";
    } else {
        $failed++;
        $failures[] = $label . ($detail !== '' ? " :: " . $detail : '');
        echo "  FAIL  $label" . ($detail !== '' ? " :: " . $detail : '') . "\n";
    }
}

function norm($s) {
    $lines = preg_split('/\r\n|\r|\n/', (string)$s);
    $lines = array_map('rtrim', $lines);
    while (!empty($lines) && end($lines) === '') {
        array_pop($lines);
    }
    return implode("\n", $lines);
}

function runCheck($runner, $lang, $code, $input, $expected, $label) {
    $res = $runner->run($lang, $code, $input, 5, 128);
    $got = norm((string)$res['output']);
    $want = norm($expected);
    $ok = $res['status'] === 'ok' && $got === $want;
    $detail = sprintf("status=%s output=%s expected=%s", $res['status'], var_export($got, true), var_export($want, true));
    t($label, $ok, $detail);
    return $res;
}

function runStatus($runner, $lang, $code, $input, $expectedStatus, $label) {
    $res = $runner->run($lang, $code, $input, 5, 128);
    $ok = $res['status'] === $expectedStatus;
    $detail = sprintf("status=%s expected=%s output=%s stderr=%s", $res['status'], $expectedStatus, var_export($res['output'], true), var_export($res['stderr'], true));
    t($label, $ok, $detail);
    return $res;
}

// ---------------------------------------------------------------------------
$SUFFIX = $langFilter !== null ? " [$langFilter]" : '';

if ($langFilter === null || $langFilter === 'cpp') {
    echo "== C++ ==\n";
    runCheck($runner, 'cpp',
        "#include <iostream>\nusing namespace std;\nint gcd(int a,int b){ return b==0 ? a : gcd(b,a%b); }\nint main(){ int a,b; cin >> a >> b; cout << gcd(a,b) << endl; return 0; }",
        "12 18", "6", "stdin \"12 18\" -> gcd(12,18)=6");
    runCheck($runner, 'cpp',
        "#include <iostream>\nusing namespace std;\nint main(){ int a; if (cin >> a) cout << \"GOT:\" << a; else cout << \"NO_INPUT\"; return 0; }",
        "", "NO_INPUT", "empty stdin detected");
    runCheck($runner, 'cpp',
        "#include <iostream>\n#include <string>\nusing namespace std;\nint main(){ string a,b; getline(cin,a); getline(cin,b); cout << a << \"|\" << b; return 0; }",
        "abc\r\ndef\r\n", "abc|def", "CRLF input normalized to LF");
    runCheck($runner, 'cpp',
        "#include <iostream>\n#include <thread>\n#include <chrono>\nusing namespace std;\nint main(){ cout << \"start\" << endl; this_thread::sleep_for(chrono::milliseconds(1200)); cout << \"end\" << endl; return 0; }",
        "", "start\nend", "waits for process to finish before reading output");
    runCheck($runner, 'cpp',
        "#include <iostream>\nusing namespace std;\nint main(){ cout << \"OK\"; return 0; }\n// \xB1\xB2\xB3",
        "", "OK", "invalid UTF-8 bytes in code do not destroy the request");
    runStatus($runner, 'cpp', "int main() { this is not valid cpp", "", 'compile_error', "compilation failure -> compile_error");
    runStatus($runner, 'cpp',
        "#include <stdexcept>\nint main(){ throw std::runtime_error(\"boom\"); }", "", 'runtime_error', "uncaught exception -> runtime_error");
    if (!$skipTimeout) {
        runStatus($runner, 'cpp', "#include <iostream>\nint main(){ long x=0; while(1){ x++; } return 0; }", "", 'timeout', "infinite loop -> timeout (Wandbox SIGKILL)");
    } else {
        echo "  SKIP  infinite loop -> timeout (--skip-timeout)\n";
    }
    // Two independent hidden-style cases for the same solution
    $sum = "#include <iostream>\nusing namespace std;\nint main(){ int a,b; cin >> a >> b; cout << a+b; return 0; }";
    runCheck($runner, 'cpp', $sum, "1 2", "3", "hidden case #1 independent");
    runCheck($runner, 'cpp', $sum, "100 200", "300", "hidden case #2 independent");
}

if ($langFilter === null || $langFilter === 'c') {
    echo "== C ==\n";
    runCheck($runner, 'c',
        "#include <stdio.h>\nint main(){ int a,b; if (scanf(\"%d %d\",&a,&b) != 2){ printf(\"PARSE_FAIL\"); return 0; } printf(\"%d\", a+b); return 0; }",
        "12 18", "30", "stdin \"12 18\" -> sum=30");
    runCheck($runner, 'c',
        "#include <stdio.h>\nint main(){ int a,b; if (scanf(\"%d %d\",&a,&b) != 2){ printf(\"PARSE_FAIL\"); return 0; } printf(\"%d\", a+b); return 0; }",
        "abc", "PARSE_FAIL", "invalid input -> program sees it (scanf fails)");
    runCheck($runner, 'c',
        "#include <stdio.h>\nint main(){ int a,b; if (scanf(\"%d %d\",&a,&b) != 2){ printf(\"PARSE_FAIL\"); return 0; } printf(\"%d\", a+b); return 0; }",
        "-5 3", "-2", "negative numbers");
    runStatus($runner, 'c', "#include <stdio.h>\nint main(){ int *p=0; *p=42; return 0; }", "", 'runtime_error', "segfault -> runtime_error");
    runStatus($runner, 'c', "#include <stdlib.h>\nint main(){ exit(3); }", "", 'runtime_error', "non-zero exit code -> runtime_error");
}

if ($langFilter === null || $langFilter === 'python') {
    echo "== Python ==\n";
    runCheck($runner, 'python', "a, b = map(int, input().split())\nprint(a + b)", "12 18", "30", "stdin \"12 18\" -> sum=30");
    runCheck($runner, 'python',
        "import sys\nnums = [int(x) for x in sys.stdin.read().split()]\nprint(sum(nums))",
        "1\n2\n3\n4", "10", "multi-line input");
    runCheck($runner, 'python', "a, b = map(int, input().split())\nprint(a + b)", "-5 3", "-2", "negative numbers");
    runCheck($runner, 'python', "a, b = map(int, input().split())\nprint(a + b)", "1000000000000000000 1000000000000000000", "2000000000000000000", "large numbers");
    runCheck($runner, 'python',
        "import sys\nline = sys.stdin.read()\nprint('LEN:' + str(len(line)))",
        "abc\xB1def", "LEN:7", "invalid UTF-8 byte in input is replaced, not dropped");
    $res = $runner->run('python', "import sys\nprint('out')\nprint('err', file=sys.stderr)", "", 5, 128);
    t("stderr captured separately", $res['status'] === 'ok' && norm($res['output']) === 'out' && norm($res['stderr']) === 'err',
        sprintf("status=%s stdout=%s stderr=%s", $res['status'], var_export($res['output'], true), var_export($res['stderr'], true)));
    runStatus($runner, 'python', "raise ValueError('boom')", "", 'runtime_error', "uncaught exception -> runtime_error");
}

if ($langFilter === null || $langFilter === 'php') {
    echo "== PHP ==\n";
    runCheck($runner, 'php',
        "<?php\n\$line = trim(fgets(STDIN));\nlist(\$a, \$b) = array_map('intval', explode(' ', \$line));\necho \$a + \$b;\n",
        "12 18", "30", "stdin \"12 18\" -> sum=30");
    runCheck($runner, 'php',
        "<?php\n\$data = trim(file_get_contents('php://stdin'));\n\$n = array_sum(array_map('intval', explode(' ', \$data)));\necho \$n;\n",
        "10 20 30", "60", "multi-value input");
    runStatus($runner, 'php', "<?php\necho str_replace(',', '', '');\nthrow new \\RuntimeException('boom');\n", "", 'runtime_error', "uncaught exception -> runtime_error");
}

if ($langFilter === null || $langFilter === 'java') {
    echo "== Java ==\n";
    runCheck($runner, 'java',
        "import java.util.*;\npublic class Main { public static void main(String[] args){ Scanner s=new Scanner(System.in); int a=s.nextInt(); int b=s.nextInt(); System.out.println(a+b);} }",
        "12 18", "30", "stdin \"12 18\" -> sum=30 (public class Main renamed)");
    runCheck($runner, 'java',
        "public class prog { public static void main(String[] a) throws Exception { System.out.println(\"start\"); Thread.sleep(1200); System.out.println(\"end\"); } }",
        "", "start\nend", "waits for process to finish before reading output");
    runStatus($runner, 'java',
        "public class prog { public static void main(String[] a) { int[] x = new int[2]; System.out.println(x[5]); } }",
        "", 'runtime_error', "ArrayIndexOutOfBounds -> runtime_error");
}

if ($langFilter === null || ($langFilter === 'html' || $langFilter === 'css')) {
    echo "== HTML / CSS (in-process validation, no code execution) ==\n";
    $htmlOk = $validator->validate(
        '<form action="/x"><input type="text" name="username"><button>Go</button></form>',
        '{"checks":[{"selector":"form"},{"selector":"input","attrs":["type=text","name=username"]},{"selector":"button"}]}'
    );
    t("HTML rule set passes", !empty($htmlOk['passed']), var_export($htmlOk, true));

    $htmlBad = $validator->validate(
        '<form><input type="text" name="username"></form>',
        '{"checks":[{"selector":"form"},{"selector":"button"}]}'
    );
    t("HTML missing element fails", empty($htmlBad['passed']) && stripos((string)($htmlBad['details'] ?? ''), 'button') !== false, var_export($htmlBad, true));

    $cssOk = $validator->validateCss('form { display: flex; justify-content: center; }', '{"checks":[{"css_selector":"form","css_property":"display","css_value":"flex"}]}');
    t("CSS rule set passes", !empty($cssOk['passed']), var_export($cssOk, true));

    $cssBad = $validator->validateCss('form { display: flex; }', '{"checks":[{"css_selector":"form","css_property":"background","css_value":"red"}]}');
    t("CSS missing property fails", empty($cssBad['passed']), var_export($cssBad, true));
}

// ---------------------------------------------------------------------------
echo "\n" . str_repeat('=', 60) . "\n";
echo "TOTAL: $passed passed, $failed failed\n";
if (!empty($failures)) {
    echo "FAILED:\n";
    foreach ($failures as $f) {
        echo "  - $f\n";
    }
}
exit($failed > 0 ? 1 : 0);
