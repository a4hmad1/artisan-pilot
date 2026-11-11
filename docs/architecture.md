# Architecture

The package is an out-of-process CLI client, not a Laravel service provider. Composer installs the fixed Artisan/tool shortcut binaries, the `pa` management entry point, and optional `pilot` synonym. The fixed names (`pas`, `pam`, `pamm`, etc.) are the primary user interface. Each tiny entry point shares `bin/bootstrap.php`, loads Composer's generated autoloader (including local/global proxy support), and invokes `Application`.

- `ShellIntegration`: exports fixed shortcuts, unique dynamic abbreviations, and valid custom names as quoted shell functions. Ambiguous names are omitted. `--shortcut=NAME` preserves wrapper option parsing for named invocation. No profile changes are performed.
- `Options`: parses only wrapper options before the command; keeps remaining tokens untouched.
- `Project`: locates the nearest ancestor containing `artisan`, independently of the package install path.
- `ComposerTools` and `ToolShortcuts`: locate project PHP Composer binaries, reject unsafe names/non-PHP launchers, honor local bin configuration, and reserve the direct Pest/PHPUnit/Pint names.
- `Invocation`: selects an execution strategy once, then delegates argument construction through `Execution\Runner`. The optional injected runner allows extensions without editing command dispatch.
- `Execution\LocalRunner`, `Execution\SailRunner`, and `Execution\DockerRunner`: immutable strategies for local PHP, the host Sail script, and Docker exec. Each owns the Artisan and Composer-tool argument construction for its environment.
- `Process`: bounded, file-backed noninteractive discovery capture and unbounded inherited-terminal execution. Unix PCNTL replaces the process; the portable fallback uses `proc_open` with shell bypass.
- `CommandCatalog`: validates Artisan JSON, includes command aliases exposed by that JSON, deduplicates and sorts the current application's commands.
- `Resolver`: stable fixed shortcuts, exact names, explicit project aliases, and generated initialisms; ambiguity fails closed.
- `AliasStore`: validates project configuration; serializes concurrent read-modify-write operations with a persistent sidecar lock and replaces a same-directory temporary file atomically.
- `Application`: parses the entry point, handles help/raw mode, builds context, and routes to a registered wrapper module or Artisan.
- `Console\CommandRegistry`: explicitly registers independent alias, diagnostics, command-browser, shortcut, and Sail handlers. Implementations follow `Console\Command`; `Application` accepts a registry for extension/testing.
- `Console\Context`: owns per-invocation dependencies and lazily memoizes discovery. Alias listing/removal and direct Sail operations do not boot Laravel.

Pail and other package Artisan commands flow through the normal registry without dedicated wrappers. `pilot sail` directly invokes the host Sail script; `--runner=sail` instead adds `artisan` to its forwarded arguments.

No application source is loaded into the wrapper process. Application dependencies and framework internals cannot collide with wrapper dependencies. There are no runtime Composer dependencies apart from PHP, JSON, and Composer's runtime API.

## Code conventions

Command modules implement the Command pattern through `Console\Command`. Execution environments implement the Strategy pattern through `Execution\Runner`. `Invocation` keeps the public command-building API stable while choosing the strategy at construction. The registry explicitly composes command modules; no service locator or runtime class scanning is needed.

Strategies capture their configuration at construction. Mutating an `Options` object later does not change an existing invocation. Runner implementations only construct argument arrays; `Process` owns execution and terminal behavior. A runner extension must return an executable and literal arguments, and must never construct a shell command string.

Implementation prose belongs in this guide rather than inline comments. Compact PHPDoc annotations remain only for machine-checked array element types and callable contracts that PHP 8.2 cannot express natively. Required executable shebangs, PHP attributes, URLs, shell argument expansion, and README formatting remain intact.

## Deliberate boundaries

No persistent discovery cache; installing a package is immediately reflected. No implicit container detection. No arbitrary user shell templates or recursive custom mappings. Shell functions are generated only through explicit activation, using validated names and quoted executable paths. No auto-updating package code. No global alias file crossing project boundaries. No auto-confirmation or added force flags.

Discovery uses the application's default/inherited environment. For command registration conditional on `--env`, use an inherited `APP_ENV` shared by discovery and execution, or raw mode. Commands hidden from Artisan's JSON list are accessible by raw mode. Reserved wrapper management names are accessible in Artisan with raw mode too.

Local alias files use filesystem permissions inherited from temporary file creation (normally owner-readable/writable). Lock files are intentionally persistent; unlinking a lock after unlocking can introduce races. Atomicity/locking assumes a local filesystem implementing PHP's `flock` and `rename` semantics. Network filesystems may not provide the same guarantees.

## Compatibility strategy

Test Laravel 11 on PHP 8.2/8.4, Laravel 12 on PHP 8.2/8.5, and Laravel 13 on PHP 8.3/8.5 in CI, plus macOS/Windows jobs. Framework major versions are fixture dependencies only. The package's runtime contract is Artisan JSON and the CLI, so newer compatible frameworks need no runtime constraint change, but must be tested before being advertised as verified.
