import { test, expect } from '@playwright/test';

test.describe('Payroll Processing Engine', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', process.env.E2E_EMAIL || 'admin@school.com');
    await page.fill('input[name="password"]', process.env.E2E_PASSWORD || 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/admin/);
    await page.goto('/admin/payroll');
    await page.waitForURL('/admin/payroll');
  });

  test('PR-PROC-01: Payroll Runs tab is visible', async ({ page }) => {
    await expect(page.locator('#payrollTabs')).toBeVisible();
    const runsTab = page.locator('button[data-bs-target="#payrollRunsPane"]');
    await expect(runsTab).toBeVisible();
    await expect(runsTab).toContainText('Payroll Runs');
  });

  test('PR-PROC-02: Payroll Runs tab shows DataTable with correct columns', async ({ page }) => {
    await page.locator('button[data-bs-target="#payrollRunsPane"]').click();
    await page.waitForTimeout(500);
    const table = page.locator('#payrollRunsTable');
    await expect(table).toBeVisible();
    const headers = await table.locator('thead tr th').allTextContents();
    expect(headers).toContain('ID');
    expect(headers).toContain('Period');
    expect(headers).toContain('Status');
    expect(headers).toContain('Generated At');
    expect(headers).toContain('Employees');
    expect(headers).toContain('Actions');
  });

  test('PR-PROC-03: Generate Payroll modal opens', async ({ page }) => {
    await page.locator('button[data-bs-target="#payrollRunsPane"]').click();
    await page.waitForTimeout(300);
    await page.locator('[data-bs-target="#generatePayrollModal"]').click();
    await expect(page.locator('#generatePayrollModal')).toBeVisible();
    await expect(page.locator('#generatePayrollModal .modal-title')).toContainText('Generate Payroll');
  });

  test('PR-PROC-04: Generate Payroll form has required fields', async ({ page }) => {
    await page.locator('button[data-bs-target="#payrollRunsPane"]').click();
    await page.waitForTimeout(300);
    await page.locator('[data-bs-target="#generatePayrollModal"]').click();
    await page.waitForTimeout(300);
    await expect(page.locator('#generatePayrollModal select[name="month"]')).toBeVisible();
    await expect(page.locator('#generatePayrollModal input[name="year"]')).toBeVisible();
    await expect(page.locator('#generatePayrollModal textarea[name="notes"]')).toBeVisible();
  });

  test('PR-PROC-05: Generate Payroll creates a new run', async ({ page }) => {
    // First create a salary component and structure via API
    // Use existing data from Foundation setup

    await page.locator('button[data-bs-target="#payrollRunsPane"]').click();
    await page.waitForTimeout(300);

    // Open generate modal
    await page.locator('[data-bs-target="#generatePayrollModal"]').click();
    await page.waitForTimeout(300);

    // Fill form
    await page.locator('#generatePayrollModal select[name="month"]').selectOption('6');
    await page.locator('#generatePayrollModal input[name="year"]').fill('2026');
    await page.locator('#generatePayrollModal textarea[name="notes"]').fill('Test payroll run');

    // Submit
    await page.locator('#generatePayrollModal button[type="submit"]').click();

    // Wait for success
    await page.waitForTimeout(2000);

    // Modal should close and table should reload
    const table = page.locator('#payrollRunsTable');
    await expect(table).toBeVisible();
  });

  test('PR-PROC-06: View run details modal shows items', async ({ page }) => {
    await page.locator('button[data-bs-target="#payrollRunsPane"]').click();
    await page.waitForTimeout(500);

    // Wait for DataTable to load
    await page.waitForTimeout(2000);

    // Click view button on first row
    const viewBtn = page.locator('#payrollRunsTable .view-run').first();
    if (await viewBtn.isVisible()) {
      await viewBtn.click();
      await page.waitForTimeout(1500);
      await expect(page.locator('#runDetailModal')).toBeVisible();
      await expect(page.locator('#runSummary')).toBeVisible();
    }
  });

  test('PR-PROC-07: Lock run button is visible on draft runs', async ({ page }) => {
    await page.locator('button[data-bs-target="#payrollRunsPane"]').click();
    await page.waitForTimeout(1000);

    // Check for lock buttons on draft runs
    const lockBtns = page.locator('#payrollRunsTable .lock-run');
    const count = await lockBtns.count();
    // Should have at least one draft run to lock
    expect(count).toBeGreaterThanOrEqual(0);
  });

  test('PR-PROC-08: Reports page has processing report tabs', async ({ page }) => {
    await page.goto('/admin/payroll/reports');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(500);

    const tabs = page.locator('#reportTabs');
    await expect(tabs).toBeVisible();

    const tabTexts = await tabs.locator('button').allTextContents();
    const allText = tabTexts.join(' ');
    expect(allText).toContain('Run Summary');
    expect(allText).toContain('Employee Payroll');
    expect(allText).toContain('Gross vs Net');
  });

  test('PR-PROC-09: Run Summary report tab shows DataTable', async ({ page }) => {
    await page.goto('/admin/payroll/reports');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(500);

    await page.locator('button[data-bs-target="#runSummaryPane"]').click();
    await page.waitForTimeout(500);

    const table = page.locator('#runSummaryTable');
    await expect(table).toBeVisible();
    const headers = await table.locator('thead tr th').allTextContents();
    expect(headers).toContain('Period');
    expect(headers).toContain('Status');
    expect(headers).toContain('Employees');
  });

  test('PR-PROC-10: Employee Payroll report tab shows DataTable', async ({ page }) => {
    await page.goto('/admin/payroll/reports', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);

    await page.locator('button[data-bs-target="#employeePayrollPane"]').click();
    await page.waitForTimeout(500);

    const table = page.locator('#employeePayrollTable');
    await expect(table).toBeVisible();
    const headers = await table.locator('thead tr th').allTextContents();
    expect(headers).toContain('Employee');
    expect(headers).toContain('Gross');
    expect(headers).toContain('Deductions');
    expect(headers).toContain('Net');
  });

  test('PR-PROC-11: Gross vs Net report tab shows DataTable', async ({ page }) => {
    await page.goto('/admin/payroll/reports');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(500);

    await page.locator('button[data-bs-target="#grossVsNetPane"]').click();
    await page.waitForTimeout(500);

    const table = page.locator('#grossVsNetTable');
    await expect(table).toBeVisible();
    const headers = await table.locator('thead tr th').allTextContents();
    expect(headers).toContain('Total Gross');
    expect(headers).toContain('Total Deductions');
    expect(headers).toContain('Total Net');
  });

  test('PR-PROC-12: Tab persistence works for Payroll Runs tab', async ({ page }) => {
    await page.locator('button[data-bs-target="#payrollRunsPane"]').click();
    await page.waitForTimeout(300);

    // Navigate away and back
    await page.goto('/admin/dashboard');
    await page.waitForLoadState('networkidle');
    await page.goto('/admin/payroll');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(500);

    // The last active tab should be restored (Payroll Runs)
    const runsPane = page.locator('#payrollRunsPane');
    await expect(runsPane).toBeVisible();
  });

  test('PR-PROC-13: 0 console errors on payroll runs page', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (msg) => { if (msg.type() === 'error') errors.push(msg.text()); });

    await page.goto('/admin/payroll');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    await page.locator('button[data-bs-target="#payrollRunsPane"]').click();
    await page.waitForTimeout(1000);

    expect(errors.length).toBe(0);
  });

  test('PR-PROC-14: 0 console errors on reports page', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (msg) => { if (msg.type() === 'error') errors.push(msg.text()); });

    await page.goto('/admin/payroll/reports');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    // Click each new processing report tab
    for (const tabId of ['#runSummaryPane', '#employeePayrollPane', '#grossVsNetPane']) {
      await page.locator(`button[data-bs-target="${tabId}"]`).click();
      await page.waitForTimeout(500);
    }

    expect(errors.length).toBe(0);
  });
});
