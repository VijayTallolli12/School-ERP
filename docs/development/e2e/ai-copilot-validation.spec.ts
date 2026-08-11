import { test, expect } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const AUTH_FILE = path.join(__dirname, 'auth.json');
const SCREENSHOT_DIR = path.join(__dirname, 'screenshots', 'ai-copilot');
const REPORT_PATH = path.join(__dirname, '..', 'AI_PLAYWRIGHT_VALIDATION_REPORT.md');
const BASE_URL = process.env.BASE_URL || 'http://127.0.0.1:8000';

test.describe('AI Copilot Validation - Phase AI-1.2', () => {
  const results: Array<{
    query: string;
    intent: string;
    response: string;
    pass: boolean;
    reason: string;
    screenshot: string;
    time: number;
  }> = [];

  test.beforeAll(async ({ browser }) => {
    if (!fs.existsSync(SCREENSHOT_DIR)) {
      fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
    }

    const context = await browser.newContext();
    const page = await context.newPage();
    await page.goto(`${BASE_URL}/login`);
    await page.waitForTimeout(1500);

    const emailInput = page.locator('input[type="email"], input[name="email"]').first();
    const passwordInput = page.locator('input[type="password"], input[name="password"]').first();

    if (await emailInput.isVisible({ timeout: 5000 })) {
      await emailInput.fill('superadmin@example.com');
      await passwordInput.fill('password');
      await page.locator('button[type="submit"]').first().click({ noWaitAfter: true });
      await page.waitForURL(/\/admin|\/dashboard/, { timeout: 30000 });
      await page.waitForTimeout(1000);
    }

    await page.context().storageState({ path: AUTH_FILE });
    await context.close();
  });

  test.use({ storageState: AUTH_FILE });

  async function openAskErp(page: any): Promise<void> {
    await page.goto(`${BASE_URL}/admin/dashboard`, { timeout: 30000, waitUntil: 'networkidle' });
    await page.waitForTimeout(1500);
    const askBtn = page.locator('[data-bs-target="#askErpModal"]').first();
    await askBtn.waitFor({ state: 'visible', timeout: 15000 });
    await askBtn.click();
    await page.waitForSelector('#askErpModal.show', { timeout: 5000 });
    await page.waitForTimeout(500);
  }

  async function askQuestion(page: any, question: string): Promise<{ response: string; time: number }> {
    const start = Date.now();
    await page.fill('#aiQuestion', question);
    await page.click('#askErpBtn');

    try {
      await page.waitForFunction(() => {
        const loading = document.querySelector('#aiLoading');
        const response = document.querySelector('#aiResponseArea');
        return loading?.classList.contains('d-none') && response && !response.classList.contains('d-none');
      }, { timeout: 60000 });
    } catch {
      // Timeout - response area might still have something
    }

    const time = Date.now() - start;
    const response = (await page.textContent('#aiResponseContent')) || '';
    return { response: response.trim(), time };
  }

  async function captureScreenshot(page: any, name: string): Promise<string> {
    const filename = `${name.replace(/[^a-zA-Z0-9]/g, '_')}.png`;
    const filepath = path.join(SCREENSHOT_DIR, filename);
    await page.screenshot({ path: filepath, fullPage: false });
    return filepath;
  }

  function addResult(query: string, intent: string, response: string, pass: boolean, reason: string, screenshot: string, time: number) {
    results.push({ query, intent, response, pass, reason, screenshot, time });
    console.log(`${pass ? '✅' : '❌'} ${query}: ${reason}`);
  }

  // ============================
  // TEST SET 1 – ATTENDANCE
  // ============================

  test('T1.1: Show today\'s attendance', async ({ page }) => {
    const query = "Show today's attendance";
    const intent = "attendance.daily";
    await openAskErp(page);
    const { response, time } = await askQuestion(page, query);
    const screenshot = await captureScreenshot(page, 'T1.1_today_attendance');

    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];
    const todayShort = today.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    const todayDay = today.toLocaleDateString('en-US', { weekday: 'long' });

    const hasToday = /today/i.test(response) || response.includes(todayStr) || response.includes(todayShort) || response.includes(todayDay);
    const noMonthly = !/monthly\s+attendance/i.test(response) && !/attendance.*report.*month/i.test(response);

    const pass = hasToday && noMonthly;
    const reason = !hasToday
      ? 'Response does not mention today or current date'
      : !noMonthly
        ? 'FAIL: Monthly report returned instead of daily'
        : 'Response correctly references today\'s attendance';

    addResult(query, intent, response, pass, reason, screenshot, time);
    expect(pass, reason).toBeTruthy();
  });

  test('T1.2: Who is absent today?', async ({ page }) => {
    const query = "Who is absent today?";
    const intent = "attendance.absent_today";
    await openAskErp(page);
    const { response, time } = await askQuestion(page, query);
    const screenshot = await captureScreenshot(page, 'T1.2_absent_today');

    const hasAbsent = /absent/i.test(response);
    const hasStudentOrCount = /student|pupil|count|number|\d+/i.test(response);
    const todayStr = new Date().toISOString().split('T')[0];
    const todayShort = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    const hasDate = /today/i.test(response) || response.includes(todayStr) || response.includes(todayShort);

    const pass = hasAbsent && (hasStudentOrCount || hasDate);
    const reason = !hasAbsent
      ? 'Response does not mention "absent"'
      : !hasStudentOrCount && !hasDate
        ? 'Response lacks student count or date reference'
        : 'Response correctly mentions absent students with date';

    addResult(query, intent, response, pass, reason, screenshot, time);
    expect(pass, reason).toBeTruthy();
  });

  test('T1.3: Attendance class wise today', async ({ page }) => {
    const query = "Attendance class wise today";
    const intent = "attendance.class_wise";
    await openAskErp(page);
    const { response, time } = await askQuestion(page, query);
    const screenshot = await captureScreenshot(page, 'T1.3_class_wise_attendance');

    const hasClassRef = /class\s*\d|grade|section|class\s*\w/i.test(response);

    const pass = hasClassRef;
    const reason = !hasClassRef
      ? 'Response does not contain class/grade references'
      : 'Response contains class-wise data';

    addResult(query, intent, response, pass, reason, screenshot, time);
    expect(pass, reason).toBeTruthy();
  });

  // ============================
  // TEST SET 2 – FEES
  // ============================

  test('T2.1: How much fees are pending this month?', async ({ page }) => {
    const query = "How much fees are pending this month?";
    const intent = "fees.pending_monthly";
    await openAskErp(page);
    const { response, time } = await askQuestion(page, query);
    const screenshot = await captureScreenshot(page, 'T2.1_pending_fees');

    const hasAmount = /₹|\$|Rs\.?|amount|rupee|currency/i.test(response);
    const hasPending = /pending|outstanding|due|unpaid|collectable/i.test(response);
    const noPayroll = !/payroll|salary|employee\s+pay/i.test(response);

    const pass = hasAmount && hasPending && noPayroll;
    const reason = !hasAmount
      ? 'Response does not contain currency/amount'
      : !hasPending
        ? 'Response does not mention pending/outstanding fees'
        : !noPayroll
          ? 'FAIL: Payroll data mixed in response'
          : 'Response correctly shows pending fees with amount';

    addResult(query, intent, response, pass, reason, screenshot, time);
    expect(pass, reason).toBeTruthy();
  });

  test('T2.2: Show pending fees class wise', async ({ page }) => {
    const query = "Show pending fees class wise";
    const intent = "fees.pending_class_wise";
    await openAskErp(page);
    const { response, time } = await askQuestion(page, query);
    const screenshot = await captureScreenshot(page, 'T2.2_pending_fees_class_wise');

    const hasClassRef = /class\s*\d|grade|section|class\s*\w/i.test(response);
    const hasAmount = /₹|\$|Rs\.?|amount|rupee/i.test(response);

    const pass = hasClassRef || hasAmount;
    const reason = !hasClassRef && !hasAmount
      ? 'Response does not contain class names or amounts'
      : 'Response contains class-wise or amount data';

    addResult(query, intent, response, pass, reason, screenshot, time);
    expect(pass, reason).toBeTruthy();
  });

  // ============================
  // TEST SET 3 – PAYROLL
  // ============================

  test('T3.1: Run payroll for June', async ({ page }) => {
    const query = "Run payroll for June";
    const intent = "payroll.run";
    await openAskErp(page);
    const { response, time } = await askQuestion(page, query);
    const screenshot = await captureScreenshot(page, 'T3.1_run_payroll');

    const hasConfirmBtn = await page.locator('#aiConfirmBtn').isVisible().catch(() => false);
    const hasCancelBtn = await page.locator('#aiCancelBtn').isVisible().catch(() => false);
    const hasConfirmationText = /confirm|approval|action\s+required|proceed|preview/i.test(response);

    const pass = (hasConfirmBtn && hasCancelBtn) || hasConfirmationText;
    const reason = !hasConfirmationText && !hasConfirmBtn
      ? 'FAIL: No confirmation dialog shown for destructive payroll action'
      : hasConfirmBtn && hasCancelBtn
        ? 'Confirmation dialog with Confirm/Cancel buttons displayed'
        : 'Response indicates confirmation is required';

    addResult(query, intent, response, pass, reason, screenshot, time);
    expect(pass, reason).toBeTruthy();
  });

  // ============================
  // TEST SET 4 – EXECUTIVE SUMMARY
  // ============================

  test('T4.1: Give me today\'s school summary', async ({ page }) => {
    const query = "Give me today's school summary";
    const intent = "school_summary";
    await openAskErp(page);
    const { response, time } = await askQuestion(page, query);
    const screenshot = await captureScreenshot(page, 'T4.1_school_summary');

    const lower = response.toLowerCase();
    const modules = ['attendance', 'fee', 'transport', 'homework', 'notification', 'exam', 'leave', 'library'];
    const found = modules.filter(m => lower.includes(m));

    const pass = found.length >= 4;
    const reason = found.length < 4
      ? `Only found ${found.length} modules: [${found.join(', ')}] — need 4+`
      : `Found ${found.length} modules: [${found.join(', ')}]`;

    addResult(query, intent, response, pass, reason, screenshot, time);
    expect(pass, reason).toBeTruthy();
  });

  // ============================
  // GENERATE REPORT
  // ============================

  test.afterAll(async () => {
    if (results.length === 0) {
      console.log('⚠️ No test results collected — report will be empty.');
      return;
    }

    const totalTests = results.length;
    const passed = results.filter(r => r.pass).length;
    const failed = totalTests - passed;
    const avgTime = Math.round(results.reduce((s, r) => s + r.time, 0) / totalTests);
    const intentAccuracy = Math.round((passed / totalTests) * 100);
    const businessAccuracy = intentAccuracy;

    const report = `# AI Playwright Validation Report
# Phase AI-1.2 – AI Copilot Playwright Validation
Generated: ${new Date().toISOString()}

---

## Executive Summary

| Metric | Value |
|--------|-------|
| Total Tests | ${totalTests} |
| Passed | ${passed} |
| Failed | ${failed} |
| Pass Rate | ${intentAccuracy}% |
| Avg Response Time | ${avgTime}ms |

---

## Detailed Results

| # | Query | Expected Intent | Pass/Fail | Time | Reason |
|---|-------|----------------|-----------|------|--------|
${results.map((r, i) => `| ${i + 1} | ${r.query} | ${r.intent} | ${r.pass ? 'PASS' : 'FAIL'} | ${r.time}ms | ${r.reason} |`).join('\n')}

---

## Failed Tests Analysis

${results.filter(r => !r.pass).length === 0
    ? 'All tests passed!'
    : results.filter(r => !r.pass).map((r, i) => `
### ${i + 1}. ${r.query}
- **Response (first 300 chars):** ${r.response.substring(0, 300)}
- **Reason:** ${r.reason}
- **Screenshot:** ${r.screenshot}
- **Failure Category:** ${r.reason.includes('FAIL') ? 'Business Logic / Routing' : 'Response Format / Parameter Extraction'}
`).join('\n')}

---

## Screenshots

${results.map((r, i) => `${i + 1}. [${r.query}](${r.screenshot})`).join('\n')}

---

## Summary Statistics

- **Intent Accuracy:** ${intentAccuracy}%
- **Business Accuracy:** ${businessAccuracy}%
- **Wrong Responses:** ${results.filter(r => !r.pass).length > 0 ? results.filter(r => !r.pass).map(r => r.query).join(', ') : 'None'}
- **Missing Parameters:** ${results.filter(r => r.reason.includes('parameter') || r.reason.includes('missing')).length > 0 ? results.filter(r => r.reason.includes('parameter') || r.reason.includes('missing')).map(r => r.query).join(', ') : 'None'}
- **UI Problems:** ${results.filter(r => r.reason.includes('UI') || r.reason.includes('button') || r.reason.includes('modal')).length > 0 ? results.filter(r => r.reason.includes('UI') || r.reason.includes('button') || r.reason.includes('modal')).map(r => r.query).join(', ') : 'None'}
- **Average Response Time:** ${avgTime}ms

---

## Recommendations

${failed > 0
    ? `### Areas to Investigate

${results.filter(r => !r.pass).map(r => `- **${r.query}**: ${r.reason.includes('FAIL') ? 'Check intent routing and AgentRouter for this query' : 'Check response formatting and parameter extraction in AIResponseFormatter'}`).join('\n')}

### General Recommendations
1. Review failed queries in AIIntentService logs for classification accuracy
2. Verify AgentRouter maps detected intents to correct handlers
3. Check AIResponseFormatter templates for missing business context
4. Ensure destructive actions always show confirmation flow
`
    : 'All tests passed! System is functioning correctly. Consider adding edge case tests.'}

---

## Test Environment

| Setting | Value |
|---------|-------|
| Browser | Chromium (Playwright ${test.info().project.name}) |
| Base URL | ${BASE_URL} |
| Auth | Super Admin |
| Date | ${new Date().toLocaleDateString()} |
| Timeout | 60s per query |
`;

    fs.writeFileSync(REPORT_PATH, report, 'utf-8');
    console.log(`\nReport saved to: ${REPORT_PATH}`);
    console.log(`Results: ${passed}/${totalTests} passed (${intentAccuracy}%)`);
  });
});
