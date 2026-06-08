# Release v0.2.0 — Delivery runbook (Windows / XAMPP)

This folder is a self-contained release bundle for the РОЗЗ contract app:

- `migration.sql` — the **single** combined DB migration (run once).
- `seed_legacy_template.php` — optional helper to create the legacy contract template.
- `DELIVERY.md` — this runbook.

**Golden rule:** the production database must not be broken. Everything here is **additive** (new columns / new tables / nullable foreign keys). The only change to existing rows is `UPDATE contracts SET currency='BGN'` (all pre-euro contracts become лева — intended). The real safety net is a **verified backup** (step 1).

> Dry-run the whole runbook on a copy (restored prod DB + deployed code) first. Only run on production after that passes.

## What this release contains
Currency BGN/EUR per contract, admin-editable & versioned contract templates, `.docx` download (replaces the old RTF), amount-in-words, default prices per НТП×землище with auto-fill, contracts-list ordering/filter/pagination fixes, and several smaller fixes. **No new Composer dependencies** — the `.docx` generator is pure PHP and needs only `ext-zip` (standard in XAMPP).

---

## 1. Back up (non-negotiable)
- Export the full DB via **phpMyAdmin → Export** (or `mysqldump -u root -p <dbname> > backup_YYYYMMDD.sql`).
- Also use the app's own backup: **Настройки → „Запази цялата база данни във файл"**.
- **Restore the dump into a scratch DB to confirm it works** — an untested backup is not a backup.
- Make a zip copy of the current app folder (code rollback point). If using git: note `git rev-parse HEAD`.

## 2. Deploy the code
- `git fetch && git checkout <new-tag>` on the server, **or** copy the files manually.
- If copying manually, don't miss: `src/RozzBundle/Resources/views/Pagination/pagination.html.twig`, `web/js/templateEditor.js`, `web/css/templateEditor.css`, this whole `sql/migrations/20260608_release/` folder, and the updated `app/config/config.yml` + `app/config/services.yml`.
- **Do not overwrite** `app/config/parameters.yml` (holds the prod DB credentials; unchanged).
- Confirm `extension=zip` is enabled in XAMPP's `php.ini` (needed for `.docx`).
- Optional cleanup (now unused): delete `web/files/rtf.rtf`, `web/files/rtf.htm`, `web/files/~$rtf.rtf`.

## 3. Apply the DB migration (phpMyAdmin — one file, once)
1. **Pre-check** (so the non-idempotent SQL can't half-apply): confirm the `contracts` table has **no** `currency` and **no** `template_id` column, and there are **no** `contract_template` / `ntp_zem_price` tables.
2. Open **phpMyAdmin → (the app DB) → SQL**, paste the contents of `migration.sql`, and run.
3. Verify:
   - `SELECT currency, COUNT(*) FROM contracts GROUP BY currency;` → everything is `BGN`.
   - `SHOW TABLES LIKE 'contract_template';` and `SHOW TABLES LIKE 'ntp_zem_price';` → both present.
   - `SHOW COLUMNS FROM contracts LIKE 'template_id';` → present.
4. If any statement errors, **stop** and assess against the backup (DDL auto-commits, so earlier statements already applied).

## 4. Clear the production cache (required)
New routes / services / config / templates won't take effect until the cache is rebuilt.
- Delete the folder `var\cache\prod` (Explorer, or `rmdir /s /q var\cache\prod`). Symfony rebuilds it on the next request.
- CLI equivalent (if available): `C:\xampp\php\php.exe bin\console cache:clear --env=prod`.

## 5. Create the legacy contract template
Existing contracts have `template_id = NULL` and render via a "legacy" template that reproduces the current wording. Create it one of two ways:
- **Default (no CLI):** log in as an **admin** and open any contract's view (`/contract/view/<id>`). The legacy template is created automatically from the bundled files and marked active.
- **Or run:** `C:\xampp\php\php.exe sql\migrations\20260608_release\seed_legacy_template.php` (from the project root).
- Verify: `SELECT id, name, is_active FROM contract_template;` → one active row „Стандартен договор (наследен)". No per-contract backfill is needed.

## 6. Smoke test
- **Existing contracts unchanged:** open a few old contracts — wording/prices identical; the **„Изтегли файл"** download is now a `.docx` that opens cleanly in Word with correct Cyrillic.
- **Contracts list:** newest first; type a filter and change pages → the filter persists; pagination shows „Предишна/Следваща страница".
- **New contract:** create one end-to-end — the **Валута** selector defaults to Евро and sits above the green „Направи договор" button; the preview's template dropdown works; saving produces a correct contract.
- **Admin-only pages:** „Настройки → Шаблони за договори" (editor loads; placeholders are blue chips) and „Цени по НТП и землище" (matrix saves). A non-admin user must get **403** on `/settings/templates` and `/settings/prices`.
- **Default-price auto-fill:** set a default for an НТП×землище, then add a matching parcel to a contract → the price pre-fills; the „Постави стандартни цени" button works.

---

## DB-safety summary
- Additive-only DDL + one intended backfill (`currency='BGN'`). No drops / retypes / deletes of existing data.
- New tables/columns use `utf8 / utf8_unicode_ci`, matching the existing schema.
- The only Cyrillic written during the upgrade is the legacy template body, seeded **through the app** (PHP/Doctrine over the app's UTF8 connection) — not via raw SQL — so there's no encoding risk.

## Rollback
- **Functional rollback (covers everything):** redeploy the previous code (commit / zipped folder). Old code ignores the new columns/tables, so the DB can stay as-is and the app reverts cleanly.
- **Full revert (only if needed):** restore the step-1 backup. Optional drop of the additions:
  ```sql
  ALTER TABLE contracts DROP FOREIGN KEY FK_2576E0FD5DA0FB8;
  ALTER TABLE contracts DROP COLUMN template_id, DROP COLUMN currency;
  ALTER TABLE new_contracts DROP COLUMN currency;
  DROP TABLE ntp_zem_price;
  DROP TABLE contract_template;
  ```

Keep the backup until production has run cleanly for a few days.
