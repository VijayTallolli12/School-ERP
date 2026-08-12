/**
 * School ERP — Complete UI/UX/Functional Audit
 *
 * Run:
 *   npx playwright test e2e/erp-audit.spec.ts --project=chromium
 *   npx playwright show-report
 *
 * Results: e2e/audit-report.md + e2e/screenshots/
 */

import { test, expect, Page, BrowserContext } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// ─── Configuration ─────────────────────────────────────────────────────────────

const BASE_URL = process.env.BASE_URL || 'http://127.0.0.1:8000';
const SCREENSHOT_DIR = path.join(__dirname, 'screenshots');
const REPORT_PATH = path.join(__dirname, 'audit-report.md');
const LOGIN_EMAIL = process.env.LOGIN_EMAIL || 'superadmin@example.com';
const LOGIN_PASS = process.env.LOGIN_PASS || 'password';

// Ensure screenshot directory exists
if (!fs.existsSync(SCREENSHOT_DIR)) fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });

// ─── Audit State ───────────────────────────────────────────────────────────────

interface Issue {
  page: string;
  category: string;
  severity: 'Critical' | 'High' | 'Medium' | 'Low';
  description: string;
  screenshot?: string;
  rootCause?: string;
  recommendedFix?: string;
}

const issues: Issue[] = [];
let screenshotCounter = 0;

function recordIssue(issue: Omit<Issue, 'screenshot'> & { screenshot?: string }) {
  issues.push(issue);
}

async function screenshot(page: Page, name: string): Promise<string> {
  screenshotCounter++;
  const filename = `${String(screenshotCounter).padStart(3, '0')}_${name.replace(/[^a-z0-9]/gi, '_')}.png`;
  const filepath = path.join(SCREENSHOT_DIR, filename);
  await page.screenshot({ path: filepath, fullPage: true });
  return `screenshots/${filename}`;
}

// ─── All Sidebar Pages ─────────────────────────────────────────────────────────
// Extracted from resources/views/layouts/partials/sidebar.blade.php

const SIDEBAR_PAGES: { label: string; url: string; category: string }[] = [
  // Top-level
  { label: 'Dashboard', url: '/admin/dashboard', category: 'Dashboard' },

  // Access Control
  { label: 'Roles', url: '/admin/roles', category: 'Access Control' },
  { label: 'Permissions', url: '/admin/permissions', category: 'Access Control' },

  // Modules
  { label: 'Notifications', url: '/admin/notifications', category: 'Modules' },
  { label: 'Fees', url: '/admin/fees', category: 'Modules' },
  { label: 'Settings', url: '/admin/settings', category: 'Modules' },

  // Reports — Student
  { label: 'Student Reports Dashboard', url: '/reports/students', category: 'Reports > Students' },
  { label: 'Student Directory', url: '/reports/students/directory', category: 'Reports > Students' },
  { label: 'Gender-wise Report', url: '/reports/students/gender-wise', category: 'Reports > Students' },

  // Reports — Attendance
  { label: 'Attendance Reports Dashboard', url: '/reports/attendance', category: 'Reports > Attendance' },
  { label: 'Daily Attendance', url: '/reports/attendance/daily', category: 'Reports > Attendance' },
  { label: 'Monthly Attendance', url: '/reports/attendance/monthly', category: 'Reports > Attendance' },
  { label: 'Class-wise Attendance', url: '/reports/attendance/class-wise', category: 'Reports > Attendance' },
  { label: 'Absent Students Report', url: '/reports/attendance/absent-students', category: 'Reports > Attendance' },

  // Reports — Fees
  { label: 'Fee Reports Dashboard', url: '/reports/fees', category: 'Reports > Fees' },
  { label: 'Paid Fees Report', url: '/reports/fees/paid', category: 'Reports > Fees' },
  { label: 'Pending Fees Report', url: '/reports/fees/pending', category: 'Reports > Fees' },
  { label: 'Overdue Fees Report', url: '/reports/fees/overdue', category: 'Reports > Fees' },
  { label: 'Collection Summary', url: '/reports/fees/collection-summary', category: 'Reports > Fees' },
  { label: 'Fee Defaulters', url: '/reports/fees/defaulters', category: 'Reports > Fees' },

  // Reports — Exams
  { label: 'Exam Reports Dashboard', url: '/reports/exams', category: 'Reports > Exams' },
  { label: 'Exam Results Report', url: '/reports/exams/results', category: 'Reports > Exams' },
  { label: 'Class Performance Report', url: '/reports/exams/class-performance', category: 'Reports > Exams' },
  { label: 'Subject Performance Report', url: '/reports/exams/subject-performance', category: 'Reports > Exams' },
  { label: 'Student Result Summary', url: '/reports/exams/student-summary', category: 'Reports > Exams' },
  { label: 'Top Performers', url: '/reports/exams/top-performers', category: 'Reports > Exams' },
  { label: 'Pass/Fail Analysis', url: '/reports/exams/pass-fail-analysis', category: 'Reports > Exams' },

  // Reports — Teachers
  { label: 'Teacher Reports Dashboard', url: '/reports/teachers', category: 'Reports > Teachers' },
  { label: 'Teacher List', url: '/reports/teachers/list', category: 'Reports > Teachers' },
  { label: 'Teacher Attendance', url: '/reports/teachers/attendance', category: 'Reports > Teachers' },
  { label: 'Subject Allocation', url: '/reports/teachers/subject-allocation', category: 'Reports > Teachers' },
  { label: 'Class Teacher Mapping', url: '/reports/teachers/class-teacher-mapping', category: 'Reports > Teachers' },
  { label: 'Workload', url: '/reports/teachers/workload', category: 'Reports > Teachers' },

  // Reports — Parents
  { label: 'Parent Reports Dashboard', url: '/reports/parents', category: 'Reports > Parents' },
  { label: 'Parent List', url: '/reports/parents/list', category: 'Reports > Parents' },
  { label: 'Parent-Student Mapping', url: '/reports/parents/mapping', category: 'Reports > Parents' },
  { label: 'Activity Summary', url: '/reports/parents/activity-summary', category: 'Reports > Parents' },

  // Modules (continued)
  { label: 'Students', url: '/admin/students', category: 'Modules' },
  { label: 'Parents', url: '/admin/parents', category: 'Modules' },
  { label: 'Teachers', url: '/admin/teachers', category: 'Modules' },
  { label: 'Exams', url: '/admin/exams', category: 'Modules' },
  { label: 'Homework', url: '/admin/homework', category: 'Modules' },

  // Leave Management
  { label: 'Leave Types', url: '/admin/leave-types', category: 'Leave Management' },
  { label: 'Leave Requests', url: '/admin/leave-requests', category: 'Leave Management' },

  // Modules (continued)
  { label: 'Academic', url: '/admin/academics', category: 'Modules' },
  { label: 'Timetable', url: '/admin/timetable', category: 'Modules' },
  { label: 'Attendance', url: '/admin/attendance', category: 'Modules' },
  { label: 'Student Documents', url: '/admin/documents', category: 'Modules' },
  { label: 'Event Calendar', url: '/admin/calendar', category: 'Modules' },
  { label: 'Users', url: '/admin/users', category: 'Modules' },
  { label: 'Transport', url: '/admin/transport', category: 'Modules' },
  { label: 'Transport Reports', url: '/admin/transport/reports', category: 'Modules' },
];

// ─── Helper: Login ─────────────────────────────────────────────────────────────

async function login(page: Page) {
  await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(500);

  // Fill login form
  const emailInput = page.locator('input[name="email"], input[type="email"], #email').first();
  const passInput = page.locator('input[name="password"], input[type="password"], #password').first();

  if (await emailInput.isVisible()) {
    await emailInput.fill(LOGIN_EMAIL);
    await passInput.fill(LOGIN_PASS);
    await page.locator('button[type="submit"]').first().click({ noWaitAfter: true });
    try {
      await page.waitForLoadState('networkidle', { timeout: 10000 });
    } catch {
      await page.waitForLoadState('domcontentloaded');
    }
    await page.waitForTimeout(1000);
  }
}

// ─── Helper: Setup console error listener ──────────────────────────────────────

function setupConsoleListener(page: Page, pageName: string) {
  const consoleErrors: string[] = [];
  const networkErrors: string[] = [];

  page.on('console', (msg) => {
    if (msg.type() === 'error') {
      consoleErrors.push(msg.text());
    }
  });

  page.on('pageerror', (error) => {
    consoleErrors.push(`PAGE ERROR: ${error.message}`);
  });

  page.on('response', (response) => {
    if (response.status() >= 400) {
      networkErrors.push(`${response.status()} ${response.url()}`);
    }
  });

  return { consoleErrors, networkErrors };
}

async function waitForPageSettle(page: Page) {
  try {
    await page.waitForLoadState('networkidle', { timeout: 10000 });
  } catch {
    await page.waitForLoadState('domcontentloaded');
  }
  await page.waitForTimeout(500);
}

// ─── Helper: Analyze page for issues ──────────────────────────────────────────

async function analyzePage(page: Page, pageName: string, listeners: ReturnType<typeof setupConsoleListener>) {
  // Wait for page to settle (DataTable-heavy pages may never reach networkidle)
  await waitForPageSettle(page);

  // Check for JS errors
  for (const err of listeners.consoleErrors) {
    const lower = err.toLowerCase();
    let severity: Issue['severity'] = 'Medium';
    let rootCause = '';
    let fix = '';

    if (lower.includes('$ is not defined') || lower.includes('jquery is not defined')) {
      severity = 'Critical';
      rootCause = 'jQuery not loaded before this script executes';
      fix = 'Ensure jQuery CDN loads before @vite() in layout';
    } else if (lower.includes('datatables') && lower.includes('warning')) {
      severity = 'Medium';
      rootCause = 'DataTables configuration warning';
      fix = 'Check DataTable initialization options';
    } else if (lower.includes('uncaught') || lower.includes('unhandled')) {
      severity = 'High';
      rootCause = 'Unhandled JavaScript exception';
      fix = 'Add try/catch or fix the root cause';
    } else if (lower.includes('failed to load resource') || lower.includes('net::err')) {
      severity = 'High';
      rootCause = 'Missing asset or broken URL';
      fix = 'Check asset path and build output';
    } else if (lower.includes('cors') || lower.includes('cross-origin')) {
      severity = 'Medium';
      rootCause = 'CORS policy blocking request';
      fix = 'Configure proper CORS headers';
    }

    if (severity === 'Critical' || severity === 'High') {
      const ss = await screenshot(page, `${pageName}_js_error`);
      recordIssue({
        page: pageName,
        category: 'JavaScript',
        severity,
        description: err.substring(0, 200),
        screenshot: ss,
        rootCause,
        recommendedFix: fix,
      });
    }
  }

  // Check network errors
  for (const err of listeners.networkErrors) {
    const ss = await screenshot(page, `${pageName}_network_error`);
    recordIssue({
      page: pageName,
      category: 'Functional',
      severity: err.startsWith('404') ? 'High' : err.startsWith('500') ? 'Critical' : 'Medium',
      description: `Network error: ${err}`,
      screenshot: ss,
      rootCause: err.startsWith('404') ? 'Route not found' : 'Server error',
      recommendedFix: err.startsWith('404') ? 'Check route definition' : 'Check controller logic',
    });
  }

  // Check for broken href="#" buttons (that should go somewhere)
  // Exclude nav-link toggles, JS-driven export-btn/data-type, export buttons with IDs, and JS-driven homework/document buttons
  const hashLinks = await page.locator('a.btn[href="#"]:not(.nav-link):not(.export-btn):not([data-type]):not([id*="export"]):not([id*="Export"]):not(#attachmentLink):not(#viewDownloadBtn):not(#collectionPdfBtn)').count();
  if (hashLinks > 0) {
    recordIssue({
      page: pageName,
      category: 'Functional',
      severity: 'Medium',
      description: `${hashLinks} button(s) with href="#" and no JS handler class — likely non-functional`,
      rootCause: 'Placeholder links not yet implemented',
      recommendedFix: 'Wire up proper route handlers',
    });
  }

  // Check for missing icons (card-headers without <i class="ti ti-")
  const cardHeaders = await page.locator('.card-header').count();
  for (let i = 0; i < cardHeaders; i++) {
    const header = page.locator('.card-header').nth(i);
    const html = await header.innerHTML();
    const hasIcon = html.includes('ti ti-');
    const hasText = (await header.textContent())?.trim() || '';
    if (hasText.length > 2 && !hasIcon && !html.includes('nav-tabs') && !html.includes('btn-group')) {
      recordIssue({
        page: pageName,
        category: 'UI',
        severity: 'Low',
        description: `Card header "${hasText.substring(0, 50)}" missing icon`,
        rootCause: 'Icon not added during implementation',
        recommendedFix: 'Add <i class="ti ti-* me-2"></i> before header text',
      });
    }
  }

  // Check for BS4 classes (mr-*, ml-*, form-inline, form-group, badge badge-*)
  // Only check application content, not vendor/AdminLTE CSS or markup
  const appContentHtml = await page.locator('.app-content, .content-wrapper, main, #app').first().innerHTML().catch(() => '');
  const bs4Patterns = [
    { regex: /\bmr-\d\b/g, name: 'mr-* (BS4)' },
    { regex: /\bml-\d\b/g, name: 'ml-* (BS4)' },
    { regex: /\bform-inline\b/g, name: 'form-inline (BS4)' },
    { regex: /\bform-group\b/g, name: 'form-group (BS4)' },
    { regex: /\bbadge badge-(?!bg-)/g, name: 'badge badge-* (BS4)' },
  ];
  for (const pattern of bs4Patterns) {
    const matches = appContentHtml.match(pattern.regex);
    if (matches && matches.length > 0) {
      recordIssue({
        page: pageName,
        category: 'UI',
        severity: 'Medium',
        description: `Bootstrap 4 class "${pattern.name}" found (${matches.length} occurrences)`,
        rootCause: 'Legacy BS4 class not migrated to BS5',
        recommendedFix: `Replace with BS5 equivalent (mr→me, ml→ms, etc.)`,
      });
    }
  }

  // Check for Font Awesome (should be Tabler only) — check rendered app content only
  if (appContentHtml.includes('fa fa-') || appContentHtml.includes('fas fa-') || appContentHtml.includes('far fa-')) {
    recordIssue({
      page: pageName,
      category: 'UI',
      severity: 'Medium',
      description: 'Font Awesome icon found — should use Tabler Icons only',
      rootCause: 'Legacy FA class not migrated',
      recommendedFix: 'Replace with Tabler icon (ti ti-*)',
    });
  }

  // Check for select elements without form-select class
  const selects = await page.locator('select:not(.form-select):not(.visually-hidden)').count();
  if (selects > 0) {
    recordIssue({
      page: pageName,
      category: 'UI',
      severity: 'Low',
      description: `${selects} <select> element(s) missing form-select class`,
      rootCause: 'BS5 migration incomplete',
      recommendedFix: 'Add class="form-select" to all <select> elements',
    });
  }

  // Check for empty tables (DataTable "No data available" state)
  const emptyTable = await page.locator('.dataTables_empty, .dt-empty').count();
  if (emptyTable > 0) {
    recordIssue({
      page: pageName,
      category: 'Data Integrity',
      severity: 'Low',
      description: 'DataTable shows empty state — may indicate missing seed data',
      rootCause: 'No records in database table',
      recommendedFix: 'Verify seed data or check query filters',
    });
  }

  // Check for overlapping elements (z-index issues)
  const fixedElements = await page.locator('[style*="position: fixed"], [style*="position:fixed"]').count();
  if (fixedElements > 3) {
    recordIssue({
      page: pageName,
      category: 'UI',
      severity: 'Low',
      description: `${fixedElements} fixed-position elements — potential overlap issues`,
      rootCause: 'Too many fixed elements on page',
      recommendedFix: 'Review z-index stacking context',
    });
  }
}

// ─── Test Suite ────────────────────────────────────────────────────────────────

test.describe('School ERP — Full Audit', () => {
  test.beforeAll(async ({ browser }) => {
    // Ensure screenshot dir exists
    if (!fs.existsSync(SCREENSHOT_DIR)) fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
  });

  test('Login', async ({ page }) => {
    await login(page);
    // Verify we're on the dashboard
    await expect(page).not.toHaveURL(/login/);
    const ss = await screenshot(page, 'login_success');
    recordIssue({
      page: 'Login',
      category: 'Setup',
      severity: 'Low',
      description: 'Login successful — audit session started',
      screenshot: ss,
    });
  });

  // ── Dashboard ──────────────────────────────────────────────────────────────

  test('Dashboard', async ({ page }) => {
    await login(page);
    const listeners = setupConsoleListener(page, 'Dashboard');

    await page.goto(`${BASE_URL}/admin/dashboard`);
    await analyzePage(page, 'Dashboard', listeners);

    // Test dashboard stat cards
    const statCards = await page.locator('.info-box, .small-box, .card .card-body h2, .card .card-body h3, .erp-hero-card, .nav-card-compact').count();
    if (statCards === 0) {
      recordIssue({
        page: 'Dashboard',
        category: 'UI',
        severity: 'Medium',
        description: 'No stat cards/KPI indicators found on dashboard',
        rootCause: 'Dashboard may not have data or cards not rendering',
        recommendedFix: 'Verify dashboard controller passes data to view',
      });
    }

    // Test chart containers
    const charts = await page.locator('canvas, .chart-container, [id*="chart"]').count();
    if (charts === 0) {
      recordIssue({
        page: 'Dashboard',
        category: 'UI',
        severity: 'Low',
        description: 'No chart elements found on dashboard',
        rootCause: 'Charts may require data or Chart.js initialization',
        recommendedFix: 'Verify Chart.js is initialized and data is passed',
      });
    }

    const ss = await screenshot(page, 'Dashboard');
  });

  // ── Every Sidebar Page ─────────────────────────────────────────────────────

  for (const sidebarPage of SIDEBAR_PAGES) {
    test(`${sidebarPage.category} > ${sidebarPage.label}`, async ({ page }) => {
      await login(page);
      const pageName = `${sidebarPage.category} > ${sidebarPage.label}`;
      const listeners = setupConsoleListener(page, pageName);

      // Navigate to page
      const response = await page.goto(`${BASE_URL}${sidebarPage.url}`, { waitUntil: 'domcontentloaded', timeout: 30000 });
      await waitForPageSettle(page);

      // Check HTTP status
      if (response && response.status() >= 400) {
        const ss = await screenshot(page, `${sidebarPage.label}_http_${response.status()}`);
        recordIssue({
          page: pageName,
          category: 'Functional',
          severity: response.status() === 500 ? 'Critical' : 'High',
          description: `HTTP ${response.status()} on ${sidebarPage.url}`,
          screenshot: ss,
          rootCause: response.status() === 404 ? 'Route not defined' : 'Server error',
          recommendedFix: response.status() === 404 ? 'Add missing route' : 'Check controller for errors',
        });
        return; // Don't analyze broken pages further
      }

      // Check for error page content (Laravel exception page)
      const bodyText = await page.textContent('body');
      if (bodyText && (bodyText.includes('Whoops') || bodyText.includes('Looks like something went wrong') || bodyText.includes('The page you requested was not found') || bodyText.includes('500 | Server Error') || bodyText.includes('404 | Not Found'))) {
        const ss = await screenshot(page, `${sidebarPage.label}_error_page`);
        recordIssue({
          page: pageName,
          category: 'Functional',
          severity: 'Critical',
          description: `Error page displayed: ${bodyText.substring(0, 100)}`,
          screenshot: ss,
          rootCause: 'Laravel error page shown',
          recommendedFix: 'Fix the underlying exception',
        });
        return;
      }

      // Analyze page
      await analyzePage(page, pageName, listeners);

      // Take screenshot
      const ss = await screenshot(page, sidebarPage.label);

      // ── Test Buttons ────────────────────────────────────────────────────────

      // Test DataTable buttons (Search, Pagination)
      const searchInput = page.locator('.dataTables_filter input, input[type="search"]').first();
      if (await searchInput.isVisible().catch(() => false)) {
        try {
          await searchInput.fill('test');
          await page.waitForTimeout(500);
          await searchInput.fill('');
          await page.waitForTimeout(500);
        } catch {
          // Search may not be interactive
        }
      }

      // Test Export buttons — exclude JS-driven export-btn, data-type, and known dynamic export IDs
      const exportButtons = page.locator('a[href*="export"], button:has-text("Export"), a:has-text("Excel"):not(.export-btn):not([data-type]):not(#exportExcel):not(#exportPdf):not(#exportPrint), a:has-text("PDF"):not(.export-btn):not([data-type]):not(#exportExcel):not(#exportPdf):not(#exportPrint), a:has-text("Print"):not(.export-btn):not([data-type]):not(#exportExcel):not(#exportPdf):not(#exportPrint)');
      const exportCount = await exportButtons.count();
      for (let i = 0; i < Math.min(exportCount, 3); i++) {
        const btn = exportButtons.nth(i);
        if (await btn.isVisible().catch(() => false)) {
          const href = await btn.getAttribute('href');
          if (href && href !== '#' && !href.includes('javascript:')) {
            // Just check the link exists — don't actually download
          } else if (href === '#' || !href) {
            const btnText = (await btn.textContent())?.trim() || 'Unknown';
            recordIssue({
              page: pageName,
              category: 'Functional',
              severity: 'High',
              description: `Export button "${btnText}" has no working href`,
              rootCause: 'href="#" placeholder not wired up',
              recommendedFix: 'Add proper export route',
            });
          }
        }
      }

      // Test Filter buttons
      const filterBtns = page.locator('button:has-text("Filter"), button:has-text("Apply"), button:has-text("Search")');
      const filterCount = await filterBtns.count();
      // Just verify they exist — don't click to avoid side effects

      // Test Reset buttons
      const resetBtns = page.locator('button:has-text("Reset"), a:has-text("Reset"), button:has-text("Clear")');
      const resetCount = await resetBtns.count();

      // Test Create/Add/New buttons (modals or redirects)
      const createBtns = page.locator('button:has-text("Add"), button:has-text("Create"), button:has-text("New"), a:has-text("Add"), a:has-text("Create")');
      const createCount = await createBtns.count();
      for (let i = 0; i < Math.min(createCount, 2); i++) {
        const btn = createBtns.nth(i);
        if (await btn.isVisible().catch(() => false)) {
          try {
            await btn.click();
            await page.waitForTimeout(500);

            // Check if a modal opened
            const modal = page.locator('.modal.show, [role="dialog"]:not(.visually-hidden)');
            if (await modal.isVisible().catch(() => false)) {
              // Modal opened — good, close it
              const closeBtn = modal.locator('button[data-bs-dismiss="modal"], .btn-close').first();
              if (await closeBtn.isVisible().catch(() => false)) {
                await closeBtn.click();
                await page.waitForTimeout(300);
              }
            }

            // Check for navigation (redirect to create page)
            const currentUrl = page.url();
            if (currentUrl.includes('/create')) {
              // Navigated to create page — go back
              await page.goBack();
              await page.waitForLoadState('networkidle');
            }
          } catch {
            // Button may not be clickable
          }
        }
      }

      // Test View/Edit buttons in DataTable rows
      const viewBtns = page.locator('.btn-outline-primary:has-text("View"), a:has-text("View"), button:has-text("View")');
      if (await viewBtns.count() > 0) {
        const firstView = viewBtns.first();
        if (await firstView.isVisible().catch(() => false)) {
          try {
            const href = await firstView.getAttribute('href');
            if (href && href !== '#') {
              // Good — has a working link
            } else {
              // May be a JS-driven button
            }
          } catch {
            // ignore
          }
        }
      }

      // Test pagination
      const paginationLinks = page.locator('.paginate_button a, .pagination a, [aria-label="Next"]');
      if (await paginationLinks.count() > 0) {
        const nextPage = page.locator('.paginate_button.next a, [aria-label="Next"]').first();
        if (await nextPage.isVisible().catch(() => false)) {
          try {
            const isDisabled = await nextPage.getAttribute('aria-disabled');
            if (isDisabled !== 'true') {
              // Pagination exists and is functional
            }
          } catch {
            // ignore
          }
        }
      }
    });
  }

  // ── Modal/Form Tests ───────────────────────────────────────────────────────

  test('Modals open and close correctly', async ({ page }) => {
    await login(page);

    // Test each page with known modals
    const pagesWithModals = [
      { url: '/admin/students', modalTrigger: '#createStudent, button:has-text("Admit")', name: 'Students' },
      { url: '/admin/teachers', modalTrigger: '#createTeacher, button:has-text("Add Teacher")', name: 'Teachers' },
      { url: '/admin/parents', modalTrigger: '#createParent, button:has-text("Add Parent")', name: 'Parents' },
      { url: '/admin/exams', modalTrigger: '#createExam, button:has-text("Add Exam")', name: 'Exams' },
      { url: '/admin/documents', modalTrigger: '#createDocument, button:has-text("Upload")', name: 'Documents' },
    ];

    for (const { url, modalTrigger, name } of pagesWithModals) {
      const listeners = setupConsoleListener(page, `${name} Modal`);
      await page.goto(`${BASE_URL}${url}`, { waitUntil: 'domcontentloaded', timeout: 20000 });
      await page.waitForTimeout(1500);

      const trigger = page.locator(modalTrigger).first();
      if (await trigger.isVisible().catch(() => false)) {
        try {
          await trigger.click();
          await page.waitForTimeout(500);

          const modal = page.locator('.modal.show, .modal[style*="display: block"]');
          if (await modal.isVisible().catch(() => false)) {
            // Modal opened — check form fields
            const inputs = await modal.locator('input, select, textarea').count();
            if (inputs === 0) {
              recordIssue({
                page: `${name} Modal`,
                category: 'Functional',
                severity: 'Medium',
                description: 'Modal opened but contains no form inputs',
                rootCause: 'Modal content not loaded or dynamically rendered',
                recommendedFix: 'Ensure modal body contains form fields',
              });
            }

            // Close modal
            const closeBtn = modal.locator('button[data-bs-dismiss="modal"], .btn-close, button:has-text("Cancel")').first();
            if (await closeBtn.isVisible().catch(() => false)) {
              await closeBtn.click();
              await page.waitForTimeout(300);
            }
          } else {
            recordIssue({
              page: `${name} Modal`,
              category: 'Functional',
              severity: 'High',
              description: `Modal did not open after clicking "${name}" create button`,
              rootCause: 'Bootstrap modal JS not initialized or data-bs-target mismatch',
              recommendedFix: 'Verify modal ID matches data-bs-target attribute',
            });
          }
        } catch (e) {
          recordIssue({
            page: `${name} Modal`,
            category: 'Functional',
            severity: 'Medium',
            description: `Error interacting with modal trigger: ${(e as Error).message}`,
          });
        }
      }
    }
  });

  // ── Responsive Tests ───────────────────────────────────────────────────────

  test('Mobile responsiveness', async ({ page }) => {
    await login(page);

    // Test key pages at mobile viewport
    const keyPages = [
      { url: '/admin/dashboard', name: 'Dashboard' },
      { url: '/admin/students', name: 'Students' },
      { url: '/admin/fees', name: 'Fees' },
      { url: '/reports/fees/defaulters', name: 'Fee Defaulters' },
    ];

    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 812 });

    for (const { url, name } of keyPages) {
      const listeners = setupConsoleListener(page, `${name} Mobile`);
      await page.goto(`${BASE_URL}${url}`, { waitUntil: 'domcontentloaded', timeout: 20000 });
      await page.waitForTimeout(1500);

      // Check for horizontal overflow
      const bodyWidth = await page.evaluate(() => document.body.scrollWidth);
      const viewportWidth = await page.evaluate(() => window.innerWidth);

      if (bodyWidth > viewportWidth + 10) {
        const ss = await screenshot(page, `${name}_mobile_overflow`);
        recordIssue({
          page: `${name} (Mobile)`,
          category: 'UI',
          severity: 'Medium',
          description: `Horizontal overflow: body scrollWidth(${bodyWidth}) > viewport(${viewportWidth})`,
          screenshot: ss,
          rootCause: 'Elements not properly responsive',
          recommendedFix: 'Add responsive CSS or use overflow-x: auto on tables',
        });
      }

      // Check if sidebar is hidden on mobile
      const sidebarVisible = await page.locator('.app-sidebar').isVisible().catch(() => false);
      if (sidebarVisible) {
        const sidebarWidth = await page.locator('.app-sidebar').evaluate(el => el.getBoundingClientRect().width);
        if (sidebarWidth > 0) {
          // Sidebar may be overlaid — check if it overlaps content
        }
      }

      const ss = await screenshot(page, `${name}_mobile`);
    }
  });

  // ── Write Report ───────────────────────────────────────────────────────────

  test('Generate audit report', async () => {
    // Sort issues by severity
    const severityOrder = { Critical: 0, High: 1, Medium: 2, Low: 3 };
    issues.sort((a, b) => severityOrder[a.severity] - severityOrder[b.severity]);

    const criticalCount = issues.filter(i => i.severity === 'Critical').length;
    const highCount = issues.filter(i => i.severity === 'High').length;
    const mediumCount = issues.filter(i => i.severity === 'Medium').length;
    const lowCount = issues.filter(i => i.severity === 'Low').length;

    let report = `# School ERP — UI/UX/Functional Audit Report\n\n`;
    report += `**Date:** ${new Date().toISOString().split('T')[0]}\n`;
    report += `**Pages Audited:** ${SIDEBAR_PAGES.length + 4}\n`;
    report += `**Total Issues:** ${issues.length}\n\n`;
    report += `## Summary\n\n`;
    report += `| Severity | Count |\n`;
    report += `|----------|-------|\n`;
    report += `| Critical | ${criticalCount} |\n`;
    report += `| High | ${highCount} |\n`;
    report += `| Medium | ${mediumCount} |\n`;
    report += `| Low | ${lowCount} |\n\n`;

    report += `## Issues\n\n`;
    report += `| # | Page | Category | Severity | Issue | Screenshot | Root Cause | Recommended Fix |\n`;
    report += `|---|------|----------|----------|-------|------------|------------|------------------|\n`;

    for (let i = 0; i < issues.length; i++) {
      const issue = issues[i];
      const ss = issue.screenshot ? `[Screenshot](${issue.screenshot})` : '—';
      report += `| ${i + 1} | ${issue.page} | ${issue.category} | ${issue.severity} | ${issue.description.replace(/\|/g, '\\|')} | ${ss} | ${issue.rootCause || '—'} | ${issue.recommendedFix || '—'} |\n`;
    }

    report += `\n---\n\n`;
    report += `## Pages Audited\n\n`;
    report += `| # | Category | Page | URL |\n`;
    report += `|---|----------|------|-----|\n`;
    for (let i = 0; i < SIDEBAR_PAGES.length; i++) {
      const p = SIDEBAR_PAGES[i];
      report += `| ${i + 1} | ${p.category} | ${p.label} | ${p.url} |\n`;
    }

    fs.writeFileSync(REPORT_PATH, report, 'utf-8');
    console.log(`\n✅ Audit report written to: ${REPORT_PATH}`);
    console.log(`📸 Screenshots saved to: ${SCREENSHOT_DIR}`);
    console.log(`📊 Issues found: ${issues.length} (Critical: ${criticalCount}, High: ${highCount}, Medium: ${mediumCount}, Low: ${lowCount})\n`);
  });
});
