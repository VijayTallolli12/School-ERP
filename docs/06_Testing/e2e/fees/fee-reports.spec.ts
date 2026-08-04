import { test, expect } from '@playwright/test';

test.describe('Fee Reports Consolidation', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', process.env.E2E_EMAIL || 'admin@school.com');
    await page.fill('input[name="password"]', process.env.E2E_PASSWORD || 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/admin/);
  });

  test('Fee Reports Dashboard loads', async ({ page }) => {
    await page.goto('/reports/fees');
    await page.waitForSelector('#content');
    const title = page.locator('.page-title, h1, h2, h3, h4, h5');
    await expect(title).toBeVisible();
  });

  test('Paid Fees Report loads with DataTable', async ({ page }) => {
    await page.goto('/reports/fees/paid');
    await page.waitForSelector('#paidTable');
    await expect(page.locator('#paidTable_wrapper')).toBeVisible();
  });

  test('Pending Fees Report loads with DataTable', async ({ page }) => {
    await page.goto('/reports/fees/pending');
    await page.waitForSelector('#pendingTable');
    await expect(page.locator('#pendingTable_wrapper')).toBeVisible();
  });

  test('Overdue Fees Report loads with DataTable', async ({ page }) => {
    await page.goto('/reports/fees/overdue');
    await page.waitForSelector('#overdueTable');
    await expect(page.locator('#overdueTable_wrapper')).toBeVisible();
  });

  test('Collection Summary loads with DataTable', async ({ page }) => {
    await page.goto('/reports/fees/collection-summary');
    await page.waitForSelector('#summaryTable');
    await expect(page.locator('#summaryTable_wrapper')).toBeVisible();
  });

  test('Fee Defaulters page loads', async ({ page }) => {
    await page.goto('/reports/fees/defaulters');
    await page.waitForSelector('#content');
    const title = page.locator('.page-title, h1, h2, h3, h4, h5');
    await expect(title).toBeVisible();
  });

  test('Legacy Fees > Reports redirects to Reports module', async ({ page }) => {
    await page.goto('/admin/fees/reports/collection');
    await page.waitForURL(/\/reports\/fees\//);
    expect(page.url()).toContain('/reports/fees/');
  });

  test('Legacy Fees > Due Report redirects to Pending', async ({ page }) => {
    await page.goto('/admin/fees/reports/due');
    await page.waitForURL(/\/reports\/fees\//);
    expect(page.url()).toContain('/reports/fees/');
  });

  test('Legacy Fees > Class-wise redirects to Collection Summary', async ({ page }) => {
    await page.goto('/admin/fees/reports/class-wise');
    await page.waitForURL(/\/reports\/fees\//);
    expect(page.url()).toContain('/reports/fees/');
  });

  test('Legacy Fees > Daily redirects to Reports', async ({ page }) => {
    await page.goto('/admin/fees/reports/daily');
    await page.waitForURL(/\/reports\/fees\//);
    expect(page.url()).toContain('/reports/fees/');
  });

  test('Fees module index has View Fee Reports link', async ({ page }) => {
    await page.goto('/admin/fees');
    const link = page.locator('a:has-text("View Fee Reports")');
    await expect(link).toBeVisible();
    await expect(link).toHaveAttribute('href', '/reports/fees');
  });

  test('No console errors on Paid Fees Report', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    await page.goto('/reports/fees/paid');
    await page.waitForTimeout(2000);
    expect(errors.filter(e => !e.includes('favicon') && !e.includes('SockJS'))).toEqual([]);
  });

  test('No console errors on Pending Fees Report', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    await page.goto('/reports/fees/pending');
    await page.waitForTimeout(2000);
    expect(errors.filter(e => !e.includes('favicon') && !e.includes('SockJS'))).toEqual([]);
  });

  test('No console errors on Overdue Fees Report', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    await page.goto('/reports/fees/overdue');
    await page.waitForTimeout(2000);
    expect(errors.filter(e => !e.includes('favicon') && !e.includes('SockJS'))).toEqual([]);
  });

  test('No console errors on Collection Summary', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    await page.goto('/reports/fees/collection-summary');
    await page.waitForTimeout(2000);
    expect(errors.filter(e => !e.includes('favicon') && !e.includes('SockJS'))).toEqual([]);
  });

  test('No console errors on Fee Defaulters', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    await page.goto('/reports/fees/defaulters');
    await page.waitForTimeout(2000);
    expect(errors.filter(e => !e.includes('favicon') && !e.includes('SockJS'))).toEqual([]);
  });
});
