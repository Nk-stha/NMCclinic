<?php
declare(strict_types=1);

require_once __DIR__ . '/media_helpers.php';

/**
 * Renders the unified Image Picker component with in-place upload and library selection.
 *
 * @param array{
 *   name: string,
 *   value: int|string|null,
 *   mediaOptions: array<array<string, mixed>>,
 *   label?: string,
 *   help?: string,
 *   allow_clear?: bool,
 *   id?: string
 * } $config
 */
function render_image_picker(array $config): void
{
    $name = $config['name'];
    $value = $config['value'] !== null && $config['value'] !== '' ? (int) $config['value'] : null;
    $mediaOptions = $config['mediaOptions'] ?? [];
    $label = $config['label'] ?? 'Image';
    $help = $config['help'] ?? 'Upload a new JPG, PNG, or WebP image directly or choose from library.';
    $allowClear = $config['allow_clear'] ?? true;
    $pickerId = $config['id'] ?? 'picker_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $name) . '_' . bin2hex(random_bytes(4));

    $selectedMedia = null;
    if ($value !== null) {
        foreach ($mediaOptions as $m) {
            if ((int) $m['id'] === $value) {
                $selectedMedia = $m;
                break;
            }
        }
        if (!$selectedMedia) {
            $selectedMedia = media_find($value);
        }
    }

    $previewUrl = $selectedMedia ? '../' . ltrim((string) $selectedMedia['file_path'], '/') : '';
    $altText = $selectedMedia ? (string) ($selectedMedia['alt_text'] ?? '') : '';
    $csrf = csrf_token();
    ?>
    <div class="image-picker-field" id="<?= e($pickerId) ?>" data-picker-name="<?= e($name) ?>">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
        <label style="font:800 13px 'Inter',Arial,sans-serif;text-transform:uppercase;letter-spacing:.04em;color:var(--text)"><?= e($label) ?></label>
        <?php if ($allowClear): ?>
          <button type="button" class="img-picker-clear-btn" style="background:none;border:none;color:var(--danger);font:700 12px 'Inter',sans-serif;cursor:pointer;display:<?= $selectedMedia ? 'inline-flex' : 'none' ?>;align-items:center;gap:4px">
            <span class="material-symbols-outlined" style="font-size:16px">close</span>Remove
          </button>
        <?php endif; ?>
      </div>

      <input type="hidden" name="<?= e($name) ?>" class="img-picker-value" value="<?= e((string) ($value ?? '')) ?>">

      <!-- Preview + Action Area -->
      <div class="img-picker-box" style="border:2px dashed var(--border);border-radius:12px;padding:16px;background:var(--surface-low);display:flex;gap:18px;align-items:center;flex-wrap:wrap">
        <div class="img-picker-preview" style="width:110px;height:110px;border-radius:10px;background:var(--surface);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;position:relative">
          <img src="<?= e($previewUrl) ?>" alt="<?= e($altText) ?>" class="img-picker-thumb" style="width:100%;height:100%;object-fit:cover;display:<?= $selectedMedia ? 'block' : 'none' ?>">
          <span class="material-symbols-outlined img-picker-empty-icon" style="font-size:42px;color:var(--outline);display:<?= $selectedMedia ? 'none' : 'block' ?>">image</span>
        </div>

        <div style="flex:1;min-width:220px">
          <div class="img-picker-info" style="margin-bottom:12px">
            <div class="img-picker-title" style="font:700 14px 'Inter',sans-serif;color:var(--text);word-break:break-all"><?= e($altText ?: ($selectedMedia ? $selectedMedia['file_path'] : 'No image selected')) ?></div>
            <div style="font:500 12px 'Public Sans',sans-serif;color:var(--outline);margin-top:2px"><?= e($help) ?></div>
          </div>

          <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <!-- Inline Upload -->
            <label class="primary-btn" style="width:auto;padding:8px 14px;font-size:13px;cursor:pointer;margin:0">
              <span class="material-symbols-outlined" style="font-size:18px">upload</span>Upload New
              <input type="file" class="img-picker-file-input" accept="image/jpeg,image/png,image/webp" style="display:none">
            </label>

            <!-- Browse Library -->
            <button type="button" class="secondary-btn img-picker-browse-btn" style="width:auto;padding:8px 14px;font-size:13px;margin:0">
              <span class="material-symbols-outlined" style="font-size:18px">photo_library</span>Choose from Library
            </button>
          </div>

          <!-- Upload Progress / Error Notification -->
          <div class="img-picker-status" style="margin-top:10px;display:none;font:600 12px 'Inter',sans-serif"></div>
        </div>
      </div>

      <!-- Modal / Drawer for Library Selection -->
      <div class="img-picker-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.65);z-index:9999;align-items:center;justify-content:center;padding:20px">
        <div style="background:var(--surface);width:min(860px,100%);max-height:85vh;border-radius:16px;box-shadow:var(--shadow-hover);display:flex;flex-direction:column;overflow:hidden">
          <div style="padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:var(--surface-low)">
            <div style="display:flex;align-items:center;gap:8px">
              <span class="material-symbols-outlined" style="color:var(--primary)">photo_library</span>
              <h3 style="margin:0;font:800 18px 'Inter',sans-serif;color:var(--primary)">Select Image from Media Library</h3>
            </div>
            <button type="button" class="img-picker-modal-close" style="background:none;border:none;cursor:pointer;font-size:24px;color:var(--outline);display:flex;align-items:center">
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>

          <div style="padding:14px 24px;border-bottom:1px solid var(--border);background:var(--surface)">
            <input type="text" class="input img-picker-filter" placeholder="Search media library by filename or alt text..." style="width:100%">
          </div>

          <div class="img-picker-grid" style="padding:20px 24px;overflow-y:auto;display:grid;grid-template-columns:repeat(auto-fill, minmax(130px, 1fr));gap:14px;flex:1">
            <?php foreach ($mediaOptions as $media): ?>
              <?php $mUrl = '../' . ltrim((string) $media['file_path'], '/'); ?>
              <div class="img-picker-item" data-id="<?= e((string) $media['id']) ?>" data-src="<?= e($mUrl) ?>" data-alt="<?= e((string) ($media['alt_text'] ?: $media['file_path'])) ?>" style="border:1px solid var(--border);border-radius:10px;overflow:hidden;cursor:pointer;background:var(--surface-low);transition:transform .15s, border-color .15s;text-align:center">
                <div style="width:100%;height:95px;background:#e2e8e7;overflow:hidden;display:flex;align-items:center;justify-content:center">
                  <img src="<?= e($mUrl) ?>" alt="<?= e((string) $media['alt_text']) ?>" style="width:100%;height:100%;object-fit:cover" loading="lazy">
                </div>
                <div style="padding:6px 8px;font:600 11px 'Inter',sans-serif;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                  <?= e((string) ($media['alt_text'] ?: basename((string) $media['file_path']))) ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div style="padding:14px 24px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;background:var(--surface-low)">
            <button type="button" class="secondary-btn img-picker-modal-close" style="width:auto;padding:8px 16px">Cancel</button>
          </div>
        </div>
      </div>
    </div>
    <?php
}
