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
            'footer' => ['label' => 'Footer', 'fields' => [
                'about' => ['type' => 'text', 'label' => 'About text', 'max' => 300, 'required' => true,
                    'default' => "A community clinic in Butwal offering specialist consultation, diagnostics, physiotherapy, women's health and kidney care."],
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
