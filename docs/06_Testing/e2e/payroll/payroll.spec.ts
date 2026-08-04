import { test, expect } from '@playwright/test';

test.describe('Payroll Module', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', process.env.E2E_EMAIL || 'admin@school.com');
    await page.fill('input[name="password"]', process.env.E2E_PASSWORD || 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/admin/);
    await page.goto('/admin/payroll');
    await page.waitForURL('/admin/payroll');
  });

  test('should load payroll index page with all tabs', async ({ page }) => {
    await expect(page.locator('#payrollTabs')).toBeVisible();
    await expect(page.locator('button:has-text("Departments")')).toBeVisible();
    await expect(page.locator('button:has-text("Designations")')).toBeVisible();
    await expect(page.locator('button:has-text("Salary Components")')).toBeVisible();
    await expect(page.locator('button:has-text("Pay Grades")')).toBeVisible();
    await expect(page.locator('button:has-text("Salary Structures")')).toBeVisible();
  });

  test('should show Departments tab as active by default', async ({ page }) => {
    await expect(page.locator('#departmentsPane')).toBeVisible();
    await expect(page.locator('#departmentsTable')).toBeVisible();
  });

  test('should have Add Department button', async ({ page }) => {
    await expect(page.locator('button:has-text("Add Department")')).toBeVisible();
  });

  test('should open Add Department modal', async ({ page }) => {
    await page.locator('button:has-text("Add Department")').click();
    await expect(page.locator('#departmentModal')).toBeVisible();
    await expect(page.locator('#departmentModal input[name="name"]')).toBeVisible();
  });

  test('should open Add Designation modal', async ({ page }) => {
    await page.locator('button:has-text("Designations")').click();
    await page.locator('button:has-text("Add Designation")').click();
    await expect(page.locator('#designationModal')).toBeVisible();
    await expect(page.locator('#designationModal input[name="name"]')).toBeVisible();
  });

  test('should open Add Salary Component modal', async ({ page }) => {
    await page.locator('button:has-text("Salary Components")').click();
    await page.locator('button:has-text("Add Salary Component")').click();
    await expect(page.locator('#salaryComponentModal')).toBeVisible();
    await expect(page.locator('#salaryComponentModal input[name="name"]')).toBeVisible();
  });

  test('should open Add Pay Grade modal', async ({ page }) => {
    await page.locator('button:has-text("Pay Grades")').click();
    await page.locator('button:has-text("Add Pay Grade")').click();
    await expect(page.locator('#payGradeModal')).toBeVisible();
    await expect(page.locator('#payGradeModal input[name="name"]')).toBeVisible();
  });

  test('should open Add Salary Structure modal', async ({ page }) => {
    await page.locator('button:has-text("Salary Structures")').click();
    await page.locator('button:has-text("Add Salary Structure")').click();
    await expect(page.locator('#salaryStructureModal')).toBeVisible();
    await expect(page.locator('#salaryStructureModal input[name="employee_id"]')).toBeVisible();
  });

  test('should load DataTables for all tabs', async ({ page }) => {
    await expect(page.locator('#departmentsTable')).toBeVisible();
    await expect(page.locator('#departmentsTable_wrapper')).toBeVisible();

    await page.locator('button:has-text("Designations")').click();
    await expect(page.locator('#designationsTable_wrapper')).toBeVisible();

    await page.locator('button:has-text("Salary Components")').click();
    await expect(page.locator('#salaryComponentsTable_wrapper')).toBeVisible();

    await page.locator('button:has-text("Pay Grades")').click();
    await expect(page.locator('#payGradesTable_wrapper')).toBeVisible();

    await page.locator('button:has-text("Salary Structures")').click();
    await expect(page.locator('#salaryStructuresTable_wrapper')).toBeVisible();
  });

  test('should navigate to reports page', async ({ page }) => {
    await page.goto('/admin/payroll/reports');
    await page.waitForURL('/admin/payroll/reports');
    await expect(page.locator('#reportTabs')).toBeVisible();
    await expect(page.locator('button:has-text("Departments")')).toBeVisible();
    await expect(page.locator('button:has-text("Designations")')).toBeVisible();
    await expect(page.locator('button:has-text("Salary Components")')).toBeVisible();
    await expect(page.locator('button:has-text("Pay Grades")')).toBeVisible();
    await expect(page.locator('button:has-text("Salary Structures")')).toBeVisible();
    await expect(page.locator('button:has-text("Employee List")')).toBeVisible();
  });

  test('should have export buttons on reports', async ({ page }) => {
    await page.goto('/admin/payroll/reports');
    await expect(page.locator('#deptExcel')).toBeVisible();
    await expect(page.locator('#deptPdf')).toBeVisible();
    await page.locator('button:has-text("Designations")').click();
    await expect(page.locator('#desExcel')).toBeVisible();
    await page.locator('button:has-text("Salary Components")').click();
    await expect(page.locator('#scExcel')).toBeVisible();
    await page.locator('button:has-text("Pay Grades")').click();
    await expect(page.locator('#pgExcel')).toBeVisible();
    await page.locator('button:has-text("Salary Structures")').click();
    await expect(page.locator('#ssExcel')).toBeVisible();
    await page.locator('button:has-text("Employee List")').click();
    await expect(page.locator('#elExcel')).toBeVisible();
  });

  test('should create a department', async ({ page }) => {
    await page.locator('button:has-text("Add Department")').click();
    await page.locator('#departmentModal input[name="name"]').fill('Test Department E2E');
    await page.locator('#departmentModal textarea[name="description"]').fill('Created by Playwright');
    await page.locator('#departmentModal button[type="submit"]').click();
    await expect(page.locator('#departmentModal')).not.toBeVisible({ timeout: 10000 });
  });

  test('should create a designation', async ({ page }) => {
    await page.locator('button:has-text("Designations")').click();
    await page.locator('button:has-text("Add Designation")').click();
    await page.locator('#designationModal input[name="name"]').fill('Test Designation E2E');
    await page.locator('#designationModal button[type="submit"]').click();
    await expect(page.locator('#designationModal')).not.toBeVisible({ timeout: 10000 });
  });

  test('should create a salary component', async ({ page }) => {
    await page.locator('button:has-text("Salary Components")').click();
    await page.locator('button:has-text("Add Salary Component")').click();
    await page.locator('#salaryComponentModal input[name="name"]').fill('Test Component E2E');
    await page.locator('#salaryComponentModal input[name="name_display"]').fill('Test Component');
    await page.locator('#salaryComponentModal input[name="value"]').fill('5000');
    await page.locator('#salaryComponentModal button[type="submit"]').click();
    await expect(page.locator('#salaryComponentModal')).not.toBeVisible({ timeout: 10000 });
  });

  test('should create a pay grade', async ({ page }) => {
    await page.locator('button:has-text("Pay Grades")').click();
    await page.locator('button:has-text("Add Pay Grade")').click();
    await page.locator('#payGradeModal input[name="name"]').fill('Test Pay Grade E2E');
    await page.locator('#payGradeModal input[name="min_salary"]').fill('25000');
    await page.locator('#payGradeModal input[name="max_salary"]').fill('50000');
    await page.locator('#payGradeModal button[type="submit"]').click();
    await expect(page.locator('#payGradeModal')).not.toBeVisible({ timeout: 10000 });
  });

  test('should show sidebar Payroll link', async ({ page }) => {
    const sidebarLink = page.locator('a.nav-link:has-text("Payroll")');
    await expect(sidebarLink).toBeVisible();
    await expect(sidebarLink).toHaveAttribute('href', /\/admin\/payroll$/);
  });

  test('should have no console errors on payroll page', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    await page.goto('/admin/payroll');
    await page.waitForTimeout(2000);
    expect(errors.filter(e => !e.includes('favicon') && !e.includes('SockJS'))).toEqual([]);
  });

  test('should have no console errors on reports page', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    await page.goto('/admin/payroll/reports');
    await page.waitForTimeout(2000);
    expect(errors.filter(e => !e.includes('favicon') && !e.includes('SockJS'))).toEqual([]);
  });

  test('should show 404 for non-existent payroll page', async ({ page }) => {
    const response = await page.goto('/admin/payroll/nonexistent');
    expect(response?.status()).toBe(404);
  });
});
