-- Adds CMS-managed clinic branches (Home "Visit us" and Contact page map picker).
-- Safe to run more than once: the table is only created if missing and the
-- seed rows are matched on slug.
--
-- Run against an existing database, for example:
--   mysql -u clinic_user -p clinic < migrations/2026-10-11-clinic-branches.sql
-- Docker:
--   docker compose exec -T db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' < migrations/2026-10-11-clinic-branches.sql

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS clinic_branches (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(191) NOT NULL,
  name VARCHAR(150) NOT NULL,
  tag_label VARCHAR(60) NULL,
  address_line VARCHAR(255) NOT NULL,
  city VARCHAR(100) NOT NULL,
  district VARCHAR(100) NULL,
  country VARCHAR(100) NOT NULL DEFAULT 'Nepal',
  phone VARCHAR(30) NULL,
  email VARCHAR(191) NULL,
  hours_note VARCHAR(255) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  map_zoom TINYINT UNSIGNED NOT NULL DEFAULT 15,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  UNIQUE KEY uq_clinic_branches_slug (slug),
  KEY idx_clinic_branches_public_order (is_visible, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The two branches already shown on the site. Coordinates are approximate;
-- confirm them in CMS > Branches.
INSERT INTO clinic_branches
  (slug, name, tag_label, address_line, city, district, country, phone, hours_note, latitude, longitude, map_zoom, is_primary, is_visible, sort_order)
VALUES
  ('butwal', 'Butwal Branch', 'Main clinic', 'Traffic Chowk', 'Butwal', 'Rupandehi', 'Nepal', '+977 71-000000', 'Sun-Fri 8 AM-8 PM, Sat 9 AM-2 PM', 27.7000000, 83.4480000, 15, 1, 1, 0),
  ('bhairahawa', 'Bhairahawa Branch', 'Branch', 'Siddharthanagar', 'Bhairahawa', 'Rupandehi', 'Nepal', '+977 98-0000000', NULL, 27.5050000, 83.4520000, 15, 0, 1, 1)
ON DUPLICATE KEY UPDATE updated_at = updated_at;
