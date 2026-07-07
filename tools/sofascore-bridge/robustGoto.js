// Fastly Bot Manager a veces no da un 403 llano sino una página de "challenge"
// (corre JS y redirige sola si lo resuelve) — pasa sobre todo desde IPs de
// datacenter/nube (ej. Render), no tanto desde IPs residenciales.
//
// waitUntil:'networkidle' NO sirve acá: Sofascore es una SPA con polling en
// vivo (marcadores en tiempo real) que nunca deja de hacer requests, así que
// networkidle se cuelga hasta el timeout siempre, haya o no challenge. Por
// eso usamos domcontentloaded (rápido, es lo que ya funcionaba) y solo
// agregamos una espera + reload manual cuando el body es específicamente el
// JSON de challenge de Fastly.
async function robustGoto(page, url, { timeout = 30000 } = {}) {
    let response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout });
    let status = response.status();
    let body = await response.text();

    if (status !== 200) {
        let isChallenge = false;
        try {
            isChallenge = JSON.parse(body)?.error?.reason === 'challenge';
        } catch { /* no era JSON de challenge */ }

        if (isChallenge) {
            await page.waitForTimeout(5000);
            response = await page.reload({ waitUntil: 'domcontentloaded', timeout });
            status = response.status();
            body = await response.text();
        }
    }

    return { status, body, finalUrl: page.url() };
}

module.exports = { robustGoto };
