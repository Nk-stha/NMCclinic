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

// R17: site links carry the base path only, so they resolve from any depth and do not depend on SITE_URL's host.
for (const start of ['/', '/about', 'FIRST_POST', '/nope', '/blog/nope', '/x/y/z']) {
  test(`every site link resolves from ${start}`, async ({ page, request }) => {
    await page.goto(await resolvePath(page, start));
    const links = await page.locator('a[href]').evaluateAll((as) =>
      as.map((a) => ({ raw: a.getAttribute('href')!, abs: (a as HTMLAnchorElement).href })));
    const origin = new URL(page.url()).origin;
    // In-page anchors (#top etc.) point at this same page, which is a 404 on the 404 starts.
    const site = links.filter((l) => l.abs.startsWith(origin) && !l.raw.startsWith('#'));
    expect(site.length, 'site links on the page').toBeGreaterThan(5);
    for (const l of site) {
      expect(l.raw, `${l.raw} is base-path-relative`).not.toMatch(/^[a-z][a-z0-9+.-]*:/i);
    }
    const targets = [...new Set(site.map((l) => l.abs.split('#')[0]))];
    for (const url of targets) {
      const res = await request.get(url, { maxRedirects: 0 });
      expect(res.status(), url).toBe(200);
    }
    // A real click from this depth (header nav, or a 404 card) lands on the contact page.
    await page.locator('a[href$="/contact"]:visible').first().click();
    await expect(page).toHaveURL(`${origin}/contact`);
  });
}
