-- Run by global-setup. The suite edits CMS content and logs in (last_login_at); teardown puts it back.
CREATE TABLE e2e_bak_page_sections LIKE page_sections;
CREATE TABLE e2e_bak_section_items LIKE section_items;
CREATE TABLE e2e_bak_admin AS SELECT id, last_login_at, updated_at FROM cms_admin_users;
INSERT INTO e2e_bak_page_sections SELECT * FROM page_sections;
INSERT INTO e2e_bak_section_items SELECT * FROM section_items;
DELETE FROM page_sections;
