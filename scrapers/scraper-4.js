import puppeteer from "puppeteer";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const city = process.argv[2];
const country = process.argv[3];

const cacheFile = `${city.toLowerCase()}-${country.toLowerCase()}-yelp.json`;
const cachePath = path.join(__dirname, "cache", cacheFile);

(async () => {
    const startTime = performance.now();
    const startCpuUsage = process.cpuUsage();

    try {
        const browser = await puppeteer.launch({ headless: false, defaultViewport: null });
        const page = await browser.newPage();

        let startIndex = 0;
        let allRestaurants = [];

        for (let pageCount = 0; pageCount < 5; pageCount++) {
            const url = `https://www.yelp.co.uk/search?find_desc=Restaurants&find_loc=${encodeURIComponent(city)},+${encodeURIComponent(country)}&start=${startIndex}`;

            await page.goto(url, { waitUntil: "networkidle2" });
            console.log("Navigated to page.");

            try {
                await page.waitForSelector("#onetrust-accept-btn-handler", { timeout: 5000 });
                await page.click("#onetrust-accept-btn-handler");
                console.log("Cookie consent accepted.");
            } catch (error) {
                console.log("No cookie consent dialog found or could not interact with it.");
            }

            await page.waitForSelector("ul li");

            const restaurants = await page.evaluate(() => {
                return Array.from(document.querySelectorAll("ul li")).map(restaurant => {
                    const nameElement = restaurant.querySelector('a[class*="y-css-1x1e1r2"]');
                    const ratingElement = restaurant.querySelector('span[class*="y-css-f73en8"]');
                    const locationElement = restaurant.querySelector('span[class*="y-css-yvhxeq"]');

                    const name = nameElement ? nameElement.innerText.trim() : null;
                    const rating = ratingElement ? ratingElement.innerText.trim() : null;
                    const location = locationElement ? locationElement.innerText.trim() : null;

                    if (name && rating && location) {
                        return { name, rating, location };
                    }
                    return null;
                }).filter(restaurant => restaurant !== null);
            });
            allRestaurants = allRestaurants.concat(restaurants);

            startIndex += 10;
        }

        const cacheDir = path.join(__dirname, "cache");
        if (!fs.existsSync(cacheDir)) {
            fs.mkdirSync(cacheDir, { recursive: true });
        }

        const endTime = performance.now();
        const endCpuUsage = process.cpuUsage(startCpuUsage);
        const elapsedTime = (endTime - startTime) / 1000;
        const cpuUsage = (endCpuUsage.user + endCpuUsage.system) / 1000000;

        const cacheData = {
            restaurants: allRestaurants,
            elapsedTime: elapsedTime.toFixed(2),
            cpuUsage: cpuUsage.toFixed(2)
        };

        fs.writeFileSync(cachePath, JSON.stringify(cacheData, null, 2));
        console.log("Data saved.");

        await browser.close();
        console.log("Browser closed.")

        console.log(`Total time: ${elapsedTime.toFixed(2)}s`);
        console.log(`CPU usage: ${cpuUsage.toFixed(2)}%`);
    } catch (error) {
        console.error("Error:", error);
    }
})();
