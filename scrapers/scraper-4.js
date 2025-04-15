import puppeteer from "puppeteer";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const filename = fileURLToPath(import.meta.url);
const directory = path.dirname(filename);

const country_name = process.argv[3];
const city_name = process.argv[2];

const cache_name = `${city_name.toLowerCase()}-${country_name.toLowerCase()}-yelp.json`;
const cache_path = path.join(directory, "cache", cache_name);

function startPerformance() {
    return { startTime: performance.now(), startCPU: process.cpuUsage() };
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
    return { browser, page };
}

async function navigateAndScrape(page, city_name, country_name) {
    let startIndex = 0;
    let total_restaurants = [];
    let maxPages = 5;

    for (let i = 0; i < maxPages; i++) {
        const url = `https://www.yelp.co.uk/search?find_desc=Restaurants&find_loc=${encodeURIComponent(city_name)},+${encodeURIComponent(country_name)}&start=${startIndex}`;
        await page.goto(url, { waitUntil: "networkidle2" });

        try {
            await page.waitForSelector("#onetrust-accept-btn-handler");
            await page.click("#onetrust-accept-btn-handler");
            await page.waitForSelector("ul li");
        } catch (error) {
            console.log("Button not found or already accepted.");
        }

        const restaurants = await page.evaluate(() => {
            return Array.from(document.querySelectorAll("ul li")).map(restaurant => {
                const nameElement = restaurant.querySelector('a[class*="y-css-1x1e1r2"]');
                const ratingElement = restaurant.querySelector('span[class*="y-css-f73en8"]');
                const locationElement = restaurant.querySelector('span[class*="y-css-yvhxeq"]');

                const name = nameElement?.innerText.trim();
                const rating = ratingElement?.innerText.trim();
                const location = locationElement?.innerText.trim();

                if (name && rating && location) {
                    return { name, rating, location };
                } else {
                    return null;
                }
            }).filter(Boolean);
        });

        total_restaurants = total_restaurants.concat(restaurants);
        startIndex += 10;
    }

    return total_restaurants;
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
        console.log(cache);
        return;
    }

    try {
        const { browser, page } = await setBrowser();

        const restaurants = await navigateAndScrape(page, city_name, country_name);
        const cacheData = {
            restaurants,
        };

        await saveCache(cacheData);
        await browser.close();
        printPerformance(track);
    } catch (error) {
        console.error("Scraping failed:", error);
    }
}

(async () => {
    await main(city_name, country_name);
})();
