// Navega la home de Sofascore (rápido, no dispara challenge) y luego pide la
// URL de la API con fetch() *dentro* del contexto de esa página, vía
// page.evaluate. Es clave hacerlo así y no con page.goto(apiUrl) directo:
// una navegación de nivel superior a un endpoint JSON no la hace nunca un
// usuario real (el sitio real trae esos datos con fetch() desde la SPA ya
// cargada) y es una señal fuerte de bot para el Bot Manager de Fastly —
// justo lo que nos daba el 403 "challenge" en Render aun con Chromium real.
// Haciéndolo vía fetch() en la página, Origin/Referer/sec-fetch-* salen
// igual que en un browser normal navegando el sitio.
async function fetchJsonViaPage(page, apiUrl, { timeout = 30000 } = {}) {
    if (page.url() === 'about:blank') {
        await page.goto('https://www.sofascore.com/', { waitUntil: 'domcontentloaded', timeout });
    }

    const doFetch = () => page.evaluate(async (url) => {
        const res = await fetch(url, { headers: { Accept: 'application/json' } });
        return { status: res.status, body: await res.text() };
    }, apiUrl);

    let { status, body } = await doFetch();

    if (status !== 200) {
        let isChallenge = false;
        try {
            isChallenge = JSON.parse(body)?.error?.reason === 'challenge';
        } catch { /* no era JSON de challenge */ }

        if (isChallenge) {
            await page.waitForTimeout(4000);
            ({ status, body } = await doFetch());
        }
    }

    return { status, body, finalUrl: page.url() };
}

module.exports = { fetchJsonViaPage };
