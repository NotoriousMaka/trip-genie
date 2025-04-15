import { chromium } from "playwright";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const filename = fileURLToPath(import.meta.url);
const directory = path.dirname(filename);

const city_name = process.argv[2];
const country_name = process.argv[3];

const formatCity = city_name.toLowerCase().replace(/\s+/g, "-");
const formatCountry = country_name.toLowerCase().replace(/\s+/g, "-");
const cache_name = `${formatCity}-${formatCountry}-atlas.json`;
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
    await page.setViewportSize({ width: 1280, height: 800 });
    return { browser, page };
}

async function navigateAndScrape(page, city_name, country_name, results = 50) {
    const main_url = `https://www.atlasobscura.com/things-to-do/${formatCity}-${formatCountry}/places`;
    let current_page = 1;
    let total_cards = [];

    while (total_cards.length < results) {
        const url = current_page === 1 ? main_url : `${main_url}?page=${current_page}`;
        await page.goto(url, { waitUntil: "domcontentloaded" });

        const first_cookie = page.locator("#onetrust-accept-btn-handler");
        if (await first_cookie.isVisible()) await first_cookie.click();

        const second_cookie = page.locator(".fc-button.fc-cta-consent.fc-primary-button");
        if (await second_cookie.isVisible()) await second_cookie.click();

        if (await page.locator("text=Something went wrong on our end.").isVisible()) {
            await page.close();
            break;
        }

        await page.waitForSelector(".geo-places .CardWrapper", { timeout: 10000 });

        const cards = await page.$$eval(".geo-places .CardWrapper", (card_elements) =>
            card_elements.map((card) => ({
                name: card.querySelector(".Card__heading span")?.innerText || null,
                description: card.querySelector(".Card__content")?.innerText || null,
            }))
        );

        total_cards = [...total_cards, ...cards];

        console.log(total_cards)

        current_page++;
    }

    return total_cards.slice(0, results);
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
        console.log("Using cached data.");
        console.log(cache);
        return;
    }

    try {
        const { browser, page } = await setBrowser();
        const data = await navigateAndScrape(page, city_name, country_name);
        await saveCache(data);
        await browser.close();
        printPerformance(track);
    } catch (error) {
        console.error("Scraping failed:", error);
    }
}

(async () => {
    await main(city_name, country_name);
})();
