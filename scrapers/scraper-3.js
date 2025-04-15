import puppeteer from "puppeteer";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const filename = fileURLToPath(import.meta.url);
const directory = path.dirname(filename);

const country_name = process.argv[3];
const city_name = process.argv[2];

const cache_name = `${city_name.toLowerCase()}-${country_name.toLowerCase()}-lonelyplanet.json`;
const cache_path = path.join(directory, "cache", cache_name);

function startPerformance() {
    return { startTime: performance.now(), startCPU: process.cpuUsage() };
}

function printPerformance(track) {
    const endTime = performance.now();
    const endCPU = process.cpuUsage(track.startCPU);
    const totalTime = (endTime - track.startTime) / 1000;
    const totalCPU = (endCPU.user + endCPU.system) / 1000000;

    console.log(`Total Time Taken (s): ${totalTime.toFixed(2)}`);
    console.log(`Total CPU Usage (%): ${totalCPU.toFixed(2)}`);
}

function checkCache() {
    if (fs.existsSync(cache_path)) {
        const cache = fs.readFileSync(cache_path, "utf-8");
        return JSON.parse(cache);
    }

    const cacheDir = path.join(directory, "cache");
    if (!fs.existsSync(cacheDir)) {
        fs.mkdirSync(cacheDir, { recursive: true });
    }

    return null;
}

async function setBrowser() {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 800 });
    await page.setRequestInterception(true);

    page.on("request", (request) => {
        const blocked = ["image", "stylesheet", "font", "media", "other"];
        if (blocked.includes(request.resourceType())) {
            request.abort();
        } else {
            request.continue();
        }
    });

    return { browser, page };
}

async function navigateToSearch(page, city_name) {
    const searchUrl = "https://www.lonelyplanet.com/search";
    await page.goto(searchUrl, { waitUntil: "networkidle2" });

    await page.waitForSelector("#onetrust-accept-btn-handler", { timeout: 5000 });
    await page.click("#onetrust-accept-btn-handler");

    await page.waitForSelector("button[popovertarget=\"search-menu-popover\"]", { visible: true, timeout: 10000 });
    await page.evaluate((selector) => {
        const btn = document.querySelector(selector);
        btn.scrollIntoView({behavior: "instant", block: "center"});
        btn.click();
    }, "button[popovertarget=\"search-menu-popover\"]");

    const searchSelector = "input#searchInputComponent";
    await page.waitForSelector(searchSelector, { visible: true });
    await page.type(searchSelector, city_name);
    await page.keyboard.press("Enter");

    await page.waitForNavigation({ waitUntil: "networkidle2" });

    const resultSelector = 'a.lg\\:text-lg.text-black.hover\\:text-black.font-semibold.card-link.mb-1.line-clamp-4';
    await page.waitForSelector(resultSelector);
    await page.click(resultSelector);

    await page.waitForNavigation({ waitUntil: "networkidle2" });
}

async function scrapeAttractions(page, max = 20) {
    try {
        await page.waitForSelector("a.card-link.line-clamp-2", { timeout: 60000 });
    } catch (error) {
        throw new Error("No attractions found.");
    }

    const links = await page.$$eval("a.card-link.line-clamp-2", (elements) =>
        elements.map((el) => el.href)
    );

    const results = [];

    for (let i = 0; i < links.length && i < max; i++) {
        const link = links[i];
        await page.goto(link, { waitUntil: "networkidle2" });

        const readMoreButton = await page.$("button.text-blue.font-semibold.mt-2.block.mx-auto");
        if (readMoreButton) {
            await readMoreButton.click();
        }

        try {
            await page.waitForSelector(".readMore_content__bv7mp", { timeout: 5000 });
        } catch {}

        const paragraphs = await page.$$eval(".readMore_content__bv7mp p", (els) =>
            els.map((el) => el.innerText.trim())
        );

        const address = await page.$eval(
            'a[href^="https://www.google.com/maps?q="]',
            (el) => el.href,
        ).catch(() => null);

        const phone = await page.$eval('a[href^="tel:"]', (el) => el.innerText).catch(() => null);

        results.push({ link, paragraphs, address, phone });
    }

    return results;
}

async function saveCache(data) {
    fs.writeFileSync(cache_path, JSON.stringify(data, null, 2));
}

async function main(city_name) {
    const cache = checkCache();
    const track = startPerformance();

    if (cache) {
        console.log("Using cached data.");
        console.log(cache);
        return;
    }

    try {
        const { browser, page } = await setBrowser();

        await navigateToSearch(page, city_name);
        const data = await scrapeAttractions(page);
        await saveCache(data);
        console.log(data);

        await browser.close();
        printPerformance(track);
    } catch (error) {
        console.error("Scraping failed:", error);
    }
}

(async () => {
    await main(city_name, country_name, cache_path);
})();
