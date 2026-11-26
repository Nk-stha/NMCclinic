import { test, expect } from '@playwright/test';
import { PUBLIC_PAGES, resolvePath, watchPage } from './helpers';

for (const p of PUBLIC_PAGES) {
  test(`${p.name} renders cleanly`, async ({ page }) => {
    const path = await resolvePath(page, p.path);
    const expected = p.name === '404' ? 404 : 200;
    const problems = watchPage(page, (url, status) => status === expected && new URL(url).pathname === path);

    const response = await page.goto(path);
    expect(response?.status()).toBe(expected);
    await expect(page.locator('h1')).toBeVisible();
    await page.waitForLoadState('networkidle');
    expect(problems).toEqual([]);
  });
}

test('skip link moves keyboard focus past the header', async ({ page }) => {
  await page.goto('/about');
  await page.keyboard.press('Tab');
  const skip = page.locator('.skip-link');
  await expect(skip).toBeFocused();
  await expect(skip).toBeInViewport();
  await page.keyboard.press('Enter');
  await expect(page).toHaveURL(/#top$/);
  await page.keyboard.press('Tab');
  // The next focus stop must be inside or after #top, not back in the header.
  const afterTarget = await page.evaluate(() => {
    const top = document.getElementById('top')!;
    const el = document.activeElement!;
    return top.contains(el) || !!(top.compareDocumentPosition(el) & Node.DOCUMENT_POSITION_FOLLOWING);
  });
  expect(afterTarget).toBe(true);
});
