<?php
declare(strict_types=1);

require_once __DIR__ . '/image_picker.php';

/**
 * Retrieve SEO record for a given target entity.
 */
function seo_find_by_target(string $targetType, int $targetId): ?array
{
    $column = match ($targetType) {
        'page' => 'page_id',
        'doctor' => 'doctor_id',
        'blog_post' => 'blog_post_id',
        default => null,
    };

    if (!$column) {
        return null;
    }

    $stmt = db()->prepare("SELECT * FROM seo_metadata WHERE {$column} = :id LIMIT 1");
    $stmt->execute([':id' => $targetId]);
    $seo = $stmt->fetch();

    return $seo ?: null;
}

/**
 * Save or update SEO metadata for a target entity inside an active transaction or standalone.
 */
function seo_save_for_target(string $targetType, int $targetId, array $data): int
{
    $column = match ($targetType) {
        'page' => 'page_id',
        'doctor' => 'doctor_id',
        'blog_post' => 'blog_post_id',
        default => throw new InvalidArgumentException("Invalid SEO target type: {$targetType}"),
    };

    $existing = seo_find_by_target($targetType, $targetId);

    $metaTitle = trim((string) ($data['seo_meta_title'] ?? $data['meta_title'] ?? ''));
    $metaDesc = trim((string) ($data['seo_meta_description'] ?? $data['meta_description'] ?? ''));
    $canonical = trim((string) ($data['seo_canonical_url'] ?? $data['canonical_url'] ?? ''));
    $robotsIndex = isset($data['seo_robots_index']) ? (int) $data['seo_robots_index'] : (isset($data['robots_index']) ? (int) $data['robots_index'] : 1);
    $robotsFollow = isset($data['seo_robots_follow']) ? (int) $data['seo_robots_follow'] : (isset($data['robots_follow']) ? (int) $data['robots_follow'] : 1);
    $ogTitle = trim((string) ($data['seo_og_title'] ?? $data['og_title'] ?? ''));
    $ogDesc = trim((string) ($data['seo_og_description'] ?? $data['og_description'] ?? ''));
    $ogImageId = !empty($data['seo_og_image_id']) ? (int) $data['seo_og_image_id'] : (!empty($data['og_image_id']) ? (int) $data['og_image_id'] : null);
    $twitterCard = in_array($data['seo_twitter_card'] ?? $data['twitter_card'] ?? '', ['summary', 'summary_large_image'], true)
        ? (string) ($data['seo_twitter_card'] ?? $data['twitter_card'])
        : 'summary_large_image';
    $schemaOverride = trim((string) ($data['seo_schema_json_override'] ?? $data['schema_json_override'] ?? ''));

    if ($existing) {
        $stmt = db()->prepare(
            'UPDATE seo_metadata SET
                meta_title = :meta_title,
                meta_description = :meta_description,
                canonical_url = :canonical_url,
                robots_index = :robots_index,
                robots_follow = :robots_follow,
                og_title = :og_title,
                og_description = :og_description,
                og_image_id = :og_image_id,
                twitter_card = :twitter_card,
                schema_json_override = :schema_json_override
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => (int) $existing['id'],
            ':meta_title' => $metaTitle,
            ':meta_description' => $metaDesc,
            ':canonical_url' => $canonical !== '' ? $canonical : null,
            ':robots_index' => $robotsIndex,
            ':robots_follow' => $robotsFollow,
            ':og_title' => $ogTitle !== '' ? $ogTitle : null,
            ':og_description' => $ogDesc !== '' ? $ogDesc : null,
            ':og_image_id' => $ogImageId,
            ':twitter_card' => $twitterCard,
            ':schema_json_override' => $schemaOverride !== '' ? $schemaOverride : null,
        ]);

        return (int) $existing['id'];
    }

    $stmt = db()->prepare(
        "INSERT INTO seo_metadata (
            {$column}, meta_title, meta_description, canonical_url, robots_index, robots_follow,
            og_title, og_description, og_image_id, twitter_card, schema_json_override
        ) VALUES (
            :target_id, :meta_title, :meta_description, :canonical_url, :robots_index, :robots_follow,
            :og_title, :og_description, :og_image_id, :twitter_card, :schema_json_override
        )"
    );
    $stmt->execute([
        ':target_id' => $targetId,
        ':meta_title' => $metaTitle,
        ':meta_description' => $metaDesc,
        ':canonical_url' => $canonical !== '' ? $canonical : null,
        ':robots_index' => $robotsIndex,
        ':robots_follow' => $robotsFollow,
        ':og_title' => $ogTitle !== '' ? $ogTitle : null,
        ':og_description' => $ogDesc !== '' ? $ogDesc : null,
        ':og_image_id' => $ogImageId,
        ':twitter_card' => $twitterCard,
        ':schema_json_override' => $schemaOverride !== '' ? $schemaOverride : null,
    ]);

    return (int) db()->lastInsertId();
}

/**
 * Renders the unified expandable SEO component.
 */
function render_seo_form_section(array $seoData, array $mediaOptions, string $targetType = 'blog_post'): void
{
    $metaTitle = (string) ($seoData['seo_meta_title'] ?? $seoData['meta_title'] ?? '');
    $metaDesc = (string) ($seoData['seo_meta_description'] ?? $seoData['meta_description'] ?? '');
    $canonical = (string) ($seoData['seo_canonical_url'] ?? $seoData['canonical_url'] ?? '');
    $robotsIndex = isset($seoData['seo_robots_index']) ? (int) $seoData['seo_robots_index'] : (isset($seoData['robots_index']) ? (int) $seoData['robots_index'] : 1);
    $robotsFollow = isset($seoData['seo_robots_follow']) ? (int) $seoData['seo_robots_follow'] : (isset($seoData['robots_follow']) ? (int) $seoData['robots_follow'] : 1);
    $ogTitle = (string) ($seoData['seo_og_title'] ?? $seoData['og_title'] ?? '');
    $ogDesc = (string) ($seoData['seo_og_description'] ?? $seoData['og_description'] ?? '');
    $ogImageId = !empty($seoData['seo_og_image_id']) ? (int) $seoData['seo_og_image_id'] : (!empty($seoData['og_image_id']) ? (int) $seoData['og_image_id'] : null);
    $twitterCard = (string) ($seoData['seo_twitter_card'] ?? $seoData['twitter_card'] ?? 'summary_large_image');
    $schemaOverride = (string) ($seoData['seo_schema_json_override'] ?? $seoData['schema_json_override'] ?? '');
    ?>
    <details class="card form-card seo-settings-panel" style="margin-top:24px;border-radius:12px;overflow:hidden">
      <summary style="padding:18px 24px;cursor:pointer;list-style:none;display:flex;align-items:center;justify-content:space-between;background:var(--surface-low);user-select:none">
        <div style="display:flex;align-items:center;gap:10px">
          <span class="material-symbols-outlined" style="color:var(--primary);font-size:22px">travel_explore</span>
          <h2 style="margin:0;font:800 18px 'Inter',sans-serif;color:var(--primary)">Search Engine Optimization (SEO) & Social Sharing</h2>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
          <span class="tag" style="background:var(--surface);border:1px solid var(--border)">
            <?= $metaTitle !== '' ? 'Customized' : 'Auto-Generated' ?>
          </span>
          <span class="material-symbols-outlined" style="color:var(--outline);font-size:20px">expand_more</span>
        </div>
      </summary>

      <div style="padding:24px;border-top:1px solid var(--border)">
        <div class="panel-head" style="padding:0 0 16px;margin-bottom:20px;border-bottom:1px solid var(--border)">
          <div>
            <h3 style="margin:0 0 4px;font:700 16px 'Inter',sans-serif;color:var(--text)">Google Search Snippet Preview</h3>
            <p class="hint" style="margin:0">Live preview of how this content may appear in search results.</p>
          </div>
        </div>

        <!-- Google SERP Snippet Preview Box -->
        <div class="serp-preview-box" style="background:#fff;border:1px solid #dfe1e5;border-radius:8px;padding:16px;margin-bottom:24px;max-width:650px;box-shadow:0 1px 3px rgba(0,0,0,0.05)">
          <div class="serp-url" style="color:#202124;font:14px Arial,sans-serif;margin-bottom:4px;display:flex;align-items:center;gap:6px">
            <span style="display:inline-block;width:16px;height:16px;border-radius:50%;background:var(--primary);color:#fff;font-size:10px;text-align:center;line-height:16px">N</span>
            <span>https://www.nmcclinic.com &rsaquo; <?= e($targetType) ?></span>
          </div>
          <div class="serp-title" style="color:#1a0dab;font:18px Arial,sans-serif;line-height:1.3;margin-bottom:4px;cursor:pointer">
            <span id="serp-preview-title"><?= e($metaTitle ?: 'Post Title | NMC Clinic Butwal') ?></span>
          </div>
          <div class="serp-desc" style="color:#4d5156;font:13px Arial,sans-serif;line-height:1.4">
            <span id="serp-preview-desc"><?= e($metaDesc ?: 'Summary description will appear here. Leave blank to auto-generate from post content.') ?></span>
          </div>
        </div>

        <div class="form-grid">
          <div class="field" style="grid-column:1/-1">
            <div style="display:flex;justify-content:space-between;align-items:center">
              <label for="seo_meta_title">Meta Title</label>
              <span class="hint" id="seo_title_count" style="font-size:12px;font-weight:600">0 / 60</span>
            </div>
            <input class="input" id="seo_meta_title" name="seo_meta_title" type="text" maxlength="255" value="<?= e($metaTitle) ?>" placeholder="Leave blank to auto-generate from content title">
            <p class="hint">Recommended: 50–60 characters. Clean, descriptive title including clinic location.</p>
          </div>

          <div class="field" style="grid-column:1/-1">
            <div style="display:flex;justify-content:space-between;align-items:center">
              <label for="seo_meta_description">Meta Description</label>
              <span class="hint" id="seo_desc_count" style="font-size:12px;font-weight:600">0 / 155</span>
            </div>
            <textarea class="textarea" id="seo_meta_description" name="seo_meta_description" rows="3" maxlength="500" placeholder="Leave blank to auto-generate from content excerpt"><?= e($metaDesc) ?></textarea>
            <p class="hint">Recommended: 120–155 characters summarizing key patient benefits or clinical advice.</p>
          </div>

          <div class="field" style="grid-column:1/-1">
            <label for="seo_canonical_url">Canonical URL</label>
            <input class="input" id="seo_canonical_url" name="seo_canonical_url" type="url" maxlength="500" value="<?= e($canonical) ?>" placeholder="https://www.nmcclinic.com/...">
            <p class="hint">Leave blank to use the default live URL of this page.</p>
          </div>

          <div class="field">
            <label>Robots Indexing</label>
            <label class="hint" style="display:flex;align-items:center;gap:8px;margin-top:6px">
              <input class="checkbox" type="checkbox" name="seo_robots_index" value="1" <?= $robotsIndex === 1 ? 'checked' : '' ?>>
              Allow search engines to index this content
            </label>
          </div>

          <div class="field">
            <label>Robots Link Following</label>
            <label class="hint" style="display:flex;align-items:center;gap:8px;margin-top:6px">
              <input class="checkbox" type="checkbox" name="seo_robots_follow" value="1" <?= $robotsFollow === 1 ? 'checked' : '' ?>>
              Allow search engines to follow links
            </label>
          </div>
        </div>

        <div class="panel-head" style="padding:16px 0 12px;margin:24px 0 16px;border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
          <div>
            <h3 style="margin:0 0 4px;font:700 16px 'Inter',sans-serif;color:var(--text)">Social Sharing (Open Graph / Twitter / Facebook)</h3>
            <p class="hint" style="margin:0">Controls how this link looks when shared on WhatsApp, Facebook, LinkedIn, or X.</p>
          </div>
        </div>

        <div class="form-grid">
          <div class="field">
            <label for="seo_og_title">Social Share Title</label>
            <input class="input" id="seo_og_title" name="seo_og_title" type="text" maxlength="255" value="<?= e($ogTitle) ?>" placeholder="Defaults to Meta Title">
          </div>

          <div class="field">
            <label for="seo_twitter_card">Twitter/X Card Type</label>
            <select class="select" id="seo_twitter_card" name="seo_twitter_card">
              <option value="summary_large_image" <?= $twitterCard === 'summary_large_image' ? 'selected' : '' ?>>Summary Large Image (Recommended)</option>
              <option value="summary" <?= $twitterCard === 'summary' ? 'selected' : '' ?>>Summary</option>
            </select>
          </div>

          <div class="field" style="grid-column:1/-1">
            <label for="seo_og_description">Social Share Description</label>
            <textarea class="textarea" id="seo_og_description" name="seo_og_description" rows="2" maxlength="500" placeholder="Defaults to Meta Description"><?= e($ogDesc) ?></textarea>
          </div>

          <div class="field" style="grid-column:1/-1">
            <?php
            render_image_picker([
                'name' => 'seo_og_image_id',
                'value' => $ogImageId,
                'mediaOptions' => $mediaOptions,
                'label' => 'Social Share (OG) Image',
                'help' => 'Optimal size: 1200x630px. If left unselected, defaults to the post featured image or clinic logo.',
            ]);
            ?>
          </div>
        </div>
      </div>
    </details>
    <?php
}
