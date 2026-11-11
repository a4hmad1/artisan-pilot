# Verification evidence

Local environment: Linux, PHP 8.5.4 CLI with PCNTL, Composer 2.9.5. Verification performed November 11–12, 2026.

## 1.1.1 refactor verification

The patch extracts immutable local, Sail, and Docker strategies behind `Execution\Runner`, retains literal argument arrays, and allows explicit runner injection. Runtime configuration is captured at invocation construction. Prose comments and generated shell comments were removed; PHPStan type contracts and executable syntax remain.

The final code passed strict Composer validation, PSR-12, PHPStan level 8, and **128 tests / 558 assertions** against the Laravel 11.57.1 fixture. The six existing Laravel 11/12/13 PHPUnit/Pest projects again passed **45 runner checks**, including deliberate failing tests returning exit 1, parallel execution, filtering, and Pint. Real Docker, Sail, bare shortcut, and PTY smoke checks passed again. The complete help audit also passed again: 121/125/130 exact command checks and 65/67/68 shortcut comparisons, with zero failures. The results match the published JSON snapshots. The full 128-test suite was rerun on Laravel 11 only for this patch; the prior Laravel 12/13 full-suite evidence below belongs to 1.1.0.

The default Packagist metadata endpoint still returned HTTP 404 on November 12. GitHub-hosted Windows/macOS/PHP matrix results remain unavailable under the account billing lock described below.

## 1.1.0 completed checks

- Composer manifest validation in strict mode.
- PSR-12 checks with PHP_CodeSniffer (source and tests), with no remaining warnings.
- PHPStan level 8 across all runtime classes, with no errors.
- Artisan Pilot suite on real Laravel 11.57.1 with Pail 1.2.7: **126 tests, 549 assertions, no skipped tests**.
- Artisan Pilot suite on real Laravel 12.72.0 with Pail 1.3.0: **126 tests, 553 assertions, no skipped tests**.
- Artisan Pilot suite on real Laravel 13.42.0 with Pail 1.3.0: **126 tests, 558 assertions, no skipped tests**.
- Full registry audit: **376 command registrations** across Laravel 11/12/13 passed exact resolution and native help checks. **200 shortcut help comparisons** matched their native targets. See the [full command audit and JSON evidence](command-support.md).
- Six separate PHPUnit/Pest projects passed **45 actual runner checks**: success/failure exit codes, filters, parallel execution, direct/generic PHP tool entry points, and Pint. Pest 4.7.8 was verified with Laravel 11/12; Pest 5.3.5 with Laravel 13.
- Real Sail and Docker execution additionally passed PHPUnit/Pest, parallel testing, and Pint checks using the Pest 13 fixture.
- The README was redesigned with centered project branding, truthful status badges, an original light/dark SVG wordmark, and linked usage/testing/command audit guides. Both SVG themes were rendered and visually inspected. GitHub's Markdown API rendered the README successfully (5 images with alt text, 1 table, 7 code blocks), and local link targets were checked. The complete GitHub layout has not been browser-inspected.
- Tinker 3.1.0 and Sail 1.70.0 were installed in the integration fixture. Pail/Sail command discovery, native Pail help, installed `pilot` proxies, and Sail's own help/exit status were verified.
- The local PHP 8.5 host is outside Laravel 11's published support range; CI covers PHP 8.2/8.4 for Laravel 11.
- Fixed Artisan names (including `patt`) executed as bare Bash commands, without `pa`/`pilot` prefixes, against a controlled Artisan fixture.
- Bare dynamic/custom functions tested with literal arguments, quoted executable paths, collision omission, wrapper options, and exit code 42.
- Zsh activation executed a bare `pamm` command successfully. Fish and PowerShell code generation is covered by unit checks; their interpreters are not installed locally.
- Real Laravel bare-command smoke passed: `pamm` generated a model and migration, `pam` migrated SQLite, and `pas` served an HTTP response. Script: `tools/bare-shortcuts-smoke.py`.
- Command arguments tested with spaces, quotes, empty values, Unicode, shell metacharacters, and option delimiters. No shell payload executed.
- Ambiguities, missing targets, reserved names, malformed JSON, alias replacement/removal, eight concurrent alias writers, and execution without PCNTL tested.
- Production migration cancellation and noninteractive confirmation cancellation verified against real Laravel. No implicit force flags are added.
- Discovery timeout, output limit, simultaneous stdout/stderr output, and nonzero discovery failures tested.
- Fresh Composer VCS installation downloaded `a4hmad1/artisan-pilot` directly from the renamed GitHub repository and successfully ran `pilot commands pail --json`, native Pail help, legacy `pa` custom-command execution, and `parl` route listing.
- A single `composer require --dev a4hmad1/artisan-pilot` was tested in a clean project with a local Composer repository providing version 1.0.0. An isolated global Composer install was tested too. The original ten bare binaries passed when the Composer bin directory was on PATH, and activated bare `pamm` passed through the installed package autoloader. Public default-registry installation remains pending Packagist registration.
- Real Sail/Compose smoke passed against Laravel 13 in an isolated PHP 8.4 container: `pilot sail up -d`, discovery through `--runner=sail`, Pail help, custom arguments, exit 42, and in-memory SQLite migration. The disposable Compose service was removed afterward. Script: `tools/sail-smoke.py`.
- Unix PTY smoke passed: stdin/stdout are terminals, wrapper PID is retained by the executed command, and Ctrl-C terminates it with SIGINT. Script: `tools/terminal-smoke.py`.
- Real Docker runner smoke passed using the existing `php:8.3-cli-alpine` image and a disposable container. Discovery, literal arguments, stdin/stdout/stderr, exit code 42, and ambiguity output passed. The container was stopped after the test. Script: `tools/docker-smoke.py`.
- The v1.1 source archive was installed into clean local and isolated global Composer homes. All 16 binaries were present, and the new direct/activated test-tool entry points preserved literal arguments. The default Packagist endpoint still returned 404 at final publication checking.
- Source ZIP and Composer consumer archive checked for generated dependencies. Source ZIP includes tests/tooling; consumer archive excludes them.

## Scope and remaining checks

The command scan resolves every command exposed by the real application's JSON registry. It does **not execute every Artisan command**, which would include destructive operations. Real execution covers custom commands, Tinker, route listing, migration against SQLite in memory, and confirmations; fixed executable forwarding is covered separately for all ten mappings.

The Docker test uses a controlled Artisan fixture to isolate runner behavior. A real Laravel application was additionally exercised through Sail/Compose in Docker. Live Pail log streaming, interactive Tinker under a PTY, macOS, and native Windows have not been exercised locally. Windows/macOS and supported PHP/framework combinations are configured in GitHub Actions; check the actual workflow results before claiming those environments are verified. The PTY smoke covers Linux with PCNTL; portable fallback streams and normal exit codes are tested separately.

The root package has no runtime third-party dependencies. The Laravel 11 fixture intentionally permits an end-of-life framework with known upstream advisories **only for compatibility testing**; this policy is not distributed to consumer applications.

GitHub publication and Packagist registration are separate. Until Packagist submission is completed, use the README's Composer VCS repository installation. Future Laravel major versions are not guaranteed before testing. No stable release tag is created automatically during development.

## Hosted CI publication result

The complete source was published to `main`. GitHub Actions run [43114549562](https://github.com/a4hmad1/artisan-pilot/actions/runs/43114549562) created the matrix jobs, but none started. GitHub's job annotation states: **"The job was not started because your account is locked due to a billing issue."** There are no hosted test results or release artifacts from that run.

The account owner must resolve the GitHub billing lock and rerun CI. Local checks remain the evidence listed above; hosted Windows/macOS/PHP matrix checks must not be represented as passed. The shortcut-first 1.0.0 package is published with the local evidence above. Hosted Windows/macOS/PHP-matrix coverage remains unavailable because of the account billing lock; a stable version tag must not be represented as a passing hosted CI result.
