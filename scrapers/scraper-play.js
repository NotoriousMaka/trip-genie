import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Get command line arguments
const city = process.argv[2];
const country = process.argv[3];

if (!city || !country) {
    console.error('Usage: node scraper-play.js <city> <country>');
    process.exit(1);
}

// Format file paths
const formattedCity = city.toLowerCase().replace(/\s+/g, '-');
const formattedCountry = country.toLowerCase().replace(/\s+/g, '-');
const fileName = `${formattedCity}-${formattedCountry}-atlas.json`;
const cacheDir = path.join(__dirname, 'cache-play');
const filePath = path.join(cacheDir, fileName);

(async () => {
    const startTime = performance.now();
    console.log(`Scraping Atlas Obscura for ${city}, ${country}...`);

    try {
        // Create cache-play directory if it doesn't exist
        if (!fs.existsSync(cacheDir)) {
            fs.mkdirSync(cacheDir, { recursive: true });
        }

        console.log('Launching browser...');
        const browser = await chromium.launch({ headless: true });
        const page = await browser.newPage();

        const url = `https://www.atlasobscura.com/things-to-do/${formattedCity}-${formattedCountry}/places`;
        console.log(`Navigating to ${url}...`);
        await page.goto(url, { waitUntil: 'domcontentloaded' });

        // Accept Cookies if the button is found
        if (await page.locator('#onetrust-accept-btn-handler').isVisible()) {
            await page.click('#onetrust-accept-btn-handler');
        }

        // Consent Button
        if (await page.locator('.fc-button.fc-cta-consent.fc-primary-button').isVisible()) {
            await page.click('.fc-button.fc-cta-consent.fc-primary-button');
        }

        let allCards = [];
        let currentPage = 1;

        while (allCards.length < 100) {
            await page.waitForSelector('.geo-places .CardWrapper', { timeout: 10000 });

            const cards = await page.locator('.geo-places .CardWrapper').evaluateAll(cardElements => {
                return cardElements.map(card => ({
                    name: card.querySelector('.Card__heading span')?.innerText || 'No name',
                    description: card.querySelector('.Card__content')?.innerText || 'No description'
                }));
            });

            allCards = [...allCards, ...cards];

            if (allCards.length >= 100) break;

            // Check if there's a next page
            const nextPageUrl = `https://www.atlasobscura.com/things-to-do/${formattedCity}-${formattedCountry}/places?page=${++currentPage}`;
            await page.goto(nextPageUrl, { waitUntil: 'domcontentloaded' });

            // Detect error page
            if (await page.locator('.col-xs-12 .icon-atlas-icon + h2.title-lg').isVisible()) {
                console.log('Error detected on this page. Stopping further scraping.');
                break;
            }
        }

        // Limit to 100 results
        allCards = allCards.slice(0, 100);

        // Save data to JSON file in scrapers/cache-play
        fs.writeFileSync(filePath, JSON.stringify(allCards, null, 2));
        console.log(`Data saved to ${filePath}`);

        await browser.close();

        console.log(`Elapsed time: ${((performance.now() - startTime) / 1000).toFixed(2)} seconds`);
    } catch (error) {
        console.error('Error:', error);
    }
})();
