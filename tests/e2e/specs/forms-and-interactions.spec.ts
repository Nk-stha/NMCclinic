import { test, expect, type Page } from '@playwright/test';
import { AUTH_FILE } from './helpers';

test('appointment form shows the success panel', async ({ page }) => {
  await page.goto('/');
  const form = page.locator('form[data-success="appointment-ok"]');
  await form.locator('#f-name').fill('E2E Appointment');
  await form.locator('#f-phone').fill('+977 9800000000');
  await form.locator('#f-time').selectOption({ index: 1 });
  await form.getByRole('checkbox').check();
  await form.getByRole('button', { name: 'Request appointment' }).click();
  const ok = page.locator('#appointment-ok');
  await expect(ok).toBeVisible();
  await expect(ok).toContainText('Request received.');
});

test('contact form shows the success panel', async ({ page }) => {
  await page.goto('/contact');
  const form = page.locator('form[data-success="contact-ok"]');
  await form.locator('#c-name').fill('E2E Contact');
  await form.locator('#c-phone').fill('+977 9800000000');
  await form.locator('#c-msg').fill('Sent by the e2e suite.');
  await form.getByRole('checkbox').check();
  await form.getByRole('button', { name: 'Send message' }).click();
  const ok = page.locator('#contact-ok');
  await expect(ok).toBeVisible();
  await expect(ok).toContainText('Message received.');
});

/** Clicks the first non-"All" tab and checks that exactly the matching cards stay visible. */
async function checkFilter(page: Page, group: string) {
  const tabs = page.locator(`[data-filter-group="${group}"] [data-filter]`);
  const cards = page.locator(`[data-filter-target="${group}"] [data-category]`);
  expect(await tabs.count(), `${group}: at least one category tab`).toBeGreaterThan(1);
  const tab = tabs.nth(1);
  const value = (await tab.getAttribute('data-filter'))!;
  await tab.click();
  await expect(tab).toHaveAttribute('aria-pressed', 'true');
  await expect(tabs.first()).toHaveAttribute('aria-pressed', 'false');
  const total = await cards.count();
  const matching = page.locator(`[data-filter-target="${group}"] [data-category="${value}"]`);
  expect(await matching.count()).toBeGreaterThan(0);
  for (let i = 0; i < total; i++) {
    const card = cards.nth(i);
    if ((await card.getAttribute('data-category')) === value) await expect(card).toBeVisible();
    else await expect(card).toBeHidden();
  }
  await tabs.first().click();
  for (let i = 0; i < total; i++) await expect(cards.nth(i)).toBeVisible();
}

test('doctor filter tab filters the cards', async ({ page }) => {
  await page.goto('/doctors');
  await checkFilter(page, 'doctors');
});

test('blog category tab filters the cards', async ({ page }) => {
  await page.goto('/blog');
  await checkFilter(page, 'posts');
});

test('branch picker updates the map caption', async ({ page }) => {
  await page.goto('/contact');
  const buttons = page.locator('[data-branch-list] .branch-select');
  expect(await buttons.count()).toBeGreaterThan(1);
  const second = buttons.nth(1);
  const name = (await second.getAttribute('data-branch-name'))!;
  await second.click();
  await expect(second).toHaveAttribute('aria-pressed', 'true');
  await expect(page.locator('[data-branch-caption]')).toHaveText(`Showing ${name}`);
  await expect(page.locator('iframe[data-branch-map]')).toHaveAttribute('title', new RegExp(`${name}$`));
});

test.describe('at 390px', () => {
  test.use({ viewport: { width: 390, height: 844 } });

  test('mobile nav opens and closes', async ({ page }) => {
    await page.goto('/');
    const menu = page.locator('.nav-mobile');
    await expect(menu).toBeHidden();
    await page.getByRole('button', { name: 'Open menu' }).click();
    await expect(menu).toBeVisible();
    await expect(page.locator('.nav-toggle')).toHaveAttribute('aria-expanded', 'true');
    await expect(menu.getByRole('link', { name: 'Doctors' })).toBeVisible();
    await page.getByRole('button', { name: 'Close menu' }).click();
    await expect(menu).toBeHidden();
    await expect(page.locator('.nav-toggle')).toHaveAttribute('aria-expanded', 'false');
  });

  test.describe('CMS', () => {
    test.use({ storageState: AUTH_FILE });

    test('phone menu opens, closes, and Escape returns focus', async ({ page }) => {
      await page.goto('/cms/content.php');
      const btn = page.getByRole('button', { name: 'Menu' });
      const sidebar = page.locator('#cms-sidebar');
      await expect(sidebar).toBeHidden();
      await btn.click();
      await expect(sidebar).toBeVisible();
      await expect(btn).toHaveAttribute('aria-expanded', 'true');
      await expect(sidebar.locator('.nav a').first()).toBeFocused();
      await btn.click();
      await expect(sidebar).toBeHidden();
      await btn.click();
      await expect(sidebar).toBeVisible();
      await page.keyboard.press('Escape');
      await expect(sidebar).toBeHidden();
      await expect(btn).toHaveAttribute('aria-expanded', 'false');
      await expect(btn).toBeFocused();
    });
  });
});
