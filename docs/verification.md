# Verification evidence

Local environment: Linux, PHP 8.5.4 CLI with PCNTL, Composer 2.9.5. Verification performed November 11–12, 2026.

## Completed

- Composer manifest validation in strict mode.
- PSR-12 checks with PHP_CodeSniffer (source and tests), with no remaining warnings.
- PHPStan level 8 across all runtime classes, with no errors.
- Artisan Pilot suite on real Laravel 11.57.1 with Pail 1.2.7: **106 tests, 499 assertions, no skipped tests**.
- Artisan Pilot suite on real Laravel 12.72.0 with Pail 1.3.0: **106 tests, 503 assertions, no skipped tests**.
- Artisan Pilot suite on real Laravel 13.42.0 with Pail 1.3.0: **106 tests, 508 assertions, no skipped tests**.
- Tinker 3.1.0 and Sail 1.70.0 were installed in the integration fixture. Pail/Sail command discovery, native Pail help, installed `pilot` proxies, and Sail's own help/exit status were verified.
- The local PHP 8.5 host is outside Laravel 11's published support range; CI covers PHP 8.2/8.4 for Laravel 11.
- All ten fixed names executed as bare Bash commands, without `pa`/`pilot` prefixes, against a controlled Artisan fixture.
- Bare dynamic/custom functions tested with literal arguments, quoted executable paths, collision omission, wrapper options, and exit code 42.
- Zsh activation executed a bare `pamm` command successfully. Fish and PowerShell code generation is covered by unit checks; their interpreters are not installed locally.
- Real Laravel bare-command smoke passed: `pamm` generated a model and migration, `pam` migrated SQLite, and `pas` served an HTTP response. Script: `tools/bare-shortcuts-smoke.py`.
- Command arguments tested with spaces, quotes, empty values, Unicode, shell metacharacters, and option delimiters. No shell payload executed.
- Ambiguities, missing targets, reserved names, malformed JSON, alias replacement/removal, eight concurrent alias writers, and execution without PCNTL tested.
- Production migration cancellation and noninteractive confirmation cancellation verified against real Laravel. No implicit force flags are added.
- Discovery timeout, output limit, simultaneous stdout/stderr output, and nonzero discovery failures tested.
- Fresh Composer VCS installation downloaded `a4hmad1/artisan-pilot` directly from the renamed GitHub repository and successfully ran `pilot commands pail --json`, native Pail help, legacy `pa` custom-command execution, and `parl` route listing.
- A single `composer require --dev a4hmad1/artisan-pilot` was tested in a clean project with a local Composer repository providing version 1.0.0. An isolated global Composer install was tested too. All ten bare binaries passed when the Composer bin directory was on PATH, and activated bare `pamm` passed through the installed package autoloader. Public default-registry installation remains pending Packagist registration.
- Real Sail/Compose smoke passed against Laravel 13 in an isolated PHP 8.4 container: `pilot sail up -d`, discovery through `--runner=sail`, Pail help, custom arguments, exit 42, and in-memory SQLite migration. The disposable Compose service was removed afterward. Script: `tools/sail-smoke.py`.
- Unix PTY smoke passed: stdin/stdout are terminals, wrapper PID is retained by the executed command, and Ctrl-C terminates it with SIGINT. Script: `tools/terminal-smoke.py`.
- Real Docker runner smoke passed using the existing `php:8.3-cli-alpine` image and a disposable container. Discovery, literal arguments, stdin/stdout/stderr, exit code 42, and ambiguity output passed. The container was stopped after the test. Script: `tools/docker-smoke.py`.
- Source ZIP and Composer consumer archive checked for generated dependencies. Source ZIP includes tests/tooling; consumer archive excludes them.

## Scope and remaining checks

The command scan resolves every command exposed by the real application's JSON registry. It does **not execute every Artisan command**, which would include destructive operations. Real execution covers custom commands, Tinker, route listing, migration against SQLite in memory, and confirmations; fixed executable forwarding is covered separately for all ten mappings.

The Docker test uses a controlled Artisan fixture to isolate runner behavior. A real Laravel application was additionally exercised through Sail/Compose in Docker. Live Pail log streaming, interactive Tinker under a PTY, macOS, and native Windows have not been exercised locally. Windows/macOS and supported PHP/framework combinations are configured in GitHub Actions; check the actual workflow results before claiming those environments are verified. The PTY smoke covers Linux with PCNTL; portable fallback streams and normal exit codes are tested separately.

The root package has no runtime third-party dependencies. The Laravel 11 fixture intentionally permits an end-of-life framework with known upstream advisories **only for compatibility testing**; this policy is not distributed to consumer applications.

GitHub publication and Packagist registration are separate. Until Packagist submission is completed, use the README's Composer VCS repository installation. Future Laravel major versions are not guaranteed before testing. No stable release tag is created automatically during development.

## Hosted CI publication result

The complete source was published to `main`. GitHub Actions run [43104819669](https://github.com/a4hmad1/artisan-pilot/actions/runs/43104819669) created the matrix jobs, but none started. GitHub's job annotation states: **"The job was not started because your account is locked due to a billing issue."** There are no hosted test results or release artifacts from that run.

The account owner must resolve the GitHub billing lock and rerun CI. Local checks remain the evidence listed above; hosted Windows/macOS/PHP matrix checks must not be represented as passed. The shortcut-first 1.0.0 package is published with the local evidence above. Hosted Windows/macOS/PHP-matrix coverage remains unavailable because of the account billing lock; a stable version tag must not be represented as a passing hosted CI result.
