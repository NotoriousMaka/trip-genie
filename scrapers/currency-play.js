import {chromium} from "playwright";
import fs from "fs";
import path from "path";
import {fileURLToPath} from "url";

const filename = fileURLToPath(import.meta.url);
const directory = path.dirname(filename);

const from = process.argv[2] || "GBP";
const to = process.argv[3] || "USD";
const amount = process.argv[4] || "1";

const cache_name = `${from}-${to}-${amount}-xrates.json`;
const cache_path = path.join(directory, "cache-play", cache_name);

function checkCache() {
    if (fs.existsSync(cache_path)) {
        const cache = fs.readFileSync(cache_path, "utf-8");
        return JSON.parse(cache);
    }
    const cacheDir = path.dirname(cache_path);
    if (!fs.existsSync(cacheDir)) {
        fs.mkdirSync(cacheDir, { recursive: true });
    }
    return null;
}

async function setBrowser() {
    const browser = await chromium.launch({ headless: false });
    const page = await browser.newPage();
    await page.setViewportSize({ width: 1280, height: 800 });
    return { browser, page };
}

async function navigateAndScrape(page, from, to, amount) {
    const url = `https://www.x-rates.com/calculator/?from=${from}&to=${to}&amount=${amount}`;
    await page.goto(url, { waitUntil: "domcontentloaded" });
    await page.waitForSelector(".ccOutputBx", { timeout: 10000 });

    return await page.evaluate(() => {
        const box = document.querySelector(".ccOutputBx");
        if (!box) return null;
        const txt = box.querySelector(".ccOutputTxt")?.innerText || "";
        const rslt = box.querySelector(".ccOutputRslt")?.innerText || "";
        return { text: txt, result: rslt };
    });
}

async function saveCache(data) {
    fs.writeFileSync(cache_path, JSON.stringify(data, null, 2));
}

async function main(from, to, amount) {
    const cache = checkCache();
    if (cache) {
        console.log(cache);
        return;
    }
    try {
        const { browser, page } = await setBrowser();
        const data = await navigateAndScrape(page, from, to, amount);
        await saveCache(data);
        await browser.close();
        console.log(data);
    } catch (error) {
        console.error("Scraping failed:", error);
    }
}

(async () => {
    await main(from, to, amount);
})();
