import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const city = process.argv[2] || 'Milan';
const country = process.argv[3] || 'Italy';

const cacheFileName = `${city.toLowerCase()}-${country.toLowerCase()}-yelp.json`;
const cachePath = path.join(__dirname, 'cache', cacheFileName);

(async () => {
    const startTime = performance.now();
    const startCpuUsage = process.cpuUsage();

    console.log(`Opening Yelp page for ${city}, ${country}...`);

    try {
        console.log('Launching browser...');
        const browser = await puppeteer.launch({ headless: false, defaultViewport: null });
        console.log('Browser launched.');

        const page = await browser.newPage();
        console.log('New page created.');

        let startIndex = 0;
        let allRestaurants = [];

        for (let pageCount = 0; pageCount < 5; pageCount++) {
            const url = `https://www.yelp.co.uk/search?find_desc=Restaurants&find_loc=${encodeURIComponent(city)},+${encodeURIComponent(country)}&start=${startIndex}`;
            console.log(`Navigating to ${url}...`);

            await page.goto(url, { waitUntil: 'networkidle2' });
            console.log(`Yelp page ${pageCount + 1} opened successfully.`);

            try {
                await page.waitForSelector('#onetrust-accept-btn-handler', { timeout: 5000 });
                await page.click('#onetrust-accept-btn-handler');
                console.log('Cookie consent accepted.');
            } catch (error) {
                console.log('No cookie consent dialog found or could not interact with it.');
            }

            await page.waitForSelector('ul li');
            console.log(`Restaurant listings loaded for page ${pageCount + 1}.`);

            const restaurants = await page.evaluate(() => {
                return Array.from(document.querySelectorAll('ul li')).map(restaurant => {
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

            console.log('Scraped Restaurants:', restaurants);
            allRestaurants = allRestaurants.concat(restaurants);

            startIndex += 10;
        }

        const endTime = performance.now();
        const endCpuUsage = process.cpuUsage(startCpuUsage);
        const elapsedTime = (endTime - startTime) / 1000; // in seconds
        const cpuUsage = (endCpuUsage.user + endCpuUsage.system) / 1000000; // in seconds

        const cacheDir = path.join(__dirname, 'cache');
        if (!fs.existsSync(cacheDir)) {
            fs.mkdirSync(cacheDir, { recursive: true });
        }

        const cacheData = {
            restaurants: allRestaurants,
            elapsedTime: elapsedTime.toFixed(2),
            cpuUsage: cpuUsage.toFixed(2)
        };
        fs.writeFileSync(cachePath, JSON.stringify(cacheData, null, 2));
        console.log(`Data saved to ${cachePath}`);

        await browser.close();
    } catch (error) {
        console.error('Error:', error);
    }
})();
