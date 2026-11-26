-- Rows the suite creates: form submissions named "E2E …" and the seeded filter category/post (exact slugs).
DELETE FROM appointment_requests WHERE full_name LIKE 'E2E %';
DELETE FROM contact_inquiries WHERE full_name LIKE 'E2E %';
DELETE FROM blog_posts WHERE slug = 'e2e-suite-post';
DELETE FROM blog_categories WHERE slug = 'e2e-suite-category';
