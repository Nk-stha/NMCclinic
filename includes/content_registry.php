<?php
declare(strict_types=1);

const CONTENT_SECTION_COLUMNS = ['eyebrow', 'heading', 'subheading', 'body', 'primary_media_id', 'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url'];
const CONTENT_ITEM_COLUMNS = ['label', 'title', 'subtitle', 'body', 'value_text', 'icon_key', 'media_id', 'link_label', 'link_url'];

/**
 * Editable content per page: [slug => ['label', 'sections' => [key => ['label', 'fields', 'items']]]].
 * Non-column field names live in settings_json; item field names must be item columns.
 */
function content_registry(): array
{
    return [
        'global' => ['label' => 'Site-wide', 'sections' => [
            'header' => ['label' => 'Header', 'fields' => [
                'logo' => ['type' => 'image', 'label' => 'Logo', 'max' => 0, 'required' => false, 'default' => ['src' => 'uploads/logo.jpeg', 'alt' => 'NMC clinic logo']],
                'brand_name' => ['type' => 'text', 'label' => 'Brand name', 'max' => 30, 'required' => true, 'default' => 'NMC'],
                'brand_sub' => ['type' => 'text', 'label' => 'Brand subtitle', 'max' => 30, 'required' => false, 'default' => 'Clinic'],
                'cta_label' => ['type' => 'text', 'label' => 'Button label', 'max' => 30, 'required' => true, 'default' => 'Book a visit'],
                'cta_url' => ['type' => 'url', 'label' => 'Button link', 'max' => 200, 'required' => true, 'default' => 'home#appointment'],
            ], 'items' => null],
            'nav' => ['label' => 'Navigation', 'fields' => [], 'items' => [
                'item_type' => 'nav', 'label' => 'Menu links', 'min' => 1, 'max' => 8,
                'fields' => [
                    'label' => ['type' => 'text', 'label' => 'Label', 'max' => 30, 'required' => true, 'default' => 'Link'],
                    'link_url' => ['type' => 'url', 'label' => 'Link', 'max' => 200, 'required' => true, 'default' => 'home'],
                ],
                'default' => [
                    ['label' => 'Home', 'link_url' => 'home'],
                    ['label' => 'About', 'link_url' => 'about'],
                    ['label' => 'Specialties', 'link_url' => 'specialties'],
                    ['label' => 'Doctors', 'link_url' => 'doctors'],
                    ['label' => 'Blog', 'link_url' => 'blog'],
                    ['label' => 'Contact', 'link_url' => 'contact'],
                ],
            ]],
            'footer' => ['label' => 'Footer', 'fields' => [
                'logo' => ['type' => 'image', 'label' => 'Footer logo', 'max' => 0, 'required' => false, 'default' => ['src' => 'uploads/logo.jpeg', 'alt' => 'NMC clinic logo']],
                'about' => ['type' => 'text', 'label' => 'About text', 'max' => 300, 'required' => true,
                    'default' => "A community clinic in Butwal offering specialist consultation, diagnostics, physiotherapy, women's health and kidney care."],
                'services_heading' => ['type' => 'text', 'label' => 'Services heading', 'max' => 30, 'required' => true, 'default' => 'Services'],
                'contact_heading' => ['type' => 'text', 'label' => 'Contact heading', 'max' => 30, 'required' => true, 'default' => 'Contact'],
                'emergency_text' => ['type' => 'text', 'label' => 'Emergency notice', 'max' => 200, 'required' => true, 'default' => 'For medical emergencies, contact the nearest emergency service or hospital immediately.'],
                'copyright' => ['type' => 'text', 'label' => 'Copyright text (after the year)', 'max' => 120, 'required' => true, 'default' => 'NMC Clinic, Butwal. All rights reserved.'],
                'legal_privacy_label' => ['type' => 'text', 'label' => 'Privacy link label', 'max' => 40, 'required' => true, 'default' => 'Privacy Policy'],
                'legal_terms_label' => ['type' => 'text', 'label' => 'Terms link label', 'max' => 40, 'required' => true, 'default' => 'Terms & Conditions'],
                'legal_accessibility_label' => ['type' => 'text', 'label' => 'Accessibility link label', 'max' => 40, 'required' => true, 'default' => 'Accessibility'],
            ], 'items' => null],
            'footer_services' => ['label' => 'Footer services links', 'fields' => [], 'items' => [
                'item_type' => 'link', 'label' => 'Links', 'min' => 0, 'max' => 10,
                'fields' => [
                    'label' => ['type' => 'text', 'label' => 'Label', 'max' => 50, 'required' => true, 'default' => 'Link'],
                    'link_url' => ['type' => 'url', 'label' => 'Link', 'max' => 200, 'required' => true, 'default' => 'specialties'],
                ],
                'default' => [
                    ['label' => 'Specialist consultation', 'link_url' => 'specialties#consultation'],
                    ['label' => 'Advanced diagnostics', 'link_url' => 'specialties#diagnostics'],
                    ['label' => 'Laboratory services', 'link_url' => 'specialties#laboratory'],
                    ['label' => 'Physiotherapy', 'link_url' => 'specialties#physiotherapy'],
                    ['label' => 'Kidney care', 'link_url' => 'specialties#kidney'],
                ],
            ]],
            'mobile_bar' => ['label' => 'Mobile bar and WhatsApp button', 'fields' => [
                'call_label' => ['type' => 'text', 'label' => 'Call label', 'max' => 20, 'required' => true, 'default' => 'Call'],
                'whatsapp_label' => ['type' => 'text', 'label' => 'WhatsApp label', 'max' => 20, 'required' => true, 'default' => 'WhatsApp'],
                'book_label' => ['type' => 'text', 'label' => 'Book label', 'max' => 30, 'required' => true, 'default' => 'Book a visit'],
                'book_url' => ['type' => 'url', 'label' => 'Book link', 'max' => 200, 'required' => true, 'default' => 'home#appointment'],
                'float_label' => ['type' => 'text', 'label' => 'Floating WhatsApp button text', 'max' => 30, 'required' => true, 'default' => 'WhatsApp us'],
            ], 'items' => null],
        ]],
        'home' => ['label' => 'Home', 'sections' => [
            'hero' => ['label' => 'Hero', 'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Pill text', 'max' => 60, 'required' => false, 'default' => 'Butwal and Bhairahawa'],
                'heading' => ['type' => 'heading', 'label' => 'Heading (wrap words in *stars* to highlight)', 'max' => 90, 'required' => true, 'default' => 'Specialists, lab tests and physio in *one visit*'],
                'subheading' => ['type' => 'text', 'label' => 'Lead text', 'max' => 250, 'required' => false, 'default' => 'See an experienced doctor, finish your tests on site, and leave knowing your next step.'],
                'primary_cta_label' => ['type' => 'text', 'label' => 'Button label', 'max' => 30, 'required' => true, 'default' => 'Book a visit'],
                'primary_cta_url' => ['type' => 'url', 'label' => 'Button link', 'max' => 200, 'required' => true, 'default' => '#appointment'],
                'primary_media_id' => ['type' => 'image', 'label' => 'Background image', 'max' => 0, 'required' => false, 'default' => ['src' => 'uploads/backgound.webp', 'alt' => '']],
            ], 'items' => null],
            'info_strip' => ['label' => 'Info strip', 'fields' => [
                'status_label' => ['type' => 'text', 'label' => 'Status label', 'max' => 40, 'required' => true, 'default' => 'Butwal clinic today'],
                'emergency_label' => ['type' => 'text', 'label' => 'Phone label', 'max' => 40, 'required' => true, 'default' => 'Emergency line'],
            ], 'items' => null],
            'services' => ['label' => 'Services (cards come from Specialties)', 'fields' => [
                'heading' => ['type' => 'heading', 'label' => 'Heading', 'max' => 90, 'required' => true, 'default' => 'Care for the health needs that matter most'],
                'subheading' => ['type' => 'text', 'label' => 'Lead text', 'max' => 250, 'required' => false, 'default' => 'From your first consultation to diagnosis and recovery, in one clinic at Traffic Chowk.'],
                'card_link_label' => ['type' => 'text', 'label' => 'Card link label', 'max' => 30, 'required' => true, 'default' => 'Learn more'],
                'empty_message' => ['type' => 'textarea', 'label' => 'Message when no specialties are published', 'max' => 200, 'required' => true, 'default' => 'Our services are being updated. Please call us to ask about a specialty.'],
            ], 'items' => null],
            'about' => ['label' => 'About', 'fields' => [
                'primary_media_id' => ['type' => 'image', 'label' => 'Photo', 'max' => 0, 'required' => false, 'default' => ['src' => 'uploads/team-BJF-74gA.jpg', 'alt' => 'The NMC clinic team, doctors, nurses, and staff at the Butwal clinic']],
                'stat_value' => ['type' => 'text', 'label' => 'Badge number', 'max' => 10, 'required' => true, 'default' => '6'],
                'stat_label' => ['type' => 'textarea', 'label' => 'Badge text (new line = line break)', 'max' => 60, 'required' => true, 'default' => "specialties in\none clinic"],
                'heading' => ['type' => 'heading', 'label' => 'Heading', 'max' => 90, 'required' => true, 'default' => 'Care that starts with listening'],
                'subheading' => ['type' => 'text', 'label' => 'Lead text', 'max' => 250, 'required' => false, 'default' => 'Specialist care, testing and recovery in one place, with a team that listens and explains your next step.'],
                'primary_cta_label' => ['type' => 'text', 'label' => 'Link label', 'max' => 40, 'required' => true, 'default' => 'More about NMC'],
                'primary_cta_url' => ['type' => 'url', 'label' => 'Link', 'max' => 200, 'required' => true, 'default' => 'about'],
            ], 'items' => null],
            'diagnostics' => ['label' => 'Diagnostics', 'fields' => [
                'heading' => ['type' => 'heading', 'label' => 'Heading', 'max' => 90, 'required' => true, 'default' => 'Faster answers. Clearer next steps.'],
                'subheading' => ['type' => 'text', 'label' => 'Lead text', 'max' => 250, 'required' => false, 'default' => 'Most tests happen here, so you are not travelling between labs while you wait for answers.'],
                'primary_cta_label' => ['type' => 'text', 'label' => 'Button label', 'max' => 40, 'required' => true, 'default' => 'See diagnostic services'],
                'primary_cta_url' => ['type' => 'url', 'label' => 'Button link', 'max' => 200, 'required' => true, 'default' => 'specialties#diagnostics'],
            ], 'items' => [
                'item_type' => 'step', 'label' => 'Steps', 'min' => 1, 'max' => 6,
                'fields' => [
                    'icon_key' => ['type' => 'icon', 'label' => 'Icon', 'max' => 0, 'required' => false, 'default' => ''],
                    'title' => ['type' => 'text', 'label' => 'Title', 'max' => 60, 'required' => true, 'default' => 'Step'],
                    'body' => ['type' => 'text', 'label' => 'Text', 'max' => 200, 'required' => false, 'default' => ''],
                ],
                'default' => [
                    ['icon_key' => 'sample', 'title' => 'Sample taken here', 'body' => 'Samples are collected at the clinic, with no second trip to another lab.'],
                    ['icon_key' => 'activity', 'title' => 'Tested in-house', 'body' => 'Our laboratory and imaging team run routine and specialist tests on site.'],
                    ['icon_key' => 'report', 'title' => 'Report ready', 'body' => 'Reception tells you when to expect your report before you leave.'],
                    ['icon_key' => 'review', 'title' => 'Reviewed with your doctor', 'body' => 'Go through the results with the right doctor and agree on the next step.'],
                ],
            ]],
            'doctors' => ['label' => 'Doctors (cards come from Doctors)', 'fields' => [
                'heading' => ['type' => 'heading', 'label' => 'Heading', 'max' => 90, 'required' => true, 'default' => 'Meet the doctors behind your care'],
                'subheading' => ['type' => 'text', 'label' => 'Lead text', 'max' => 250, 'required' => false, 'default' => "See each doctor's specialty and clinic days, then book the one who fits."],
                'primary_cta_label' => ['type' => 'text', 'label' => 'Link label', 'max' => 40, 'required' => true, 'default' => 'See all doctors'],
                'primary_cta_url' => ['type' => 'url', 'label' => 'Link', 'max' => 200, 'required' => true, 'default' => 'doctors'],
                'card_photo_placeholder' => ['type' => 'text', 'label' => 'Card text when a doctor has no photo', 'max' => 40, 'required' => true, 'default' => 'doctor photo'],
                'card_reg_prefix' => ['type' => 'text', 'label' => 'Card registration prefix', 'max' => 30, 'required' => true, 'default' => 'NMC Reg. No.'],
                'card_cta_label' => ['type' => 'text', 'label' => 'Card button label', 'max' => 30, 'required' => true, 'default' => 'Book a visit'],
                'empty_message' => ['type' => 'textarea', 'label' => 'Message when no doctors are published', 'max' => 200, 'required' => true, 'default' => 'Our doctor profiles are being updated. Please call us to book.'],
            ], 'items' => null],
            'why_choose' => ['label' => 'Why choose us', 'fields' => [
                'heading' => ['type' => 'heading', 'label' => 'Heading', 'max' => 90, 'required' => true, 'default' => 'Why patients choose NMC'],
                'subheading' => ['type' => 'text', 'label' => 'Lead text', 'max' => 250, 'required' => false, 'default' => 'Specialists, tests and follow-up in one place, with a team that explains things plainly.'],
            ], 'items' => [
                'item_type' => 'reason', 'label' => 'Reasons', 'min' => 1, 'max' => 6,
                'fields' => [
                    'icon_key' => ['type' => 'icon', 'label' => 'Icon', 'max' => 0, 'required' => false, 'default' => ''],
                    'title' => ['type' => 'text', 'label' => 'Title', 'max' => 60, 'required' => true, 'default' => 'Reason'],
                    'body' => ['type' => 'text', 'label' => 'Text', 'max' => 200, 'required' => false, 'default' => ''],
                ],
                'default' => [
                    ['icon_key' => 'award', 'title' => 'Experienced specialists', 'body' => 'Consult qualified doctors across the specialties families need most.'],
                    ['icon_key' => 'building', 'title' => 'Diagnostics under one roof', 'body' => 'Consultation and the tests you need, in the same building on the same day.'],
                    ['icon_key' => 'compass', 'title' => 'Clear guidance at every step', 'body' => 'Understand your condition, your results and your options before you go home.'],
                    ['icon_key' => 'team', 'title' => 'Respectful, coordinated care', 'body' => 'Doctors, lab and reception work together, so you do not repeat your story.'],
                ],
            ]],
            'appointment' => ['label' => 'Appointment form (services come from Specialties)', 'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'max' => 40, 'required' => false, 'default' => 'Appointments'],
                'heading' => ['type' => 'heading', 'label' => 'Heading', 'max' => 90, 'required' => true, 'default' => 'Book your visit'],
                'subheading' => ['type' => 'text', 'label' => 'Lead text', 'max' => 250, 'required' => false, 'default' => 'Choose a service and a day that suits you. Our reception team will call to confirm the time.'],
                'help_title' => ['type' => 'text', 'label' => 'Help box title', 'max' => 60, 'required' => true, 'default' => 'Not sure which department?'],
                'help_text' => ['type' => 'text', 'label' => 'Help box text (the phone number follows it)', 'max' => 60, 'required' => true, 'default' => 'Call reception at'],
                'name_label' => ['type' => 'text', 'label' => 'Name label', 'max' => 40, 'required' => true, 'default' => 'Full name'],
                'name_placeholder' => ['type' => 'text', 'label' => 'Name placeholder', 'max' => 60, 'required' => true, 'default' => 'Your name'],
                'phone_label' => ['type' => 'text', 'label' => 'Phone label', 'max' => 40, 'required' => true, 'default' => 'Phone number'],
                'phone_placeholder' => ['type' => 'text', 'label' => 'Phone placeholder', 'max' => 60, 'required' => true, 'default' => '+977 …'],
                'service_label' => ['type' => 'text', 'label' => 'Service label', 'max' => 40, 'required' => true, 'default' => 'Service or department'],
                'service_placeholder' => ['type' => 'text', 'label' => 'Service first option', 'max' => 40, 'required' => true, 'default' => 'Select a service'],
                'date_label' => ['type' => 'text', 'label' => 'Date label', 'max' => 40, 'required' => true, 'default' => 'Preferred date'],
                'time_label' => ['type' => 'text', 'label' => 'Time label', 'max' => 40, 'required' => true, 'default' => 'Preferred time'],
                'time_any' => ['type' => 'text', 'label' => 'Time first option', 'max' => 40, 'required' => true, 'default' => 'Any time'],
                'time_morning' => ['type' => 'text', 'label' => 'Morning option (sent with the request)', 'max' => 40, 'required' => true, 'default' => 'Morning (8 AM-12 PM)'],
                'time_afternoon' => ['type' => 'text', 'label' => 'Afternoon option (sent with the request)', 'max' => 40, 'required' => true, 'default' => 'Afternoon (12-4 PM)'],
                'time_evening' => ['type' => 'text', 'label' => 'Evening option (sent with the request)', 'max' => 40, 'required' => true, 'default' => 'Evening (4-8 PM)'],
                'message_label' => ['type' => 'text', 'label' => 'Message label', 'max' => 40, 'required' => true, 'default' => 'Message'],
                'optional_label' => ['type' => 'text', 'label' => 'Optional marker', 'max' => 20, 'required' => true, 'default' => '(optional)'],
                'message_placeholder' => ['type' => 'text', 'label' => 'Message placeholder', 'max' => 80, 'required' => true, 'default' => 'Anything you\'d like us to know'],
                'consent_text' => ['type' => 'text', 'label' => 'Consent checkbox text', 'max' => 200, 'required' => true, 'default' => 'I agree to be contacted by NMC about this appointment request.'],
                'submit_label' => ['type' => 'text', 'label' => 'Submit button label', 'max' => 40, 'required' => true, 'default' => 'Request appointment'],
                'success_message' => ['type' => 'text', 'label' => 'Message after sending', 'max' => 200, 'required' => true, 'default' => 'Request received. Our reception team will call you to confirm the time.'],
                'form_note' => ['type' => 'text', 'label' => 'Note under the form', 'max' => 250, 'required' => false, 'default' => 'This request does not confirm your appointment. Our reception team will call you to confirm the date and time.'],
            ], 'items' => null],
            'before_visit' => ['label' => 'Before you come in', 'fields' => [
                'heading' => ['type' => 'heading', 'label' => 'Heading', 'max' => 90, 'required' => true, 'default' => 'Before you come in'],
                'subheading' => ['type' => 'text', 'label' => 'Lead text', 'max' => 250, 'required' => false, 'default' => 'Four things that make the visit quicker for you and for the doctor.'],
                'primary_cta_label' => ['type' => 'text', 'label' => 'Link label', 'max' => 40, 'required' => true, 'default' => 'Read the full patient guide'],
                'primary_cta_url' => ['type' => 'url', 'label' => 'Link', 'max' => 200, 'required' => true, 'default' => 'contact'],
            ], 'items' => [
                'item_type' => 'tip', 'label' => 'Tips', 'min' => 1, 'max' => 6,
                'fields' => [
                    'icon_key' => ['type' => 'icon', 'label' => 'Icon', 'max' => 0, 'required' => false, 'default' => ''],
                    'title' => ['type' => 'text', 'label' => 'Title', 'max' => 60, 'required' => true, 'default' => 'Tip'],
                    'body' => ['type' => 'text', 'label' => 'Text', 'max' => 200, 'required' => false, 'default' => ''],
                ],
                'default' => [
                    ['icon_key' => 'clipboard', 'title' => 'What to bring', 'body' => 'A valid ID, previous reports, prescriptions and a list of the medicines you take.'],
                    ['icon_key' => 'clock', 'title' => 'Opening hours', 'body' => 'Check the schedule before travelling, especially around public holidays.'],
                    ['icon_key' => 'preparation', 'title' => 'Test preparation', 'body' => 'Some tests need fasting or other preparation. Call us if you are unsure.'],
                    ['icon_key' => 'payment', 'title' => 'Reports and payment', 'body' => 'Ask reception when your report will be ready and which payment methods we accept.'],
                ],
            ]],
            'articles' => ['label' => 'Health articles (latest 3 blog posts)', 'fields' => [
                'heading' => ['type' => 'heading', 'label' => 'Heading', 'max' => 90, 'required' => true, 'default' => 'Clear information for better health decisions'],
                'subheading' => ['type' => 'text', 'label' => 'Lead text', 'max' => 250, 'required' => false, 'default' => 'Practical guides, reviewed by our doctors, on common conditions, tests and treatments.'],
                'primary_cta_label' => ['type' => 'text', 'label' => 'Button label', 'max' => 40, 'required' => true, 'default' => 'All health guides'],
                'primary_cta_url' => ['type' => 'url', 'label' => 'Button link', 'max' => 200, 'required' => true, 'default' => 'blog'],
                'card_image_placeholder' => ['type' => 'text', 'label' => 'Card text when a post has no image', 'max' => 40, 'required' => true, 'default' => 'article image'],
                'card_read_time_suffix' => ['type' => 'text', 'label' => 'Reading time suffix (after the minutes)', 'max' => 20, 'required' => true, 'default' => 'min read'],
                'card_no_read_time' => ['type' => 'text', 'label' => 'Card text when reading time is unknown', 'max' => 30, 'required' => true, 'default' => 'Health guide'],
                'card_reviewed_by' => ['type' => 'text', 'label' => 'Reviewer prefix', 'max' => 30, 'required' => true, 'default' => 'Reviewed by'],
                'card_default_reviewer' => ['type' => 'text', 'label' => 'Reviewer when none is set', 'max' => 60, 'required' => true, 'default' => 'NMC medical team'],
                'card_reviewed_date' => ['type' => 'text', 'label' => 'Review date prefix', 'max' => 30, 'required' => true, 'default' => 'Reviewed'],
                'card_link_label' => ['type' => 'text', 'label' => 'Card link label', 'max' => 30, 'required' => true, 'default' => 'Read article'],
                'empty_message' => ['type' => 'textarea', 'label' => 'Message when no posts are published', 'max' => 200, 'required' => true, 'default' => 'New health guides are on the way. Please check back soon.'],
            ], 'items' => null],
            'visit' => ['label' => 'Visit us (branches come from Branches)', 'fields' => [
                'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'max' => 40, 'required' => false, 'default' => 'Visit us'],
                'heading' => ['type' => 'heading', 'label' => 'Heading', 'max' => 90, 'required' => true, 'default' => 'Find a branch near you'],
                'subheading' => ['type' => 'text', 'label' => 'Lead text', 'max' => 250, 'required' => false, 'default' => 'Choose a branch to see it on the map. Call ahead, or walk in during opening hours.'],
                'email_label' => ['type' => 'text', 'label' => 'Email label (address comes from Settings)', 'max' => 40, 'required' => true, 'default' => 'Email us'],
                'hours_label' => ['type' => 'text', 'label' => 'Hours label (hours come from Settings)', 'max' => 40, 'required' => true, 'default' => 'Opening hours'],
                'directions_label' => ['type' => 'text', 'label' => 'Directions button label', 'max' => 40, 'required' => true, 'default' => 'Get directions'],
                'empty_message' => ['type' => 'textarea', 'label' => 'Message when no branches are listed', 'max' => 200, 'required' => true, 'default' => 'Branch details are being updated. Please call us for directions.'],
            ], 'items' => null],
        ]],
    ];
}

function content_page_spec(string $slug): array
{
    $registry = content_registry();
    if (!isset($registry[$slug])) {
        throw new InvalidArgumentException('Unknown content page: ' . $slug);
    }

    return $registry[$slug];
}
