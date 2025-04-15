import { chromium } from "playwright";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const city = process.argv[2];
const country = process.argv[3];
const cacheDirectory = path.join(__dirname, "cache-play");
const cacheFile = path.join(cacheDirectory, `${city.toLowerCase()}-${country.toLowerCase()}-yelp.json`);

(async () => {
    const startTime = performance.now();
    const startCPU = process.cpuUsage();

    try {
        const browser = await chromium.launch({ headless: false, slowMo: 100 });
        const page = await (await browser.newContext()).newPage();

        let allRestaurants = [];

        for (let i = 0; i < 5; i++) {
            const start = i * 10;
            const url = `https://www.yelp.co.uk/search?find_desc=Restaurants&find_loc=${encodeURIComponent(city)}%2C+${encodeURIComponent(country)}&start=${start}`;
            await page.goto(url);

            if (i === 0) {
                try {
                    const cookieButton = await page.waitForSelector("#onetrust-accept-btn-handler", { timeout: 5000 });
                    if (cookieButton) await cookieButton.click();
                } catch {
                    console.log("No cookie consent dialog found.");
                }
            }

            await page.waitForSelector("[data-testid=\"serp-ia-card\"]", { timeout: 10000 });

            const restaurants = await page.evaluate(() => {
                return Array.from(document.querySelectorAll("[data-testid=\"serp-ia-card\"]")).map(listing => {
                    const name = listing.querySelector("a[name]")?.textContent?.trim();
                    if (!name) return null;

                    return {
                        name,
                        rating: listing.querySelector("[aria-label*=\"star rating\"]")?.getAttribute("aria-label"),
                        reviews: listing.querySelector("span.y-css-1vi7y4e")?.textContent?.trim(),
                        location: listing.querySelectorAll("span.y-css-yvhxeq")?.[0]?.textContent?.trim(),
                        price: listing.querySelectorAll("span.y-css-yvhxeq")?.[1]?.textContent?.trim()
                    };
                }).filter(Boolean);
            });

            allRestaurants.push(...restaurants);
            await page.waitForTimeout(1000);
        }

        if (!fs.existsSync(cacheDirectory)) fs.mkdirSync(cacheDirectory, { recursive: true });

        const elapsedTime = ((performance.now() - startTime) / 1000).toFixed(2);
        const cpuUsage = ((process.cpuUsage(startCPU).user + process.cpuUsage(startCPU).system) / 1000000).toFixed(2);

        const cacheData = {
            city, country,
            count: allRestaurants.length,
            restaurants: allRestaurants,
            elapsedTime, cpuUsage,
        };

        fs.writeFileSync(cacheFile, JSON.stringify(cacheData, null, 2));

        await browser.close();
        console.log("Total time:", elapsedTime, "s");
        console.log("CPU Usage:", cpuUsage, "%");
    } catch (error) {
        console.error("Error:", error);
    }
})();
