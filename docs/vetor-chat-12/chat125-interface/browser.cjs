const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require('/opt/infra-abrasil/backups/prospect11/browser/node_modules/playwright');

const base = 'http://127.0.0.1:8765';
const output = __dirname;
const testPassword = process.env.CHAT125_TEST_PASSWORD;
if (!testPassword) throw new Error('Set CHAT125_TEST_PASSWORD for the synthetic browser accounts.');
const pagesToCheck = [
  ['/dashboard', 'dashboard', 'owner'],
  ['/prospecting', 'prospecting', 'owner'],
  ['/prospecting/prospects', 'prospects', 'owner'],
  ['/leads', 'crm', 'owner'],
  ['/atendimento', 'support-inbox', 'owner'],
  ['/atendimento/contatos', 'received-contacts', 'owner'],
  ['/settings/support-automation', 'automation-rules-simulator', 'owner'],
  ['/settings/leads', 'lead-settings', 'root'],
  ['/settings/profile', 'profile-settings', 'owner'],
];

async function login(page, account) {
  await page.goto(`${base}/login`, { waitUntil: 'domcontentloaded' });
  await page.locator('input[name="email"]').fill(account === 'root' ? 'root-chat125@example.test' : 'owner-chat125@example.test');
  await page.locator('input[name="password"]').fill(testPassword);
  await page.locator('button[type="submit"]').click();
  await page.waitForURL(url => !url.pathname.endsWith('/login'), { timeout: 10000 });
}

(async () => {
  fs.mkdirSync(output, { recursive: true });
  const browser = await chromium.launch({ headless: true });
  const report = { browser: 'Chromium via Playwright', base, checks: [], errors: [] };
  for (const viewport of [{ name: '1440', width: 1440, height: 1000 }, { name: '390', width: 390, height: 844 }]) {
    for (const account of ['owner', 'root']) {
      const context = await browser.newContext({ viewport: { width: viewport.width, height: viewport.height }, deviceScaleFactor: 1, isMobile: viewport.width < 500, hasTouch: viewport.width < 500 });
      const page = await context.newPage();
      page.on('pageerror', error => report.errors.push({ viewport: viewport.name, account, type: 'pageerror', message: error.message }));
      page.on('console', message => { if (message.type() === 'error') report.errors.push({ viewport: viewport.name, account, type: 'console', message: message.text() }); });
      await login(page, account);
      for (const [route, name, requiredAccount] of pagesToCheck.filter(([, , role]) => role === account)) {
        const response = await page.goto(`${base}${route}`, { waitUntil: 'domcontentloaded', timeout: 20000 });
        await page.waitForTimeout(450);
        let simulatorResult = null;
        if (name === 'automation-rules-simulator') {
          await page.getByLabel('Regra para prévia').selectOption({ label: 'CHAT-12.5 Demo Draft · rascunho inativo' });
          await page.getByLabel('Mensagem simulada').fill('teste');
          await page.getByRole('button', { name: 'Simular', exact: true }).click();
          await page.getByRole('status').getByText(/Prévia/).waitFor({ timeout: 10000 });
          simulatorResult = await page.getByRole('status').innerText();
          if (!simulatorResult.includes('Resposta sintética de homologação.')) {
            report.errors.push({ viewport: viewport.name, account, route, type: 'simulator-result', message: simulatorResult });
          }
        }
        const state = await page.evaluate(() => ({
          title: document.title,
          heading: document.querySelector('h1')?.innerText?.trim() ?? '',
          body: document.body.innerText.slice(0, 650),
          width: document.documentElement.clientWidth,
          scrollWidth: document.documentElement.scrollWidth,
          horizontalOverflow: document.documentElement.scrollWidth > document.documentElement.clientWidth + 2,
        }));
        const status = response?.status() ?? 0;
        if (status >= 400) report.errors.push({ viewport: viewport.name, account, route, type: 'http', status });
        if (state.horizontalOverflow) report.errors.push({ viewport: viewport.name, account, route, type: 'horizontal-overflow', width: state.width, scrollWidth: state.scrollWidth });
        await page.screenshot({ path: path.join(output, `${viewport.name}-${name}.png`), fullPage: true });
        report.checks.push({ viewport: viewport.name, account, route, status, url: page.url(), simulatorResult, ...state });
      }
      await context.close();
    }
  }
  await browser.close();
  fs.writeFileSync(path.join(output, 'results.json'), JSON.stringify(report, null, 2));
  console.log(JSON.stringify({ checks: report.checks.length, errors: report.errors.length, results: report.checks.map(({ viewport, account, route, status, heading, horizontalOverflow }) => ({ viewport, account, route, status, heading, horizontalOverflow })), errors: report.errors }, null, 2));
  if (report.errors.length) process.exitCode = 1;
})().catch(error => { console.error(error); process.exit(1); });
