import { test, expect, type Dialog } from '@playwright/test';
import { AUTH_FILE, firstPostPath, openSection, resetSection, saveSection, setField } from './helpers';

test.use({ storageState: AUTH_FILE });

const stamp = () => `E2E ${Date.now()}`;

test('footer about text shows on public pages, then resets', async ({ page }) => {
  const value = `${stamp()} footer about`;
  try {
    await setField(page, 'global', 'footer', 'about', value);
    for (const path of ['/', '/contact']) {
      await page.goto(path);
      await expect(page.locator('.footer-about')).toHaveText(value);
    }
  } finally {
    await resetSection(page, 'global', 'footer');
  }
  await page.goto('/');
  await expect(page.locator('.footer-about')).not.toHaveText(value);
});

// One field per page slug: [slug, section, field, public path].
const PER_PAGE: [string, string, string, string][] = [
  ['home', 'hero', 'subheading', '/'],
  ['about', 'banner', 'subheading', '/about'],
  ['specialties', 'banner', 'subheading', '/specialties'],
  ['doctors', 'banner', 'subheading', '/doctors'],
  ['blog', 'banner', 'subheading', '/blog'],
  ['blog_detail', 'disclaimer', 'body', 'FIRST_POST'],
  ['contact', 'banner', 'subheading', '/contact'],
  ['404', 'banner', 'subheading', '/does-not-exist'],
];

for (const [slug, key, field, where] of PER_PAGE) {
  test(`edit ${slug}.${key}.${field} shows on the public page, then resets`, async ({ page }) => {
    const path = where === 'FIRST_POST' ? await firstPostPath(page) : where;
    const value = `${stamp()} ${slug}`;
    try {
      await setField(page, slug, key, field, value);
      await page.goto(path);
      await expect(page.getByText(value, { exact: true })).toBeVisible();
    } finally {
      await resetSection(page, slug, key);
    }
    await page.goto(path);
    await expect(page.getByText(value, { exact: true })).toHaveCount(0);
  });
}

test('nav item list: add, move up, save, remove, reset', async ({ page }) => {
  const label = `${stamp()} nav`;
  try {
    let panel = await openSection(page, 'global', 'nav');
    const rows = panel.locator('fieldset.content-item');
    const before = await rows.count();
    await panel.getByRole('button', { name: 'Add menu links entry' }).click();
    await expect(rows).toHaveCount(before + 1);
    const added = rows.last();
    await added.getByLabel('Label').fill(label);
    await added.getByLabel('Link', { exact: false }).last().fill('contact');
    await added.getByRole('button', { name: `Move item ${before + 1} up` }).click();
    await saveSection(panel);

    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto('/');
    const links = page.locator('.nav-main a');
    await expect(links.nth(before - 1)).toHaveText(label); // moved one above the old last item
    await expect(links).toHaveCount(before + 1);

    panel = await openSection(page, 'global', 'nav');
    const row = panel.locator('fieldset.content-item').filter({ has: page.locator(`input[value="${label}"]`) });
    await row.getByRole('button', { name: /^Remove item/ }).click();
    await expect(panel.locator('fieldset.content-item')).toHaveCount(before);
    await saveSection(panel);
    await page.goto('/');
    await expect(page.locator('.nav-main').getByText(label)).toHaveCount(0);
  } finally {
    await resetSection(page, 'global', 'nav');
  }
});

test('script tags in content render as literal text', async ({ page }) => {
  const payload = '<script>alert(1)</script>';
  let dialogs = 0;
  const onDialog = (d: Dialog) => { dialogs++; void d.dismiss(); };
  try {
    await setField(page, 'home', 'hero', 'subheading', payload);
    page.on('dialog', onDialog);
    await page.goto('/');
    await expect(page.locator('.hero').getByText(payload, { exact: true })).toBeVisible();
    expect(dialogs).toBe(0);
  } finally {
    page.off('dialog', onDialog); // the reset button's confirm() is expected
    await resetSection(page, 'home', 'hero');
  }
});

test('javascript: URL is rejected and the old value is kept', async ({ page }) => {
  let panel = await openSection(page, 'home', 'hero');
  const input = panel.locator('#f-hero-primary_cta_url');
  const old = await input.inputValue();
  await input.fill('javascript:alert(1)');
  await panel.getByRole('button', { name: /^Save / }).click();
  await expect(page.locator('#f-hero-primary_cta_url-err')).toBeVisible();
  await expect(page.locator('#f-hero-primary_cta_url')).toHaveAttribute('aria-invalid', 'true');
  expect(page.url()).not.toContain('saved=');
  panel = await openSection(page, 'home', 'hero');
  await expect(panel.locator('#f-hero-primary_cta_url')).toHaveValue(old);
});

test('a tampered csrf_token is rejected', async ({ page }) => {
  const value = `${stamp()} csrf`;
  const panel = await openSection(page, 'home', 'hero');
  await panel.locator('input[name="csrf_token"]').evaluate((el) => { (el as HTMLInputElement).value = 'tampered'; });
  await panel.locator('#f-hero-subheading').fill(value);
  await panel.getByRole('button', { name: /^Save / }).click();
  await expect(page.getByRole('alert').filter({ hasText: 'Invalid request' })).toBeVisible();
  expect(page.url()).not.toContain('saved=');
  await page.goto('/');
  await expect(page.getByText(value)).toHaveCount(0);
});

test('an empty required field shows an inline error', async ({ page }) => {
  const panel = await openSection(page, 'home', 'hero');
  await panel.locator('#f-hero-heading').fill('');
  await panel.getByRole('button', { name: /^Save / }).click();
  const err = page.locator('#f-hero-heading-err');
  await expect(err).toHaveText(/This field is required\./);
  await expect(page.locator('#f-hero-heading')).toHaveAttribute('aria-invalid', 'true');
  await expect(page.locator('#f-hero-heading')).toBeFocused();
});
