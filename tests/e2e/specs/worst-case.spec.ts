import { test, expect, type Page } from '@playwright/test';
import { AUTH_FILE, expectNoHorizontalOverflow, openSection, resetSection, saveSection, setField } from './helpers';

test.use({ storageState: AUTH_FILE });

// Max-length values from the registry: hero heading 90, nav label 30, doctors card button 30.
const DEVANAGARI = Array.from('नेपाल मेडिकल क्लिनिक बुटवल स्वास्थ्य सेवा '.repeat(4)).slice(0, 89).join('') + 'ल'; // ends on a letter: the editor trims spaces
const NAV_LABEL = 'E2E' + 'W'.repeat(27);
const CARD_CTA = 'E2E' + 'Appointment'.repeat(3).slice(0, 27);

async function expectInside(page: Page, selector: string, label: string) {
  const els = page.locator(selector);
  for (let i = 0; i < (await els.count()); i++) {
    const el = els.nth(i);
    if (!(await el.isVisible())) continue;
    const box = (await el.boundingBox())!;
    const vw = page.viewportSize()!.width;
    expect(box.x, `${label} left edge`).toBeGreaterThanOrEqual(-0.5);
    expect(box.x + box.width, `${label} right edge (viewport ${vw})`).toBeLessThanOrEqual(vw + 0.5);
  }
}

test('long CMS values do not overflow at 320, 390 and 768', async ({ page }) => {
  test.setTimeout(120_000);
  await setField(page, 'home', 'hero', 'heading', DEVANAGARI);
  const nav = await openSection(page, 'global', 'nav');
  await nav.locator('fieldset.content-item').first().getByLabel('Label').fill(NAV_LABEL);
  await saveSection(nav);
  await setField(page, 'doctors', 'listing', 'card_cta_label', CARD_CTA);

  try {
    for (const width of [320, 390, 768]) {
      await page.setViewportSize({ width, height: 900 });
      for (const path of ['/', '/doctors']) {
        await page.goto(path);
        await page.evaluate(() => document.fonts.ready);
        if (path === '/') await expect(page.locator('h1')).toHaveText(DEVANAGARI);
        await expectNoHorizontalOverflow(page, `${path}@${width}`);
        await expectInside(page, 'h1', `h1 ${path}@${width}`);
        // The long label is the doctors page's; home cards are a swipe row that runs off-screen by design.
        if (path === '/doctors') await expect(page.locator('.doc-actions a').getByText(CARD_CTA).first()).toBeVisible();
        if (path === '/doctors') await expectInside(page, '.doc-actions a', `doctor card button ${path}@${width}`);
        // The long nav label lives in the mobile menu at these widths.
        await page.getByRole('button', { name: 'Open menu' }).click();
        await expect(page.locator('.nav-mobile').getByText(NAV_LABEL)).toBeVisible();
        await expectNoHorizontalOverflow(page, `${path}@${width} menu open`);
        await expectInside(page, '.nav-mobile a', `mobile nav ${path}@${width}`);
      }
    }
  } finally {
    await resetSection(page, 'home', 'hero');
    await resetSection(page, 'global', 'nav');
    await resetSection(page, 'doctors', 'listing');
  }
});
