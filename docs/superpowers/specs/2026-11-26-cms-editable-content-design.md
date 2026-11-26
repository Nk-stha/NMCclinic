# CMS-Editable Page Content — Design

Date: 2026-11-26 · Status: draft, awaiting approval (builder check done)

## 1. Intent

Clinic staff must be able to change everything a visitor reads or sees on the public site from the CMS, without editing PHP. They change **content only**: the design, section order and layout stay fixed. The reference point is "like WordPress" for editing, not for page building.

Success: a non-technical admin can change any heading, paragraph, image, button, list item, header/footer text or legal link on any public page, save, and see it live on reload. Nothing on the live site changes on the day this ships until someone edits it.

## 2. Goals, non-goals, anti-goals

**Goals**
- Every piece of hard-coded copy and imagery on `index.php about.php specialties.php doctors.php blog.php blog-detail.php contact.php 404.php`, plus the shared header, footer, mobile bar and WhatsApp float, becomes CMS-editable.
- Repeatable groups (FAQ-style lists, "why choose us" cards, values, story steps, stats, info cards, diagnostics list, footer link lists) can have items added, removed, edited and reordered.
- Public pages render final content on the server (no fallback-then-JS-swap).

**Non-goals**
- Adding, removing or reordering *sections*; creating new pages; choosing layouts or block types.
- Visual/inline on-page editing; WYSIWYG rich text.
- Revisions/version history, scheduled publishing, multi-language (the `locale` column stays `en`).
- Re-doing what is already CMS-managed: doctors, specialties (incl. the per-specialty blocks on `specialties.php`), blog posts/categories, branches, business contact settings, opening hours, social links, SEO metadata. These stay on their existing CMS screens.

**Anti-goals** (failure even if it "works")
- An editor can break the page layout or inject HTML/JS.
- The site renders blank or broken text when a field is empty or the DB is unreachable.
- Visible content changes at deploy time.

## 3. Current state (what this builds on)

- `db.sql` has `pages`, `page_sections` (eyebrow, heading, subheading, body, primary_media_id, two CTAs, sort_order, is_visible, status, settings_json) and `section_items` (item_type, label, title, subtitle, body, value_text, icon_key, media_id, link_label, link_url, sort_order, is_visible). **No code uses the two section tables yet.**
- `pages` is seeded with `home about specialties doctors blog contact`.
- Header (lines ~22–68) and footer/mobile bar/WhatsApp float (~487–end) are copy-pasted into every page, differing only in `aria-current`.
- Pages ship hard-coded fallback content; `assets/script.js` then fetches `*-data.php` JSON and swaps content in.
- CMS conventions: `require_admin()`, `csrf_token()/csrf_is_valid()`, `e()`, one `*_form.php` helper per entity in `cms/includes/`, layout via `cms_header()/cms_footer()`, media picker pattern in `doctor_form.php`/`blog_form.php` (`*_media_options()`), uploads via `media_upload()`.

## 4. Approaches considered

| | Approach | Trade-off |
|---|---|---|
| **A (chosen)** | PHP **section registry** describes each page's sections and fields; a generic CMS editor renders forms from it; data lives in existing `page_sections`/`section_items` (+ `settings_json` for odd fields); current copy seeded as defaults. | Reuses schema built for this; lists are first-class; one editor for all pages; a few sections fit generic columns loosely. |
| B | Key-value `content_fields` table (page, key, value). | Simplest model, but lists become numbered keys, ignores existing tables, flat long form. |
| C | Dedicated table per section type (hero, faqs, …). | Strict typing, but ~15–20 tables and screens; every new section needs schema + form code. |

Chosen by the user: **A**.

## 5. Design

### 5.1 Section registry — `includes/content_registry.php`

A single PHP file returning an array keyed by page slug (`global`, `home`, `about`, `specialties`, `doctors`, `blog`, `blog_detail`, `contact`, `404`). `blog_detail` holds the article template's shared copy (CTA, sidebar), not per-post content. Each page lists its sections in render order; each section declares:

- `key` — stable id, e.g. `hero`, `why_choose`, `cta_band`. Maps to `page_sections.section_key`.
- `label` — what the editor sees ("Hero banner").
- `fields` — map of field name → spec. Field name is either a `page_sections` column (`eyebrow heading subheading body primary_media_id primary_cta_label primary_cta_url secondary_cta_label secondary_cta_url`) or any other name, which is stored in `settings_json`.
- `items` (optional) — a repeatable group: `item_type`, `label`, `min`, `max`, and its own `fields` mapped to `section_items` columns (`label title subtitle body value_text icon_key media_id link_label link_url`).
- Every field spec has: `type` (`text`, `textarea`, `heading`, `url`, `image`, `icon`), `label`, `max` length, `required` (bool), and `default` = the exact copy/image currently hard-coded.

The registry is the single source of truth for both the CMS form and the defaults. Adding an editable field later = one registry entry + one template line.

### 5.2 Storage

- Reuse `pages`, `page_sections`, `section_items`. Add `pages` rows `slug='global'`, `slug='blog_detail'`, `slug='404'`, all `page_type='generic'`, `status='published'` (migration + `db.sql`). Exclude these three from the SEO target list and the sitemap.
- No other schema change. `page_sections.status` is written as `published`; `is_visible` stays 1 (sections can't be hidden — non-goal).
- Rows are created lazily on first save. **No DB rows = render registry defaults.** This is what guarantees "nothing changes at deploy".
- Items: on save, the section's items are replaced (delete + insert in one transaction) in submitted order; `sort_order` = position.

### 5.3 Rendering — `includes/content.php`

- `page_content(string $slug): array` — one query for the page's sections + one for their items, merged over registry defaults, field by field (empty/NULL DB value → default for `required` fields; optional fields may be blank). Cached per request (static).
- On DB error: log, return pure registry defaults. The page never fails because content failed.
- Templates read values via small helpers that escape at output: `c($section, 'heading')` → `public_e()`, `c_url()` (allows only `http(s)://`, `tel:`, `mailto:`, `#…`, or a relative path; anything else → `#`), `c_img()` (media row → path + alt, falling back to the default file).
- **Heading accent markup:** headings render `*text*` as the existing accent element (`<em>` on home hero, `<span class="accent">` on banners — the registry field spec says which). Everything else is escaped. Body `textarea` fields render blank-line-separated paragraphs and single newlines as `<br>`, same rule as `blog_text_to_sanitized_html()`. No raw HTML from editors, ever.
- **Shared chrome:** extract header into `includes/site_header.php` (takes the active page for `aria-current`) and footer + emergency strip + WhatsApp float + mobile bar into `includes/site_footer.php`; both read the `global` content plus existing `business_contact_settings`/`social_links`. Used by every page that has a header/footer today, **including `blog-detail.php`** (it has its own copy now). `404.php` keeps its current standalone layout (no header/footer added) and only gets its own registry fields.
- **Ownership of overlapping data (no field has two editors):**
  - Footer legal links: the registry stores the **labels**; the **URLs** come from `business_contact_settings.privacy_url/terms_url/accessibility_url` (CMS > Settings). A link with no URL set is not rendered. The `script.js` text-matching href swap is removed.
  - Contact page emergency block and response-time note: rendered from the existing `contact_page` table (CMS > Contact already edits it); not in the registry. The footer emergency strip text is a `global` registry field.
  - Phone, WhatsApp, address, hours, map: from existing settings/branches, never duplicated in the registry.
- **Empty or failed lists** (doctors, specialties, blog posts, branches): render the DB rows. Zero rows or DB error → the list area shows a short editable empty-state message from the registry (e.g. "Our doctor profiles are being updated. Call us to book.") and the section heading stays. The old placeholder cards (`Dr. [Name]` etc.) are deleted, never rendered.
- **Server-side data:** pages also server-render the already-dynamic lists (doctors, specialties, blog cards, branches, site settings) by calling the same queries the `*-data.php` endpoints use — move each query into a function in `includes/` that both the endpoint and the page call. `assets/script.js` keeps filters, form submit, branch picker, open-status and reveal; its fetch-and-replace code is removed once each page renders server-side.

### 5.4 CMS editor — `cms/content.php`

- New sidebar item **"Page Content"** in `cms_header()` nav.
- `cms/content.php` lists the pages (Global, Home, About, Specialties, Doctors, Blog, Contact, 404) with last-updated time.
- `cms/content.php?page=home` shows one form per page: sections as collapsible panels in registry order, each field rendered from its spec (text input / textarea / image picker from the media library using the existing `*_media_options()` pattern / icon select from a fixed list of the SVG icons already in the markup). Repeatable items render as rows with Add / Remove / Move up / Move down (plain JS, no new dependency).
- Each section panel saves independently (own `<form>`, own CSRF token, POST back to the same page with `section=<key>`) so a validation error in one section doesn't lose edits in another. On success, redirect back with a flash message and anchor to the section.
- Validation server-side from the registry: required, max length (mb), URL scheme rule, media id exists, item count within `min/max`. Errors shown inline, input preserved.
- Each section has a "View on site" link to the page anchor and a "Reset to default" action that deletes that section's rows.
- `require_admin()` on every request; CSRF on every POST.

### 5.5 Seed / defaults

- No content rows are seeded. Defaults live in the registry (copied verbatim from current markup), so `db.sql` stays small and the live site is unchanged until edited.
- Images currently referenced as static files (`uploads/backgound.webp`, `uploads/team-BJF-74gA.jpg`, `uploads/logo.jpeg`, …) are the defaults for image fields; editors replace them by picking a media-library asset.

### 5.6 Rollout order (each step ships on its own, site unchanged after each)

1. Registry + `content.php` render helpers + `global` page; extract header/footer partials into all pages.
2. CMS editor (`cms/content.php`) working against the registry.
3. Convert pages one by one (home, about, specialties, doctors, blog, contact, 404): register sections with current copy as defaults, replace hard-coded markup with helper calls, server-render the dynamic lists.
4. Remove now-dead JS fetch/replace code and any `*-data.php` endpoint nothing calls anymore.

## 6. Constraints

- Plain PHP 8.2, MySQL 8.4, no Composer/npm, no new dependencies.
- Follow existing CMS conventions (§3) and styling (`cms/includes/layout.php` CSS); public styling via existing `assets/style.css` tokens — no visual redesign.
- Clean URLs and SEO (`public_seo.php`) unchanged.
- Works both under XAMPP (`/NMCclinic/` base path) and Docker (`/`).
- Every schema change ships as `migrations/<date>-*.sql` **and** in `db.sql`.

## 7. Security

- Trust boundary: CMS POST → DB → public HTML. All editor values are escaped at output; only the `*accent*` and paragraph/`<br>` transforms produce markup, and they wrap already-escaped text.
- URL fields restricted by scheme (blocks `javascript:` / `data:`).
- Image fields store media ids only, validated against `media_assets` (`deleted_at IS NULL`).
- Auth + CSRF on every CMS request; no new public write endpoints.

## 8. Testing / verification

- A PHP self-check script (`cms/tools/content_check.php`, CLI only) that asserts: every registry field has a default; every section key renders on its page with zero DB rows; `c_url()` rejects `javascript:`; accent and paragraph transforms escape `<script>`.
- Before/after: with an empty `page_sections` table and the same DB data, each converted page's visible text matches the pre-change page **after its JS has run** (capture both with Playwright). Only expected difference: places that showed placeholder cards now show the empty-state message.
- Browser check per CLAUDE.md: desktop 1440 / mobile 390 screenshots of each page and the CMS editor, console clean, edit → save → reload shows the change, long-text worst-case (`break-ui`).

## 9. Left to the builder

- Exact section/field inventory per page (derived from current markup, following §5.1 rules).
- Helper names and file split inside `includes/`.
- CMS editor CSS details within the existing CMS look.
- `page_sections.section_type` value (e.g. = section key), XAMPP base-path handling for relative URLs, per-page hero background image (currently one CSS rule, `style.css:208`; move to an inline `style` from the image field).
- Whether `*-data.php` endpoints are deleted or kept after step 4 (delete if unreferenced).
