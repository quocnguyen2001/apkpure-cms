const express = require('express');
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');

chromium.use(StealthPlugin());

const app = express();

const STORAGE_STATE_PATH = path.resolve(__dirname, 'storage-state.json');
let cachedStorageState = null;

if (fs.existsSync(STORAGE_STATE_PATH)) {
  try {
    cachedStorageState = JSON.parse(fs.readFileSync(STORAGE_STATE_PATH, 'utf8'));
    console.log('[storage] Loaded cached storage state');
  } catch (error) {
    console.log('[storage] Failed to load cached storage state:', error.message);
  }
}

const CLOUDFLARE_CHALLENGE_SELECTORS = [
  '#cf-challenge-running',
  '#challenge-running',
  '#cf-spinner-please-wait',
  '#cf-wrapper',
  'iframe[src*="challenges.cloudflare.com"]',
  'form#challenge-form',
  'input[name="cf_captcha_kind"]'
];

const CLOUDFLARE_CHALLENGE_TEXT = [
  /just a moment/i,
  /checking your browser/i,
  /verify you are human/i,
  /attention required/i,
  /cloudflare/i
];

const persistStorageState = async context => {
  try {
    const state = await context.storageState();
    cachedStorageState = state;
    fs.writeFileSync(STORAGE_STATE_PATH, JSON.stringify(state, null, 2));
    console.log('[storage] Storage state saved');
  } catch (error) {
    console.log('[storage] Failed to persist storage state:', error.message);
  }
};

const humanizePage = async page => {
  try {
    const viewport = page.viewportSize();
    if (!viewport) {
      return;
    }

    await page.mouse.move(viewport.width * 0.2, viewport.height * 0.2);
    await page.waitForTimeout(300);
    await page.mouse.move(viewport.width * 0.7, viewport.height * 0.3);
    await page.waitForTimeout(300);
    await page.mouse.move(viewport.width * 0.5, viewport.height * 0.7);
    await page.waitForTimeout(300);
    await page.mouse.wheel(0, 600);
    await page.waitForTimeout(400);
    await page.mouse.wheel(0, -400);
  } catch (error) {
    console.log('[humanize] Failed to simulate user actions:', error.message);
  }
};

const detectCloudflareChallenge = async page => {
  try {
    const { title, bodyText, hasChallengeElement } = await page.evaluate(selectors => {
      const pageTitle = document.title || '';
      const pageText = document.body ? document.body.innerText.slice(0, 2000) : '';
      const hasElement = selectors.some(selector => document.querySelector(selector));

      return {
        title: pageTitle,
        bodyText: pageText,
        hasChallengeElement: hasElement
      };
    }, CLOUDFLARE_CHALLENGE_SELECTORS);

    if (hasChallengeElement) {
      return true;
    }

    const combinedText = `${title}\n${bodyText}`;
    return CLOUDFLARE_CHALLENGE_TEXT.some(pattern => pattern.test(combinedText));
  } catch (error) {
    console.log('[cloudflare] Challenge detection failed:', error.message);
    return false;
  }
};

const waitForCloudflareBypass = async (page, context, options = {}) => {
  const {
    timeout = 60000,
    pollInterval = 1000,
    reloadLimit = 1
  } = options;

  const startTime = Date.now();
  let reloads = 0;

  while (Date.now() - startTime < timeout) {
    const isChallenge = await detectCloudflareChallenge(page);

    if (!isChallenge) {
      return { bypassed: true, reason: 'no-challenge' };
    }

    const cookies = await context.cookies();
    if (cookies.some(cookie => cookie.name === 'cf_clearance')) {
      return { bypassed: true, reason: 'clearance-cookie' };
    }

    if (reloads < reloadLimit && Date.now() - startTime > 8000) {
      reloads += 1;
      console.log('[cloudflare] Challenge detected, reloading page...');
      await page.reload({ waitUntil: 'domcontentloaded', timeout: 60000 }).catch(() => {
        console.log('[cloudflare] Reload timed out, continuing');
      });

      await page.waitForLoadState('networkidle', { timeout: 10000 }).catch(() => {
        console.log('[cloudflare] Network idle not reached after reload');
      });
    }

    await page.waitForTimeout(pollInterval);
  }

  return { bypassed: false, reason: 'timeout' };
};

app.use(express.json({ limit: '100mb' }));

app.get('/health', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

app.post('/scrape', async (req, res) => {
  const { url, options = {} } = req.body;

  console.log('========================================');
  console.log('[1] Received scrape request');
  console.log('URL:', url);
  console.log('Timestamp:', new Date().toISOString());

  if (!url) {
    return res.status(400).json({ error: 'URL is required' });
  }

  let browser;
  try {
    const {
      timeout = 30000,
      waitForSelector = null,
      executeScript = null,
      screenshot = false,
      cookies = [],
      userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
      clickAllVersionsButton = false,
      cloudflareTimeout = 60000,
      cloudflareReloads = 1
    } = options;

    console.log('Options:', options);

    console.log('[2] Options parsed:', {
      timeout,
      waitForSelector,
      executeScript,
      screenshot,
      cookiesCount: cookies.length,
      clickAllVersionsButton,
      cloudflareTimeout,
      cloudflareReloads
    });

    // Launch browser with stealth
    console.log('[3] Launching browser...');
    browser = await chromium.launch({
      headless: true,
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-blink-features=AutomationControlled',
        '--disable-web-security'
      ]
    });
    console.log('[4] Browser launched successfully');

    console.log('[5] Creating browser context...');
    const context = await browser.newContext({
      userAgent,
      viewport: { width: 1920, height: 1080 },
      locale: 'en-US',
      timezoneId: 'America/New_York',
      bypassCSP: true,
      ignoreHTTPSErrors: true,
      storageState: cachedStorageState || undefined,
      extraHTTPHeaders: {
        'Accept-Language': 'en-US,en;q=0.9',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Sec-Fetch-Dest': 'document',
        'Sec-Fetch-Mode': 'navigate',
        'Sec-Fetch-Site': 'none',
        'Sec-Fetch-User': '?1',
        'Upgrade-Insecure-Requests': '1'
      }
    });
    console.log('[6] Browser context created');

    if (cookies.length > 0) {
      console.log('[7] Adding cookies to context:', cookies);
      await context.addCookies(cookies);
    }

    // Stealth scripts
    console.log('[8] Adding stealth scripts...');
    await context.addInitScript(() => {
      Object.defineProperty(navigator, 'webdriver', { get: () => false });
      Object.defineProperty(navigator, 'plugins', { get: () => [1, 2, 3, 4, 5] });
      Object.defineProperty(navigator, 'languages', { get: () => ['en-US', 'en'] });
      Object.defineProperty(navigator, 'platform', { get: () => 'Win32' });
      Object.defineProperty(navigator, 'hardwareConcurrency', { get: () => 8 });
      Object.defineProperty(navigator, 'deviceMemory', { get: () => 8 });
      const originalQuery = window.navigator.permissions.query;
      window.navigator.permissions.query = parameters => (
        parameters && parameters.name === 'notifications'
          ? Promise.resolve({ state: Notification.permission })
          : originalQuery(parameters)
      );
      window.chrome = { runtime: {} };
    });

    console.log('[9] Creating new page...');
    let page = await context.newPage();

    // Use 'domcontentloaded' instead of 'networkidle' for better compatibility
    // networkidle can timeout on sites with continuous background requests
    console.log('[10] Navigating to URL:', url);
    const response = await page.goto(url, {
      waitUntil: 'domcontentloaded',
      timeout: timeout * 2
    });
    console.log('[11] Page navigation completed (domcontentloaded)');
    console.log('Response status:', response.status());

    // Wait for page to be interactive and CF challenge to complete
    console.log('[12] Waiting for network idle...');
    await page.waitForLoadState('networkidle', { timeout: 10000 }).catch(() => {
      // Ignore if networkidle is never reached
      console.log('[12a] Network idle not reached, continuing anyway');
    });

    await humanizePage(page);

    console.log('[13] Waiting additional 5 seconds for CF challenge...');
    await page.waitForTimeout(5000);

    console.log('[13b] Checking for Cloudflare challenge...');
    const cloudflareResult = await waitForCloudflareBypass(page, context, {
      timeout: cloudflareTimeout,
      reloadLimit: cloudflareReloads
    });
    console.log('[13c] Cloudflare result:', cloudflareResult);

    if (cloudflareResult.bypassed) {
      const cfCookies = await context.cookies();
      if (cfCookies.some(cookie => cookie.name === 'cf_clearance')) {
        await persistStorageState(context);
      }
    } else {
      console.log('[13d] Cloudflare challenge persists, retrying navigation...');
      await page.close();
      page = await context.newPage();
      await page.goto(url, { waitUntil: 'domcontentloaded', timeout: timeout * 2 });
      await page.waitForLoadState('networkidle', { timeout: 10000 }).catch(() => {
        console.log('[13e] Network idle not reached after retry');
      });
      await page.waitForTimeout(4000);
      await humanizePage(page);

      const retryResult = await waitForCloudflareBypass(page, context, {
        timeout: cloudflareTimeout,
        reloadLimit: cloudflareReloads + 1
      });
      console.log('[13f] Cloudflare retry result:', retryResult);

      if (retryResult.bypassed) {
        const cfCookies = await context.cookies();
        if (cfCookies.some(cookie => cookie.name === 'cf_clearance')) {
          await persistStorageState(context);
        }
      }
    }

    // Check for "All Versions" button and click if requested
    if (clickAllVersionsButton) {
      console.log('[13a] Checking for "All Versions" button...');
      const allVersionsSelector = 'a.go-all-versions-btn.more-version[dt-eid="all_versions_button"]';

      try {
        const allVersionsButton = await page.$(allVersionsSelector);

        if (allVersionsButton) {
          console.log('[13b] "All Versions" button found, clicking...');
          await allVersionsButton.click();
          console.log('[13c] Button clicked, waiting for content to load...');

          // Wait for the page to update after clicking
          await page.waitForTimeout(2000);
          console.log('[13d] "All Versions" content should now be visible');
        } else {
          console.log('[13b] "All Versions" button not found on page');
        }
      } catch (error) {
        console.log('[13e] Error while trying to click "All Versions" button:', error.message);
      }
    }

    if (waitForSelector) {
      console.log('[14] Waiting for selector:', waitForSelector);
      await page.waitForSelector(waitForSelector, { timeout: 10000 });
    }

    let scriptResult = null;
    if (executeScript) {
      console.log('[15] Executing custom script...');
      scriptResult = await page.evaluate(executeScript);
      console.log('[15a] Script result:', scriptResult);
    }

    console.log('[16] Getting page content...');
    let html = await page.content();
    const finalUrl = page.url();
    const title = await page.title();
    console.log('[17] Page content retrieved');
    console.log('Final URL:', finalUrl);
    console.log('Page Title:', title);
    console.log('HTML length:', html.length);

    console.log('[18] Getting cookies...');
    const responseCookies = await context.cookies();
    console.log('[19] Cookies retrieved, count:', responseCookies.length);
    console.log('All cookies:', responseCookies.map(c => ({ name: c.name, value: c.value })));

    // Handle /download URLs - extract download_id cookie and inject into HTML
    console.log('[20] Checking if URL contains /download...');
    console.log('Original URL contains /download:', url.includes('/download'));
    console.log('Final URL contains /download:', finalUrl.includes('/download'));

    if (url.includes('/download') || finalUrl.includes('/download')) {
      console.log('[21] This is a /download URL, searching for download_id cookie...');
      const downloadIdCookie = responseCookies.find(cookie => cookie.name === 'download_id');

      console.log('[22] download_id cookie:', downloadIdCookie);

      if (downloadIdCookie) {
        const downloadId = downloadIdCookie.value;
        console.log('[23] Found download_id:', downloadId);

        // Inject download_id into HTML content
        const downloadIdDiv = `<div id="download_id">${downloadId}</div>`;
        console.log('[24] Injecting download_id div into HTML...');

        // Insert before closing body tag if it exists, otherwise append to end
        if (html.includes('</body>')) {
          html = html.replace('</body>', `${downloadIdDiv}\n</body>`);
          console.log('[25] Injected before </body> tag');
        } else {
          html += downloadIdDiv;
          console.log('[25] Appended to end of HTML (no </body> tag found)');
        }
        console.log('[26] Download ID successfully injected into HTML');
      } else {
        console.log('[23] No download_id cookie found!');
      }
    } else {
      console.log('[21] Not a /download URL, skipping download_id injection');
    }

    let screenshotData = null;
    if (screenshot) {
      console.log('[27] Taking screenshot...');
      screenshotData = await page.screenshot({
        type: 'png',
        fullPage: true,
        encoding: 'base64'
      });
      console.log('[28] Screenshot captured');
    }

    console.log('[29] Closing browser...');
    await browser.close();
    console.log('[30] Browser closed');

    console.log('[31] Sending response...');
    res.json({
      success: true,
      data: {
        url: finalUrl,
        originalUrl: url,
        title,
        html,
        cookies: responseCookies,
        screenshot: screenshotData,
        scriptResult,
        timestamp: new Date().toISOString()
      }
    });
    console.log('[32] Response sent successfully');
    console.log('========================================\n');

  } catch (error) {
    console.error('[ERROR] Exception caught:', error.message);
    console.error('[ERROR] Stack trace:', error.stack);
    if (browser) {
      console.log('[ERROR] Closing browser after error...');
      await browser.close();
    }
    res.status(500).json({
      success: false,
      error: { message: error.message }
    });
    console.log('========================================\n');
  }
});

app.post('/extract/download-url', async (req, res) => {
  const { url } = req.body;

  console.log('========================================');
  console.log('[download-url] Received request');
  console.log('URL:', url);
  console.log('Timestamp:', new Date().toISOString());

  if (!url) {
    return res.status(400).json({ error: 'URL is required' });
  }

  let browser;
  try {
    console.log('[download-url] Launching browser...');
    browser = await chromium.launch({
      headless: true,
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-blink-features=AutomationControlled',
        '--disable-web-security'
      ]
    });

    const context = await browser.newContext({
      acceptDownloads: true,
      userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
      viewport: { width: 1920, height: 1080 },
      locale: 'en-US',
      timezoneId: 'America/New_York',
      bypassCSP: true,
      ignoreHTTPSErrors: true,
      storageState: cachedStorageState || undefined,
      extraHTTPHeaders: {
        'Accept-Language': 'en-US,en;q=0.9',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Sec-Fetch-Dest': 'document',
        'Sec-Fetch-Mode': 'navigate',
        'Sec-Fetch-Site': 'none',
        'Sec-Fetch-User': '?1',
        'Upgrade-Insecure-Requests': '1'
      }
    });

    await context.addInitScript(() => {
      Object.defineProperty(navigator, 'webdriver', { get: () => false });
      Object.defineProperty(navigator, 'plugins', { get: () => [1, 2, 3, 4, 5] });
      Object.defineProperty(navigator, 'languages', { get: () => ['en-US', 'en'] });
      Object.defineProperty(navigator, 'platform', { get: () => 'Win32' });
      Object.defineProperty(navigator, 'hardwareConcurrency', { get: () => 8 });
      Object.defineProperty(navigator, 'deviceMemory', { get: () => 8 });
      const originalQuery = window.navigator.permissions.query;
      window.navigator.permissions.query = parameters => (
        parameters && parameters.name === 'notifications'
          ? Promise.resolve({ state: Notification.permission })
          : originalQuery(parameters)
      );
      window.chrome = { runtime: {} };
    });

    const page = await context.newPage();

    let downloadUrl = null;
    const downloadUrlPattern = /https:\/\/[^\s"']+winudf\.com\/b\/(XAPK|APK)\//i;

    page.on('request', request => {
      if (downloadUrl) {
        return;
      }

      const requestUrl = request.url();
      if (downloadUrlPattern.test(requestUrl)) {
        downloadUrl = requestUrl;
        console.log('[download-url] Matched download request:', downloadUrl);
      }
    });

    console.log('[download-url] Navigating to URL:', url);
    const downloadPromise = page.waitForEvent('download', { timeout: 15000 }).catch(() => null);
    const navigationPromise = page.goto(url, {
      waitUntil: 'domcontentloaded',
      timeout: 60000
    });

    const [navigationResult] = await Promise.allSettled([navigationPromise]);
    let response = null;

    if (navigationResult.status === 'fulfilled') {
      response = navigationResult.value;
    } else {
      const message = String(navigationResult.reason?.message || navigationResult.reason);
      if (!message.includes('Download is starting')) {
        throw navigationResult.reason;
      }
      console.log('[download-url] Download started during navigation');
    }

    if (response) {
      await page.waitForLoadState('networkidle', { timeout: 10000 }).catch(() => {
        console.log('[download-url] Network idle not reached, continuing');
      });
    }

    console.log('[download-url] Checking for Cloudflare challenge...');
    const cloudflareResult = await waitForCloudflareBypass(page, context, {
      timeout: 60000,
      reloadLimit: 1
    });
    console.log('[download-url] Cloudflare result:', cloudflareResult);

    if (cloudflareResult.bypassed) {
      const cfCookies = await context.cookies();
      if (cfCookies.some(cookie => cookie.name === 'cf_clearance')) {
        await persistStorageState(context);
      }
    }

    await page.waitForTimeout(4000);

    const downloadEvent = await downloadPromise;
    if (downloadEvent && !downloadUrl) {
      downloadUrl = downloadEvent.url();
      console.log('[download-url] Download event URL:', downloadUrl);
    }

    const finalUrl = response ? response.url() : page.url();
    const resolvedUrl = downloadUrl || finalUrl;

    console.log('[download-url] Resolved URL:', resolvedUrl);

    await browser.close();

    if (!resolvedUrl) {
      return res.status(404).json({
        success: false,
        error: { message: 'Download URL not found' }
      });
    }

    res.json({
      success: true,
      data: {
        url: resolvedUrl,
        originalUrl: url,
        finalUrl,
        timestamp: new Date().toISOString()
      }
    });
    console.log('[download-url] Response sent successfully');
    console.log('========================================\n');
  } catch (error) {
    console.error('[download-url] Error:', error.message);
    if (browser) {
      console.log('[download-url] Closing browser after error...');
      await browser.close();
    }
    res.status(500).json({
      success: false,
      error: { message: error.message }
    });
    console.log('========================================\n');
  }
});

app.listen(3000, () => console.log('Playwright service on port 3000'));
