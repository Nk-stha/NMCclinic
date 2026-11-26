import { test as setup, expect } from '@playwright/test';
import { ADMIN_EMAIL, ADMIN_PASSWORD, AUTH_FILE } from './helpers';

setup('log in to the CMS', async ({ page }) => {
  await page.goto('/cms/login.php');
  await page.getByLabel('Work Email').fill(ADMIN_EMAIL);
  await page.getByLabel('Password', { exact: true }).fill(ADMIN_PASSWORD);
  await page.getByRole('button', { name: /sign in|log in/i }).click();
  await expect(page).toHaveURL(/dashboard\.php/);
  await page.context().storageState({ path: AUTH_FILE });
});
