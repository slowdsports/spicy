// Bridge Playwright para esquivar el bot-detection (Fastly/JA3) de Sofascore.
// Uso: node fetch.js <url-de-la-api-sofascore>
// Imprime el JSON de la respuesta en stdout. Cualquier error va a stderr con
// exit code != 0.
const { chromium } = require('playwright');

async function main() {
    const targetUrl = process.argv[2];
    if (!targetUrl) {
        console.error('Uso: node fetch.js <url>');
        process.exit(1);
    }

    const browser = await chromium.launch({ headless: true });
    try {
        const context = await browser.newContext({
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        });
        const page = await context.newPage();

        // Sofascore le hace un chequeo de bot-detection a la sesión del
        // navegador antes de servir la API; visitar el home primero deja esa
        // sesión "calentada" tal como pasaría con un usuario real.
        await page.goto('https://www.sofascore.com/', { waitUntil: 'domcontentloaded', timeout: 30000 });

        const response = await page.goto(targetUrl, { waitUntil: 'domcontentloaded', timeout: 30000 });
        const status = response.status();
        const body = await response.text();

        if (status !== 200) {
            console.error(`HTTP ${status}: ${body.slice(0, 300)}`);
            process.exit(2);
        }

        process.stdout.write(body);
    } finally {
        await browser.close();
    }
}

main().catch((err) => {
    console.error('Error Playwright: ' + err.message);
    process.exit(1);
});
