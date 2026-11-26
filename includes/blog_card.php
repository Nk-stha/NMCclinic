<?php
declare(strict_types=1);

require_once __DIR__ . '/content.php';

/**
 * One blog post card, shared by the home page and the blog index.
 * $labels holds the page's editable strings (card_*). $featured adds the
 * blog index's big card: card_featured_placeholder and card_featured_prefix.
 */
function render_blog_card(array $post, array $labels, bool $featured = false): void
{
    $reviewed = $post['reviewed_at'] !== '' ? $post['reviewed_at'] : $post['published_at'];
    $arrow = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';
    $h = $featured ? 'h2' : 'h3';
    $placeholder = $featured ? 'card_featured_placeholder' : 'card_image_placeholder';
    ?>
        <a class="<?= $featured ? 'featured' : 'post-card' ?>" href="<?= public_e(content_safe_url($post['url'])) ?>" data-category="<?= public_e($post['category']) ?>">
          <div class="<?= $featured ? 'featured-photo' : 'post-photo' ?>"><?php if ($post['image_path']): ?><img src="<?= public_e(content_safe_url($post['image_path'])) ?>" alt="<?= public_e($post['image_alt']) ?>" loading="lazy"><?php else: ?><span class="ph-label"><?= c($labels, $placeholder) ?></span><?php endif; ?><span class="doc-tag"><?= $featured ? c($labels, 'card_featured_prefix') . ' ' : '' ?><?= public_e($post['category']) ?></span></div>
          <div class="<?= $featured ? 'featured-body' : 'post-body' ?>">
            <div class="post-meta"><?= $post['reading_time_minutes'] ? public_e((string) $post['reading_time_minutes']) . ' ' . c($labels, 'card_read_time_suffix') : c($labels, 'card_no_read_time') ?></div>
            <<?= $h ?>><?= public_e($post['title']) ?></<?= $h ?>>
            <p><?= public_e($post['excerpt']) ?></p>
            <div class="post-byline"><?= c($labels, 'card_reviewed_by') ?> <?= $post['reviewer_name'] !== '' ? public_e($post['reviewer_name']) : c($labels, 'card_default_reviewer') ?><?= $reviewed !== '' ? ' · ' . c($labels, 'card_reviewed_date') . ' ' . public_e($reviewed) : '' ?></div>
            <span class="link-cta inline"><?= c($labels, 'card_link_label') ?> <?= $arrow ?></span>
          </div>
        </a>
<?php
}
