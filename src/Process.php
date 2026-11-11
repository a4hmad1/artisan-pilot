<?php

declare(strict_types=1);

namespace ArtisanShortcuts;

final class Process
{
    /** @param non-empty-list<string> $command */
    public function capture(array $command, string $cwd, float $timeout = 30.0): string
    {
        if (!function_exists('proc_open')) {
            throw new ShortcutException('PHP proc_open is disabled. Enable it in your CLI PHP configuration.');
        }
        // File-backed capture avoids Windows pipe blocking and stdout/stderr deadlocks.
        $stdout = tmpfile();
        $stderr = tmpfile();
        if ($stdout === false || $stderr === false) {
            if (is_resource($stdout)) {
                fclose($stdout);
            }
            if (is_resource($stderr)) {
                fclose($stderr);
            }
            throw new ShortcutException('Cannot create discovery output files. Check your temporary directory.');
        }
        try {
            $process = proc_open(
                $command,
                [0 => ['pipe', 'r'], 1 => $stdout, 2 => $stderr],
                $pipes,
                $cwd,
                null,
                ['bypass_shell' => true]
            );
            if (!is_resource($process)) {
                throw new ShortcutException('Cannot start discovery. Check PHP and the selected runner.');
            }
            fclose($pipes[0]);
            $started = microtime(true);
            $failure = null;
            $exit = -1;
            try {
                do {
                    $status = proc_get_status($process);
                    $size = (fstat($stdout)['size'] ?? 0) + (fstat($stderr)['size'] ?? 0);
                    if ($size > 16 * 1024 * 1024) {
                        $failure = 'Discovery exceeded 16 MiB. Check application boot output.';
                        break;
                    }
                    if (!$status['running']) {
                        $exit = $status['exitcode'];
                        break;
                    }
                    if (microtime(true) - $started > $timeout) {
                        $failure = 'Discovery timed out. Check application boot, Docker, or Sail.';
                        break;
                    }
                    usleep(10000);
                } while (true);
            } finally {
                if ($failure !== null) {
                    proc_terminate($process);
                    usleep(50000);
                    if (proc_get_status($process)['running']) {
                        proc_terminate($process, 9);
                    }
                }
                $closed = proc_close($process);
            }
            if ($failure !== null) {
                throw new ShortcutException($failure);
            }
            rewind($stdout);
            rewind($stderr);
            $output = stream_get_contents($stdout, 16 * 1024 * 1024);
            $errors = stream_get_contents($stderr, 16 * 1024 * 1024);
            if ($output === false || $errors === false) {
                throw new ShortcutException('Cannot read captured Artisan output.');
            }
            $exit = $exit >= 0 ? $exit : $closed;
            if ($exit !== 0) {
                throw new ShortcutException(
                    "Artisan discovery failed (exit {$exit}). " . trim($errors . "\n" . $output)
                );
            }
            return $output;
        } finally {
            fclose($stdout);
            fclose($stderr);
        }
    }

    /** Replace the process on Unix; inherit terminal streams on other platforms.
     *  @param non-empty-list<string> $command
     */
    public function run(array $command, string $cwd): int
    {
        if (function_exists('pcntl_exec') && PHP_OS_FAMILY !== 'Windows') {
            if (!chdir($cwd)) {
                throw new ShortcutException("Cannot enter project: {$cwd}");
            }
            $binary = array_shift($command);
            // pcntl_exec does not search PATH. Resolve runner executables explicitly.
            if (!str_contains($binary, '/')) {
                $binary = $this->executable($binary);
            }
            pcntl_exec($binary, $command);
            throw new ShortcutException("Cannot execute {$binary}. Check its path and permissions.");
        }
        if (!function_exists('proc_open')) {
            throw new ShortcutException('PHP proc_open is disabled. Enable it in your CLI PHP configuration.');
        }
        $process = proc_open(
            $command,
            [0 => STDIN, 1 => STDOUT, 2 => STDERR],
            $pipes,
            $cwd,
            null,
            ['bypass_shell' => true]
        );
        if (!is_resource($process)) {
            throw new ShortcutException('Cannot start Artisan. Check the selected runner.');
        }
        $exit = proc_close($process);
        return $exit >= 0 ? $exit : 1;
    }

    private function executable(string $name): string
    {
        foreach (explode(PATH_SEPARATOR, getenv('PATH') ?: '') as $directory) {
            $candidate = $directory . DIRECTORY_SEPARATOR . $name;
            if ($directory !== '' && is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }
        throw new ShortcutException("Executable {$name} was not found in PATH.");
    }
}
