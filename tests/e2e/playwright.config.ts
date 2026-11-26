import { defineConfig, devices } from '@playwright/test';

export const baseURL = process.env.E2E_BASE_URL || 'http://localhost:8082';

// Safety guard: the suite writes to the database, so it must never hit the live site by accident.
const host = new URL(baseURL).hostname;
if (!['localhost', '127.0.0.1'].includes(host) && process.env.E2E_ALLOW_REMOTE !== '1') {
  throw new Error(`E2E_BASE_URL host "${host}" is not localhost. Set E2E_ALLOW_REMOTE=1 only for a disposable test server.`);
}

const projects = [
  { name: 'auth', testMatch: /auth\.setup\.ts/ },
  { name: 'chromium', use: { ...devices['Desktop Chrome'] }, dependencies: ['auth'] },
];
// WebKit needs extra host libraries (`sudo npx playwright install-deps`); opt in with E2E_WEBKIT=1.
if (process.env.E2E_WEBKIT === '1') {
  projects.push({
    name: 'webkit-mobile',
    use: { ...devices['iPhone 13'] },
    dependencies: ['auth'],
    testMatch: /(public-pages|forms-and-interactions)\.spec\.ts/,
  } as (typeof projects)[number]);
}

export default defineConfig({
  testDir: './specs',
  // One worker: every test shares the one dev database.
  workers: 1,
  fullyParallel: false,
  retries: 0,
  timeout: 60_000,
  reporter: [['list'], ['html', { open: 'never' }]],
  globalSetup: './db/global-setup.ts',
  globalTeardown: './db/global-teardown.ts',
  use: {
    baseURL,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  projects,
});
