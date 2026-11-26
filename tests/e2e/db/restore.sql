-- Run by global-teardown, or by hand after an interrupted run (see README).
-- Guard: this errors (and mysql exits non-zero before touching anything) unless the full
-- e2e_bak_* set exists. The set only appears through snapshot.sql's atomic RENAME, so it is complete.
SELECT 'e2e_bak_* backup set found' AS restore_guard FROM e2e_bak_page_sections, e2e_bak_section_items, e2e_bak_admin LIMIT 0;
START TRANSACTION;
DELETE FROM page_sections;
INSERT INTO page_sections SELECT * FROM e2e_bak_page_sections;
INSERT INTO section_items SELECT * FROM e2e_bak_section_items;
UPDATE cms_admin_users u JOIN e2e_bak_admin b ON b.id = u.id SET u.last_login_at = b.last_login_at, u.updated_at = b.updated_at;
COMMIT;
DROP TABLE e2e_bak_page_sections, e2e_bak_section_items, e2e_bak_admin;
