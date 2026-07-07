// Bridge HTTP para esquivar el bot-detection (Fastly/JA3) de Sofascore.
// Mantiene un Chromium real (Playwright) vivo y reenvía peticiones GET
// /fetch?url=<api-sofascore-url> con el body/status tal cual los devuelve
// Sofascore. Pensado para correr local (dev) o desplegado en Render.
//
// Auth: header "x-bridge-key" debe igualar BRIDGE_SECRET (si está seteado).
// Restringido a URLs bajo dominios *.sofascore.* para no ser un proxy abierto.
const express = require('express');
const { chromium } = require('playwright');

const PORT = process.env.PORT || 4321;
const BRIDGE_SECRET = process.env.BRIDGE_SECRET || '';
const ALLOWED_HOST_SUFFIX = '.sofascore.com';

let context;

async function initBrowser() {
    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
    });
    // Render free tier puede matar el proceso de Chromium por memoria; si el
    // browser se desconecta, la próxima request a /fetch vuelve a lanzarlo
    // (ver getContext) en vez de dejar el bridge muerto hasta el próximo deploy.
    browser.on('disconnected', () => { context = null; });

    context = await browser.newContext({
        userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    });

    // Visita al home una sola vez al arrancar: deja la sesión/cookies del
    // contexto "calentadas" como las de un usuario real. Como el contexto se
    // reutiliza entre requests, no hace falta repetirlo en cada /fetch.
    const warmupPage = await context.newPage();
    await warmupPage.goto('https://www.sofascore.com/', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await warmupPage.close();
}

async function getContext() {
    if (!context) await initBrowser();
    return context;
}

function isAllowedUrl(raw) {
    try {
        const u = new URL(raw);
        return u.protocol === 'https:' && u.hostname.endsWith(ALLOWED_HOST_SUFFIX);
    } catch {
        return false;
    }
}

const app = express();

app.get('/health', (req, res) => res.json({ ok: true }));

app.get('/fetch', async (req, res) => {
    if (BRIDGE_SECRET && req.get('x-bridge-key') !== BRIDGE_SECRET) {
        return res.status(401).json({ error: 'unauthorized' });
    }

    const target = req.query.url;
    if (typeof target !== 'string' || !isAllowedUrl(target)) {
        return res.status(400).json({ error: 'url inválida o no permitida' });
    }

    let page;
    try {
        const ctx = await getContext();
        page = await ctx.newPage();
        const response = await page.goto(target, { waitUntil: 'domcontentloaded', timeout: 30000 });
        const status = response.status();
        const body = await response.text();
        res.status(status).type('application/json').send(body);
    } catch (err) {
        res.status(502).json({ error: 'bridge error: ' + err.message });
    } finally {
        if (page) await page.close();
    }
});

initBrowser()
    .then(() => {
        app.listen(PORT, () => console.log(`Sofascore bridge escuchando en :${PORT}`));
    })
    .catch((err) => {
        console.error('No se pudo inicializar el navegador:', err);
        process.exit(1);
    });
