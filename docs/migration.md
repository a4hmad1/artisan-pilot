# Moving to Artisan Pilot

The repository is now `a4hmad1/artisan-pilot`, and the Composer package name is `a4hmad1/artisan-pilot`. The old GitHub URL redirects to the renamed repository, but the Composer package name inside it has changed.

In applications that already installed the old development package:

```bash
composer remove --dev a4hmad1/artisan-shortcuts
composer config --unset repositories.artisan-shortcuts
composer config repositories.artisan-pilot vcs https://github.com/a4hmad1/artisan-pilot.git
composer require --dev a4hmad1/artisan-pilot:dev-main
./vendor/bin/pilot doctor
```

Skip the repository removal command if that entry does not exist. For a global install, use `composer global remove a4hmad1/artisan-shortcuts`, configure the new global VCS repository, and run `composer global require a4hmad1/artisan-pilot:dev-main`.

Do not install both packages together: they provide the same legacy executable names. Your project `.artisan-shortcuts.json` remains in place and works with `pilot`, `pa`, and the ten original binaries. Existing dynamic abbreviations retain their `pa` prefix. Existing shell profiles do not need to change if they already point at the same Composer bin directory.

The PHP namespace `ArtisanShortcuts` is retained to avoid unnecessary source-level breakage. The main public entry point is now `pilot`; `pa` remains supported. Newly reserved management module names are `commands`, `sail`, and `shell`; an exact Artisan command with a reserved name is still available through `pilot -- COMMAND`.

If an existing custom alias is named `commands`, `sail`, or `shell`, recreate it under another name with `alias:set`, then remove the old entry. Those names now select Pilot modules.
