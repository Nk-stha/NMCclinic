# CMS-Editable Page Content Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Every piece of copy and imagery on the public site becomes editable from a new CMS "Page Content" screen. Layout stays fixed. Pages render on the server.

**Architecture:** A PHP section registry (`includes/content_registry.php`) declares each page's sections, fields and verbatim defaults. `includes/content.php` merges saved rows from the existing `page_sections`/`section_items` tables over those defaults, and exposes escaping render helpers. `cms/content.php` generates edit forms from the same registry. Already-dynamic data (doctors, specialties, blog, branches, settings) moves from JSON endpoints into shared `includes/site_data.php` functions, so pages render it server-side.

**Tech Stack:** PHP 8.2 (no framework, no Composer), MySQL 8.4, PDO via existing `db()`, vanilla JS, Apache `.htaccess`.

**Spec:** `docs/superpowers/specs/2026-11-26-cms-editable-content-design.md`

## Global Constraints

- Plain PHP 8.2, MySQL 8.4, no Composer/npm, **no new dependencies**.
- Content only: no adding/removing/reordering sections, no new pages, no WYSIWYG, no revisions, `locale` stays `en`.
- "No DB rows = render registry defaults." Visible text must not change at deploy, except list placeholders replaced by the empty-state message.
- No raw HTML from editors, ever. Only `*accent*` in headings and paragraph/`<br>` in textareas produce markup, wrapping already-escaped text.
- URL fields allow only `http://`, `https://`, `tel:`, `mailto:`, `#…`, or a relative path. Anything else renders `#`.
- Every CMS request calls `require_admin()`. Every CMS POST checks `csrf_is_valid()`.
- Every schema/data change ships as `migrations/2026-11-26-*.sql` **and** in `db.sql`.
- Must work under XAMPP (`/NMCclinic/` base path) and Docker (`/`). Clean URLs and `public_seo.php` behaviour unchanged.
- Public styling uses existing `assets/style.css` tokens, CMS styling uses existing `cms/includes/layout.php` CSS. No visual redesign.
- One field, one editor: phone/WhatsApp/address/hours/map stay in CMS > Settings and Branches. Legal URLs stay in Settings, with labels in the registry. The contact emergency block and response note come from the `contact_page` table.

## Review Focus

1. Editor types `<script>alert(1)</script>` or `<b>` into any field → shown as literal text everywhere. Pinned in Task 1 (`escapes_html_*`).
2. URL tricks such as `JaVaScRiPt:alert(1)`, `  javascript:…` with leading spaces, `data:text/html,…`, and protocol-relative `//evil.com` → render `#` and fail CMS validation. Pinned in Task 1 (`url_*`) and Task 4 (`validate_rejects_bad_url`).
3. Nepali/Devanagari text near a field's `max` → counted in characters (`mb_strlen`), not bytes. Long unbroken strings don't break layout. Pinned in Task 4 (`validate_counts_multibyte`), plus `break-ui` in Task 12.
4. A media asset picked for an image field is later soft-deleted → the image falls back to the registry default and never renders a broken `<img>`. Pinned in Task 1 (`image_falls_back_when_media_missing`).
5. DB down or a required field saved empty in a legacy row → the page renders the defaults and never shows blank text or a PHP error. Pinned in Task 1 (`required_empty_uses_default`, `db_down_returns_defaults`).

## Prerequisites (Task 0, blocking)

This machine currently has **no `php`, `mysql` or `docker`**. Before Task 1, the executor must ask the user to install one of these:
- **Docker (preferred):** Docker Engine + compose plugin. Add a dev override `docker-compose.override.yml` that bind-mounts `./:/var/www/html` into `web`, so edits show without a rebuild. Gitignore the override.
- **Native:** `php8.2-cli php8.2-mysql php8.2-mbstring` + MySQL 8, with `db.sql` imported into database `clinic`.

`PHP` in commands below means `docker compose exec -T web php` (Docker) or `php` (native). `BASE` means the site URL (`http://localhost:8082` in Docker).

Then capture baselines **from the unchanged code**:
- [ ] Load realistic sample data: CMS > add 2 doctors, 2 specialties, 2 blog posts. Keep the 2 seeded branches.
- [ ] Use the `playwright-cli` skill to open `BASE/`, `/about`, `/specialties`, `/doctors`, `/blog`, `/blog/<a seeded slug>`, `/contact` and `/nope` (the 404 page). Wait for network idle, then save `document.body.innerText` to `tests/visual-baseline/<page>.txt`. Also save a 1440px screenshot of each to `tests/visual-baseline/<page>.png`.
- [ ] Add `RewriteRule ^(tests|docs|cms/tools)(/|$) - [F,END]` to `.htaccess` next to the existing `migrations|plan` rule.
- [ ] Commit: `git add tests/visual-baseline .htaccess && git commit -m "test: capture pre-change visible-text baselines"`

---

## File Structure

| File | Responsibility |
|---|---|
| `includes/content_registry.php` (create) | `content_registry()`: every page's sections, fields, item groups and verbatim defaults. Data only. |
| `includes/content_icons.php` (create) | `content_icons()`: icon key → trusted inline SVG, copied from the current markup. |
| `includes/content.php` (create) | Merge, image resolution, `page_content()`, render helpers, `content_safe_url()`, `content_paragraphs()`. |
| `includes/site_data.php` (create) | DB reads for settings, branches, specialties, doctors, blog posts and form options, plus the formatting helpers moved out of the `*-data.php` files. |
| `includes/site_header.php`, `includes/site_footer.php` (create) | Shared chrome partials. |
| `cms/includes/content_form.php` (create) | `content_validate_section()`, `content_save_section()`, `content_reset_section()`, `content_last_updated()`, `content_render_field()`. |
| `cms/content.php` (create) | Page list and per-page editor. |
| `cms/tools/content_check.php` (create) | CLI-only assert-based test runner. Exits 1 on any failure. |
| `migrations/2026-11-26-content-pages.sql` (create) + `db.sql` (modify) | `pages` rows `global`, `blog_detail`, `404`. |
| `*-data.php` (modify) | Thin wrappers around `site_data.php`. Deleted in Task 12 if unreferenced. |
| Public pages (modify) | Replace hard-coded markup with helper calls. |
| `assets/script.js` (modify) | Drop the fetch/replace code. Keep filters, forms, branch picker, open status, reveal and nav. |

---

### Task 1: Content core (registry shape, merge, render helpers, test runner)

**Files:**
- Create: `includes/content_registry.php`, `includes/content_icons.php`, `includes/content.php`, `cms/tools/content_check.php`, `migrations/2026-11-26-content-pages.sql`
- Modify: `db.sql` (append the same `pages` inserts in section 08)

**Interfaces:**
- Produces:
  - `content_registry(): array`. Shape: `[slug => ['label' => string, 'sections' => [key => SectionSpec]]]`.
    - `SectionSpec = ['label' => string, 'fields' => [name => FieldSpec], 'items' => ?ItemsSpec]`.
    - `FieldSpec = ['type' => 'text'|'textarea'|'heading'|'url'|'image'|'icon', 'label' => string, 'max' => int, 'required' => bool, 'default' => string | ['src' => string, 'alt' => string]]`. Image defaults are `{src, alt}`.
    - `ItemsSpec = ['item_type' => string, 'label' => string, 'min' => int, 'max' => int, 'fields' => [name => FieldSpec], 'default' => list<array<name, value>>]`.
    - Field names that equal a column in `CONTENT_SECTION_COLUMNS` / `CONTENT_ITEM_COLUMNS` are stored there. Other names go in `settings_json`. Items have no JSON column, so item field names **must** be item columns. Image fields: section image → `primary_media_id`, or a JSON key holding an int id. Item image → `media_id`.
  - `const CONTENT_SECTION_COLUMNS = ['eyebrow','heading','subheading','body','primary_media_id','primary_cta_label','primary_cta_url','secondary_cta_label','secondary_cta_url'];`
  - `const CONTENT_ITEM_COLUMNS = ['label','title','subtitle','body','value_text','icon_key','media_id','link_label','link_url'];`
  - `content_page_spec(string $slug): array` throws `InvalidArgumentException` for an unknown slug.
  - `content_merge(array $pageSpec, array $sectionRows, array $itemRowsBySectionId): array` (pure). `$sectionRows` holds `page_sections` rows keyed by `section_key`. The result is `[sectionKey => [fieldName => value, ..., 'items' => list<[fieldName => value]>]]`. `items` is present only when the spec has `items`.
    - No row for a section: all defaults, including the default items.
    - Row exists: each field takes its DB value (column or `settings_json`), but a required field whose value is `null`/`''` takes its default.
    - Items when a row exists: the saved items in `sort_order`, which may be an empty list. Each saved item gets the same required-default rule.
  - `content_resolve_images(array $pageSpec, array $merged, array $mediaById): array` (pure). Every image field becomes `['src' => string, 'alt' => string]`. A numeric id found in `$mediaById` (`id => ['file_path','alt_text']`) is used; anything else falls back to the field default. Empty alt falls back to the default alt.
  - `page_content(string $slug): array`. Static per-request cache. Runs one query for the page's sections (joined via `pages.slug`), one for their items, and one `IN` query for media with `deleted_at IS NULL`. Then calls merge and resolve. On any `Throwable` it calls `error_log()` and returns the defaults (`content_merge($spec, [], [])` then resolve with `[]`).
  - `content_safe_url(string $url): string`. Trims the value. Returns it unchanged if it matches `^(https?:|tel:|mailto:|#)` case-insensitively, or if it is a relative path not starting with `//` and with no `:` before the first `/`. Otherwise returns `#`.
  - `content_paragraphs(string $text): string`. Blank-line-separated blocks become `<p>`, single newlines become `<br>`, and everything is escaped with `public_e()`. Same rule as `blog_text_to_sanitized_html()`.
  - `c(array $values, string $field): string`. Escaped text.
  - `c_heading(array $values, string $field, string $accent = 'em'): string`. Escaped, then `*x*` → `<em>x</em>` (`'em'`) or `<span class="accent">x</span>` (`'accent'`).
  - `c_text(array $values, string $field): string`. Returns `content_paragraphs()`.
  - `c_url(array $values, string $field): string`. Escaped `content_safe_url()`.
  - `c_img(array $values, string $field): array{src: string, alt: string}`. Values escaped.
  - `c_icon(array $values, string $field): string`. Trusted SVG from `content_icons()`, `''` for an unknown key.
  - `content_icons(): array<string, string>`. Starts with the icons used in the `index.php` services cards and `specialties.php` eyebrows. Later tasks add icons.
- In this task the registry contains only page `global` with section `footer` and one field `about` (text, max 300, required, default = the current footer-about sentence). This is enough to test the machinery. Task 3 fills the rest.

- [ ] **Step 1: Write `cms/tools/content_check.php`** with a guard (`if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }`), a `check(string $name, bool $ok): void` helper that prints `PASS/FAIL name`, and a final `exit($failures ? 1 : 0)`. Add these checks, using a hand-built `$spec` fixture inside the test with one text, one required heading, one image and an `items` group:
  - `escapes_html_text`: `c(['x' => '<script>a</script>'], 'x') === '&lt;script&gt;a&lt;/script&gt;'`
  - `escapes_html_heading_accent`: `c_heading(['h' => '<b>Hi</b> *one visit*'], 'h') === '&lt;b&gt;Hi&lt;/b&gt; <em>one visit</em>'`, and with `'accent'` the result contains `<span class="accent">one visit</span>`
  - `paragraphs`: `content_paragraphs("a\nb\n\n<c>") === "<p>a<br>b</p><p>&lt;c&gt;</p>"`
  - `url_blocks_scripts`: `content_safe_url` returns `'#'` for `'javascript:alert(1)'`, `'  JaVaScRiPt:alert(1)'`, `'data:text/html,x'`, `'//evil.com'` and `'vbscript:x'`
  - `url_allows_safe`: it returns the input unchanged for `'https://x.com/a'`, `'tel:+97771000000'`, `'mailto:a@b.c'`, `'#appointment'`, `'contact'` and `'specialties#kidney'`
  - `merge_no_rows_uses_defaults`: `content_merge($spec, [], [])` equals the defaults, including the default items
  - `required_empty_uses_default`: a row with `heading => ''` → the default heading. Optional text `''` stays `''`
  - `settings_json_field`: a non-column field is read from `settings_json` (a JSON string)
  - `saved_empty_items_stay_empty`: a row exists and has no items → `'items' => []`
  - `image_falls_back_when_media_missing`: image id `99` not in `$mediaById` → the default `{src, alt}`. Id `5` present → its `file_path`/`alt_text`
  - `registry_defaults_complete`: for every page/section/field in `content_registry()`, a default exists, required defaults are non-empty, and `mb_strlen(default) <= max` for text-like defaults
  - `db_down_returns_defaults`: before any `db()` call, `putenv('NMC_DB_HOST=nonexistent.invalid')`, then `page_content('global')['footer']['about'] === <registry default>`

- [ ] **Step 2: Run** `PHP cms/tools/content_check.php`. Expected: fatal error (functions undefined).
- [ ] **Step 3: Implement** the files and signatures above. The migration inserts `('global','generic','en','published',NOW())`, `('blog_detail',…)` and `('404',…)` with `ON DUPLICATE KEY UPDATE status = status`. Mirror it in `db.sql`.
- [ ] **Step 4: Run** `PHP cms/tools/content_check.php`. Expected: all PASS, exit 0. Apply the migration to the dev DB.
- [ ] **Step 5: Confirm exclusions.** `grep -n "generic" includes/public_seo.php cms/includes/seo_form.php sitemap.php`. If `seo_target_options()` or `public_sitemap_urls()` would list `generic` pages, filter `page_type <> 'generic'` there, and add check `sitemap_excludes_generic` (static-scan or query-string assertion).
- [ ] **Step 6: Commit** `git add includes/content*.php cms/tools migrations/2026-11-26-content-pages.sql db.sql && git commit -m "feat(content): registry, merge and safe render helpers"`

---

### Task 2: Shared site data functions

**Files:**
- Create: `includes/site_data.php`
- Modify: `site-settings-data.php`, `branches-data.php`, `specialties-data.php`, `doctors-data.php`, `blog-data.php`, `form-options.php`

**Interfaces:**
- Produces (each one catches `Throwable`, calls `error_log()`, and returns its empty shape):
  - `site_settings(): array`. Shape `['settings' => ?array, 'hours' => array, 'hours_summary' => string, 'socials' => array]`, exactly the current `site-settings-data.php` JSON minus `ok`. `settings` also carries `privacy_url`, `terms_url` and `accessibility_url`.
  - `site_branches(): array`, `site_specialties(): array`, `site_doctors(): array`, `site_blog_posts(): array`, `site_form_options(): array`. Each returns the same rows/shape its endpoint returns today under its data key(s).
  - The moved helpers keep their names: `phone_href`, `whatsapp_url`, `map_embed_url`, `day_label`, `short_day`, `time_label`, `hours_summary`, `day_name`, `format_blog_month`.
  - Each `*-data.php` becomes: `require_once includes/site_data.php`, then the JSON header, then `json_encode(['ok' => true] + <function result>)`.

- [ ] **Step 1: Snapshot** each endpoint before changing it: `curl -s BASE/<file>.php > /tmp…/before-<file>.json` (use the scratchpad) for all 6.
- [ ] **Step 2: Implement** `includes/site_data.php` by moving the queries and helpers verbatim, then thin the endpoints.
- [ ] **Step 3: Verify** each endpoint again and run `diff` against its snapshot. Expected: no diff for all 6. Add check `site_data_db_down_shapes` to `content_check.php` (runs in the same DB-down section): `site_doctors() === []` and `site_settings()['settings'] === null`.
- [ ] **Step 4: Commit** `git commit -m "refactor: share site data queries between endpoints and pages"`

---

### Task 3: Shared header/footer partials + `global` registry

**Files:**
- Create: `includes/site_header.php`, `includes/site_footer.php`
- Modify: `includes/content_registry.php` (the full `global` page), `index.php about.php specialties.php doctors.php blog.php blog-detail.php contact.php` (replace the header block, about `index.php:22-68`, and everything from `<footer` to before `<script`), `assets/script.js` (delete the `.footer-legal a` text-matching block at ~`:503-507` and `replaceFooterContact`)

**Interfaces:**
- Consumes: `page_content()`, `c*()` (Task 1), `site_settings()` (Task 2).
- Produces:
  - `site_header(string $active): void`. `$active` is one of `home about specialties doctors blog contact`. It sets `aria-current="page"` on the matching brand/nav links in both the desktop and mobile navs.
  - `site_footer(): void`. Renders the footer, emergency strip, WhatsApp float and mobile bar.
- `global` sections, with defaults copied verbatim from `index.php`:
  - `header`: logo image, brand name, brand sub, header CTA label/url.
  - `nav`: items with `label` + `link_url`, min 1, max 8, the 6 current links.
  - `footer`: logo image, `about`, services heading, contact heading, `emergency_text`, copyright text after the year, and the 3 legal **labels** (`legal_privacy_label`, `legal_terms_label`, `legal_accessibility_label`).
  - `footer_services`: items `label` + `link_url`, max 10, the 5 current links.
  - `mobile_bar`: call / WhatsApp / book labels and the book url.
- Phone, WhatsApp, address and legal URLs come from `site_settings()`. If `settings` is null, fall back to the values hard-coded today. A legal link whose URL is empty is not rendered.

- [ ] **Step 1: Add checks** to `content_check.php`:
  - `header_marks_active`: capture `site_header('about')` with `ob_start()` → exactly the About links carry `aria-current="page"` (count = 2: desktop + mobile).
  - `footer_hides_legal_without_url`: in DB-down mode, `site_footer()` output contains no `Privacy Policy` anchor with `href="#"`.
- [ ] **Step 2: Run.** Expected: FAIL (functions undefined).
- [ ] **Step 3: Implement** the partials and the registry. Replace the blocks in the 7 pages. `blog-detail.php` uses `site_header('blog')`.
- [ ] **Step 4: Run** the checks → PASS. With Playwright, re-capture the innerText of all 8 pages and diff against `tests/visual-baseline/`. Expected: no diff (legal links show only if Settings has URLs: set them in the dev DB first to match the baseline, or note the expected removal).
- [ ] **Step 5: Commit** `git commit -m "feat(content): shared editable header and footer"`

---

### Task 4: CMS "Page Content" editor

**Files:**
- Create: `cms/includes/content_form.php`, `cms/content.php`
- Modify: `cms/includes/layout.php` (nav array: add `'content' => ['Page Content', 'content.php', 'article']` after `settings`)

**Interfaces:**
- Consumes: `content_registry()`, `content_page_spec()`, `content_safe_url()`, `content_icons()`, `page_content()`, `CONTENT_*_COLUMNS` (Task 1). `require_admin()`, `csrf_token()`, `csrf_is_valid()`, `e()`, `cms_header()`, `cms_footer()`.
- Produces:
  - `content_validate_section(array $sectionSpec, array $input): array`. Returns `['values' => array, 'items' => list<array>, 'errors' => [fieldPath => message]]`. `$input` is `['fields' => [...], 'items' => [[...], ...]]` from POST. Values are trimmed and line endings normalised. Rules:
    - required non-empty
    - `mb_strlen <= max`
    - URL fields must satisfy `content_safe_url($v) === $v` or be empty
    - image is `''` or a positive int string
    - icon is a key of `content_icons()`
    - `min <= count(items) <= max`
    - Error paths look like `heading` and `items.2.title`.
  - `content_save_section(string $slug, string $sectionKey, array $values, array $items): void`. In one transaction:
    - resolve `pages.id` by slug
    - verify every image id exists in `media_assets` with `deleted_at IS NULL` (else throw `RuntimeException('Selected image no longer exists.')`)
    - upsert `page_sections` (`section_type` = section key, `status` = `'published'`, columns from `CONTENT_SECTION_COLUMNS`, other fields as `settings_json`)
    - delete that section's `section_items`
    - insert the items with `item_type` from the spec and `sort_order` = index
  - `content_reset_section(string $slug, string $sectionKey): void`. Deletes the section row; items cascade.
  - `content_last_updated(): array<string slug, ?string datetime>`. `MAX(page_sections.updated_at)` per page.
  - `content_media_options(): array`. Same query as `doctor_media_options()`.
- `cms/content.php` behaviour:
  - No `?page` → table of registry pages: label, last updated, Edit link.
  - `?page=<slug>` (404 if not in the registry) → one `<form method="post">` per section with hidden `csrf_token`, `page` and `section`, and an `id="section-<key>"` anchor.
  - Fields render by type: text → `input`, textarea/heading → `textarea`, url → `input type=text`, image → `select` from `content_media_options()` plus a "Default image" option and a thumbnail, icon → `select`.
  - Items render as rows with Add / Remove / Up / Down buttons. A small inline `<script>` clones a `<template>` row and renumbers `name="items[i][field]"` on submit.
  - "View on site" link goes to the public page anchor. "Reset to default" is a separate POST with `action=reset` and a JS `confirm()`.
  - On a successful POST, PRG-redirect to `content.php?page=<slug>&saved=<key>#section-<key>` and show a flash. On errors, re-render that section with the posted input and inline messages. Other sections show their stored values.
  - Use the heading hint text "Wrap words in *asterisks* to highlight them."

- [ ] **Step 1: Add checks** to `content_check.php`, using a fixture spec:
  - `validate_required`: empty required heading → `errors['heading']` set.
  - `validate_counts_multibyte`: `max` 10 with `str_repeat('क', 10)` → no error. With 11 → error.
  - `validate_rejects_bad_url`: `primary_cta_url = 'javascript:x'` → error. `'contact'` → ok.
  - `validate_items_bounds`: count below `min` / above `max` → `errors['items']`.
  - `validate_item_field_path`: an empty required item title at index 2 → `errors['items.2.title']`.
  - `validate_icon_whitelist`: unknown icon → error.
- [ ] **Step 2: Run** → FAIL.
- [ ] **Step 3: Implement** `content_form.php` and `cms/content.php`.
- [ ] **Step 4: Run** the checks → PASS. Manual check with `playwright-cli`: log in, edit the Global footer `about` to `Test <b>x</b>`, save, and confirm the public footer shows the literal `<b>`. Then reset and confirm the default is back. Add a nav item, reorder it, delete it. Post with a tampered CSRF token → rejected.
- [ ] **Step 5: Commit** `git commit -m "feat(cms): Page Content editor driven by the section registry"`

---

### Tasks 5–11: Convert each page

The same recipe applies to each page. One task per page, in this order:

| Task | Page (registry slug) | Notes |
|---|---|---|
| 5 | `index.php` (`home`) | 11 sections: `hero`, `info_strip`, `services` (intro only; cards come from `site_specialties()`), `about`, `diagnostics` (items), `doctors` (intro + `empty_message`; cards from `site_doctors()`, first 3 featured/ordered as the JS does now), `why_choose` (items), `appointment` (copy only; specialty `<select>` from `site_form_options()`), `before_visit` (items), `articles` (intro + `empty_message`; latest 3 from `site_blog_posts()`), `visit` (copy; branch list from `site_branches()`). Hero uses accent `'em'`. Hero background moves from `style.css:208` to an inline `style="--hero-bg:url(...)"` read by that CSS rule. |
| 6 | `about.php` (`about`) | `banner`, `intro`, the `band-light` stats/cards (items), `values` (items), `story` (items), `cta_band`. Banner accent `'accent'`. |
| 7 | `specialties.php` (`specialties`) | `banner`, `cta_band`, `empty_message`. Quick-nav and the specialty blocks are server-rendered from `site_specialties()` (+ points), with the per-block markup taken from the current hard-coded block. Icon per block: map the specialty slug to an existing icon key, falling back to a generic icon. |
| 8 | `doctors.php` (`doctors`) | `banner`, `listing` (filter "All" label + `empty_message`), `cta_band`. Filter tabs from distinct specialty titles. Delete the `Dr. [Name]` placeholder cards. |
| 9 | `blog.php` (`blog`) | `banner`, `listing` (`empty_message`), `newsletter` copy. Featured = first `is_featured` post, else the newest. Category tabs from the posts. |
| 10 | `blog-detail.php` (`blog_detail`) | Only the template's shared copy (sidebar headings, CTA box, related-posts heading). Post content stays from `blog_posts`. |
| 11 | `contact.php` (`contact`) + `404.php` (`404`) | Contact: `banner`, info cards copy, form intro (subjects from `site_form_options()`), map section copy. The emergency block (`contact.php:216-221`) and `response_time_note` render from `contact_page_settings()` fields, moving that read-only query into `site_data.php` as `site_contact_page(): array`. If it returns no row, use the current hard-coded text. 404: its banner/body copy and links; layout unchanged (no partials). |

**Per-page steps (Task N):**

**Interfaces:**
- Consumes: `page_content()`, `c()`, `c_heading()`, `c_text()`, `c_url()`, `c_img()`, `c_icon()` (Task 1), `site_*()` (Task 2), `site_header()`/`site_footer()` (Task 3).
- Produces: registry page `<slug>`, and the server-rendered page.

- [ ] **Step 1: Write the registry entries** for the page, with every default copied **verbatim** from the current markup (including `&amp;` decoded to `&`, `’` kept as-is) and `*…*` around the text that is currently inside `<em>` / `<span class="accent">`. Every list area gets an `empty_message` field (textarea, max 200, required, default e.g. "Our doctor profiles are being updated. Please call us to book.").
- [ ] **Step 2: Run** `PHP cms/tools/content_check.php`. `registry_defaults_complete` must PASS for the new page.
- [ ] **Step 3: Replace the markup** with helper calls and server-rendered loops. Keep every class, id and `data-*` attribute that `script.js` or CSS uses (`data-filter-*`, `data-category`, `data-branch-*`, `data-reveal`, `data-open-status`). Remove the page's `data-cms-*` hooks and the placeholder fallback markup. When a list is empty, render `<p class="empty-state">` + `c_text($section, 'empty_message')` (add a 3-line `.empty-state` rule to `style.css` using existing tokens if no suitable class exists).
- [ ] **Step 4: Verify the text diff.** With an empty `page_sections` table and the Task 0 sample data, capture the page's innerText and diff it against `tests/visual-baseline/<page>.txt`. Expected: identical, except where the baseline showed placeholder cards (DB lists empty) and the empty-state message now appears instead. Also run the same capture with JS disabled → the same text (proves server rendering).
- [ ] **Step 5: Verify an edit round-trip.** In the CMS, edit one field and one item of this page, reload the public page and see the change, then reset.
- [ ] **Step 6: Commit** `git commit -m "feat(content): make <page> editable"`

---

### Task 12: Remove dead JS/endpoints and final verification

**Files:**
- Modify: `assets/script.js`, the public pages (`?v=` cache-bust on `script.js`/`style.css` → `20261126`)
- Delete: any `*-data.php` and `form-options.php` with no remaining reference

**Interfaces:**
- Consumes: everything above.
- The open-status needs hours without a fetch: `site_footer()` (Task 3) or the info strip renders `data-hours='<?= public_e(json_encode(site_settings()['hours'])) ?>'` on the `[data-open-status]` element. `renderOpenStatus` reads it from there.

- [ ] **Step 1: Delete from `script.js`:** `endpointUrl`, `cmsText`, `blogReadTime`, `blogByline`, `addBlogImage`, `createBlogPostCard`, `calendarSvg`, `scheduleText`, `createDoctorCard`, `createHomeSpecialtyCard`, `setAnchor`, `applySiteSettings`, and every `fetch(endpointUrl(...))` block. Also any branch-list *building* code (keep the branch picker behaviour over server-rendered `[data-branch-*]` buttons). Change `renderOpenStatus` to read `data-hours` and keep `DEFAULT_HOURS` as its fallback.
- [ ] **Step 2: Remove unreferenced files.** Run `grep -rn "data.php\|form-options" --include=*.php --include=*.js .`, then delete each endpoint that is no longer referenced.
- [ ] **Step 3: Run the full checks.** `PHP cms/tools/content_check.php` → all PASS. Then `PHP -l` on every changed PHP file → `No syntax errors`.
- [ ] **Step 4: Browser verification (CLAUDE.md).** With `playwright-cli`, take 1440px and 390px screenshots of all 8 public pages plus `cms/content.php?page=home`, and look at them. The console must have 0 errors. Filters, the appointment form, the contact form, the branch picker and the mobile nav must all still work. Re-run the text diff for all pages against the baselines.
- [ ] **Step 5: Worst-case data.** Run the `break-ui` skill on the home page hero, a nav item and a doctor card, with a 300-character Devanagari heading, a 120-character unbroken string, and 8 nav items. Fix any overflow using existing tokens (`overflow-wrap: anywhere` where needed). Then run the `web-design-guidelines` review on `cms/content.php` and fix label/focus issues.
- [ ] **Step 6: Security pass.** Grep for unescaped output in changed files (`grep -n "<?= \$" includes/site_*.php *.php | grep -v "c(\|c_\|public_e\|e("`). Every hit must be justified (trusted SVG via `c_icon`, or already-escaped helper output). Confirm `cms/tools/` returns 403 over HTTP. Confirm a CMS POST without a CSRF token is rejected.
- [ ] **Step 7: Commit** `git commit -m "refactor: drop client-side content swapping; pages render server-side"`
