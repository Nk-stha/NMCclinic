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
