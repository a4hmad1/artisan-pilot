<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Console;

final class Help
{
    public static function text(): string
    {
        return <<<'HELP'
Artisan Pilot — one CLI for your Laravel application

Usage: pilot [WRAPPER OPTIONS] COMMAND [ARTISAN ARGUMENTS/OPTIONS...]
       pilot [WRAPPER OPTIONS] -- EXACT_ARTISAN_COMMAND [ARGUMENTS...]
       pilot sail [SAIL ARGUMENTS...]

  commands [NAMESPACE] [--json]  Browse all installed Artisan commands
  shortcuts                      Show fixed, dynamic and custom shortcuts
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

Examples (package commands must be installed in your application):
  pilot make:model Post --migration
  pilot migrate
  pilot pail --filter=ERROR
  pilot horizon
  pilot --runner=sail pail
  pilot sail up -d
  pilot sail test
  pilot commands make
  pilot help make:model

Compatibility: pa and all ten existing shortcut executables still work:
  pas serve    pat tinker    pad down    paup up    pam migrate
  pamf migrate:fresh    pamm make:model    pamc make:controller
  parl route:list    paoc optimize:clear

Dynamic shortcuts keep their pa prefix: queue:work -> paqw; run pilot paqw.
Ambiguity stops with candidates. Artisan confirmations remain intact; no added --force.

HELP;
    }
}
