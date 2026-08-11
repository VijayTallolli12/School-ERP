import { test, expect } from '@playwright/test';
import * as path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const STORAGE = path.join(__dirname, 'auth.json');

test.describe('Ask ERP MVP - Global Modal & Query', () => {

  test.beforeAll(async ({ browser }) => {
    const context = await browser.newContext({ storageState: undefined });
    const page = await context.newPage();
    await page.goto('/login');
    await page.waitForTimeout(1000);

    // Attempt login with test credentials
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
    await page.goto('/admin/dashboard', { timeout: 30000 });
  });

  test('should show Ask ERP button in navbar', async ({ page }) => {
    const askBtn = page.locator('[data-bs-target="#askErpModal"]');
    await expect(askBtn).toBeVisible({ timeout: 10000 });
    await expect(askBtn).toContainText(/Ask ERP/i);
  });

  test('should open Ask ERP modal on button click', async ({ page }) => {
    await page.locator('[data-bs-target="#askErpModal"]').click();
    const modal = page.locator('#askErpModal');
    await expect(modal).toBeVisible({ timeout: 5000 });
    await expect(modal.locator('.modal-title')).toContainText(/Ask ERP/i);
  });

  test('should show question input and ask button in modal', async ({ page }) => {
    await page.locator('[data-bs-target="#askErpModal"]').click();
    await page.waitForTimeout(500);

    await expect(page.locator('#aiQuestion')).toBeVisible();
    await expect(page.locator('#askErpBtn')).toBeVisible();
    await expect(page.locator('#askErpBtn')).toContainText('Ask');
  });

  test('should reject empty question', async ({ page }) => {
    await page.locator('[data-bs-target="#askErpModal"]').click();
    await page.waitForTimeout(500);

    await page.locator('#askErpBtn').click();
    // Should still show input, no loading
    await expect(page.locator('#aiLoading')).not.toBeVisible();
  });

  test('should answer "total students" question', async ({ page }) => {
    await page.locator('[data-bs-target="#askErpModal"]').click();
    await page.waitForTimeout(500);

    await page.locator('#aiQuestion').fill('total students');
    await page.locator('#askErpBtn').click();

    // Wait for response area to appear
    await expect(page.locator('#aiResponseArea')).toBeVisible({ timeout: 10000 });
    const responseText = await page.locator('#aiResponseContent').textContent();
    expect(responseText).toMatch(/total|student|active/i);
  });

  test('should answer "absent today" question', async ({ page }) => {
    await page.locator('[data-bs-target="#askErpModal"]').click();
    await page.waitForTimeout(500);

    await page.locator('#aiQuestion').fill('absent today');
    await page.locator('#askErpBtn').click();

    await expect(page.locator('#aiResponseArea')).toBeVisible({ timeout: 10000 });
    const responseText = await page.locator('#aiResponseContent').textContent();
    expect(responseText).toMatch(/absent|today/i);
  });

  test('should answer "pending fees" question', async ({ page }) => {
    await page.locator('[data-bs-target="#askErpModal"]').click();
    await page.waitForTimeout(500);

    await page.locator('#aiQuestion').fill('total outstanding fees');
    await page.locator('#askErpBtn').click();

    await expect(page.locator('#aiResponseArea')).toBeVisible({ timeout: 10000 });
    const responseText = await page.locator('#aiResponseContent').textContent();
    expect(responseText).toMatch(/outstanding|pending|fees/i);
  });

  test('should answer "payroll" question', async ({ page }) => {
    await page.locator('[data-bs-target="#askErpModal"]').click();
    await page.waitForTimeout(500);

    await page.locator('#aiQuestion').fill('latest payroll run');
    await page.locator('#askErpBtn').click();

    await expect(page.locator('#aiResponseArea')).toBeVisible({ timeout: 10000 });
    const responseText = await page.locator('#aiResponseContent').textContent();
    expect(responseText).toMatch(/payroll|run|employee/i);
  });

  test('should answer "transport" question', async ({ page }) => {
    await page.locator('[data-bs-target="#askErpModal"]').click();
    await page.waitForTimeout(500);

    await page.locator('#aiQuestion').fill('route occupancy');
    await page.locator('#askErpBtn').click();

    await expect(page.locator('#aiResponseArea')).toBeVisible({ timeout: 10000 });
    const responseText = await page.locator('#aiResponseContent').textContent();
    expect(responseText).toMatch(/route|occupancy|capacity|student/i);
  });

  test('should answer "library" question', async ({ page }) => {
    await page.locator('[data-bs-target="#askErpModal"]').click();
    await page.waitForTimeout(500);

    await page.locator('#aiQuestion').fill('books issued');
    await page.locator('#askErpBtn').click();

    await expect(page.locator('#aiResponseArea')).toBeVisible({ timeout: 10000 });
    const responseText = await page.locator('#aiResponseContent').textContent();
    expect(responseText).toMatch(/issued|book/i);
  });

  test('should show error for unrecognized question', async ({ page }) => {
    await page.locator('[data-bs-target="#askErpModal"]').click();
    await page.waitForTimeout(500);

    await page.locator('#aiQuestion').fill('xyzzy magic query');
    await page.locator('#askErpBtn').click();

    await expect(page.locator('#aiResponseArea')).toBeVisible({ timeout: 10000 });
    const responseText = await page.locator('#aiResponseContent').textContent();
    expect(responseText).toMatch(/couldn't understand|try asking/i);
  });

  test('should respect school context for queries', async ({ page }) => {
    await page.locator('[data-bs-target="#askErpModal"]').click();
    await page.waitForTimeout(500);

    await page.locator('#aiQuestion').fill('total students');
    await page.locator('#askErpBtn').click();

    await expect(page.locator('#aiResponseArea')).toBeVisible({ timeout: 10000 });
    const responseText = await page.locator('#aiResponseContent').textContent();
    // Response should reference a specific number, not "all schools"
    expect(responseText).not.toMatch(/all schools/i);
  });

  test('should have copy response button', async ({ page }) => {
    await page.locator('[data-bs-target="#askErpModal"]').click();
    await page.waitForTimeout(500);

    await page.locator('#aiQuestion').fill('total students');
    await page.locator('#askErpBtn').click();
    await expect(page.locator('#aiResponseArea')).toBeVisible({ timeout: 10000 });

    await expect(page.locator('#copyResponseBtn')).toBeVisible();
  });

  test('should handle Enter key to submit question', async ({ page }) => {
    await page.locator('[data-bs-target="#askErpModal"]').click();
    await page.waitForTimeout(500);

    await page.locator('#aiQuestion').fill('total students');
    await page.locator('#aiQuestion').press('Enter');

    await expect(page.locator('#aiResponseArea')).toBeVisible({ timeout: 10000 });
    const responseText = await page.locator('#aiResponseContent').textContent();
    expect(responseText).toMatch(/total|student/i);
  });
});
