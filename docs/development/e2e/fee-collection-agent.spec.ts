import { test, expect } from '@playwright/test';
import * as path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const STORAGE = path.join(__dirname, 'auth.json');

test.describe('AI Agent Framework', () => {

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

  test('shows Fee Collection Agent card', async ({ page }) => {
    await expect(page.locator('.card-title:has-text("Fee Collection Agent")')).toBeVisible({ timeout: 5000 });
  });

  test('shows Run Agent button on card', async ({ page }) => {
    const feeCard = page.locator('.card-title:has-text("Fee Collection Agent")').locator('..').locator('..');
    await expect(feeCard.locator('.btn:has-text("Run Agent")')).toBeVisible({ timeout: 5000 });
  });

  test('opens agent modal on Run Agent click', async ({ page }) => {
    const feeCard = page.locator('.card-title:has-text("Fee Collection Agent")').locator('..').locator('..');
    await feeCard.locator('.btn:has-text("Run Agent")').click();
    const modal = page.locator('#agentModal');
    await expect(modal).toBeVisible({ timeout: 5000 });
    await expect(modal.locator('.modal-title')).toContainText('Fee Collection Agent');
  });

  async function openModal(page: any) {
    const feeCard = page.locator('.card-title:has-text("Fee Collection Agent")').locator('..').locator('..');
    await feeCard.locator('.btn:has-text("Run Agent")').click();
    await page.waitForTimeout(500);
    await expect(page.locator('#agentModal')).toBeVisible({ timeout: 5000 });
  }

  test('shows 30/60/90 day options in modal', async ({ page }) => {
    await openModal(page);
    const select = page.locator('#agentConfigFields select.agent-param');
    await expect(select).toBeVisible();
    await expect(select.locator('option[value="30"]')).toHaveText('30 Days');
    await expect(select.locator('option[value="60"]')).toHaveText('60 Days');
    await expect(select.locator('option[value="90"]')).toHaveText('90 Days');
  });

  test('shows Preview button in modal', async ({ page }) => {
    await openModal(page);
    await expect(page.locator('#agentPreviewBtn')).toBeVisible();
    await expect(page.locator('#agentPreviewBtn')).toContainText('Preview');
  });

  test('previews overdue students on button click', async ({ page }) => {
    await openModal(page);
    await page.locator('#agentPreviewBtn').click();
    await expect(page.locator('#agentStepConfirm')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('#agentPreviewSummary')).toBeVisible();
  });

  test('shows Run Agent button after preview', async ({ page }) => {
    await openModal(page);
    await page.locator('#agentPreviewBtn').click();
    await expect(page.locator('#agentStepConfirm')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('#agentRunBtn')).toBeVisible();
  });

  test('executes agent and shows results', async ({ page }) => {
    await openModal(page);
    await page.locator('#agentPreviewBtn').click();
    await expect(page.locator('#agentStepConfirm')).toBeVisible({ timeout: 15000 });
    await page.locator('#agentRunBtn').click();
    await expect(page.locator('#agentStepResult')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('#agentResultSummary')).toBeVisible();
  });

  test('shows Done button after execution', async ({ page }) => {
    await openModal(page);
    await page.locator('#agentPreviewBtn').click();
    await expect(page.locator('#agentStepConfirm')).toBeVisible({ timeout: 15000 });
    await page.locator('#agentRunBtn').click();
    await expect(page.locator('#agentStepResult')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('#agentDoneBtn')).toBeVisible();
  });

  test('navigates back after execution', async ({ page }) => {
    await openModal(page);
    await page.locator('#agentPreviewBtn').click();
    await expect(page.locator('#agentStepConfirm')).toBeVisible({ timeout: 15000 });
    await page.locator('#agentRunBtn').click();
    await expect(page.locator('#agentStepResult')).toBeVisible({ timeout: 15000 });

    await page.goto('/admin/agents', { timeout: 30000 });
    await page.locator('.card-title:has-text("Fee Collection Agent")').waitFor({ timeout: 10000 });
    await expect(page.locator('.card-title:has-text("Fee Collection Agent")')).toBeVisible();
  });

  test('shows student details after preview', async ({ page }) => {
    await openModal(page);
    await page.locator('#agentPreviewBtn').click();
    await expect(page.locator('#agentStepConfirm')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('#agentPreviewDetail')).toBeVisible();
  });

  test('toggles days selection', async ({ page }) => {
    await openModal(page);
    const select = page.locator('#agentConfigFields select.agent-param');
    await select.selectOption('90');
    await expect(select).toHaveValue('90');
    await select.selectOption('30');
    await expect(select).toHaveValue('30');
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
