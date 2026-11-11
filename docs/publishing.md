# GitHub and Packagist publishing

Repository: <https://github.com/a4hmad1/artisan-pilot>
Composer name: **a4hmad1/artisan-pilot**. The Composer name does not need to match the repository name.

## 1. Verify the exact release source

```bash
composer install
composer check
php tools/prepare-fixture.php
composer install --working-dir=tests/laravel --no-interaction
composer test
git status --short
```

Review GitHub Actions for the commit to be tagged. Review the available local and hosted results before release; do not represent a version tag as evidence that unrun platform checks passed. Review `docs/verification.md` for local evidence and platform limits. Update the changelog from release candidate to the release date and set `src/Version.php` to the release version. Do not put a `version` field in `composer.json`; Composer reads Git tags.

## 2. Push the source

```bash
git remote -v
git push -u origin main
```

If authentication fails, authenticate GitHub CLI (`gh auth login`) or configure an authorized SSH credential. Never commit tokens or put them in URLs. Repository creation is already complete.

GitHub and Packagist are separate services. A successful GitHub push alone does not register the Composer name on Packagist.

## Historical release candidate

The earlier renamed package was published as `v1.0.0-rc.1`, with `src/Version.php` set to `1.0.0-rc.1`. A prerelease provides downloadable source without claiming the unavailable hosted platform checks have passed. The release workflow marks tags containing a hyphen as prereleases. GitHub VCS consumers can install `dev-main` or explicitly require `a4hmad1/artisan-pilot:1.0.0-rc.1` after the tag is published.

## 3. Publish a stable version for one-command installation

Version 1.0.0 restores the original shortcut-first workflow. After the applicable checks pass and the changelog is final:

```bash
git tag -a v1.0.0 -m "Artisan Pilot 1.0.0"
git push origin v1.0.0
```

A tag workflow checks the source, builds `artisan-pilot-1.0.0.zip` and a SHA-256 checksum, and uploads both to a GitHub Release. Do not overwrite published tags. Fix release defects with a new patch release.

## 4. Register on Packagist

1. Sign into <https://packagist.org/> with the account authorized to publish under `a4hmad1`.
2. Open <https://packagist.org/packages/submit>.
3. Submit `https://github.com/a4hmad1/artisan-pilot.git`.
4. Confirm Packagist detects `a4hmad1/artisan-pilot`, the MIT license, PHP requirement, binaries, and `v1.0.0`.
5. Enable Packagist's GitHub integration/auto-update hook using the instructions shown in your Packagist account. Configure any required credentials in service settings, never in this repository.
6. Check <https://packagist.org/packages/a4hmad1/artisan-pilot>. If a tag is missing, use the package's Update action and inspect its update log.

Packagist submission requires your authenticated account and vendor permission. No account tokens are needed for developers installing a public package. See the [official Packagist submission guide](https://packagist.org/about#how-to-submit-packages) and [Composer binary documentation](https://getcomposer.org/doc/articles/vendor-binaries.md).

## 5. Verify public installation from a clean project

After Packagist indexes the tag, in a disposable Laravel project:

```bash
composer require --dev a4hmad1/artisan-pilot:^1.0
./vendor/bin/pa doctor
./vendor/bin/pamm Demo --migration
./vendor/bin/parl
```

Verify global installation in a disposable Composer home before using your normal global config:

```bash
COMPOSER_HOME=/tmp/artisan-pilot-global composer global require a4hmad1/artisan-pilot:^1.0
COMPOSER_HOME=/tmp/artisan-pilot-global composer global config bin-dir --absolute
```

Run the installed `pa` from the absolute global bin directory inside the test Laravel application. Use a unique temporary path on your platform; Windows users can set `COMPOSER_HOME` through PowerShell's `$env:COMPOSER_HOME`.

Before Packagist registration, the README's VCS install commands work against `dev-main`. After a stable tag exists, a VCS repository can also install `^1.0` without Packagist.

## Release ZIP

```bash
php tools/release.php
```

This creates a clean ZIP and checksum under `dist/` from tracked files, excluding dependencies and generated files. It requires Git and PHP's ZIP extension. Consumers install the extracted source with `composer install --no-dev`; contributors can install development tools normally. GitHub's automatically generated source archives remain available as well.
