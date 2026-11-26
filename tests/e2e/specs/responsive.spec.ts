import { test, expect } from '@playwright/test';
import { mkdirSync } from 'node:fs';
import path from 'node:path';
import { AUTH_FILE, PUBLIC_PAGES, cmsBreakpoint, expectNoHorizontalOverflow, firstPostPath, siteBreakpoints } from './helpers';

const VIEWPORTS = [
  [320, 640], [360, 740], [375, 667], [390, 844], [414, 896],
  [768, 1024], [820, 1180], [1024, 768], [1440, 900],
] as const;

const CMS_PAGES = [
  { name: 'cms-content', path: '/cms/content.php' },
  { name: 'cms-content-home', path: '/cms/content.php?page=home' },
  { name: 'cms-settings', path: '/cms/settings.php' },
  { name: 'cms-dashboard', path: '/cms/dashboard.php' },
];

const { navMax, barMax } = siteBreakpoints();
const cmsMax = cmsBreakpoint();
const SHOTS = path.join(__dirname, '../screenshots');
mkdirSync(SHOTS, { recursive: true });

// Login state for every test; public pages ignore the CMS cookie (it is scoped to /cms).
test.use({ storageState: AUTH_FILE });

let postPath = '';
test.beforeAll(async ({ browser }) => {
  const page = await browser.newPage();
  postPath = await firstPostPath(page);
  await page.close();
});

for (const [width, height] of VIEWPORTS) {
  test.describe(`${width}x${height}`, () => {
    test.use({ viewport: { width, height } });

    for (const p of [...PUBLIC_PAGES, ...CMS_PAGES]) {
      const isCms = p.path.startsWith('/cms');
      test(p.name, async ({ page }, testInfo) => {
        await page.goto(p.path === 'FIRST_POST' ? postPath : p.path);
        await page.evaluate(() => document.fonts.ready);

        await expectNoHorizontalOverflow(page, `${p.name}@${width}`);

        if (p.name === '404') {
          // The 404 page is standalone by design: no site header, nav or mobile bar.
          await expect(page.locator('.site-header, .mobile-bar')).toHaveCount(0);
        } else if (!isCms) {
          const toggle = page.locator('.nav-toggle');
          if (width <= navMax) {
            await expect(toggle).toBeVisible();
            await expect(page.locator('.nav-main')).toBeHidden();
          } else {
            await expect(toggle).toBeHidden();
            await expect(page.locator('.nav-main')).toBeVisible();
          }
          await (width <= barMax ? expect(page.locator('.mobile-bar')).toBeVisible() : expect(page.locator('.mobile-bar')).toBeHidden());
        } else {
          // The 44px check below only measures visible targets, so prove the menu button is there first.
          await (width <= cmsMax ? expect(page.locator('.menu-btn')).toBeVisible() : expect(page.locator('.menu-btn')).toBeHidden());
        }

        const m = await page.evaluate(() => {
          const vw = window.innerWidth;
          const shown = (el: Element) => {
            const r = el.getBoundingClientRect();
            return r.width > 1 && r.height > 1 && getComputedStyle(el).visibility !== 'hidden';
          };
          const describe = (el: Element) => `${el.tagName.toLowerCase()}#${el.id}.${el.className}`;
          const controls = [...document.querySelectorAll('form :is(input, select, textarea, button), form a.btn')].filter(shown);
          const outside = controls
            .filter((el) => { const r = el.getBoundingClientRect(); return r.left < -0.5 || r.right > vw + 0.5; })
            .map(describe);
          const targets = [...document.querySelectorAll('.mobile-bar a, .nav-toggle, .menu-btn')].filter(shown);
          const small = targets
            .filter((el) => { const r = el.getBoundingClientRect(); return r.width < 44 || r.height < 44; })
            .map((el) => { const r = el.getBoundingClientRect(); return `${describe(el)} ${r.width}x${r.height}`; });
          const textInputs = [...document.querySelectorAll('input:not([type=checkbox]):not([type=radio]):not([type=hidden]):not([type=submit]), select, textarea')].filter(shown);
          const tinyFont = textInputs
            .filter((el) => parseFloat(getComputedStyle(el).fontSize) < 16)
            .map((el) => `${describe(el)} ${getComputedStyle(el).fontSize}`);
          return { outside, small, tinyFont };
        });
        expect(m.outside, 'form controls outside the viewport').toEqual([]);
        expect(m.small, 'touch targets under 44x44').toEqual([]);
        if (width < 768) expect(m.tinyFont, 'inputs under 16px (iOS zoom)').toEqual([]);

        const file = path.join(SHOTS, `${p.name}-${width}.png`);
        await page.screenshot({ path: file, fullPage: true });
        await testInfo.attach(`${p.name}-${width}`, { path: file, contentType: 'image/png' });
      });
    }
  });
}
