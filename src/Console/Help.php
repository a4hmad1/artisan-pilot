<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Console;

final class Help
{
    public static function text(): string
    {
        return <<<'HELP'
Artisan Pilot — direct Laravel Artisan shortcuts

Usage: pas|pat|pad|paup|pam|pamf|pamm|pamc|parl|paoc [WRAPPER OPTIONS] [ARGS...]
       pa [WRAPPER OPTIONS] COMMAND [ARTISAN ARGUMENTS/OPTIONS...]
       pilot [WRAPPER OPTIONS] -- EXACT_ARTISAN_COMMAND [ARGUMENTS...]
       pilot sail [SAIL ARGUMENTS...]

  commands [NAMESPACE] [--json]  Browse all installed Artisan commands
  shortcuts                      Show fixed, dynamic and custom shortcuts
  shell bash|zsh|fish|powershell  Print shell functions for bare shortcut commands
  doctor                         Diagnose project, runtime, commands and aliases
  alias:set NAME COMMAND [ARGS]  Create or replace a project alias
  alias:remove NAME              Remove a project alias
  alias:list                     Print project aliases as JSON
  sail [ARGS...]                 Forward directly to the application's Sail script
  help [COMMAND]                 Wrapper help, module usage, or Artisan command help
  --version                      Show package version

  --project PATH                 Find Laravel from this directory (default: cwd)
  --php PATH                     PHP executable for the local runner
  --runner local|sail|docker      Artisan execution environment (default: local)
  --container NAME               Docker container name or ID
  --container-workdir PATH       Docker project path (default: /var/www/html)

Your everyday commands:
  pas                         php artisan serve
  pat                         php artisan tinker
  pad / paup                  php artisan down / up
  pam / pamf                  php artisan migrate / migrate:fresh
  pamm Post --migration       php artisan make:model Post --migration
  pamc PostController         php artisan make:controller PostController
  parl                        php artisan route:list
  paoc                        php artisan optimize:clear

Composer installs the fixed binaries in vendor/bin. For bare commands, put that
absolute directory on PATH or activate shell functions:
  eval "$(./vendor/bin/pa shell bash)"

The shell activation also exposes unambiguous generated and custom shortcuts.
Regenerate it after adding commands or aliases. pilot remains an optional entry.

Dynamic shortcuts keep their pa prefix: queue:work -> paqw; run pa paqw, or paqw after shell activation.
Ambiguity stops with candidates. Artisan confirmations remain intact; no added --force.

HELP;
    }
}
