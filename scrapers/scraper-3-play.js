import { chromium } from "playwright";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const filename = fileURLToPath(import.meta.url);
const directory = path.dirname(filename);

const city_name = process.argv[2];
const country_name = process.argv[3];

const city_formatted = city_name.toLowerCase().replace(/\s+/g, "-");
const country_formatted = country_name.toLowerCase().replace(/\s+/g, "-");
const cache_name = `${city_formatted}-${country_formatted}-lonelyplanet.json`;
const cache_path = path.join(directory, "cache-play", cache_name);

function startPerformance() {
    return { startTime: performance.now(), startCPU: process.cpuUsage() };
}

function checkCache() {
    if (fs.existsSync(cache_path)) {
        const cache = fs.readFileSync(cache_path, "utf-8");
        return JSON.parse(cache);
    }

    const directory = path.dirname(cache_path);
    if (!fs.existsSync(directory)) {
        fs.mkdirSync(directory, { recursive: true });
    }

    return null;
}

async function setBrowser() {
    const browser = await chromium.launch({ headless: false });
    const page = await browser.newPage();
    return { browser, page };
}

async function cookie(page) {
    const cookie = page.locator("#onetrust-accept-btn-handler");
    if (await cookie.isVisible()) await cookie.click();
}

async function clickPage(page, city_name) {
    const searchUrl = `https://www.lonelyplanet.com/search?q=${encodeURIComponent(city_name)}`;
    await page.goto(searchUrl, { waitUntil: "networkidle" });

    await cookie(page);

    const selector = 'a.lg\\:text-lg.text-black.hover\\:text-black.font-semibold.card-link.mb-1.line-clamp-4';
    await page.waitForSelector(selector);
    const first_button = await page.locator(selector).first();
    await first_button.click();
}

async function retrieveAttraction(page, url) {
    await page.goto(url, { waitUntil: "networkidle" });

    const read_more = await page.$("button.text-blue.font-semibold.mt-2.block.mx-auto");
    if (read_more) await read_more.click().catch(() => {});
    await page.waitForSelector(".readMore_content__bv7mp");
    const paragraphs = await page.$$eval(".readMore_content__bv7mp p", els => els.map(p => p.innerText));

    const addres_element = await page.$("a[href^='https://www.google.com/maps?q=']");
    const address = addres_element ? await page.evaluate(el => el.href, addres_element) : null;

    const phone_element = await page.$("a[href^='tel:']");
    const phone = phone_element ? await page.evaluate(el => el.innerText, phone_element) : null;
    return { link: url, paragraphs, address, phone };
}

async function scraper(city_name) {
    const { browser, page } = await setBrowser();
    const total_locations = 20;
    const results = [];

    try {
        await clickPage(page, city_name);
        await page.waitForSelector("a.card-link.line-clamp-2");
        const links = await page.$$eval("a.card-link.line-clamp-2", els => els.map(el => el.href));

        for (let i = 0; i < links.length && results.length < total_locations; i++) {
            const data = await retrieveAttraction(page, links[i]);
            if (data) results.push(data);
        }
    } finally {
        await browser.close();
    }
    return results;
}

async function saveCache(data) {
    fs.writeFileSync(cache_path, JSON.stringify(data, null, 2));
}

function printPerformance(track) {
    const endTime = performance.now();
    const endCPU = process.cpuUsage(track.startCPU);
    const totalTime = (endTime - track.startTime) / 1000;
    const totalCPU = (endCPU.user + endCPU.system) / 1000000;

    console.log("Total Time Taken (s): ", totalTime);
    console.log("Total CPU Usage (%): ", totalCPU);
}

async function main(city_name, country_name) {
    const cache = checkCache();
    const track = startPerformance();

    if (cache) {
        return;
    }

    const data = await scraper(city_name);
    await saveCache(data);
    printPerformance(track);
}

(async () => {
    await main(city_name, country_name);
})();
