-- Run by global-setup. The suite edits CMS content and logs in (last_login_at); teardown puts it back.
-- Backups are built as e2e_tmp_* and renamed to e2e_bak_* in one atomic RENAME, so an e2e_bak_* set
-- always holds a complete copy. Nothing destructive runs before that rename. mysql stops at the first error.
DROP TABLE IF EXISTS e2e_tmp_page_sections, e2e_tmp_section_items, e2e_tmp_admin;
CREATE TABLE e2e_tmp_page_sections LIKE page_sections;
INSERT INTO e2e_tmp_page_sections SELECT * FROM page_sections;
CREATE TABLE e2e_tmp_section_items LIKE section_items;
INSERT INTO e2e_tmp_section_items SELECT * FROM section_items;
CREATE TABLE e2e_tmp_admin AS SELECT id, last_login_at, updated_at FROM cms_admin_users;
RENAME TABLE e2e_tmp_page_sections TO e2e_bak_page_sections,
             e2e_tmp_section_items TO e2e_bak_section_items,
             e2e_tmp_admin TO e2e_bak_admin;

DELETE FROM page_sections;

-- A second blog category with one published post, so the blog filter test really hides cards.
-- Older than the sample posts and not featured, so the first post on /blog is unchanged.
INSERT INTO blog_categories (name, slug, description, sort_order, is_visible)
VALUES ('E2E Category', 'e2e-suite-category', 'Created by the e2e suite; removed in teardown.', 99, 1);
INSERT INTO blog_posts (category_id, slug, title, excerpt, body_html_sanitized, published_at, status, is_featured, sort_order)
VALUES (LAST_INSERT_ID(), 'e2e-suite-post', 'E2E filter post', 'Created by the e2e suite.', '<p>Created by the e2e suite.</p>',
        '2000-01-01 00:00:00', 'published', 0, 99);
