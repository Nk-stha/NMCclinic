-- Dummy sample data for the dev DB only (visual baselines and the e2e suite). It deletes and replaces
-- contact settings and opening hours, so never load it into a real database.
DELETE FROM specialties WHERE slug IN ('nephrology','physiotherapy');
DELETE FROM doctors WHERE slug IN ('dr-sample-one','dr-sample-two');
DELETE FROM blog_posts WHERE slug IN ('sample-post-one','sample-post-two');
DELETE FROM blog_categories WHERE slug='health-tips';
DELETE FROM business_contact_settings;
DELETE FROM opening_hours;

INSERT INTO specialties (slug,title,short_description,status,sort_order,is_visible) VALUES
('nephrology','Nephrology','Kidney care and dialysis support.','published',1,1),
('physiotherapy','Physiotherapy','Rehabilitation and movement therapy.','published',2,1);
INSERT INTO specialty_points (specialty_id,body,sort_order) SELECT id,'Kidney function testing',1 FROM specialties WHERE slug='nephrology';
INSERT INTO specialty_points (specialty_id,body,sort_order) SELECT id,'Dialysis guidance',2 FROM specialties WHERE slug='nephrology';
INSERT INTO specialty_points (specialty_id,body,sort_order) SELECT id,'Back and neck pain therapy',1 FROM specialties WHERE slug='physiotherapy';
INSERT INTO specialty_points (specialty_id,body,sort_order) SELECT id,'Post-surgery rehabilitation',2 FROM specialties WHERE slug='physiotherapy';

INSERT INTO doctors (slug,full_name,specialty_id,short_bio,nmc_registration_number,status,availability_status,sort_order)
SELECT 'dr-sample-one','Dr. Sample One',id,'Sample nephrologist bio.','NMC-0001','published','accepting',1 FROM specialties WHERE slug='nephrology';
INSERT INTO doctors (slug,full_name,specialty_id,short_bio,nmc_registration_number,status,availability_status,sort_order)
SELECT 'dr-sample-two','Dr. Sample Two',id,'Sample physiotherapist bio.','NMC-0002','published','accepting',2 FROM specialties WHERE slug='physiotherapy';
INSERT INTO doctor_qualifications (doctor_id,qualification,institution,qualification_year,sort_order) SELECT id,'MD Nephrology','Sample University',2010,1 FROM doctors WHERE slug='dr-sample-one';
INSERT INTO doctor_qualifications (doctor_id,qualification,institution,qualification_year,sort_order) SELECT id,'BPT','Sample College',2012,1 FROM doctors WHERE slug='dr-sample-two';
INSERT INTO doctor_schedules (doctor_id,day_of_week,start_time,end_time,location_label,sort_order) SELECT id,1,'10:00:00','14:00:00','Main Clinic',1 FROM doctors WHERE slug='dr-sample-one';
INSERT INTO doctor_schedules (doctor_id,day_of_week,start_time,end_time,location_label,sort_order) SELECT id,3,'09:00:00','13:00:00','Main Clinic',1 FROM doctors WHERE slug='dr-sample-two';

INSERT INTO blog_categories (name,slug,description,sort_order,is_visible) VALUES ('Health Tips','health-tips','General health tips.',1,1);
INSERT INTO blog_posts (category_id,slug,title,excerpt,body_html_sanitized,author_name,published_at,status,is_featured,sort_order,reading_time_minutes)
SELECT id,'sample-post-one','Sample Post One','First sample excerpt.','<p>First sample body.</p>','Dr. Sample One','2026-01-10 09:00:00','published',1,1,3 FROM blog_categories WHERE slug='health-tips';
INSERT INTO blog_posts (category_id,slug,title,excerpt,body_html_sanitized,author_name,published_at,status,is_featured,sort_order,reading_time_minutes)
SELECT id,'sample-post-two','Sample Post Two','Second sample excerpt.','<p>Second sample body.</p>','Dr. Sample Two','2026-01-05 09:00:00','published',0,2,4 FROM blog_categories WHERE slug='health-tips';

INSERT INTO business_contact_settings (clinic_name,address_line,city,district,country,primary_phone,whatsapp_number,email,privacy_url,terms_url,accessibility_url)
VALUES ('NMC Clinic','Traffic Chowk','Butwal','Rupandehi','Nepal','+977 71-000000','+977 9800000000','info@example.com','#privacy','#terms','#accessibility');
INSERT INTO opening_hours (day_of_week,opens_at,closes_at,is_closed,sort_order) VALUES
(0,'08:00','20:00',0,0),(1,'08:00','20:00',0,1),(2,'08:00','20:00',0,2),(3,'08:00','20:00',0,3),(4,'08:00','20:00',0,4),(5,'08:00','20:00',0,5),(6,'09:00','14:00',0,6);
