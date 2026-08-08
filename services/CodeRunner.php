<?php
class CodeRunner {
    private $tmpRoot;
    private $available = null;

    public function __construct($tmpRoot = null) {
        $this->tmpRoot = $tmpRoot ?: rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'mtaita_coding';
    }

    public function isAvailable() {
        if ($this->available !== null) return $this->available;
        if (!function_exists('proc_open')) return $this->available = false;
        $disabled = array_map('trim', explode(',', ini_get('disable_functions')));
        if (in_array('proc_open', $disabled) || in_array('proc_close', $disabled)) {
            return $this->available = false;
        }
        return $this->available = true;
    }

    public function isLanguageSupported($language) {
        $binaries = $this->languageBinaries($language);
        if ($language === 'html' || $language === 'css') return true;
        if ($language === 'php') return $this->isAvailable();
        foreach ($binaries as $b) {
            if ($this->locate($b)) return true;
        }
        return false;
    }

    public function run($language, $code, $input, $timeLimit = 5, $memoryLimit = 128) {
        if (!$this->isAvailable()) {
            return $this->result('unavailable', 'Code execution is disabled on this server (proc_open is not available).');
        }
        $code = (string)$code;
        if (strlen($code) > 51200) {
            return $this->result('error', 'Code exceeds the maximum allowed size of 50KB.');
        }
        $timeLimit = max(1, min(30, (int)$timeLimit));
        $memoryLimit = max(32, min(1024, (int)$memoryLimit));

        $workDir = $this->makeWorkDir();
        if (!$workDir) {
            return $this->result('error', 'Could not create an isolated work directory.');
        }

        try {
            switch ($language) {
                case 'c':
                    return $this->runC($workDir, $code, $input, $timeLimit, $memoryLimit);
                case 'cpp':
                    return $this->runCpp($workDir, $code, $input, $timeLimit, $memoryLimit);
                case 'python':
                    return $this->runPython($workDir, $code, $input, $timeLimit, $memoryLimit);
                case 'java':
                    return $this->runJava($workDir, $code, $input, $timeLimit, $memoryLimit);
                case 'php':
                    return $this->runPhp($workDir, $code, $input, $timeLimit, $memoryLimit);
                default:
                    return $this->result('error', 'Unsupported language for execution.');
            }
        } finally {
            $this->cleanup($workDir);
        }
    }

    private function runC($dir, $code, $input, $timeLimit, $memoryLimit) {
        $compiler = $this->locate(['gcc', 'cc']);
        if (!$compiler) return $this->result('unavailable', 'C compiler (gcc) is not installed on this server.');
        $src = $dir . DIRECTORY_SEPARATOR . 'main.c';
        $bin = $dir . DIRECTORY_SEPARATOR . 'prog';
        if (file_put_contents($src, $code) === false) return $this->result('error', 'Could not write source file.');
        $cmd = escapeshellarg($compiler) . ' -O2 -w -std=c11 -o ' . escapeshellarg($bin) . ' ' . escapeshellarg($src);
        $compile = $this->execWithLimits($cmd, '', 20, 262144, $dir);
        if ($compile['timedout']) return $this->result('timeout', 'Compilation timed out.', '', $compile['time']);
        if ($compile['code'] !== 0) {
            return $this->result('compile_error', $this->clip($compile['stderr'] !== '' ? $compile['stderr'] : $compile['stdout']), '', $compile['time']);
        }
        $run = $this->execWithLimits(escapeshellarg($bin), $input, $timeLimit, $memoryLimit * 1024, $dir);
        return $this->execResult($run);
    }

    private function runCpp($dir, $code, $input, $timeLimit, $memoryLimit) {
        $compiler = $this->locate(['g++']);
        if (!$compiler) return $this->result('unavailable', 'C++ compiler (g++) is not installed on this server.');
        $src = $dir . DIRECTORY_SEPARATOR . 'main.cpp';
        $bin = $dir . DIRECTORY_SEPARATOR . 'prog';
        if (file_put_contents($src, $code) === false) return $this->result('error', 'Could not write source file.');
        $cmd = escapeshellarg($compiler) . ' -O2 -w -std=c++17 -o ' . escapeshellarg($bin) . ' ' . escapeshellarg($src);
        $compile = $this->execWithLimits($cmd, '', 20, 262144, $dir);
        if ($compile['timedout']) return $this->result('timeout', 'Compilation timed out.', '', $compile['time']);
        if ($compile['code'] !== 0) {
            return $this->result('compile_error', $this->clip($compile['stderr'] !== '' ? $compile['stderr'] : $compile['stdout']), '', $compile['time']);
        }
        $run = $this->execWithLimits(escapeshellarg($bin), $input, $timeLimit, $memoryLimit * 1024, $dir);
        return $this->execResult($run);
    }

    private function runPython($dir, $code, $input, $timeLimit, $memoryLimit) {
        $interp = $this->locate(['python3', 'python']);
        if (!$interp) return $this->result('unavailable', 'Python interpreter is not installed on this server.');
        $src = $dir . DIRECTORY_SEPARATOR . 'main.py';
        if (file_put_contents($src, $code) === false) return $this->result('error', 'Could not write source file.');
        $cmd = escapeshellarg($interp) . ' -I -E -s -B ' . escapeshellarg($src);
        $run = $this->execWithLimits($cmd, $input, $timeLimit, $memoryLimit * 1024, $dir);
        return $this->execResult($run);
    }

    private function runJava($dir, $code, $input, $timeLimit, $memoryLimit) {
        $javac = $this->locate(['javac']);
        $java = $this->locate(['java']);
        if (!$javac || !$java) return $this->result('unavailable', 'Java JDK (javac/java) is not installed on this server.');
        $src = $dir . DIRECTORY_SEPARATOR . 'Main.java';
        if (file_put_contents($src, $code) === false) return $this->result('error', 'Could not write source file.');
        $cmd = escapeshellarg($javac) . ' ' . escapeshellarg($src);
        $compile = $this->execWithLimits($cmd, '', 20, 262144, $dir);
        if ($compile['timedout']) return $this->result('timeout', 'Compilation timed out.', '', $compile['time']);
        if ($compile['code'] !== 0) {
            return $this->result('compile_error', $this->clip($compile['stderr'] !== '' ? $compile['stderr'] : $compile['stdout']), '', $compile['time']);
        }
        $cmd = escapeshellarg($java) . ' -Xmx' . (int)$memoryLimit . 'm -Xss8m -cp ' . escapeshellarg($dir) . ' Main';
        $run = $this->execWithLimits($cmd, $input, $timeLimit, $memoryLimit * 1024, $dir);
        return $this->execResult($run);
    }

    private function runPhp($dir, $code, $input, $timeLimit, $memoryLimit) {
        $interp = $this->locate([PHP_BINARY, 'php', 'php8']);
        if (!$interp) return $this->result('unavailable', 'PHP CLI is not installed on this server.');
        $src = $dir . DIRECTORY_SEPARATOR . 'main.php';
        if (file_put_contents($src, $code) === false) return $this->result('error', 'Could not write source file.');
        $cmd = escapeshellarg($interp) . ' -d memory_limit=' . (int)$memoryLimit . 'M'
             . ' -d max_execution_time=' . (int)$timeLimit
             . ' -d disable_functions=system,shell_exec,exec,passthru,proc_open,proc_close,popen,pcntl_exec,putenv,mail'
             . ' ' . escapeshellarg($src);
        $run = $this->execWithLimits($cmd, $input, $timeLimit, $memoryLimit * 1024, $dir);
        return $this->execResult($run);
    }

    private function execResult($run) {
        if ($run['timedout']) return $this->result('timeout', 'Execution timed out.', '', $run['time']);
        if ($run['code'] === 0) return $this->result('ok', $run['stdout'], $run['stderr'], $run['time']);
        $stderr = trim($run['stderr']);
        if ($stderr !== '' && !$this->looksLikeOutput($stderr)) {
            return $this->result('runtime_error', $this->clip($stderr), $run['stdout'], $run['time']);
        }
        return $this->result('runtime_error', $this->clip($stderr !== '' ? $stderr : $run['stdout']), '', $run['time']);
    }

    private function execWithLimits($cmd, $input, $timeLimit, $memKb, $cwd = null) {
        $tmpRoot = $cwd ?: $this->makeWorkDir();
        if (!$tmpRoot) {
            return ['code' => -1, 'stdout' => '', 'stderr' => 'Could not create work directory', 'time' => 0, 'timedout' => false];
        }

        if (PHP_OS_FAMILY !== 'Windows' && $memKb > 0) {
            $cmd = "sh -c " . escapeshellarg('ulimit -v ' . (int)$memKb . '; ulimit -t ' . (int)$timeLimit . '; exec ' . $cmd);
        }

        $descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $env = [
            'PATH' => (string)getenv('PATH'),
            'HOME' => $tmpRoot,
            'TMPDIR' => $tmpRoot,
            'TMP' => $tmpRoot,
            'LANG' => 'C.UTF-8',
            'LC_ALL' => 'C.UTF-8',
        ];

        $proc = @proc_open($cmd, $descriptors, $pipes, $tmpRoot, $env);
        if (!is_resource($proc)) {
            if ($cwd === null) $this->cleanup($tmpRoot);
            return ['code' => -1, 'stdout' => '', 'stderr' => 'Failed to start process', 'time' => 0, 'timedout' => false];
        }

        stream_set_blocking($pipes[0], false);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        if ($input !== '' && $input !== null) {
            $chunks = str_split($input, 4096);
            foreach ($chunks as $chunk) {
                $written = @fwrite($pipes[0], $chunk);
                if ($written === false) break;
            }
        }
        fclose($pipes[0]);

        $stdout = '';
        $stderr = '';
        $start = microtime(true);
        $deadline = $start + (float)$timeLimit + 1.0;
        $timedout = false;

        while (true) {
            $status = @proc_get_status($proc);
            if ($status === false) break;
            $stdout .= (string)stream_get_contents($pipes[1]);
            $stderr .= (string)stream_get_contents($pipes[2]);
            if (!$status['running']) break;
            if (microtime(true) > $deadline) {
                $timedout = true;
                $this->killProcess($proc, $status['pid'] ?? null);
                break;
            }
            usleep(10000);
        }

        $stdout .= (string)stream_get_contents($pipes[1]);
        $stderr .= (string)stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        if ($timedout) {
            $exitCode = -1;
            @proc_close($proc);
        } else {
            $final = @proc_get_status($proc);
            $exitCode = isset($final['exitcode']) ? (int)$final['exitcode'] : -1;
            @proc_close($proc);
        }

        $time = round(microtime(true) - $start, 4);
        if ($cwd === null) $this->cleanup($tmpRoot);

        return ['code' => $exitCode, 'stdout' => $stdout, 'stderr' => $stderr, 'time' => $time, 'timedout' => $timedout];
    }

    private function killProcess($proc, $pid) {
        if (PHP_OS_FAMILY === 'Windows' && $pid) {
            @proc_terminate($proc);
            $kill = @proc_open('taskkill /F /T /PID ' . (int)$pid, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $kp);
            if (is_resource($kill)) {
                @fclose($kp[0]);
                @stream_get_contents($kp[1]);
                @proc_close($kill);
            }
        } else {
            @proc_terminate($proc);
            if ($pid) @exec('kill -9 ' . (int)$pid . ' 2>/dev/null');
        }
    }

    private function locate(array $candidates) {
        foreach ($candidates as $c) {
            if (PHP_OS_FAMILY === 'Windows') {
                $cmd = 'where ' . $c;
            } else {
                $cmd = 'command -v ' . $c . ' 2>/dev/null';
            }
            $descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
            $proc = @proc_open($cmd, $descriptors, $pipes);
            if (!is_resource($proc)) continue;
            fclose($pipes[0]);
            $out = (string)stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            @fclose($pipes[2]);
            $code = (int)proc_close($proc);
            if ($code === 0 && trim($out) !== '') {
                return trim(explode("\n", $out)[0]);
            }
        }
        return null;
    }

    private function languageBinaries($language) {
        switch ($language) {
            case 'c': return ['gcc', 'cc'];
            case 'cpp': return ['g++'];
            case 'python': return ['python3', 'python'];
            case 'java': return ['javac', 'java'];
            case 'php': return ['php'];
            default: return [];
        }
    }

    private function makeWorkDir() {
        if (!is_dir($this->tmpRoot)) @mkdir($this->tmpRoot, 0700, true);
        if (!is_dir($this->tmpRoot)) return null;
        $dir = $this->tmpRoot . DIRECTORY_SEPARATOR . 'run_' . bin2hex(random_bytes(6));
        if (!@mkdir($dir, 0700, true)) return null;
        return $dir;
    }

    private function cleanup($dir) {
        if (!$dir || !is_dir($dir)) return;
        $items = scandir($dir);
        if ($items === false) { @rmdir($dir); return; }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path) && !is_link($path)) {
                $this->cleanup($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    private function clip($text, $max = 4096) {
        $text = (string)$text;
        if (mb_strlen($text) <= $max) return $text;
        return mb_substr($text, 0, $max) . "\n... (output truncated)";
    }

    private function looksLikeOutput($text) {
        return preg_match('/[\x20-\x7E\n\r\t]/', $text) > 0 && !preg_match('/warning:|error:|Exception|Traceback|Notice|Fatal/i', $text);
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
