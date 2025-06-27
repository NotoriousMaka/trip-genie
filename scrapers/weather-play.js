import {chromium} from "playwright";
import fs from "fs";
import path from "path";
import {fileURLToPath} from "url";

const filename = fileURLToPath(import.meta.url);
const directory = path.dirname(filename);

const country = (process.argv[2]).toLowerCase();
const city = (process.argv[3]).toLowerCase();

const cache_name = `${country}-${city}-weather.json`;
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

async function navigateAndScrape(page, country, city) {
    const url = `https://www.timeanddate.com/weather/${country}/${city}/ext`;
    await page.goto(url, { waitUntil: "domcontentloaded" });
    await page.waitForSelector("#wt-ext", { timeout: 20000 });

    return await page.evaluate(() => {
        const table = document.querySelector("#wt-ext");
        if (!table) return null;
        const rows = Array.from(table.querySelectorAll("tbody tr"));
        return rows.map(row => {
            const cells = row.querySelectorAll("th, td");
            return {
                day: cells[0]?.innerText.trim(),
                temperature: cells[2]?.innerText.trim(),
                weather: cells[3]?.innerText.trim(),
                feels_like: cells[4]?.innerText.trim(),
                wind: cells[5]?.innerText.trim(),
                humidity: cells[7]?.innerText.trim(),
                precipitation_chance: cells[8]?.innerText.trim(),
                precipitation_amount: cells[9]?.innerText.trim(),
                uv: cells[10]?.innerText.trim(),
                sunrise: cells[11]?.innerText.trim(),
                sunset: cells[12]?.innerText.trim()
            };
        });
    });
}

async function saveCache(data) {
    fs.writeFileSync(cache_path, JSON.stringify(data, null, 2));
}

async function main(country, city) {
    const cache = checkCache();
    if (cache) {
        console.log(cache);
        return;
    }
    try {
        const { browser, page } = await setBrowser();
        const data = await navigateAndScrape(page, country, city);
        await saveCache(data);
        await browser.close();
        console.log(data);
    } catch (error) {
        console.error("Scraping failed:", error);
    }
}

(async () => {
    await main(country, city);
})();
