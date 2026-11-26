import { expect, type Locator, type Page } from '@playwright/test';
import { readFileSync } from 'node:fs';
import path from 'node:path';
import { baseURL } from '../playwright.config';

// Dev-only credentials: the defaults are the local dev seed, never a real account.
export const ADMIN_EMAIL = process.env.E2E_ADMIN_EMAIL || 'admin@nmcclinic.com';
export const ADMIN_PASSWORD = process.env.E2E_ADMIN_PASSWORD || 'admin123';
export const AUTH_FILE = path.join(__dirname, '../.auth/admin.json');

export const PUBLIC_PAGES = [
  { name: 'home', path: '/' },
  { name: 'about', path: '/about' },
  { name: 'specialties', path: '/specialties' },
  { name: 'doctors', path: '/doctors' },
  { name: 'blog', path: '/blog' },
  { name: 'blog-detail', path: 'FIRST_POST' }, // resolved at run time from /blog
  { name: 'contact', path: '/contact' },
  { name: '404', path: '/does-not-exist' },
] as const;

/** Path of the first post card on /blog (featured card first). */
export async function firstPostPath(page: Page): Promise<string> {
  await page.goto('/blog');
  const href = await page.locator('a.featured, a.post-card').first().getAttribute('href');
  expect(href, 'a post link on /blog').toBeTruthy();
  return new URL(href!, page.url()).pathname;
}

export async function resolvePath(page: Page, p: string): Promise<string> {
  return p === 'FIRST_POST' ? firstPostPath(page) : p;
}

/** Collects console errors, page errors and failed same-origin requests from now on. */
export function watchPage(page: Page, allowStatus: (url: string, status: number) => boolean = () => false) {
  const problems: string[] = [];
  const origin = new URL(baseURL).origin;
  page.on('console', (m) => {
    if (m.type() !== 'error') return;
    // Chrome also logs the expected status of an allowed response (the 404 page) as a console error.
    const status = Number(m.text().match(/status of (\d{3})/)?.[1]);
    if (status && allowStatus(m.location().url, status)) return;
    problems.push(`console: ${m.text()} (${m.location().url})`);
  });
  page.on('pageerror', (e) => problems.push(`pageerror: ${e.message}`));
  page.on('requestfailed', (r) => {
    if (r.url().startsWith(origin)) problems.push(`requestfailed: ${r.url()} ${r.failure()?.errorText}`);
  });
  page.on('response', (r) => {
    if (r.url().startsWith(origin) && r.status() >= 400 && !allowStatus(r.url(), r.status())) {
      problems.push(`HTTP ${r.status()}: ${r.url()}`);
    }
  });
  return problems;
}

/** The site's mobile-nav and mobile-bar breakpoints, read from assets/style.css. */
export function siteBreakpoints() {
  const css = readFileSync(path.join(__dirname, '../../../assets/style.css'), 'utf8');
  const nav = css.match(/@media \(max-width: (\d+)px\) \{\s*\.nav-main, \.header-actions \{ display: none; \}/);
  const bar = css.match(/@media \(max-width: (\d+)px\) \{\s*\.mobile-bar \{/);
  if (!nav || !bar) throw new Error('Could not read the nav / mobile-bar breakpoints from assets/style.css');
  return { navMax: Number(nav[1]), barMax: Number(bar[1]) };
}

/** Opens one section panel of the Page Content editor. */
export async function openSection(page: Page, slug: string, key: string): Promise<Locator> {
  await page.goto(`/cms/content.php?page=${encodeURIComponent(slug)}`);
  const panel = page.locator(`#section-${key}`);
  if (!(await panel.evaluate((d) => (d as HTMLDetailsElement).open))) await panel.locator('summary').click();
  return panel;
}

export async function saveSection(panel: Locator) {
  await panel.getByRole('button', { name: /^Save / }).click();
  await panel.page().waitForURL(/[?&]saved=/);
}

export async function resetSection(page: Page, slug: string, key: string) {
  const panel = await openSection(page, slug, key);
  page.once('dialog', (d) => d.accept());
  await panel.getByRole('button', { name: 'Reset to default' }).click();
  await page.waitForURL(/[?&]reset=/);
}

/** Saves one plain field of a section through the editor. */
export async function setField(page: Page, slug: string, key: string, field: string, value: string) {
  const panel = await openSection(page, slug, key);
  await panel.locator(`#f-${key}-${field}`).fill(value);
  await saveSection(panel);
}

/** Fails with the offending element list when the page scrolls sideways. */
export async function expectNoHorizontalOverflow(page: Page, label: string) {
  const r = await page.evaluate(() => {
    const vw = window.innerWidth;
    const wide = [...document.querySelectorAll<HTMLElement>('body *')]
      .filter((el) => el.getBoundingClientRect().right > vw + 1)
      .slice(0, 5)
      .map((el) => `${el.tagName.toLowerCase()}.${el.className}`);
    return { sw: document.documentElement.scrollWidth, vw, wide };
  });
  expect(r.sw, `${label}: scrollWidth ${r.sw} > innerWidth ${r.vw}; wide: ${r.wide.join(', ')}`).toBeLessThanOrEqual(r.vw);
}
