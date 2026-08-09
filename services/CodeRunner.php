<?php
/**
 * CodeRunner — executes user code through the Wandbox public compile API.
 *
 * Wandbox (https://wandbox.org) is a free, key-less online compiler API that
 * supports C, C++, Python, Java and PHP, so code execution works on shared
 * hosting without local compilers/interpreters.
 */

class CodeRunner {
    private $endpoint = 'https://wandbox.org/api/compile.json';

    private const COMPILERS = [
        'c' => 'gcc-head-c',
        'cpp' => 'gcc-head',
        'python' => 'cpython-3.13.8',
        'java' => 'openjdk-jdk-21+35',
        'php' => 'php-8.3.12',
    ];

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
        $code = (string)$code;
        if (strlen($code) > 51200) {
            return $this->result('error', 'Code exceeds the maximum allowed size of 50KB.');
        }

        if ($language === 'java') {
            $code = preg_replace('/\bclass\s+Main\b/', 'class prog', $code);
        }

        $payload = [
            'compiler' => $compiler,
            'code' => $code,
            'stdin' => (string)$input,
        ];

        $start = microtime(true);
        $response = $this->postJson($payload);
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
        $signal = (string)($response['signal'] ?? '');

        if ($compilerError !== '') {
            return $this->result('compile_error', $this->clip($compilerError), '', $time);
        }
        if ($status === '0') {
            return $this->result('ok', $stdout, $stderr, $time);
        }
        if ($status === '137' || $signal === 'Killed' || $signal === 'SIGKILL') {
            return $this->result('timeout', 'Execution timed out.', $stderr, $time);
        }

        $msg = trim($stderr) !== '' ? $stderr : (trim($message) !== '' ? $message : $stdout);
        return $this->result('runtime_error', $this->clip($msg), $stdout, $time);
    }

    private function postJson(array $payload) {
        if (!function_exists('curl_init')) {
            return 'cURL is not available on this server.';
        }
        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
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
