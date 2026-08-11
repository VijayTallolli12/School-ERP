import { test, expect, type Page } from '@playwright/test';

const RUN_NOTES = 'Payslip test payroll run';
const CTC = 600000;

test.describe('Payslip Management', () => {
  const now = Date.now();
  const BASE_MONTH = ((now % 12) + 1);
  const RUN_YEAR = 2026 + (now % 10);
  const suffix = String(now).slice(-8);

  async function csrf(page: Page): Promise<string> {
    return await page.evaluate(() => {
      const meta = document.querySelector('meta[name="csrf-token"]');
      return meta ? meta.getAttribute('content') : '';
    });
  }

  async function apiPost(page: Page, url: string, data: Record<string, any>): Promise<{status: number, body: any}> {
    const token = await csrf(page);
    const res = await page.request.post(url, {
      data,
      headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
    });
    return { status: res.status(), body: await res.json() };
  }

  async function apiGet(page: Page, url: string): Promise<{status: number, body: any}> {
    const token = await csrf(page);
    const res = await page.request.get(url, {
      headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
    });
    return { status: res.status(), body: await res.json() };
  }

  async function apiDelete(page: Page, url: string): Promise<void> {
    const token = await csrf(page);
    await page.request.post(url, {
      headers: { 'X-CSRF-TOKEN': token },
      form: { _method: 'DELETE' },
    });
  }

  async function login(page: Page) {
    await page.goto('/login', { timeout: 30000 });
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(1000);
    await page.fill('input[name="email"]', process.env.E2E_EMAIL || 'admin@school.com');
    await page.fill('input[name="password"]', process.env.E2E_PASSWORD || 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/admin/, { timeout: 20000 });
  }

  test('PAY-ALL: Full payslip lifecycle', { timeout: 180000 }, async ({ page }) => {
    await login(page);

    // Navigate to payroll
    await page.goto('/admin/payroll', { timeout: 30000, waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);

    // UI checks: Payslips tab
    await expect(page.locator('#payrollTabs')).toBeVisible();
    const payslipTab = page.locator('button[data-bs-target="#payslipsPane"]');
    await expect(payslipTab).toBeVisible();
    await expect(payslipTab).toContainText('Payslips');
    await payslipTab.click();
    await page.waitForTimeout(500);
    await expect(page.locator('#psFilterRun')).toBeVisible();
    await expect(page.locator('#bulkGenerateBtn')).toBeVisible();
    await expect(page.locator('#payslipsTable')).toBeVisible();

    // ─── Create test data ────────────────────────────────────────────────

    const basic = await apiPost(page, '/admin/payroll/salary-components', {
      name: `PS-Basic ${suffix}`, name_display: `Basic Pay ${suffix}`,
      component_type: 'earning', calculation_type: 'fixed', value: 5000, sort_order: 1, status: 'active',
    });
    expect(basic.status).toBe(200);
    const basicComponentId = basic.body.data.id;

    const da = await apiPost(page, '/admin/payroll/salary-components', {
      name: `PS-DA ${suffix}`, name_display: `Dearness Allowance ${suffix}`,
      component_type: 'earning', calculation_type: 'percentage', value: 10, sort_order: 2, status: 'active',
    });
    expect(da.status).toBe(200);
    const daComponentId = da.body.data.id;

    const pf = await apiPost(page, '/admin/payroll/salary-components', {
      name: `PS-PF ${suffix}`, name_display: `Provident Fund ${suffix}`,
      component_type: 'deduction', calculation_type: 'fixed', value: 500, sort_order: 3, status: 'active',
    });
    expect(pf.status).toBe(200);
    const pfComponentId = pf.body.data.id;

    const tax = await apiPost(page, '/admin/payroll/salary-components', {
      name: `PS-Tax ${suffix}`, name_display: `Income Tax ${suffix}`,
      component_type: 'deduction', calculation_type: 'percentage', value: 5, sort_order: 4, status: 'active',
    });
    expect(tax.status).toBe(200);
    const taxComponentId = tax.body.data.id;

    const structure = await apiPost(page, '/admin/payroll/salary-structures', {
      employee_id: `PS-EMP-${suffix}`, employee_type: 'teacher', pay_grade_id: '',
      effective_from: '2026-01-01', total_ctc: CTC, status: 'active',
    });
    expect(structure.status).toBe(200);
    const structureId = structure.body.data.id;

    // ─── Generate & lock payroll run ─────────────────────────────────────

    let runRes;
    let runMonth = BASE_MONTH;
    for (let i = 0; i < 12; i++) {
      runRes = await apiPost(page, '/admin/payroll/runs/generate', {
        month: runMonth, year: RUN_YEAR, notes: RUN_NOTES,
      });
      if (runRes.status === 200) break;
      runMonth = (runMonth % 12) + 1;
    }
    expect(runRes.status).toBe(200);
    const runId = runRes.body.data.id;

    const locked = await apiPost(page, `/admin/payroll/runs/${runId}/lock`, { notes: RUN_NOTES });
    expect(locked.status).toBe(200);
    expect(locked.body.data.status).toBe('locked');

    // ─── Get first item ──────────────────────────────────────────────────

    const itemsRes = await apiGet(page, `/admin/payroll/runs/${runId}/items/data`);
    expect(itemsRes.status).toBe(200);
    const itemsData = itemsRes.body.data || [];
    expect(itemsData.length).toBeGreaterThan(0);
    const itemId = itemsData[0].id;

    // ─── Generate single payslip ─────────────────────────────────────────

    const payslip = await apiPost(page, '/admin/payroll/payslips/generate', {
      payroll_run_id: runId, payroll_item_id: itemId,
    });
    console.log('generate payslip:', JSON.stringify(payslip));
    expect(payslip.status).toBe(200);
    expect(payslip.body.success).toBe(true);
    expect(payslip.body.data.payslip_number).toMatch(/^PS-\d{4}-\d{2}-\d{6}$/);
    expect(payslip.body.data.employee_name).toBeTruthy();
    expect(parseFloat(payslip.body.data.gross_salary)).toBeGreaterThan(0);
    expect(parseFloat(payslip.body.data.net_salary)).toBeGreaterThan(0);
    const payslipId = payslip.body.data.id;

    // ─── Duplicate prevention ──────────────────────────────────────────

    const duplicate = await apiPost(page, '/admin/payroll/payslips/generate', {
      payroll_run_id: runId, payroll_item_id: itemId,
    });
    if (duplicate.status === 422) {
      expect(duplicate.body.errors).toBeTruthy();
    } else {
      expect(duplicate.body.success).toBe(false);
    }

    // ─── Bulk generate ─────────────────────────────────────────────────

    const bulk = await apiPost(page, '/admin/payroll/payslips/bulk-generate', {
      payroll_run_id: runId,
    });
    expect(bulk.status).toBe(200);
    expect(bulk.body.success).toBe(true);

    // ─── Show endpoint ─────────────────────────────────────────────────

    const showRes = await apiGet(page, `/admin/payroll/payslips/${payslipId}`);
    expect(showRes.status).toBe(200);
    expect(showRes.body.success).toBe(true);
    expect(showRes.body.data.payslip_number).toMatch(/^PS-/);

    // ─── Draft run prevention ──────────────────────────────────────────

    const draftMonth = ((runMonth + 3) % 12) + 1;
    const draftRun = await apiPost(page, '/admin/payroll/runs/generate', {
      month: draftMonth, year: RUN_YEAR, notes: 'Draft for payslip test',
    });
    if (draftRun.status === 200 && !draftRun.body.data.locked) {
      const draftItemsRes = await apiGet(page, `/admin/payroll/runs/${draftRun.body.data.id}/items/data`);
      const draftItemData = draftItemsRes.body.data || [];
      if (draftItemData.length > 0) {
        const draftGen = await apiPost(page, '/admin/payroll/payslips/generate', {
          payroll_run_id: draftRun.body.data.id, payroll_item_id: draftItemData[0].id,
        });
        if (draftGen.status === 422) {
          expect(draftGen.body.errors).toBeTruthy();
        } else {
          expect(draftGen.body.success).toBe(false);
          expect((draftGen.body.message || '').toLowerCase()).toContain('lock');
        }
      }
    }

    // ─── Print view ────────────────────────────────────────────────────

    await page.goto(`/admin/payroll/payslips/${payslipId}/print`, { timeout: 30000 });
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    await expect(page.locator('#payslip-print-area')).toBeVisible();

    // ─── PDF download ──────────────────────────────────────────────────

    const pdfResp = await page.request.get(`/admin/payroll/payslips/${payslipId}/pdf`);
    expect(pdfResp.status()).toBe(200);
    expect((pdfResp.headers()['content-type'] || '')).toContain('application/pdf');

    // ─── Payslip History tab ───────────────────────────────────────────

    await page.goto('/admin/payroll/reports', { timeout: 30000 });
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(500);
    await page.locator('button[data-bs-target="#payslipHistoryPane"]').click();
    await page.waitForTimeout(1000);

    const histTable = page.locator('#payslipHistoryTable');
    await expect(histTable).toBeVisible();
    const headers = await histTable.locator('thead tr th').allTextContents();
    expect(headers).toContain('Payslip #');
    expect(headers).toContain('Employee');
    expect(headers).toContain('Actions');

    // ─── Cleanup ───────────────────────────────────────────────────────

    await apiDelete(page, `/admin/payroll/salary-components/${basicComponentId}`);
    await apiDelete(page, `/admin/payroll/salary-components/${daComponentId}`);
    await apiDelete(page, `/admin/payroll/salary-components/${pfComponentId}`);
    await apiDelete(page, `/admin/payroll/salary-components/${taxComponentId}`);
    await apiDelete(page, `/admin/payroll/salary-structures/${structureId}`);
  });
});
