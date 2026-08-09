<?php
/**
 * CodeRunner — executes user code through the Wandbox public compile API.
 *
 * Wandbox (https://wandbox.org) is a free, key-less online compiler API that
 * supports C, C++, Python, Java and PHP. Code is compiled and executed inside
 * Wandbox's sandbox (network disabled, CPU/memory/time limits, restricted
 * filesystem), so no student code ever runs directly on this server.
 *
 * Pipeline: PHP/API -> json payload {compiler, code, stdin} -> Wandbox
 *           -> {status, program_output, program_error, compiler_error}
 *           -> PHP/API
 *
 * stdin is passed verbatim via the JSON `stdin` field. The payload is always
 * encoded to valid UTF-8 first so json_encode can never fail silently and
 * send an empty request body (which would make the program read no input).
 *
 * NOTE on time/memory limits: the Wandbox API does not accept per-request
 * time or memory values. Its sandbox enforces its own fixed wall-clock CPU
 * limit and memory cap and kills offending programs with SIGKILL (reported
 * as status 137), which this runner maps to the "timeout" status.
 */

class CodeRunner {
    private $endpoint = 'https://wandbox.org/api/compile.json';

    /** Set to false (test-only) to skip TLS verification. Production keeps true. */
    private $sslVerify = true;

    private const COMPILERS = [
        'c' => 'gcc-head-c',
        'cpp' => 'gcc-head',
        'python' => 'cpython-3.13.8',
        'java' => 'openjdk-jdk-21+35',
        'php' => 'php-8.3.12',
    ];

    private const MAX_CODE_BYTES = 51200;
    private const MAX_OUTPUT_CHARS = 4096;

    public function setSslVerify(bool $verify) {
        $this->sslVerify = $verify;
    }

    public function isAvailable() {
        return function_exists('curl_init');
    }

    public function isLanguageSupported($language) {
        if ($language === 'html' || $language === 'css') return true;
        return isset(self::COMPILERS[$language]);
    }

    public function run($language, $code, $input, $timeLimit = 5, $memoryLimit = 128) {
        $compiler = self::COMPILERS[$language] ?? null;
        if ($compiler === null) {
            return $this->result('unavailable', 'Unsupported language for execution.');
        }

        // HTML/CSS are validated in-process (no code execution), never sent to Wandbox.
        if ($language === 'html' || $language === 'css') {
            return $this->result('ok', (string)$code, '', 0);
        }

        $code = $this->toUtf8((string)$code);
        if (strlen($code) > self::MAX_CODE_BYTES) {
            return $this->result('error', 'Code exceeds the maximum allowed size of 50KB.');
        }

        // Normalize the test input so stored line endings can never corrupt it:
        // CRLF/CR -> LF, then guarantee valid UTF-8. The exact input is kept.
        $input = $this->normalizeInput($input);

        if ($language === 'java') {
            $code = preg_replace('/\bclass\s+Main\b/', 'class prog', $code);
        }

        $payload = [
            'compiler' => $compiler,
            'code' => $code,
            'stdin' => $input,
        ];

        $jsonBody = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($jsonBody === false) {
            return $this->result('error', 'The submission could not be encoded for the remote judge: ' . json_last_error_msg());
        }

        $start = microtime(true);
        $response = $this->postJson($jsonBody);
        $time = round(microtime(true) - $start, 4);

        if (!is_array($response)) {
            return $this->result('unavailable', $response === null
                ? 'The remote code judge could not be reached. Please contact the administrator.'
                : $response, '', $time);
        }

        $status = (string)($response['status'] ?? '0');
        $compilerError = (string)($response['compiler_error'] ?? '');
        $stdout = (string)($response['program_output'] ?? '');
        $stderr = (string)($response['program_error'] ?? '');
        $message = (string)($response['program_message'] ?? '');

        if ($compilerError !== '') {
            return $this->result('compile_error', $this->clip($compilerError), '', $time);
        }

        if ($status === '0') {
            return $this->result('ok', $stdout, $stderr, $time);
        }

        // Wandbox kills runaway programs (infinite loop / memory blow-up) with SIGKILL.
        if ($status === '137') {
            return $this->result('timeout', 'Execution timed out or exceeded the memory limit.', $stderr, $time);
        }

        // Common signal deaths -> readable runtime errors.
        $signals = [
            '139' => 'Segmentation fault (SIGSEGV)',
            '134' => 'Program aborted (SIGABRT, e.g. uncaught exception)',
            '136' => 'Floating point exception (SIGFPE)',
            '131' => 'Bus error (SIGBUS)',
            '132' => 'Illegal instruction (SIGILL)',
            '133' => 'Trace/breakpoint trap (SIGTRAP)',
            '143' => 'Program terminated (SIGTERM)',
        ];
        if (isset($signals[$status])) {
            $detail = trim($stderr) !== '' ? trim($stderr) : $signals[$status];
            return $this->result('runtime_error', $this->clip($detail), $stdout, $time);
        }

        // Any other non-zero exit code is a runtime error.
        $msg = trim($stderr) !== ''
            ? trim($stderr)
            : (trim($message) !== '' ? trim($message) : 'Program exited with code ' . $status . '.');
        return $this->result('runtime_error', $this->clip($msg), $stdout, $time);
    }

    /**
     * Convert any string to valid UTF-8 so json_encode() can never return
     * false. Invalid bytes (e.g. code pasted from a non-UTF-8 source) are
     * replaced instead of silently destroying the whole request body.
     */
    private function toUtf8($s) {
        $s = (string)$s;
        if (function_exists('mb_check_encoding') && mb_check_encoding($s, 'UTF-8')) {
            return $s;
        }
        if (function_exists('mb_convert_encoding')) {
            $fixed = @mb_convert_encoding($s, 'UTF-8', 'UTF-8');
            if ($fixed !== false) return $fixed;
        }
        if (function_exists('iconv')) {
            $fixed = @iconv('UTF-8', 'UTF-8//IGNORE', $s);
            if ($fixed !== false) return $fixed;
        }
        return preg_replace('/[^\x00-\x7F]/', '?', $s);
    }

    /** Normalize line endings to LF and guarantee valid UTF-8. */
    private function normalizeInput($input) {
        $s = (string)$input;
        $s = str_replace(["\r\n", "\r"], "\n", $s);
        return $this->toUtf8($s);
    }

    private function postJson($jsonBody) {
        if (!function_exists('curl_init')) {
            return 'cURL is not available on this server.';
        }
        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonBody,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => $this->sslVerify,
            CURLOPT_SSL_VERIFYHOST => $this->sslVerify ? 2 : 0,
            CURLOPT_ENCODING => '',
            CURLOPT_USERAGENT => 'MtaitaTech-Coding/1.0',
        ]);
        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $errno !== 0) {
            return null;
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return 'The remote code judge returned an invalid response (HTTP ' . $http . ').';
        }
        if ($http !== 200) {
            $message = (string)($data['message'] ?? 'Unknown error');
            return 'The remote code judge returned HTTP ' . $http . ': ' . $message;
        }
        return $data;
    }

    private function clip($text, $max = 4096) {
        $text = (string)$text;
        if (strlen($text) <= $max) return $text;
        return substr($text, 0, $max) . "\n... (output truncated)";
    }

    private function result($status, $output, $stderr = '', $time = 0.0) {
        return [
            'status' => $status,
            'output' => (string)$output,
            'stderr' => (string)$stderr,
            'time' => round((float)$time, 4),
        ];
    }
}
