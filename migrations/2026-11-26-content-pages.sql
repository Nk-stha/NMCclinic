-- Hidden pages that own site-wide / shared editable content (not routed, not in the sitemap).
INSERT INTO pages (slug, page_type, locale, status, published_at) VALUES
('global', 'generic', 'en', 'published', NOW()),
('blog_detail', 'generic', 'en', 'published', NOW()),
('404', 'generic', 'en', 'published', NOW())
ON DUPLICATE KEY UPDATE status = status;
