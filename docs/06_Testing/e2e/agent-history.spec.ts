import { test, expect } from '@playwright/test';
import * as path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const STORAGE = path.join(__dirname, 'auth.json');

test.describe('Agent Execution History', () => {

  test.beforeAll(async ({ browser }) => {
    const context = await browser.newContext({ storageState: undefined });
    const page = await context.newPage();
    await page.goto('/login');
    await page.waitForTimeout(500);

    const emailInput = page.locator('input[type="email"], input[name="email"]').first();
    const passwordInput = page.locator('input[type="password"], input[name="password"]').first();

    if (await emailInput.isVisible()) {
      await emailInput.fill('superadmin@example.com');
      await passwordInput.fill('password');
      await page.locator('button[type="submit"]').first().click({ noWaitAfter: true });
      await page.waitForURL(/\/admin\/dashboard|\/dashboard/, { timeout: 30000 });
    }

    await page.context().storageState({ path: STORAGE });
    await page.close();
  });

  test.use({ storageState: STORAGE });

  test('sidebar shows AI Agents link', async ({ page }) => {
    await page.goto('/admin/dashboard', { timeout: 30000 });
    await page.waitForLoadState('networkidle', { timeout: 20000 });
    const link = page.locator('aside.app-sidebar a.nav-link[href*="/admin/agents"]').filter({ hasText: 'AI Agents' });
    await expect(link).toBeVisible({ timeout: 5000 });
  });

  test('History link visible on agent dashboard', async ({ page }) => {
    await page.goto('/admin/agents', { timeout: 30000 });
    await page.locator('h3:has-text("AI Workspace")').waitFor({ timeout: 15000 });
    await expect(page.locator('a[href*="/admin/agents/history"]').first()).toBeVisible({ timeout: 5000 });
  });

  test('loads history page directly', async ({ page }) => {
    await page.goto('/admin/agents/history', { timeout: 30000 });
    await expect(page.locator('h3.card-title:has-text("Execution Log")')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('#executionTable')).toBeVisible({ timeout: 10000 });
  });

  test('Back to Dashboard link works', async ({ page }) => {
    await page.goto('/admin/agents/history', { timeout: 30000 });
    await page.locator('h3.card-title:has-text("Execution Log")').waitFor({ timeout: 10000 });
    const backLink = page.locator('a[href*="/admin/agents"]').filter({ hasText: 'Back to Dashboard' });
    await expect(backLink).toBeVisible({ timeout: 5000 });
  });

  test('DataTable has expected columns', async ({ page }) => {
    await page.goto('/admin/agents/history', { timeout: 30000 });
    await page.waitForTimeout(2000);
    const headers = page.locator('#executionTable thead th');
    await expect(headers.nth(0)).toHaveText('#');
    await expect(headers.nth(1)).toHaveText('Agent');
    await expect(headers.nth(2)).toHaveText('Executed By');
    await expect(headers.nth(3)).toHaveText('Status');
    await expect(headers.nth(4)).toHaveText('Started');
    await expect(headers.nth(5)).toHaveText('Duration');
    await expect(headers.nth(6)).toHaveText('Records');
    await expect(headers.nth(7)).toHaveText('Summary');
    await expect(headers.nth(8)).toHaveText('Actions');
  });

  test('navigates from dashboard to history and back', async ({ page }) => {
    await page.goto('/admin/agents', { timeout: 30000 });
    await page.locator('h3:has-text("AI Workspace")').waitFor({ timeout: 15000 });
    const historyLink = page.locator('a[href*="/admin/agents/history"]').first();
    await historyLink.click();
    await page.waitForURL(/\/admin\/agents\/history/, { timeout: 10000 });
    await expect(page.locator('#executionTable')).toBeVisible({ timeout: 10000 });

    const backLink = page.locator('a[href*="/admin/agents"]').filter({ hasText: 'Back to Dashboard' });
    await backLink.click();
    await page.waitForURL(/\/admin\/agents$/, { timeout: 10000 });
    await expect(page.locator('.aiw-agent-name:has-text("Fee Collection Agent")')).toBeVisible({ timeout: 10000 });
  });

  test('no console errors on history page', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') errors.push(msg.text());
    });

    await page.goto('/admin/agents/history', { timeout: 30000 });
    await page.locator('h3.card-title:has-text("Execution Log")').waitFor({ timeout: 15000 });
    expect(errors.filter(e => !e.includes('favicon') && !e.includes('SockJS'))).toEqual([]);
  });
});
