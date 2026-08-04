import { test, expect } from '@playwright/test';
import * as path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const STORAGE = path.join(__dirname, 'auth.json');

test.describe('Assistant → Agent Handoff', () => {

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
    await page.goto('/admin/dashboard', { timeout: 30000 });
    await page.waitForTimeout(1000);
  });

  async function openAskErpModal(page: any) {
    const btn = page.locator('button[data-bs-target="#askErpModal"]');
    await btn.click();
    await expect(page.locator('#askErpModal')).toBeVisible({ timeout: 5000 });
  }

  async function askQuestion(page: any, question: string) {
    await openAskErpModal(page);
    const input = page.locator('#aiQuestion');

    // In some cases the input may be covered by the modal backdrop
    await input.waitFor({ state: 'visible', timeout: 5000 });
    await input.fill(question);

    await page.locator('#askErpBtn').click();
    await page.waitForTimeout(1500);
  }

  function extractAgentName(href: string | null): string | null {
    if (!href) return null;
    const match = href.match(/preselect=([^&]+)/);
    return match ? decodeURIComponent(match[1]) : null;
  }

  test('Ask ERP button is visible in navbar', async ({ page }) => {
    await expect(page.locator('button[data-bs-target="#askErpModal"]')).toBeVisible({ timeout: 5000 });
  });

  test('opens Ask ERP modal', async ({ page }) => {
    await openAskErpModal(page);
  });

  test('fee question returns answer with Fee Collection Agent recommendation', async ({ page }) => {
    await askQuestion(page, 'pending fees above 10000');

    const responseContent = page.locator('#aiResponseContent');
    await expect(responseContent).toBeVisible({ timeout: 10000 });

    // Should show the answer text
    await expect(responseContent).toContainText(/pending|outstanding|fees/i);

    // Should show recommendation card
    await expect(responseContent).toContainText('Recommended Action');
    await expect(responseContent).toContainText('Fee Collection Agent');

    // Run Agent link should have preselect=fee_collection
    const link = responseContent.locator('a.btn-info');
    await expect(link).toBeVisible();
    const href = await link.getAttribute('href');
    expect(extractAgentName(href)).toBe('fee_collection');
    expect(href).toContain('days=30');
  });

  test('attendance question returns answer with Attendance Agent recommendation', async ({ page }) => {
    await askQuestion(page, 'students absent today');

    const responseContent = page.locator('#aiResponseContent');
    await expect(responseContent).toBeVisible({ timeout: 10000 });

    await expect(responseContent).toContainText(/absent|today/i);
    await expect(responseContent).toContainText('Recommended Action');
    await expect(responseContent).toContainText('Attendance Agent');

    const link = responseContent.locator('a.btn-info');
    await expect(link).toBeVisible();
    const href = await link.getAttribute('href');
    expect(extractAgentName(href)).toBe('attendance');
    expect(href).toContain('date=');
  });

  test('library question returns answer with Library Agent recommendation', async ({ page }) => {
    await askQuestion(page, 'overdue books');

    const responseContent = page.locator('#aiResponseContent');
    await expect(responseContent).toBeVisible({ timeout: 10000 });

    await expect(responseContent).toContainText(/overdue|books/i);
    await expect(responseContent).toContainText('Recommended Action');
    await expect(responseContent).toContainText('Library Agent');

    const link = responseContent.locator('a.btn-info');
    await expect(link).toBeVisible();
    const href = await link.getAttribute('href');
    expect(extractAgentName(href)).toBe('library');
    expect(href).toContain('days=1');
  });

  test('payroll question returns answer with Payroll Agent recommendation', async ({ page }) => {
    await askQuestion(page, 'latest payroll run');

    const responseContent = page.locator('#aiResponseContent');
    await expect(responseContent).toBeVisible({ timeout: 10000 });

    await expect(responseContent).toContainText(/payroll|run/i);
    await expect(responseContent).toContainText('Recommended Action');
    await expect(responseContent).toContainText('Payroll Agent');

    const link = responseContent.locator('a.btn-info');
    await expect(link).toBeVisible();
    const href = await link.getAttribute('href');
    expect(extractAgentName(href)).toBe('payroll');
    expect(href).toContain('month=');
    expect(href).toContain('year=');
  });

  test('Run Agent link navigates to agents page with preselect param', async ({ page }) => {
    await askQuestion(page, 'students absent today');

    const responseContent = page.locator('#aiResponseContent');
    await expect(responseContent).toBeVisible({ timeout: 10000 });

    const link = responseContent.locator('a.btn-info');
    const href = await link.getAttribute('href');
    expect(href).toContain('/admin/agents');
    expect(href).toContain('preselect=attendance');
  });

  async function navigateAndCheckPreselect(page: any, url: string, expectedLabel: string, paramCheck: (page: any) => Promise<void>) {
    await page.goto(url, { timeout: 30000, waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);

    // Modal should now be visible (opened by preselect JS)
    const modal = page.locator('#agentModal');
    await expect(modal).toHaveClass(/show/, { timeout: 10000 });
    await expect(page.locator('#agentModalTitle')).toContainText(expectedLabel, { timeout: 5000 });

    await paramCheck(page);
  }

  test('agents page opens correct modal from preselect param', async ({ page }) => {
    const today = new Date().toISOString().split('T')[0];
    await navigateAndCheckPreselect(page,
      '/admin/agents?preselect=attendance&date=' + today,
      'Attendance Agent',
      async (p) => {
        await expect(p.locator('#agentConfigFields input[type="date"]')).toHaveValue(today);
      }
    );
  });

  test('agents page pre-selects fee_collection with days param', async ({ page }) => {
    await navigateAndCheckPreselect(page,
      '/admin/agents?preselect=fee_collection&days=90',
      'Fee Collection Agent',
      async (p) => {
        await expect(p.locator('#agentConfigFields select.agent-param')).toHaveValue('90');
      }
    );
  });

  test('agents page pre-selects library with days param', async ({ page }) => {
    await navigateAndCheckPreselect(page,
      '/admin/agents?preselect=library&days=7',
      'Library Agent',
      async (p) => {
        await expect(p.locator('#agentConfigFields select.agent-param')).toHaveValue('7');
      }
    );
  });

  test('agents page pre-selects payroll with month and year params', async ({ page }) => {
    const now = new Date();
    const month = String(now.getMonth() + 1);
    const year = String(now.getFullYear());
    await navigateAndCheckPreselect(page,
      '/admin/agents?preselect=payroll&month=' + month + '&year=' + year,
      'Payroll Agent',
      async (p) => {
        const selects = p.locator('#agentConfigFields select.agent-param');
        await expect(selects.first()).toHaveValue(month);
        await expect(selects.nth(1)).toHaveValue(year);
      }
    );
  });

  test('unrelated question has no recommendation', async ({ page }) => {
    await askQuestion(page, 'total students');

    const responseContent = page.locator('#aiResponseContent');
    await expect(responseContent).toBeVisible({ timeout: 10000 });

    // Student queries should not have agent recommendations
    await expect(responseContent).not.toContainText('Recommended Action');
  });

  test('no console errors on assistant page', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') errors.push(msg.text());
    });

    await page.goto('/admin/dashboard', { timeout: 30000 });
    await expect(page.locator('button[data-bs-target="#askErpModal"]')).toBeVisible({ timeout: 5000 });
    expect(errors.filter(e => !e.includes('favicon') && !e.includes('SockJS'))).toEqual([]);
  });
});
