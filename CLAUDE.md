# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## ⚠️ No tests — validate manually

This project has **no working test suite** (`tests/` contains only the unmodified Symfony demo `DefaultControllerTest.php`). Every change must be validated **manually in the running app**. Make small, incremental changes and verify each one before moving on. Do not assume a change works because it compiles.

The UI and all user-facing strings are in **Bulgarian**. Preserve existing Bulgarian wording when editing templates, form labels, and generated documents.

## What this is

A Symfony **3.2** web app (PHP 7.0, runs on `>=5.5.9`) for a municipality (община Велинград) to manage land-use **contracts** (договори) between the municipality and land **holders** (ползватели/holders). It imports cadastral land data, lets a clerk select land parcels, generates RTF contract documents, and produces statistics/Excel exports. All code lives in a single bundle: **`RozzBundle`** (`src/RozzBundle`).

## Commands

Symfony console is `bin/console` (or `php bin/console`). Common:

```bash
composer install                          # install deps (runs Symfony post-install scripts)
php bin/console cache:clear --env=dev      # clear cache after config/route/annotation changes
php bin/console cache:clear --env=prod
php bin/console doctrine:schema:update --dump-sql   # preview schema changes (no migrations bundle)
php bin/console server:run                 # built-in PHP dev server (if available)
```

Web entry points: `web/app.php` (prod), `web/app_dev.php` (dev, with profiler).

### Database
Doctrine ORM on **MySQL/MariaDB**, db name `symfony`. There is **no migrations setup** — schema is managed via raw SQL:
- `sql/schema.sql` — the table definitions (also serves as the entity-relationship reference).
- `sql/20250404155509.sql` — large seed/data dump.
- `sql/update_kat.sql`, `update_kat.sql` — patches.

Configure DB credentials in `app/config/parameters.yml` (copy from `parameters.yml.dist`). For the Docker setup use host `rozz_db`.

### Docker
`ops/docker-compose.yml` runs the app (nginx + php7.0-fpm, built from `ops/Dockerfile`) and a `mariadb` container. App on port 80, DB on 3306. Xdebug is preconfigured (idekey `PHPSTORM`, client port 9003).

## Architecture

### Layering
`Controller → Service → Repository/Entity`. Controllers are thin-ish but contain a lot of form-building logic inline. Business logic lives in **Services** (`src/RozzBundle/Services`), wired in `app/config/services.yml`. Services that need DB/config take `@service_container` or the entity manager via constructor injection. Routing is **annotation-based** (`@Route` on controller actions); `app/config/routing.yml` just imports the controller dir.

### Core domain model (see `sql/schema.sql` for full columns)
- **`Lands`** — a land parcel (имот): `num`, `area`, plus FKs to lookup tables **`Mest`** (местност/locality), **`Zem`** (землище/land district), **`Ntp`** (начин на трайно ползване / land-use type), **`Kat`** (category), **`Doc`** (document). Parcels are imported from cadastral files.
- **`Holders`** — contract counterparties (EGN/Bulstat + address). **`Mayors`** (кметове) and **`Examiners`** (комисия/examiners) are the other contract parties.
- **`Contracts`** — a finalized contract: links a `User`, `Holder`, `Mayor`, and one-or-many `Examiners` (both ManyToOne `examiner` and ManyToMany `examiners` exist). Has `type`, `status`, `num`, `start`/`expire` dates, generated `docFile` (RTF), and `annex_contract_id` for annexes.
- **`UsedArea`** — the join between a `Contract` and a `Land`: how much `area` of a parcel at what `price` a contract uses, plus an **`active`** flag. This is the heart of capacity accounting.
- **`NewContracts`** + **`SelectedLand`** — a **per-user draft/working area** (one `NewContracts` row per user, `UNIQUE(user_id)`). The clerk first selects parcels into `SelectedLand`, fills in contract details on `NewContracts`, previews, then commits to a real `Contracts` + `UsedArea` rows. `NewContracts.type` (1/2/3) = blank new / from-existing / annex.

### Contract creation flow (ContractsController + ContractService)
1. `/newContract/type` — choose contract type (нов празен / от съществуващ / анекс).
2. `/selected` & `/holder/select` — pick land parcels (`SelectedLand`) and the holder.
3. `contract/preview` → `contract/create` — `ContractService::checkDataForContract` validates, then `persistContract` writes the `Contracts` + `UsedArea` and renders the RTF.
4. `ContractService::createRtf` / `populateRtf` — fills an RTF template by `str_replace` of `%placeholders%`, converting UTF-8 → **Windows-1251** via `iconv`. RTF/Excel/SQL output dirs are `web/files/{,exl_files,sql_files}` (see `rtf_dir`/`exl_dir`/`sql_dir` params).

### Free-area / agro-year accounting (LandsService)
A parcel's **free area** = `land.area − Σ(active UsedArea)` (`LandsService::calculateLandFreeAreaForSelectedLands` / free-area calc). The **agro year** (`ApplicationSettings.agro_year`) defines the active window: `LandsService::setUsedAreaForActiveContracts` recomputes every `UsedArea.active` flag — a row is active iff its contract's `start <= agroYearStart`, `expire >= agroYearEnd`, and `status != 2`. This runs in batches with `em->clear()` for memory. Recent work centers on this logic — be careful: changing activeness affects which parcels appear free for new contracts. Set via `/set-current-agro-year`.

### Cadastral import (CadService, InputDataController, camellia)
`CadService` parses CAD/ZEM cadastral files into `Cad`/`Contur`/`Line`/`Point` geometry + `Lands`. `src/camellia-master` (`Camellia_Converter`) is a vendored pure-PHP charset converter used for the legacy Cyrillic encodings in those files. CSV import via `CsvService`. Big CAD uploads require raised `php.ini` limits (see `README.md`: `memory_limit`, `post_max_size`, `upload_max_filesize`).

### Other services
- `ExcelService` (liuggio/ExcelBundle) — statistics export at `/contract/statistics` + download route.
- `BackUpService` — DB dump/import via `/settings/db/dump` and `/settings/db/import`.
- `EgnBulstatService` — validates Bulgarian EGN (personal ID) / Bulstat (company ID) numbers; route `/valid-egn-bulstat`.
- `AppSettingsService`, `FormHandler`, `NewContractService` — settings and form/draft helpers.

### Auth
Form login (`security.yml`), `User` entity with bcrypt, provider by `username`. Single firewall over `/.*`; most routes require auth except `/login`, `/register`, `/page`, `home`.

## Conventions & gotchas
- Repository classes are inconsistently cased (`contractsRepository`, `landsRepository`, `docRepository` lower-case alongside PascalCase ones) — match the existing file when referencing.
- Templates use the `@Rozz/...` Twig namespace (`src/RozzBundle/Resources/views`); base layout is `main.html.twig`. Front-end assets (bootstrap, JS) live under `Resources/bs` and `web/`.
- App version lives in both `app/config/config.yml` (`app.version`) and `parameters.yml`; exposed to Twig as the global `version`.
- Timezone is hard-set to `Europe/Sofia` in `app/AppKernel.php`.
- After changing routes, annotations, services, or config, **clear the cache** or routes/services won't update.
