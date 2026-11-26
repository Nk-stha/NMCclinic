# End-to-end and responsive tests (Playwright)

Dev-only browser tests for the public site and the CMS Page Content editor. The site does not
depend on anything in this folder, and `tests/` is blocked over HTTP by `.htaccess`.

## Run

```sh
docker compose up -d                    # from the repo root: site on http://localhost:8082
# Dev DB only: load the dummy doctors/posts/settings the specs and visual baselines expect.
docker compose exec -T db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' < tests/fixtures/sample-data.sql
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
`E2E_DB_EXEC` must reach the same database that the site at `E2E_BASE_URL` uses. Otherwise setup
and cleanup run against one DB while the tests edit another.

## Specs

- `public-pages`: all 8 public URLs. Each must return the right status, show an `h1`, log no console errors and have no failed same-origin requests. Also checks the skip link.
- `cms-editing`: edits footer text, one field per page, and the nav item list (add, move, remove), then resets each one. Security checks: script tags render as text, a `javascript:` URL is rejected, a tampered CSRF token is rejected. A required field left empty shows an inline error.
- `forms-and-interactions`: the appointment and contact forms, the doctor and blog filter tabs, the branch picker, the mobile nav, and the CMS phone menu.
- `responsive`: 9 viewports from 320 to 1440px, on the 8 public pages and 4 CMS pages (Page Content index and home, Settings, Dashboard). Checks overflow, the nav and mobile-bar breakpoints (read from `assets/style.css`), form controls inside the viewport, 44px touch targets, and 16px inputs under 768px. Full-page screenshots go to `screenshots/` (gitignored) and are attached to the report.
- `worst-case`: saves max-length values through the CMS (Devanagari hero heading, unbroken nav label, unbroken doctor card button), checks overflow at 320, 390 and 768px, then resets.

## What it touches, and cleanup

Tests run with one worker, because they share the dev DB.

- **globalSetup** first removes leftovers: `E2E …` form rows that also carry the suite's marker
  phone `+977 9800000000` (contact rows also the email `e2e-suite@example.test`), the seeded `e2e-suite-*` blog rows,
  and any `e2e_tmp_*` tables. It then copies `page_sections`, `section_items` and the admin
  `last_login_at`/`updated_at` columns into `e2e_tmp_*` tables. One atomic `RENAME TABLE` turns
  those into `e2e_bak_*`. Only after that does setup do anything destructive: it empties
  `page_sections`, so every test starts from registry defaults. It also seeds the blog category
  `e2e-suite-category` with one published post, `e2e-suite-post`, so the blog filter test has a
  card to hide.
- During the run, tests save and reset CMS sections (each in `try/finally`), and submit forms
  named `E2E Appointment` and `E2E Contact`, with the marker phone (and email on the contact form).
- **globalTeardown** deletes the marked `E2E …` form rows and the two `e2e-suite-*` blog rows. It then
  checks that the full `e2e_bak_*` set (3 tables) exists. If the set is incomplete, it stops with
  an error and restores nothing. Otherwise it runs `db/restore.sql`, which:
  1. Fails before touching any data unless all 3 backup tables exist.
  2. Restores the content and login times in one transaction.
  3. Drops the backups.

The DB is left as it was found, except for AUTO_INCREMENT counters.

If a run is killed before teardown, the next run refuses to start while `e2e_bak_*` tables exist.
Because the backups appear only through the atomic rename, an `e2e_bak_*` set is always complete.
Restore it by hand from the repo root:

```sh
docker compose exec -T db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' < tests/e2e/db/restore.sql
docker compose exec -T db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' < tests/e2e/db/cleanup.sql
```
