const { chromium } = require('playwright');
(async () => {
  try {
    const browser = await chromium.launch();
    const page = await browser.newPage();
    const url = 'http://127.0.0.1:8000/login';
    console.log('navigating to', url);
    const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
    console.log('status', response && response.status());
    console.log('url', page.url());
    const html = await page.content();
    console.log(html.slice(0, 400));
    await browser.close();
    process.exit(0);
  } catch (error) {
    console.error('ERROR', error);
    process.exit(1);
  }
})();
