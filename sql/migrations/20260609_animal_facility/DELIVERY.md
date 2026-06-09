# Release — „Животновъден обект" (Delivery runbook, Windows / XAMPP)

This folder is a self-contained release bundle for the РОЗЗ contract app:

- `migration.sql` — the **single** DB migration (run once).
- `DELIVERY.md` — this runbook.

**Golden rule:** the production database must not be broken. Everything here is **additive** (two new nullable columns). No existing data is changed. The real safety net is a **verified backup** (step 1).

## What this release contains
A new optional **„Животновъден обект"** field (free text). It is entered on the holder-selection screen („Избери наемател"), stored on the draft and copied to the finalized contract, and exposed in the contract template as the placeholder **`[животновъден_обект]`**. The field is **not required** — if left empty, the placeholder renders as an empty string. **No new Composer dependencies.**

---

## 1. Back up (non-negotiable)
- Export the full DB via **phpMyAdmin → Export** (or `mysqldump -u root -p <dbname> > backup_YYYYMMDD.sql`).
- Also use the app's own backup: **Настройки → „Запази цялата база данни във файл"**.
- **Restore the dump into a scratch DB to confirm it works** — an untested backup is not a backup.
- Make a zip copy of the current app folder (code rollback point). If using git: note `git rev-parse HEAD`.

## 2. Deploy the code
- `git fetch && git checkout <new-tag>` on the server, **or** copy the files manually.
- Key changed files: `src/RozzBundle/Entity/Contracts.php`, `src/RozzBundle/Entity/NewContracts.php`, `src/RozzBundle/Controller/ContractsController.php`, `src/RozzBundle/Controller/ContractTemplatesController.php`, `src/RozzBundle/Services/ContractService.php`, `src/RozzBundle/Services/ContractTemplateRenderer.php`, `src/RozzBundle/Resources/views/Contracts/select_holder.html.twig`, and this `sql/migrations/20260609_animal_facility/` folder.
- **Do not overwrite** `app/config/parameters.yml` (holds the prod DB credentials; unchanged).

## 3. Apply the DB migration (phpMyAdmin — one file, once)
1. **Pre-check** (so the non-idempotent SQL can't half-apply): confirm the `contracts` and `new_contracts` tables have **no** `animal_facility` column:
   `SHOW COLUMNS FROM contracts LIKE 'animal_facility';` → empty.
   `SHOW COLUMNS FROM new_contracts LIKE 'animal_facility';` → empty.
2. Open **phpMyAdmin → (the app DB) → SQL**, paste the contents of `migration.sql`, and run.
3. Verify:
   - `SHOW COLUMNS FROM contracts LIKE 'animal_facility';` → present.
   - `SHOW COLUMNS FROM new_contracts LIKE 'animal_facility';` → present.
4. If a statement errors, **stop** and assess against the backup (DDL auto-commits).

## 4. Clear the production cache (required)
New config / templates won't take effect until the cache is rebuilt.
- Delete the folder `var\cache\prod` (Explorer, or `rmdir /s /q var\cache\prod`). Symfony rebuilds it on the next request.
- CLI equivalent (if available): `C:\xampp\php\php.exe bin\console cache:clear --env=prod`.

## 5. Add the placeholder to a template (optional)
The new placeholder is available immediately but only appears in generated contracts where it is used. In **Настройки → Шаблони за договори**, insert the blue chip **„Животновъден обект"** (`[животновъден_обект]`) where it should appear. Existing contracts are unaffected.

## 6. Smoke test
- Open **„Избери наемател"** — there is a new **„Животновъден обект"** text input below the name selector. Leaving it empty is allowed.
- Create a contract end-to-end with a value entered → the value is stored and, if the template uses `[животновъден_обект]`, appears in the generated document.
- Create a contract with the field left empty → no error; the placeholder renders empty.

---

## DB-safety summary
- Additive-only DDL (two new nullable `VARCHAR(255)` columns). No drops / retypes / deletes.
- New columns inherit the table charset (`utf8 / utf8_unicode_ci`).

## Rollback
- **Functional rollback:** redeploy the previous code. Old code ignores the new columns, so the DB can stay as-is.
- **Full revert (only if needed):**
  ```sql
  ALTER TABLE contracts     DROP COLUMN animal_facility;
  ALTER TABLE new_contracts DROP COLUMN animal_facility;
  ```

Keep the backup until production has run cleanly for a few days.
