import { test, expect } from '@playwright/test';

test.describe('Library Module', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', process.env.E2E_EMAIL || 'admin@school.com');
    await page.fill('input[name="password"]', process.env.E2E_PASSWORD || 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/admin/);
    await page.goto('/admin/library');
    await page.waitForURL('/admin/library');
  });

  test('should load library index page with all tabs', async ({ page }) => {
    await expect(page.locator('#libraryTabs')).toBeVisible();
    await expect(page.locator('button:has-text("Books")')).toBeVisible();
    await expect(page.locator('button:has-text("Categories")')).toBeVisible();
    await expect(page.locator('button:has-text("Authors")')).toBeVisible();
    await expect(page.locator('button:has-text("Publishers")')).toBeVisible();
    await expect(page.locator('button:has-text("Issue / Return")')).toBeVisible();
    await expect(page.locator('button:has-text("Fine Settings")')).toBeVisible();
  });

  test('should show Books tab as active by default', async ({ page }) => {
    await expect(page.locator('#booksPane')).toBeVisible();
    await expect(page.locator('#booksTable')).toBeVisible();
  });

  test('should have Add Book button', async ({ page }) => {
    await expect(page.locator('button:has-text("Add Book")')).toBeVisible();
  });

  test('should open Add Book modal', async ({ page }) => {
    await page.locator('button:has-text("Add Book")').click();
    await expect(page.locator('#bookModal')).toBeVisible();
    await expect(page.locator('#bookModal input[name="title"]')).toBeVisible();
    await expect(page.locator('#bookModal input[name="isbn"]')).toBeVisible();
    await expect(page.locator('#bookModal select[name="category_id"]')).toBeVisible();
    await expect(page.locator('#bookModal input[name="quantity"]')).toBeVisible();
  });

  test('should open Add Category modal', async ({ page }) => {
    await page.locator('button:has-text("Categories")').click();
    await page.locator('button:has-text("Add Category")').click();
    await expect(page.locator('#categoryModal')).toBeVisible();
    await expect(page.locator('#categoryModal input[name="name"]')).toBeVisible();
  });

  test('should open Add Author modal', async ({ page }) => {
    await page.locator('button:has-text("Authors")').click();
    await page.locator('button:has-text("Add Author")').click();
    await expect(page.locator('#authorModal')).toBeVisible();
    await expect(page.locator('#authorModal input[name="name"]')).toBeVisible();
  });

  test('should open Add Publisher modal', async ({ page }) => {
    await page.locator('button:has-text("Publishers")').click();
    await page.locator('button:has-text("Add Publisher")').click();
    await expect(page.locator('#publisherModal')).toBeVisible();
    await expect(page.locator('#publisherModal input[name="name"]')).toBeVisible();
  });

  test('should open Issue Book modal', async ({ page }) => {
    await page.locator('button:has-text("Issue / Return")').click();
    await page.locator('button:has-text("Issue Book")').click();
    await expect(page.locator('#issueModal')).toBeVisible();
    await expect(page.locator('#issueModal select[name="book_id"]')).toBeVisible();
    await expect(page.locator('#issueModal select[name="issueable_type"]')).toBeVisible();
  });

  test('should open Fine Settings modal', async ({ page }) => {
    await page.locator('button:has-text("Fine Settings")').click();
    await page.locator('button:has-text("Add Fine Configuration")').click();
    await expect(page.locator('#fineSettingModal')).toBeVisible();
    await expect(page.locator('#fineSettingModal input[name="fine_per_day"]')).toBeVisible();
  });

  test('should load DataTables for all tabs', async ({ page }) => {
    await expect(page.locator('#booksTable')).toBeVisible();
    await expect(page.locator('#booksTable_wrapper')).toBeVisible();

    await page.locator('button:has-text("Categories")').click();
    await expect(page.locator('#categoriesTable_wrapper')).toBeVisible();

    await page.locator('button:has-text("Authors")').click();
    await expect(page.locator('#authorsTable_wrapper')).toBeVisible();

    await page.locator('button:has-text("Publishers")').click();
    await expect(page.locator('#publishersTable_wrapper')).toBeVisible();

    await page.locator('button:has-text("Issue / Return")').click();
    await expect(page.locator('#issuesTable_wrapper')).toBeVisible();

    await page.locator('button:has-text("Fine Settings")').click();
    await expect(page.locator('#fineSettingsTable_wrapper')).toBeVisible();
  });

  test('should navigate to reports page', async ({ page }) => {
    await page.goto('/admin/library/reports');
    await page.waitForURL('/admin/library/reports');
    await expect(page.locator('#reportTabs')).toBeVisible();
    await expect(page.locator('button:has-text("Books Inventory")')).toBeVisible();
    await expect(page.locator('button:has-text("Issued Books")')).toBeVisible();
    await expect(page.locator('button:has-text("Overdue Books")')).toBeVisible();
    await expect(page.locator('button:has-text("Fine Collection")')).toBeVisible();
    await expect(page.locator('button:has-text("Student History")')).toBeVisible();
    await expect(page.locator('button:has-text("Teacher History")')).toBeVisible();
  });

  test('should have export buttons on reports', async ({ page }) => {
    await page.goto('/admin/library/reports');
    await expect(page.locator('#invExcel')).toBeVisible();
    await expect(page.locator('#invPdf')).toBeVisible();
    await page.locator('button:has-text("Issued Books")').click();
    await expect(page.locator('#issExcel')).toBeVisible();
    await page.locator('button:has-text("Overdue Books")').click();
    await expect(page.locator('#ovExcel')).toBeVisible();
    await page.locator('button:has-text("Fine Collection")').click();
    await expect(page.locator('#fineExcel')).toBeVisible();
    await page.locator('button:has-text("Student History")').click();
    await expect(page.locator('#shExcel')).toBeVisible();
    await page.locator('button:has-text("Teacher History")').click();
    await expect(page.locator('#thExcel')).toBeVisible();
  });

  test('should create a category', async ({ page }) => {
    await page.locator('button:has-text("Categories")').click();
    await page.locator('button:has-text("Add Category")').click();
    await page.locator('#categoryModal input[name="name"]').fill('Test Category E2E');
    await page.locator('#categoryModal textarea[name="description"]').fill('Created by Playwright');
    await page.locator('#categoryModal button[type="submit"]').click();
    await expect(page.locator('#categoryModal')).not.toBeVisible({ timeout: 10000 });
  });

  test('should create an author', async ({ page }) => {
    await page.locator('button:has-text("Authors")').click();
    await page.locator('button:has-text("Add Author")').click();
    await page.locator('#authorModal input[name="name"]').fill('Test Author E2E');
    await page.locator('#authorModal button[type="submit"]').click();
    await expect(page.locator('#authorModal')).not.toBeVisible({ timeout: 10000 });
  });

  test('should create a publisher', async ({ page }) => {
    await page.locator('button:has-text("Publishers")').click();
    await page.locator('button:has-text("Add Publisher")').click();
    await page.locator('#publisherModal input[name="name"]').fill('Test Publisher E2E');
    await page.locator('#publisherModal button[type="submit"]').click();
    await expect(page.locator('#publisherModal')).not.toBeVisible({ timeout: 10000 });
  });

  test('should create a book', async ({ page }) => {
    await page.locator('button:has-text("Add Book")').click();
    await page.locator('#bookModal input[name="title"]').fill('Test Book E2E');
    await page.locator('#bookModal input[name="isbn"]').fill('978-0-00-000000-0');
    await page.locator('#bookModal input[name="quantity"]').fill('3');
    await page.locator('#bookModal select[name="status"]').selectOption('active');
    await page.locator('#bookModal button[type="submit"]').click();
    await expect(page.locator('#bookModal')).not.toBeVisible({ timeout: 10000 });
  });

  test('should show sidebar Library link', async ({ page }) => {
    const sidebarLink = page.locator('a.nav-link:has-text("Library")');
    await expect(sidebarLink).toBeVisible();
    await expect(sidebarLink).toHaveAttribute('href', '/admin/library');
  });

  test('should have no console errors on library page', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    await page.goto('/admin/library');
    await page.waitForTimeout(2000);
    expect(errors.filter(e => !e.includes('favicon') && !e.includes('SockJS'))).toEqual([]);
  });

  test('should have no console errors on reports page', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    await page.goto('/admin/library/reports');
    await page.waitForTimeout(2000);
    expect(errors.filter(e => !e.includes('favicon') && !e.includes('SockJS'))).toEqual([]);
  });

  test('should create fine configuration', async ({ page }) => {
    await page.locator('button:has-text("Fine Settings")').click();
    await page.locator('button:has-text("Add Fine Configuration")').click();
    await page.locator('#fineSettingModal input[name="fine_per_day"]').fill('2');
    await page.locator('#fineSettingModal input[name="max_fine"]').fill('100');
    await page.locator('#fineSettingModal input[name="grace_period_days"]').fill('1');
    await page.locator('#fineSettingModal select[name="status"]').selectOption('active');
    await page.locator('#fineSettingModal button[type="submit"]').click();
    await expect(page.locator('#fineSettingModal')).not.toBeVisible({ timeout: 10000 });
  });

  test('should show 404 for non-existent library page', async ({ page }) => {
    const response = await page.goto('/admin/library/nonexistent');
    expect(response?.status()).toBe(404);
  });
});
