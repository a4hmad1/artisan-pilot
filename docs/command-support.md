# Complete discovered command audit

These reports cover every command exposed by the real fixture application's Artisan JSON registry on Laravel 11, 12, and 13. Fixtures include application commands, Tinker, Pail, Sail, and Laravel's Collision test command. Pest/PHPUnit/Pint execution is checked separately below.

For each discovered command, the audit verifies exact name resolution and runs its native help through the package. Every available fixed or unambiguous shortcut is also checked against that native help output. Ambiguous and shadowed generated names are not silently executed.

**Help verification does not execute destructive handlers.** Database deletion, deployment, external notifications, interactive services, coverage, and arbitrary third-party plugins are not certified by a help scan. Hidden commands omitted by Artisan's JSON are available through raw mode but are outside this registry snapshot.

## Registry results

| Framework | Exact resolution + native help | Shortcut help comparisons | Failures | JSON evidence |
| --- | --- | --- | --- | --- |
| Laravel Framework 11.57.1 | 121 passed | 65 passed | 0 | [report](reports/commands-laravel11.json) |
| Laravel Framework 12.72.0 | 125 passed | 67 passed | 0 | [report](reports/commands-laravel12.json) |
| Laravel Framework 13.42.0 | 130 passed | 68 passed | 0 | [report](reports/commands-laravel13.json) |

## Test runner execution

The following are actual test executions, including expected failing tests, filtering, parallel runs with two processes, direct tool entry points, and Pint checks.

| Laravel | Mode | Pest | PHPUnit | ParaTest | Cases | JSON evidence |
| --- | --- | --- | --- | --- | --- | --- |
| v11.57.1 | phpunit | not installed | 11.5.57 | v7.8.6 | 7 passed | [report](reports/runner-phpunit-laravel11.json) |
| v11.57.1 | pest | v4.7.8 | 12.5.33 | v7.20.0 | 8 passed | [report](reports/runner-pest-laravel11.json) |
| v12.72.0 | phpunit | not installed | 11.5.57 | v7.8.6 | 7 passed | [report](reports/runner-phpunit-laravel12.json) |
| v12.72.0 | pest | v4.7.8 | 12.5.33 | v7.20.0 | 8 passed | [report](reports/runner-pest-laravel12.json) |
| v13.42.0 | phpunit | not installed | 11.5.57 | v7.8.6 | 7 passed | [report](reports/runner-phpunit-laravel13.json) |
| v13.42.0 | pest | v5.3.5 | 13.4.5 | v7.27.5 | 8 passed | [report](reports/runner-pest-laravel13.json) |

Local reports use PHP 8.5.4. Laravel 11 is tested locally outside its published PHP 8.5 support range; supported PHP combinations are configured in CI. Hosted CI availability and unverified platforms are recorded in [verification evidence](verification.md).

## Full command lists

A dash in the shortcut column means the generated name was ambiguous, shadowed, or unsuitable for shell export. Use the exact command through `pa COMMAND`. The live registry is always authoritative; these lists are versioned test snapshots, not hardcoded runtime mappings.

<details>
<summary>Laravel 11 — 121 commands</summary>

| Artisan command | Shortcut checked | Exact/help |
| --- | --- | --- |
| `_complete` | — | Pass |
| `about` | `paa` | Pass |
| `auth:clear-resets` | `paacr` | Pass |
| `cache:clear` | — | Pass |
| `cache:forget` | `pacf` | Pass |
| `cache:prune-stale-tags` | `pacpst` | Pass |
| `channel:list` | `pacl` | Pass |
| `clear-compiled` | — | Pass |
| `completion` | — | Pass |
| `config:cache` | — | Pass |
| `config:clear` | — | Pass |
| `config:publish` | `pacp` | Pass |
| `config:show` | `pacs` | Pass |
| `db` | — | Pass |
| `db:monitor` | `padm` | Pass |
| `db:seed` | — | Pass |
| `db:show` | — | Pass |
| `db:table` | `padt` | Pass |
| `db:wipe` | `padw` | Pass |
| `docs` | — | Pass |
| `down` | `pad` | Pass |
| `env` | `pae` | Pass |
| `env:decrypt` | `paed` | Pass |
| `env:encrypt` | `paee` | Pass |
| `event:cache` | — | Pass |
| `event:clear` | — | Pass |
| `event:generate` | `paeg` | Pass |
| `event:list` | `pael` | Pass |
| `help` | `pah` | Pass |
| `install:api` | `paia` | Pass |
| `install:broadcasting` | `paib` | Pass |
| `invoke-serialized-closure` | `paisc` | Pass |
| `key:generate` | `pakg` | Pass |
| `lang:publish` | `palp` | Pass |
| `list` | `pal` | Pass |
| `make:cache-table` | `pamct` | Pass |
| `make:cast` | — | Pass |
| `make:channel` | — | Pass |
| `make:class` | — | Pass |
| `make:command` | — | Pass |
| `make:component` | — | Pass |
| `make:controller` | `pamc` | Pass |
| `make:enum` | — | Pass |
| `make:event` | — | Pass |
| `make:exception` | — | Pass |
| `make:factory` | — | Pass |
| `make:interface` | — | Pass |
| `make:job` | `pamj` | Pass |
| `make:job-middleware` | `pamjm` | Pass |
| `make:listener` | `paml` | Pass |
| `make:mail` | — | Pass |
| `make:middleware` | — | Pass |
| `make:migration` | — | Pass |
| `make:model` | `pamm` | Pass |
| `make:notification` | `pamn` | Pass |
| `make:notifications-table` | `pamnt` | Pass |
| `make:observer` | `pamo` | Pass |
| `make:policy` | — | Pass |
| `make:provider` | — | Pass |
| `make:queue-batches-table` | `pamqbt` | Pass |
| `make:queue-failed-table` | `pamqft` | Pass |
| `make:queue-table` | `pamqt` | Pass |
| `make:request` | — | Pass |
| `make:resource` | — | Pass |
| `make:rule` | — | Pass |
| `make:scope` | — | Pass |
| `make:seeder` | — | Pass |
| `make:session-table` | `pamst` | Pass |
| `make:test` | — | Pass |
| `make:trait` | — | Pass |
| `make:view` | `pamv` | Pass |
| `migrate` | `pam` | Pass |
| `migrate:fresh` | `pamf` | Pass |
| `migrate:install` | — | Pass |
| `migrate:refresh` | — | Pass |
| `migrate:reset` | — | Pass |
| `migrate:rollback` | — | Pass |
| `migrate:status` | — | Pass |
| `model:prune` | — | Pass |
| `model:show` | — | Pass |
| `optimize` | `pao` | Pass |
| `optimize:clear` | `paoc` | Pass |
| `package:discover` | `papd` | Pass |
| `pail` | `pap` | Pass |
| `queue:clear` | `paqc` | Pass |
| `queue:failed` | — | Pass |
| `queue:flush` | — | Pass |
| `queue:forget` | — | Pass |
| `queue:listen` | `paql` | Pass |
| `queue:monitor` | `paqm` | Pass |
| `queue:prune-batches` | `paqpb` | Pass |
| `queue:prune-failed` | `paqpf` | Pass |
| `queue:restart` | — | Pass |
| `queue:retry` | — | Pass |
| `queue:retry-batch` | `paqrb` | Pass |
| `queue:work` | `paqw` | Pass |
| `route:cache` | — | Pass |
| `route:clear` | — | Pass |
| `route:list` | `parl` | Pass |
| `sail:add` | `pasa` | Pass |
| `sail:install` | — | Pass |
| `sail:publish` | — | Pass |
| `schedule:clear-cache` | `pascc` | Pass |
| `schedule:finish` | `pasf` | Pass |
| `schedule:interrupt` | — | Pass |
| `schedule:list` | — | Pass |
| `schedule:run` | `pasr` | Pass |
| `schedule:test` | `past` | Pass |
| `schedule:work` | `pasw` | Pass |
| `schema:dump` | `pasd` | Pass |
| `serve` | `pas` | Pass |
| `shortcuts:probe-check` | `paspc` | Pass |
| `storage:link` | — | Pass |
| `storage:unlink` | `pasu` | Pass |
| `stub:publish` | — | Pass |
| `test` | `patt` | Pass |
| `tinker` | `pat` | Pass |
| `up` | `paup` | Pass |
| `vendor:publish` | `pavp` | Pass |
| `view:cache` | — | Pass |
| `view:clear` | — | Pass |

</details>

<details>
<summary>Laravel 12 — 125 commands</summary>

| Artisan command | Shortcut checked | Exact/help |
| --- | --- | --- |
| `_complete` | — | Pass |
| `about` | `paa` | Pass |
| `auth:clear-resets` | `paacr` | Pass |
| `cache:clear` | — | Pass |
| `cache:forget` | `pacf` | Pass |
| `cache:prune-stale-tags` | `pacpst` | Pass |
| `channel:list` | `pacl` | Pass |
| `clear-compiled` | — | Pass |
| `completion` | — | Pass |
| `config:cache` | — | Pass |
| `config:clear` | — | Pass |
| `config:publish` | `pacp` | Pass |
| `config:show` | `pacs` | Pass |
| `db` | — | Pass |
| `db:monitor` | `padm` | Pass |
| `db:seed` | — | Pass |
| `db:show` | — | Pass |
| `db:table` | `padt` | Pass |
| `db:wipe` | `padw` | Pass |
| `docs` | — | Pass |
| `down` | `pad` | Pass |
| `env` | `pae` | Pass |
| `env:decrypt` | `paed` | Pass |
| `env:encrypt` | `paee` | Pass |
| `event:cache` | — | Pass |
| `event:clear` | — | Pass |
| `event:generate` | `paeg` | Pass |
| `event:list` | `pael` | Pass |
| `help` | `pah` | Pass |
| `install:api` | `paia` | Pass |
| `install:broadcasting` | `paib` | Pass |
| `invoke-serialized-closure` | `paisc` | Pass |
| `key:generate` | `pakg` | Pass |
| `lang:publish` | `palp` | Pass |
| `list` | `pal` | Pass |
| `make:cache-table` | `pamct` | Pass |
| `make:cast` | — | Pass |
| `make:channel` | — | Pass |
| `make:class` | — | Pass |
| `make:command` | — | Pass |
| `make:component` | — | Pass |
| `make:config` | — | Pass |
| `make:controller` | `pamc` | Pass |
| `make:enum` | — | Pass |
| `make:event` | — | Pass |
| `make:exception` | — | Pass |
| `make:factory` | — | Pass |
| `make:interface` | — | Pass |
| `make:job` | `pamj` | Pass |
| `make:job-middleware` | `pamjm` | Pass |
| `make:listener` | `paml` | Pass |
| `make:mail` | — | Pass |
| `make:middleware` | — | Pass |
| `make:migration` | — | Pass |
| `make:model` | `pamm` | Pass |
| `make:notification` | `pamn` | Pass |
| `make:notifications-table` | `pamnt` | Pass |
| `make:observer` | `pamo` | Pass |
| `make:policy` | — | Pass |
| `make:provider` | — | Pass |
| `make:queue-batches-table` | `pamqbt` | Pass |
| `make:queue-failed-table` | `pamqft` | Pass |
| `make:queue-table` | `pamqt` | Pass |
| `make:request` | — | Pass |
| `make:resource` | — | Pass |
| `make:rule` | — | Pass |
| `make:scope` | — | Pass |
| `make:seeder` | — | Pass |
| `make:session-table` | `pamst` | Pass |
| `make:test` | — | Pass |
| `make:trait` | — | Pass |
| `make:view` | `pamv` | Pass |
| `migrate` | `pam` | Pass |
| `migrate:fresh` | `pamf` | Pass |
| `migrate:install` | — | Pass |
| `migrate:refresh` | — | Pass |
| `migrate:reset` | — | Pass |
| `migrate:rollback` | — | Pass |
| `migrate:status` | — | Pass |
| `model:prune` | — | Pass |
| `model:show` | — | Pass |
| `optimize` | `pao` | Pass |
| `optimize:clear` | `paoc` | Pass |
| `package:discover` | `papd` | Pass |
| `pail` | `pap` | Pass |
| `queue:clear` | `paqc` | Pass |
| `queue:failed` | — | Pass |
| `queue:flush` | — | Pass |
| `queue:forget` | — | Pass |
| `queue:listen` | `paql` | Pass |
| `queue:monitor` | `paqm` | Pass |
| `queue:pause` | `paqp` | Pass |
| `queue:prune-batches` | `paqpb` | Pass |
| `queue:prune-failed` | `paqpf` | Pass |
| `queue:restart` | — | Pass |
| `queue:resume` | — | Pass |
| `queue:retry` | — | Pass |
| `queue:retry-batch` | `paqrb` | Pass |
| `queue:work` | `paqw` | Pass |
| `reload` | `par` | Pass |
| `route:cache` | — | Pass |
| `route:clear` | — | Pass |
| `route:list` | `parl` | Pass |
| `sail:add` | `pasa` | Pass |
| `sail:install` | — | Pass |
| `sail:publish` | — | Pass |
| `schedule:clear-cache` | `pascc` | Pass |
| `schedule:finish` | `pasf` | Pass |
| `schedule:interrupt` | — | Pass |
| `schedule:list` | — | Pass |
| `schedule:run` | `pasr` | Pass |
| `schedule:test` | `past` | Pass |
| `schedule:work` | `pasw` | Pass |
| `schema:dump` | `pasd` | Pass |
| `serve` | `pas` | Pass |
| `shortcuts:probe-check` | `paspc` | Pass |
| `storage:link` | — | Pass |
| `storage:unlink` | `pasu` | Pass |
| `stub:publish` | — | Pass |
| `test` | `patt` | Pass |
| `tinker` | `pat` | Pass |
| `up` | `paup` | Pass |
| `vendor:publish` | `pavp` | Pass |
| `view:cache` | — | Pass |
| `view:clear` | — | Pass |

</details>

<details>
<summary>Laravel 13 — 130 commands</summary>

| Artisan command | Shortcut checked | Exact/help |
| --- | --- | --- |
| `_complete` | — | Pass |
| `about` | `paa` | Pass |
| `auth:clear-resets` | `paacr` | Pass |
| `cache:clear` | — | Pass |
| `cache:forget` | `pacf` | Pass |
| `cache:prune-stale-tags` | `pacpst` | Pass |
| `channel:list` | `pacl` | Pass |
| `clear-compiled` | — | Pass |
| `completion` | — | Pass |
| `config:cache` | — | Pass |
| `config:clear` | — | Pass |
| `config:publish` | `pacp` | Pass |
| `config:show` | `pacs` | Pass |
| `db` | — | Pass |
| `db:monitor` | `padm` | Pass |
| `db:seed` | — | Pass |
| `db:show` | — | Pass |
| `db:table` | `padt` | Pass |
| `db:wipe` | `padw` | Pass |
| `dev` | — | Pass |
| `dev:list` | `padl` | Pass |
| `docs` | — | Pass |
| `down` | `pad` | Pass |
| `env` | `pae` | Pass |
| `env:decrypt` | `paed` | Pass |
| `env:encrypt` | `paee` | Pass |
| `event:cache` | — | Pass |
| `event:clear` | — | Pass |
| `event:generate` | `paeg` | Pass |
| `event:list` | `pael` | Pass |
| `help` | `pah` | Pass |
| `install:api` | `paia` | Pass |
| `install:broadcasting` | `paib` | Pass |
| `invoke-serialized-closure` | `paisc` | Pass |
| `key:generate` | `pakg` | Pass |
| `lang:publish` | `palp` | Pass |
| `list` | `pal` | Pass |
| `make:cache-table` | `pamct` | Pass |
| `make:cast` | — | Pass |
| `make:channel` | — | Pass |
| `make:class` | — | Pass |
| `make:command` | — | Pass |
| `make:component` | — | Pass |
| `make:config` | — | Pass |
| `make:controller` | `pamc` | Pass |
| `make:enum` | — | Pass |
| `make:event` | — | Pass |
| `make:exception` | — | Pass |
| `make:factory` | — | Pass |
| `make:interface` | — | Pass |
| `make:job` | `pamj` | Pass |
| `make:job-middleware` | `pamjm` | Pass |
| `make:listener` | `paml` | Pass |
| `make:mail` | — | Pass |
| `make:middleware` | — | Pass |
| `make:migration` | — | Pass |
| `make:model` | `pamm` | Pass |
| `make:notification` | `pamn` | Pass |
| `make:notifications-table` | `pamnt` | Pass |
| `make:observer` | `pamo` | Pass |
| `make:policy` | — | Pass |
| `make:provider` | — | Pass |
| `make:queue-batches-table` | `pamqbt` | Pass |
| `make:queue-failed-table` | `pamqft` | Pass |
| `make:queue-table` | `pamqt` | Pass |
| `make:request` | — | Pass |
| `make:resource` | — | Pass |
| `make:rule` | — | Pass |
| `make:scope` | — | Pass |
| `make:seeder` | — | Pass |
| `make:session-table` | `pamst` | Pass |
| `make:test` | — | Pass |
| `make:trait` | — | Pass |
| `make:view` | `pamv` | Pass |
| `migrate` | `pam` | Pass |
| `migrate:fresh` | `pamf` | Pass |
| `migrate:install` | — | Pass |
| `migrate:refresh` | — | Pass |
| `migrate:reset` | — | Pass |
| `migrate:rollback` | — | Pass |
| `migrate:status` | — | Pass |
| `model:prune` | — | Pass |
| `model:show` | — | Pass |
| `optimize` | `pao` | Pass |
| `optimize:clear` | `paoc` | Pass |
| `package:discover` | `papd` | Pass |
| `pail` | `pap` | Pass |
| `queue:clear` | `paqc` | Pass |
| `queue:failed` | — | Pass |
| `queue:flush` | — | Pass |
| `queue:forget` | — | Pass |
| `queue:listen` | `paql` | Pass |
| `queue:monitor` | `paqm` | Pass |
| `queue:pause` | `paqp` | Pass |
| `queue:prune-batches` | `paqpb` | Pass |
| `queue:prune-failed` | `paqpf` | Pass |
| `queue:restart` | — | Pass |
| `queue:resume` | — | Pass |
| `queue:retry` | — | Pass |
| `queue:retry-batch` | `paqrb` | Pass |
| `queue:work` | `paqw` | Pass |
| `reload` | `par` | Pass |
| `route:cache` | — | Pass |
| `route:clear` | — | Pass |
| `route:list` | `parl` | Pass |
| `sail:add` | `pasa` | Pass |
| `sail:install` | — | Pass |
| `sail:publish` | — | Pass |
| `schedule:clear-cache` | `pascc` | Pass |
| `schedule:finish` | `pasf` | Pass |
| `schedule:interrupt` | — | Pass |
| `schedule:list` | — | Pass |
| `schedule:pause` | — | Pass |
| `schedule:resume` | — | Pass |
| `schedule:run` | — | Pass |
| `schedule:test` | `past` | Pass |
| `schedule:work` | `pasw` | Pass |
| `schema:dump` | `pasd` | Pass |
| `serve` | `pas` | Pass |
| `session:garbage-collect` | `pasgc` | Pass |
| `shortcuts:probe-check` | `paspc` | Pass |
| `storage:link` | — | Pass |
| `storage:unlink` | `pasu` | Pass |
| `stub:publish` | — | Pass |
| `test` | `patt` | Pass |
| `tinker` | `pat` | Pass |
| `up` | `paup` | Pass |
| `vendor:publish` | `pavp` | Pass |
| `view:cache` | — | Pass |
| `view:clear` | — | Pass |

</details>
