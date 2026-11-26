-- Rows the suite creates: form submissions named "E2E …" that also carry the suite's phone/email
-- marker (see specs/forms-and-interactions.spec.ts), and the seeded filter category/post (exact slugs).
DELETE FROM appointment_requests WHERE full_name LIKE 'E2E %' AND phone = '+977 9800000000';
DELETE FROM contact_inquiries WHERE full_name LIKE 'E2E %' AND phone = '+977 9800000000' AND email = 'e2e-suite@example.test';
DELETE FROM blog_posts WHERE slug = 'e2e-suite-post';
DELETE FROM blog_categories WHERE slug = 'e2e-suite-category';
