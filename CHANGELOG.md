# Changelog

## 1.0.0

- Restore the original shortcut-first workflow: `pas`, `pam`, `pamm`, and all ten fixed commands are the primary interface.
- Export bare dynamic/custom shortcuts for Bash, Zsh, Fish, and PowerShell through explicit shell activation.
- Omit ambiguous generated functions and preserve wrapper options, argument arrays, and exit status.
- Support direct execution from Composer-installed package binaries as well as Composer proxies.
- Add executable shell tests and real bare `pas`, `pamm`, and `pam` Laravel smoke coverage.
- Document the single Composer installation and its Packagist registration prerequisite, separately from shell PATH setup.

## 1.0.0-rc.1 — release candidate

- Rename the project and Composer package to Artisan Pilot and add the main `pilot` executable.
- Preserve `pa`, all ten fixed binaries, dynamic abbreviations, and existing alias files.
- Split wrapper features into registered command modules with a lazy execution context.
- Add grouped/filterable command browsing, JSON registry output, module usage, and native Artisan help.
- Forward Sail commands directly without requiring Artisan discovery first.
- Add real Pail/Sail package discovery and help integration coverage.
- Show generated names shadowed by fixed shortcuts and report missing alias targets in diagnostics.

- Discover application, custom, and third-party Artisan commands through the selected runtime.
- Provide `pa` and ten fixed Composer binaries.
- Generate deterministic abbreviations and reject ambiguous resolutions.
- Manage project aliases using validated JSON, locking, and atomic writes.
- Preserve literal arguments, terminal streams, confirmations, and exit codes.
- Add explicit local, Sail, and Docker execution environments.
- Include PHPUnit, real Laravel fixtures, PHPStan, PSR-12 checks, and CI.
- Document local/global installation, Windows PATH, architecture, and publishing.

A stable release tag should be created after reviewing the CI results.
