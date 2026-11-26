# End-to-end and responsive tests (Playwright)

Dev-only browser tests for the public site and the CMS Page Content editor. The site does not
depend on anything in this folder, and `tests/` is blocked over HTTP by `.htaccess`.

## Run

```sh
docker compose up -d                    # from the repo root: site on http://localhost:8082
cd tests/e2e
npm ci
npx playwright install chromium
npx playwright test                     # or: npm test
npx playwright show-report              # HTML report of the last run
```

| Env var | Default | Purpose |
| --- | --- | --- |
| `E2E_BASE_URL` | `http://localhost:8082` | Site under test. Must be localhost/127.0.0.1. |
| `E2E_ALLOW_REMOTE` | unset | Set to `1` to allow another host. Use it only for a disposable test server, because the suite writes to the DB. |
| `E2E_ADMIN_EMAIL` / `E2E_ADMIN_PASSWORD` | `admin@nmcclinic.com` / `admin123` | CMS login. These are the dev seed values only. |
| `E2E_DB_EXEC` | see below | Shell command that reads SQL on stdin. It runs from the repo root. |
| `E2E_WEBKIT` | unset | Set to `1` to add an iPhone 13 WebKit project. It needs `sudo npx playwright install-deps`. |

`E2E_DB_EXEC` defaults to
`docker compose exec -T db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"'`.
If the docker socket says "permission denied", the suite retries the command through
`sg docker -c "…"`.

## Specs

- `public-pages`: all 8 public URLs. Each must return the right status, show an `h1`, log no console errors and have no failed same-origin requests. Also checks the skip link.
- `cms-editing`: edits footer text, one field per page, and the nav item list (add, move, remove), then resets each one. Security checks: script tags render as text, a `javascript:` URL is rejected, a tampered CSRF token is rejected. A required field left empty shows an inline error.
- `forms-and-interactions`: the appointment and contact forms, the doctor and blog filter tabs, the branch picker, the mobile nav, and the CMS phone menu.
- `responsive`: 9 viewports from 320 to 1440px, on the 8 public pages and 2 CMS pages. Checks overflow, the nav and mobile-bar breakpoints (read from `assets/style.css`), form controls inside the viewport, 44px touch targets, and 16px inputs under 768px. Full-page screenshots go to `screenshots/` (gitignored) and are attached to the report.
- `worst-case`: saves max-length values through the CMS (Devanagari hero heading, unbroken nav label, unbroken doctor card button), checks overflow at 320, 390 and 768px, then resets.

## What it touches, and cleanup

Tests run with one worker, because they share the dev DB.

- **globalSetup** first deletes old `E2E …` form rows. It then copies `page_sections`, `section_items` and the admin `last_login_at`/`updated_at` columns into `e2e_bak_*` tables. Last, it empties `page_sections`, so every test starts from registry defaults.
- During the run, tests save and reset CMS sections, and submit forms named `E2E Appointment` and `E2E Contact`.
- **globalTeardown** deletes `appointment_requests` and `contact_inquiries` rows whose `full_name` starts with `E2E `. It then restores the backed-up content and login times, and drops the `e2e_bak_*` tables.

The DB is left as it was found, except for AUTO_INCREMENT counters.

If a run is killed before teardown, the next run refuses to start while `e2e_bak_*` tables exist.
Restore them by hand from the repo root:

```sh
docker compose exec -T db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' < tests/e2e/db/restore.sql
```
