-- NMC Clinic final CMS schema draft
-- Source: plan/Homepage.txt, plan/aboutpage.txt, plan/Specialties.txt,
-- plan/Doctors.txt, plan/Blogs.txt, plan/BlogDetail.txt, plan/Contact.txt
--
-- Target: MySQL 8.x / MariaDB-compatible InnoDB schema for a PHP/LAMPP stack.
-- Note: the exact database engine/version is not confirmed in the current source.
-- This file creates schema only. It does not insert production content.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- 01. Shared CMS foundation
-- ---------------------------------------------------------------------------

CREATE TABLE pages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(191) NOT NULL,
  page_type ENUM('home', 'about', 'specialties', 'doctors', 'blog_index', 'contact', 'privacy', 'terms', 'accessibility', 'generic') NOT NULL,
  locale VARCHAR(10) NOT NULL DEFAULT 'en',
  status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  UNIQUE KEY uq_pages_slug_locale (slug, locale),
  KEY idx_pages_type_status (page_type, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE media_assets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  file_path VARCHAR(500) NOT NULL,
  mime_type VARCHAR(100) NOT NULL,
  width INT UNSIGNED NULL,
  height INT UNSIGNED NULL,
  file_size BIGINT UNSIGNED NULL,
  alt_text VARCHAR(255) NULL,
  title_text VARCHAR(255) NULL,
  caption VARCHAR(255) NULL,
  focal_point_x DECIMAL(5,2) NULL,
  focal_point_y DECIMAL(5,2) NULL,
  is_public TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  KEY idx_media_mime_type (mime_type),
  KEY idx_media_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cms_admin_users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(191) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  status ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cms_admin_users_email (email),
  KEY idx_cms_admin_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE page_sections (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_id BIGINT UNSIGNED NOT NULL,
  section_key VARCHAR(100) NOT NULL,
  section_type VARCHAR(100) NOT NULL,
  eyebrow VARCHAR(100) NULL,
  heading VARCHAR(255) NULL,
  subheading VARCHAR(255) NULL,
  body TEXT NULL,
  primary_media_id BIGINT UNSIGNED NULL,
  primary_cta_label VARCHAR(100) NULL,
  primary_cta_url VARCHAR(500) NULL,
  secondary_cta_label VARCHAR(100) NULL,
  secondary_cta_url VARCHAR(500) NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
  settings_json JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  UNIQUE KEY uq_page_sections_key (page_id, section_key),
  KEY idx_page_sections_order (page_id, sort_order, is_visible),
  KEY idx_page_sections_media (primary_media_id),
  CONSTRAINT fk_page_sections_page
    FOREIGN KEY (page_id) REFERENCES pages(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_page_sections_media
    FOREIGN KEY (primary_media_id) REFERENCES media_assets(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE section_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  section_id BIGINT UNSIGNED NOT NULL,
  item_type VARCHAR(100) NOT NULL,
  label VARCHAR(100) NULL,
  title VARCHAR(255) NULL,
  subtitle VARCHAR(255) NULL,
  body TEXT NULL,
  value_text VARCHAR(100) NULL,
  icon_key VARCHAR(100) NULL,
  media_id BIGINT UNSIGNED NULL,
  link_label VARCHAR(100) NULL,
  link_url VARCHAR(500) NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  KEY idx_section_items_order (section_id, sort_order, is_visible),
  KEY idx_section_items_media (media_id),
  CONSTRAINT fk_section_items_section
    FOREIGN KEY (section_id) REFERENCES page_sections(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_section_items_media
    FOREIGN KEY (media_id) REFERENCES media_assets(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 02. Contact and global business data
-- ---------------------------------------------------------------------------

CREATE TABLE business_contact_settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  clinic_name VARCHAR(150) NOT NULL,
  address_line VARCHAR(255) NOT NULL,
  city VARCHAR(100) NOT NULL,
  district VARCHAR(100) NULL,
  country VARCHAR(100) NOT NULL,
  primary_phone VARCHAR(30) NOT NULL,
  secondary_phone VARCHAR(30) NULL,
  whatsapp_number VARCHAR(30) NULL,
  email VARCHAR(191) NOT NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  map_zoom TINYINT UNSIGNED NOT NULL DEFAULT 14,
  logo_media_id BIGINT UNSIGNED NULL,
  privacy_url VARCHAR(500) NULL,
  terms_url VARCHAR(500) NULL,
  accessibility_url VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_business_logo
    FOREIGN KEY (logo_media_id) REFERENCES media_assets(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE opening_hours (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  day_of_week TINYINT UNSIGNED NOT NULL,
  opens_at TIME NULL,
  closes_at TIME NULL,
  label VARCHAR(100) NULL,
  is_closed TINYINT(1) NOT NULL DEFAULT 0,
  sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_opening_hours_day (day_of_week),
  KEY idx_opening_hours_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE social_links (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  platform VARCHAR(50) NOT NULL,
  label VARCHAR(100) NOT NULL,
  url VARCHAR(500) NOT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_social_platform (platform),
  KEY idx_social_visible_order (is_visible, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_page (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_id BIGINT UNSIGNED NOT NULL,
  response_time_note VARCHAR(255) NULL,
  emergency_title VARCHAR(150) NULL,
  emergency_description TEXT NULL,
  emergency_phone_label VARCHAR(100) NULL,
  emergency_phone_number VARCHAR(30) NULL,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_contact_page_page (page_id),
  CONSTRAINT fk_contact_page_page
    FOREIGN KEY (page_id) REFERENCES pages(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_subjects (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(100) NOT NULL,
  value VARCHAR(100) NOT NULL,
  recipient_email VARCHAR(191) NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_contact_subject_value (value),
  KEY idx_contact_subjects_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_inquiries (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(191) NULL,
  subject_id BIGINT UNSIGNED NULL,
  message TEXT NOT NULL,
  consent_accepted TINYINT(1) NOT NULL DEFAULT 0,
  consent_at DATETIME NULL,
  status ENUM('new', 'contacted', 'closed', 'spam') NOT NULL DEFAULT 'new',
  source_page VARCHAR(191) NULL,
  ip_hash CHAR(64) NULL,
  user_agent VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  responded_at DATETIME NULL,
  deleted_at DATETIME NULL,
  KEY idx_contact_inquiries_status (status, created_at),
  KEY idx_contact_inquiries_subject (subject_id),
  CONSTRAINT fk_contact_inquiries_subject
    FOREIGN KEY (subject_id) REFERENCES contact_subjects(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 03. Specialties
-- ---------------------------------------------------------------------------

CREATE TABLE specialties (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(191) NOT NULL,
  title VARCHAR(150) NOT NULL,
  short_description VARCHAR(500) NULL,
  image_id BIGINT UNSIGNED NULL,
  status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  UNIQUE KEY uq_specialties_slug (slug),
  KEY idx_specialties_public_order (status, is_visible, sort_order),
  KEY idx_specialties_image (image_id),
  CONSTRAINT fk_specialties_image
    FOREIGN KEY (image_id) REFERENCES media_assets(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE specialty_points (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  specialty_id BIGINT UNSIGNED NOT NULL,
  body VARCHAR(255) NOT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_specialty_points_order (specialty_id, sort_order, is_visible),
  CONSTRAINT fk_specialty_points_specialty
    FOREIGN KEY (specialty_id) REFERENCES specialties(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 04. Doctors
-- ---------------------------------------------------------------------------

CREATE TABLE doctors (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(191) NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  specialty_id BIGINT UNSIGNED NULL,
  photo_id BIGINT UNSIGNED NULL,
  short_bio TEXT NULL,
  nmc_registration_number VARCHAR(100) NULL,
  status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
  availability_status ENUM('accepting', 'unavailable', 'hidden') NOT NULL DEFAULT 'hidden',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  UNIQUE KEY uq_doctors_slug (slug),
  KEY idx_doctors_public_order (status, availability_status, sort_order),
  KEY idx_doctors_specialty (specialty_id),
  KEY idx_doctors_photo (photo_id),
  CONSTRAINT fk_doctors_specialty
    FOREIGN KEY (specialty_id) REFERENCES specialties(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_doctors_photo
    FOREIGN KEY (photo_id) REFERENCES media_assets(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE doctor_qualifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  doctor_id BIGINT UNSIGNED NOT NULL,
  qualification VARCHAR(255) NOT NULL,
  institution VARCHAR(255) NULL,
  qualification_year SMALLINT UNSIGNED NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_doctor_qualifications_order (doctor_id, sort_order),
  CONSTRAINT fk_doctor_qualifications_doctor
    FOREIGN KEY (doctor_id) REFERENCES doctors(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE doctor_schedules (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  doctor_id BIGINT UNSIGNED NOT NULL,
  day_of_week TINYINT UNSIGNED NOT NULL,
  start_time TIME NULL,
  end_time TIME NULL,
  location_label VARCHAR(150) NULL,
  appointment_required TINYINT(1) NOT NULL DEFAULT 1,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_doctor_schedules_doctor_day (doctor_id, day_of_week, sort_order),
  CONSTRAINT fk_doctor_schedules_doctor
    FOREIGN KEY (doctor_id) REFERENCES doctors(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 05. Blog and medical article content
-- ---------------------------------------------------------------------------

CREATE TABLE blog_categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(191) NOT NULL,
  description TEXT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  UNIQUE KEY uq_blog_categories_slug (slug),
  KEY idx_blog_categories_visible (is_visible, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blog_posts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id BIGINT UNSIGNED NULL,
  slug VARCHAR(191) NOT NULL,
  title VARCHAR(255) NOT NULL,
  excerpt VARCHAR(500) NOT NULL,
  body_html_sanitized MEDIUMTEXT NULL,
  featured_image_id BIGINT UNSIGNED NULL,
  author_name VARCHAR(150) NULL,
  reviewer_name VARCHAR(150) NULL,
  reviewed_at DATETIME NULL,
  published_at DATETIME NULL,
  status ENUM('draft', 'review', 'published', 'archived') NOT NULL DEFAULT 'draft',
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  reading_time_minutes SMALLINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at DATETIME NULL,
  UNIQUE KEY uq_blog_posts_slug (slug),
  KEY idx_blog_posts_status_date (status, published_at),
  KEY idx_blog_posts_featured (is_featured, status),
  KEY idx_blog_posts_category (category_id, status, published_at),
  KEY idx_blog_posts_featured_image (featured_image_id),
  KEY idx_blog_posts_order (status, sort_order),
  CONSTRAINT fk_blog_posts_category
    FOREIGN KEY (category_id) REFERENCES blog_categories(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_blog_posts_featured_image
    FOREIGN KEY (featured_image_id) REFERENCES media_assets(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 06. Appointment and newsletter submissions
-- ---------------------------------------------------------------------------

CREATE TABLE appointment_requests (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  specialty_id BIGINT UNSIGNED NULL,
  preferred_date DATE NULL,
  preferred_time_slot VARCHAR(100) NULL,
  message TEXT NULL,
  consent_accepted TINYINT(1) NOT NULL DEFAULT 0,
  consent_at DATETIME NULL,
  status ENUM('new', 'contacted', 'scheduled', 'closed', 'spam') NOT NULL DEFAULT 'new',
  source_page VARCHAR(191) NULL,
  ip_hash CHAR(64) NULL,
  user_agent VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  responded_at DATETIME NULL,
  deleted_at DATETIME NULL,
  KEY idx_appointment_requests_status (status, created_at),
  KEY idx_appointment_requests_specialty (specialty_id),
  CONSTRAINT fk_appointment_requests_specialty
    FOREIGN KEY (specialty_id) REFERENCES specialties(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE newsletter_subscribers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(191) NOT NULL,
  status ENUM('pending', 'active', 'unsubscribed') NOT NULL DEFAULT 'pending',
  consent_source VARCHAR(191) NULL,
  subscribed_at DATETIME NULL,
  unsubscribed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_newsletter_email (email),
  KEY idx_newsletter_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE newsletter_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  subscriber_id BIGINT UNSIGNED NOT NULL,
  event_type VARCHAR(100) NOT NULL,
  metadata_json JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_newsletter_events_subscriber (subscriber_id, created_at),
  CONSTRAINT fk_newsletter_events_subscriber
    FOREIGN KEY (subscriber_id) REFERENCES newsletter_subscribers(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 07. SEO metadata
-- ---------------------------------------------------------------------------

CREATE TABLE seo_metadata (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_id BIGINT UNSIGNED NULL,
  doctor_id BIGINT UNSIGNED NULL,
  blog_post_id BIGINT UNSIGNED NULL,
  meta_title VARCHAR(255) NOT NULL,
  meta_description VARCHAR(500) NOT NULL,
  canonical_url VARCHAR(500) NULL,
  robots_index TINYINT(1) NOT NULL DEFAULT 1,
  robots_follow TINYINT(1) NOT NULL DEFAULT 1,
  og_title VARCHAR(255) NULL,
  og_description VARCHAR(500) NULL,
  og_image_id BIGINT UNSIGNED NULL,
  twitter_card ENUM('summary', 'summary_large_image') NOT NULL DEFAULT 'summary_large_image',
  schema_json_override JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_seo_page (page_id),
  UNIQUE KEY uq_seo_doctor (doctor_id),
  UNIQUE KEY uq_seo_blog_post (blog_post_id),
  KEY idx_seo_og_image (og_image_id),
  CONSTRAINT fk_seo_page
    FOREIGN KEY (page_id) REFERENCES pages(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_seo_doctor
    FOREIGN KEY (doctor_id) REFERENCES doctors(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_seo_blog_post
    FOREIGN KEY (blog_post_id) REFERENCES blog_posts(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_seo_og_image
    FOREIGN KEY (og_image_id) REFERENCES media_assets(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
