import { test, expect } from '@playwright/test';
import * as path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const STORAGE = path.join(__dirname, 'auth.json');

test.describe('Payroll Agent', () => {

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

  test.beforeEach(async ({ page }) => {
    await page.goto('/admin/agents', { timeout: 30000 });
    await page.locator('h3:has-text("AI Workspace")').waitFor({ timeout: 15000 });
  });

  test('sidebar shows AI Agents link', async ({ page }) => {
    const link = page.locator('aside.app-sidebar a.nav-link[href*="/admin/agents"]').filter({ hasText: 'AI Agents' });
    await expect(link).toBeVisible({ timeout: 5000 });
  });

  test('shows Payroll Agent card', async ({ page }) => {
    await expect(page.locator('.card-title:has-text("Payroll Agent")')).toBeVisible({ timeout: 5000 });
  });

  test('shows Run Agent button on card', async ({ page }) => {
    const payrollCard = page.locator('.card-title:has-text("Payroll Agent")').locator('..').locator('..');
    await expect(payrollCard.locator('.btn:has-text("Run Agent")')).toBeVisible({ timeout: 5000 });
  });

  test('opens agent modal on Run Agent click', async ({ page }) => {
    const payrollCard = page.locator('.card-title:has-text("Payroll Agent")').locator('..').locator('..');
    await payrollCard.locator('.btn:has-text("Run Agent")').click();
    const modal = page.locator('#agentModal');
    await expect(modal).toBeVisible({ timeout: 5000 });
    await expect(modal.locator('.modal-title')).toContainText('Payroll Agent');
  });

  async function openModal(page: any) {
    const payrollCard = page.locator('.card-title:has-text("Payroll Agent")').locator('..').locator('..');
    await payrollCard.locator('.btn:has-text("Run Agent")').click();
    await page.waitForTimeout(500);
    await expect(page.locator('#agentModal')).toBeVisible({ timeout: 5000 });
  }

  test('shows month and year selects in modal', async ({ page }) => {
    await openModal(page);
    const selects = page.locator('#agentConfigFields select.agent-param');
    await expect(selects).toHaveCount(2);
    const currentMonth = String(new Date().getMonth() + 1);
    await expect(selects.first()).toHaveValue(currentMonth);
  });

  test('shows Preview button in modal', async ({ page }) => {
    await openModal(page);
    await expect(page.locator('#agentPreviewBtn')).toBeVisible();
    await expect(page.locator('#agentPreviewBtn')).toContainText('Preview');
  });

  test('previews payroll readiness on button click', async ({ page }) => {
    await openModal(page);
    await page.locator('#agentPreviewBtn').click();
    await expect(page.locator('#agentStepConfirm')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('#agentPreviewSummary')).toBeVisible();
  });

  test('shows validation summary or estimates after preview', async ({ page }) => {
    await openModal(page);
    await page.locator('#agentPreviewBtn').click();
    await expect(page.locator('#agentStepConfirm')).toBeVisible({ timeout: 15000 });
    const summary = page.locator('#agentPreviewSummary');
    await expect(summary).toBeVisible();
  });

  test('no console errors on agents page', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') errors.push(msg.text());
    });

    await page.goto('/admin/agents', { timeout: 30000 });
    await page.locator('h3:has-text("AI Workspace")').waitFor({ timeout: 15000 });
    expect(errors.filter(e => !e.includes('favicon') && !e.includes('SockJS'))).toEqual([]);
  });
});
