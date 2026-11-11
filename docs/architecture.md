# Architecture

The package is an out-of-process CLI client, not a Laravel service provider. Composer installs `pilot`, the compatible `pa` entry point, and ten fixed binaries. Each tiny entry point shares `bin/bootstrap.php`, loads Composer's generated autoloader (including local/global proxy support), and invokes `Application`.

- `Options`: parses only wrapper options before the command; keeps remaining tokens untouched.
- `Project`: locates the nearest ancestor containing `artisan`, independently of the package install path.
- `Invocation`: builds argument arrays for the explicit local, Sail, or Docker runner.
- `Process`: bounded, file-backed noninteractive discovery capture and unbounded inherited-terminal execution. Unix PCNTL replaces the process; the portable fallback uses `proc_open` with shell bypass.
- `CommandCatalog`: validates Artisan JSON, includes command aliases exposed by that JSON, deduplicates and sorts the current application's commands.
- `Resolver`: stable fixed shortcuts, exact names, explicit project aliases, and generated initialisms; ambiguity fails closed.
- `AliasStore`: validates project configuration; serializes concurrent read-modify-write operations with a persistent sidecar lock and replaces a same-directory temporary file atomically.
- `Application`: parses the entry point, handles help/raw mode, builds context, and routes to a registered wrapper module or Artisan.
- `Console\CommandRegistry`: explicitly registers independent alias, diagnostics, command-browser, shortcut, and Sail handlers. Implementations follow `Console\Command`; `Application` accepts a registry for extension/testing.
- `Console\Context`: owns per-invocation dependencies and lazily memoizes discovery. Alias listing/removal and direct Sail operations do not boot Laravel.

Pail and other package Artisan commands flow through the normal registry without dedicated wrappers. `pilot sail` directly invokes the host Sail script; `--runner=sail` instead adds `artisan` to its forwarded arguments.

No application source is loaded into the wrapper process. Application dependencies and framework internals cannot collide with wrapper dependencies. There are no runtime Composer dependencies apart from PHP, JSON, and Composer's runtime API.

## Deliberate boundaries

No persistent discovery cache; installing a package is immediately reflected. No implicit container detection. No shell aliases, arbitrary shell templates, or recursive custom mappings. No auto-updating package code. No global alias file crossing project boundaries. No auto-confirmation or added force flags.

Discovery uses the application's default/inherited environment. For command registration conditional on `--env`, use an inherited `APP_ENV` shared by discovery and execution, or raw mode. Commands hidden from Artisan's JSON list are accessible by raw mode. Reserved wrapper management names are accessible in Artisan with raw mode too.

Local alias files use filesystem permissions inherited from temporary file creation (normally owner-readable/writable). Lock files are intentionally persistent; unlinking a lock after unlocking can introduce races. Atomicity/locking assumes a local filesystem implementing PHP's `flock` and `rename` semantics. Network filesystems may not provide the same guarantees.

## Compatibility strategy

Test Laravel 11 on PHP 8.2/8.4, Laravel 12 on PHP 8.2/8.5, and Laravel 13 on PHP 8.3/8.5 in CI, plus macOS/Windows jobs. Framework major versions are fixture dependencies only. The package's runtime contract is Artisan JSON and the CLI, so newer compatible frameworks need no runtime constraint change, but must be tested before being advertised as verified.
