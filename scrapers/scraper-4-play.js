import { chromium } from "playwright";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const filename = fileURLToPath(import.meta.url);
const directory = path.dirname(filename);

const city = process.argv[2];
const country = process.argv[3];

const city_formatted = city.toLowerCase().replace(/\s+/g, "-");
const country_formatted = country.toLowerCase().replace(/\s+/g, "-");
const file = `${city_formatted}-${country_formatted}-yelp.json`;
const cache_directory = path.join(directory, "cache-play");
const cache_path = path.join(cache_directory, file);

function startPerformance() {
    return { start: performance.now(), cpu: process.cpuUsage() };
}

function checkCache() {
    if (fs.existsSync(cache_path)) {
        return JSON.parse(fs.readFileSync(cache_path, "utf-8"));
    }

    if (!fs.existsSync(cache_directory)) {
        fs.mkdirSync(cache_directory, { recursive: true });
    }

    return null;
}

async function setBrowser() {
    const browser = await chromium.launch({ headless: false });
    const context = await browser.newContext();
    const page = await context.newPage();
    return { browser, page };
}

async function cookie(page) {
    const button = await page.waitForSelector("#onetrust-accept-btn-handler", { timeout: 5000 });
    if (button) await button.click();
}

async function scraper(page, city, country) {
    const total_restaurants = [];
    let pages = 5;

    for (let i = 0; i < pages; i++) {
        const start = i * 10;
        const url = `https://www.yelp.co.uk/search?find_desc=Restaurants&find_loc=${encodeURIComponent(city)}%2C+${encodeURIComponent(country)}&start=${start}`;
        await page.goto(url, { waitUntil: "domcontentloaded" });

        if (i === 0) await cookie(page);

        await page.waitForSelector("[data-testid='serp-ia-card']", { timeout: 10000 });

        const restaurants = await page.evaluate(() => {
            return Array.from(document.querySelectorAll("[data-testid='serp-ia-card']")).map(card => {
                const name = card.querySelector("a[name]")?.textContent?.trim();
                if (!name) return null;

                return {
                    name,
                    rating: card.querySelector("[aria-label*='star rating']")?.getAttribute("aria-label"),
                    reviews: card.querySelector("span.y-css-1vi7y4e")?.textContent?.trim(),
                    location: card.querySelectorAll("span.y-css-yvhxeq")?.[0]?.textContent?.trim(),
                    price: card.querySelectorAll("span.y-css-yvhxeq")?.[1]?.textContent?.trim()
                };
            }).filter(Boolean);
        });

        total_restaurants.push(...restaurants);
        await page.waitForTimeout(1000);
    }

    return total_restaurants;
}

function printPerformance(track) {
    const totalTime = ((performance.now() - track.start) / 1000).toFixed(2);
    const totalCPU = ((process.cpuUsage(track.cpu).user + process.cpuUsage(track.cpu).system) / 1000000).toFixed(2);
    console.log("Total time: ", totalTime);
    console.log("CPU usage: ", totalCPU);
    return { duration: totalTime, cpuTime: totalCPU };
}

async function saveCache(result) {
    fs.writeFileSync(cache_path, JSON.stringify(result, null, 2));
}

async function main(city, country) {
    const track = startPerformance();
    const cache = checkCache();

    if (cache) {
        return;
    }

    const { browser, page } = await setBrowser();

    try {
        const restaurants = await scraper(page, city, country, 5);
        const performance = printPerformance(track);

        const result = {
            restaurants,
            elapsedTime: performance.duration,
            cpuUsage: performance.cpuTime
        };

        await saveCache(result);
    } finally {
        await browser.close();
    }
}

main(city, country);
