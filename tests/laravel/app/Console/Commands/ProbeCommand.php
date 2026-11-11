<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class ProbeCommand extends Command
{
    protected $signature = 'shortcuts:probe-check {value?} {--fail} {--confirm}';
    protected $description = 'Integration fixture for custom command discovery and terminal behavior';

    public function handle(): int
    {
        if ($this->option('confirm') && !$this->confirm('Continue?')) {
            return 3;
        }
        $this->line('probe:' . $this->argument('value'));
        return $this->option('fail') ? 42 : 0;
    }
}
