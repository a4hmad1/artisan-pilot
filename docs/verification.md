# Verification evidence

Local environment: Linux, PHP 8.5.4 CLI with PCNTL, Composer 2.9.5. Verification performed November 11–12, 2026.

## Completed

- Composer manifest validation in strict mode.
- PSR-12 checks with PHP_CodeSniffer (source and tests), with no remaining warnings.
- PHPStan level 8 across all runtime classes, with no errors.
- Final suite on real Laravel 12.72.0: **83 tests, 332 assertions, no skipped tests**.
- Final suite on real Laravel 11.57.1: **83 tests, 328 assertions, no skipped tests**, including all discovered exact command resolutions, custom commands, Tinker, route listing, SQLite migration, and confirmation handling. The local PHP 8.5 host is outside Laravel 11's published support range; CI covers PHP 8.2/8.4.
- Final suite on real Laravel 13.42.0 with Tinker 3.1.0: **83 tests, 337 assertions, no skipped tests**.
- All ten fixed executable mappings tested with a controlled Artisan fixture.
- Command arguments tested with spaces, quotes, empty values, Unicode, shell metacharacters, and option delimiters. No shell payload executed.
- Ambiguities, missing targets, reserved names, malformed JSON, alias replacement/removal, eight concurrent alias writers, and execution without PCNTL tested.
- Production migration cancellation and noninteractive confirmation cancellation verified against real Laravel. No implicit force flags are added.
- Discovery timeout, output limit, simultaneous stdout/stderr output, and nonzero discovery failures tested.
- Fresh Composer VCS installation downloaded the published package directly from GitHub and successfully ran diagnostics, a custom command, and route listing.
- Clean local Composer install and isolated `composer global install` from the extracted release ZIP; generated proxy binaries ran diagnostics, a custom Laravel command, and route listing.
- Unix PTY smoke passed: stdin/stdout are terminals, wrapper PID is retained by the executed command, and Ctrl-C terminates it with SIGINT. Script: `tools/terminal-smoke.py`.
- Real Docker runner smoke passed using the existing `php:8.3-cli-alpine` image and a disposable container. Discovery, literal arguments, stdin/stdout/stderr, exit code 42, and ambiguity output passed. The container was stopped after the test. Script: `tools/docker-smoke.py`.
- Source ZIP and Composer consumer archive checked for generated dependencies. Source ZIP includes tests/tooling; consumer archive excludes them.

## Scope and remaining checks

The command scan resolves every command exposed by the real application's JSON registry. It does **not execute every Artisan command**, which would include destructive operations. Real execution covers custom commands, Tinker, route listing, migration against SQLite in memory, and confirmations; fixed executable forwarding is covered separately for all ten mappings.

The Docker test uses a controlled Artisan fixture to isolate runner behavior. A complete Laravel application in Docker, live Sail containers, interactive Tinker under a PTY, macOS, and native Windows have not been exercised locally. Windows/macOS and supported PHP/framework combinations are configured in GitHub Actions; check the actual workflow results before claiming those environments are verified. The PTY smoke covers Linux with PCNTL; portable fallback streams and normal exit codes are tested separately.

The root package has no runtime third-party dependencies. The Laravel 11 fixture intentionally permits an end-of-life framework with known upstream advisories **only for compatibility testing**; this policy is not distributed to consumer applications.

GitHub publication and Packagist registration are separate. Until Packagist submission is completed, use the README's Composer VCS repository installation. Future Laravel major versions are not guaranteed before testing. No stable release tag is created automatically during development.

## Hosted CI publication result

The complete source was published to `main`. GitHub Actions run [43101588783](https://github.com/a4hmad1/Shortcut-arsitan---laravel-/actions/runs/43101588783) created the matrix jobs, but none started. GitHub's job annotation states: **"The job was not started because your account is locked due to a billing issue."** There are no hosted test results or release artifacts from that run.

The account owner must resolve the GitHub billing lock and rerun CI. Local checks remain the evidence listed above; hosted Windows/macOS/PHP matrix checks must not be represented as passed. No stable tag has been created while those checks remain unavailable.
