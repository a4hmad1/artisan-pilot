# Usage guide

[![CI](https://github.com/a4hmad1/artisan-pilot/actions/workflows/ci.yml/badge.svg)](https://github.com/a4hmad1/artisan-pilot/actions)

**Type `pas` instead of `php artisan serve`.** Install the package in your Laravel project and use short terminal commands for Artisan.

```bash
pas                           # php artisan serve
pat                           # php artisan tinker
pam                           # php artisan migrate
pamf                          # php artisan migrate:fresh
pamm Post --migration         # php artisan make:model Post --migration
pamc PostController           # php artisan make:controller PostController
parl                          # php artisan route:list
paoc                          # php artisan optimize:clear
```

## One Composer command to install

The public installation command is:

```bash
composer require --dev a4hmad1/artisan-pilot
```

**Publication status:** Packagist submission is pending. This command will work after the repository is registered and Packagist indexes the stable tag. Until then, the GitHub installation at the end of this README is the verified alternative. A GitHub release alone does not register a Composer package.

Composer creates the fixed executables in your project's `vendor/bin`. They work immediately by their paths:

```bash
./vendor/bin/pas
./vendor/bin/pamm Post --migration
```

### Enable bare commands such as `pas`

A Composer library cannot change the PATH or functions of the terminal that installed it. If your Composer bin directory is already on PATH, the fixed names work directly. Otherwise activate the shortcuts in your current shell:

Bash:

```bash
eval "$(./vendor/bin/pa shell bash)"
```

Zsh:

```zsh
eval "$(./vendor/bin/pa shell zsh)"
```

Fish:

```fish
./vendor/bin/pa shell fish | source
```

PowerShell:

```powershell
.\vendor\bin\pa shell powershell | Out-String | Invoke-Expression
```

Then use the commands directly:

```bash
pas --port=8001
pamm Post --migration
pam
parl --path=api
```

Shell activation also creates functions for safe dynamic abbreviations and your custom aliases. It prints shell code that you explicitly load; the package does not edit your shell profile. Functions apply to the current session. Regenerate them after installing packages or changing aliases. They use the PHP/package path from activation and find the Laravel application from your current directory; regenerate them if that package installation moves or is removed.

For the fixed commands, another option is to put the **absolute** Composer bin directory on PATH using your normal shell settings. Do not permanently add a relative `vendor/bin` to PATH. Windows Composer installs `.bat` proxies; `.\vendor\bin\pas` works in PowerShell without activation.

## Fixed shortcuts

| Shortcut | Artisan command |
| --- | --- |
| `pas` | `php artisan serve` |
| `pat` | `php artisan tinker` |
| `pad` | `php artisan down` |
| `paup` | `php artisan up` |
| `pam` | `php artisan migrate` |
| `pamf` | `php artisan migrate:fresh` |
| `pamm` | `php artisan make:model` |
| `pamc` | `php artisan make:controller` |
| `parl` | `php artisan route:list` |
| `paoc` | `php artisan optimize:clear` |
| `patt` | `php artisan test` |
| `papest` | `vendor/bin/pest` |
| `papu` | `vendor/bin/phpunit` |
| `papint` | `vendor/bin/pint` |

All arguments and options are passed through. Interactive input/output, confirmations, and normal exit codes are preserved. Tinker must be installed in the application for `pat` to work.

For Laravel tests, use `patt`; `pat` keeps its original Tinker meaning. Pest, PHPUnit, and Pint shortcuts run installed PHP Composer tools. See the [testing guide](testing.md).

## Every application's commands, discovered dynamically

Built-in, custom, and installed package commands come from your application's `artisan list --format=json`; there is no hardcoded command catalogue or persistent command cache.

```bash
./vendor/bin/pa shortcuts
./vendor/bin/pa commands
./vendor/bin/pa commands make --json
```

Generated names use `pa` plus the first letter of each colon/hyphen/underscore-separated word. Examples:

| Artisan command | Generated shortcut |
| --- | --- |
| `queue:work` | `paqw` |
| `pail` | `pap` |
| `sail:install` | `pasi` |
| `vendor:sync-orders` | `pavso` |

After shell activation, run an unambiguous generated name directly, such as `pap --filter=ERROR`. Without activation, use `./vendor/bin/pa pap --filter=ERROR` or the exact command, `./vendor/bin/pa pail --filter=ERROR`. Pail, Sail, and other optional packages must be installed in the application.

A generated collision, such as `queue:work` and `queue:watch` both producing `paqw`, is never chosen silently. The CLI reports candidates, and shell activation omits that ambiguous name. Choose the exact command or define an explicit custom alias.

Fixed shortcuts retain their meanings: `pamm` always means `make:model`, even if `make:middleware` has the same generated abbreviation. The shortcuts screen shows shadowed names. Resolution order is fixed shortcuts, exact Artisan names, custom aliases, then unambiguous generated names.

## Custom shortcuts

```bash
./vendor/bin/pa alias:set models make:model Post --migration
./vendor/bin/pa alias:set palogs pail --filter=ERROR
./vendor/bin/pa alias:set paqw queue:work --once
./vendor/bin/pa alias:list
```

Refresh shell activation, then use the names directly:

```bash
eval "$(./vendor/bin/pa shell bash)"
models --factory
palogs
paqw --queue=mail
```

Replace an alias with `alias:set` again; delete one with `alias:remove NAME`. Stored arguments are JSON arrays, and new arguments are appended without shell parsing. Targets must be exact, currently discovered Artisan commands. Aliases are not recursive shell scripts.

Aliases live in the existing `.artisan-shortcuts.json`, with locked/atomic updates. Invalid files are never silently overwritten. `alias:list` and `alias:remove` work without booting Laravel. Names start with a lowercase letter and contain lowercase letters, digits, `_`, or `-` (64 characters maximum). Bare shell exports use portable lowercase letters/digits/underscores; invoke hyphenated aliases through `pa NAME`. Reserved management names and fixed/exact command names cannot be overwritten.

Shared aliases can explicitly contain destructive options, so review them before loading. Do not put secrets in alias files. Ignore the JSON and `.lock` sidecar in Git for personal aliases, or commit the JSON for team mappings.

## Sail, Docker, and project options

The same shortcuts work through an explicitly selected runner:

```bash
pas --runner=sail --host=0.0.0.0
pam --runner=sail --no-interaction
pamm --project /path/to/app Invoice --migration
pam --runner=docker --container=my-app --container-workdir=/var/www/html
```

Put wrapper options before the first Artisan argument/option. `--project PATH` searches from that directory for the nearest `artisan` file. `--php PATH` selects local PHP. The default runner is `local`; containers are never selected automatically.

Sail needs Bash, Docker, and the application's `vendor/bin/sail`. Native Windows users run Sail in WSL. To control Sail itself, the optional management CLI provides `pa sail up -d`, `pa sail test`, and other arguments forwarded to Sail. `--runner=sail` instead runs an Artisan command through Sail. `pilot` is an optional synonym for the management CLI, not required before shortcuts.

Docker needs an existing container and application/dependencies at the selected container workdir (default `/var/www/html`), plus a local `artisan` for project selection/aliases. Execution keeps stdin open and allocates a TTY only when local stdin/stdout are terminals. Discovery does not allocate a TTY. See [Sail documentation](https://laravel.com/docs/13.x/sail).

## Help, diagnostics, and raw commands

```bash
./vendor/bin/pa doctor
./vendor/bin/pa help migrate
./vendor/bin/pa -- list --format=json
./vendor/bin/pa -- some:hidden-command --help
```

Raw mode skips discovery and alias resolution. Use it for hidden commands, reserved wrapper names (`commands`, `doctor`, `shortcuts`, `shell`, `sail`, `bin`, `alias:*`), or broken boot-time output. Wrapper module usage is available through `pa help shell` or `pa alias:set --help`.

Shell functions use `--shortcut=NAME` internally so wrapper options remain available with bare dynamic/custom commands. This selects a name using the same safe resolver; it never turns arguments into shell code.

## Runtime behavior and compatibility

- PHP 8.2+ and Composer 2.2+ are required by this package. Laravel 13 itself requires PHP 8.3+. Laravel 11/12/13 are integration targets; future compatible Artisan CLI versions may work but must be tested before being advertised as verified.
- Runtime execution uses argument arrays, with no unsafe shell interpolation. Generated shell code validates function names and quotes executable paths; forwarded arguments remain arrays.
- No implicit `--force` or `--no-interaction` is added to executed commands. Destructive commands retain Laravel's own behavior; `migrate:fresh` can erase development data without a Laravel confirmation.
- Discovery is a separate noninteractive process, bounded to 30 seconds and 16 MiB. Execution has no timeout. Both inherit the environment; use an inherited `APP_ENV` or raw mode for environment-dependent command registration.
- On Unix with PCNTL, execution replaces the PHP wrapper and preserves terminal signals. Other platforms inherit standard streams through a child process; signal semantics follow PHP/OS behavior.
- Wrapper usage/discovery failures return `2`; unexpected wrapper errors return `1`; executed Artisan exit codes pass through. Shell-specific status conventions still apply, such as PowerShell's `$LASTEXITCODE`.
- Use only trusted applications and alias files. Discovery and execution each boot the application. The package has no Laravel runtime dependency or Composer plugin and does not silently modify shell profiles.

## GitHub installation before Packagist registration

```bash
composer config repositories.artisan-pilot vcs https://github.com/a4hmad1/artisan-pilot.git
composer require --dev a4hmad1/artisan-pilot:dev-main
```

This alternative needs two Composer commands because the repository has not yet been registered on Packagist. The one-command public installation above depends on that final registration.

A global installation is also supported after Packagist registration with `composer global require a4hmad1/artisan-pilot`. Add Composer's absolute global bin directory to PATH (`composer global config bin-dir --absolute`) and the fixed shortcuts are available across Laravel projects. Before registration, configure the global VCS repository first.

## Development

```bash
composer install
composer check
php tools/prepare-fixture.php
composer install --working-dir=tests/laravel
composer test
python3 tools/bare-shortcuts-smoke.py
php tools/release.php
```

See [verification evidence](verification.md), [architecture](architecture.md), [publishing](publishing.md), and [migration](migration.md). Real Laravel integration tests explicitly skip if fixture dependencies are absent. Bash/Zsh execution is verified locally; native PowerShell/Fish execution and Windows/macOS checks are listed honestly in the verification report.

MIT licensed. See [LICENSE](../LICENSE).
