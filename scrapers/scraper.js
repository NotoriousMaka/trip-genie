import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Get command line arguments
const city = process.argv[2];
const country = process.argv[3];

// Define cache filename
const cacheFileName = `${city.toLowerCase()}-${country.toLowerCase()}-atlas.json`;
const cachePath = path.join(__dirname, 'cache', cacheFileName);

(async () => {
    const startTime = performance.now();
    const startCpuUsage = process.cpuUsage();

    console.log(`Scraping Atlas Obscura for ${city}, ${country}...`);

    try {
        // Create cache directory if it doesn't exist
        const cacheDir = path.join(__dirname, 'cache');
        if (!fs.existsSync(cacheDir)) {
            fs.mkdirSync(cacheDir, { recursive: true });
        }

        console.log('Launching browser...');
        const browser = await puppeteer.launch({ headless: false });
        console.log('Browser launched.');

        const page = await browser.newPage();
        console.log('New page created.');

        // Construct the URL using the city and country
        const url = `https://www.atlasobscura.com/things-to-do/${city.toLowerCase()}-${country.toLowerCase()}/places`;
        console.log(`Navigating to ${url}...`);

        await page.goto(url);
        console.log('Navigated to the specified URL.');

        // Check for error message
        const errorExists = await page.evaluate(() => {
            const errorElement = document.querySelector('.col-xs-12 .icon-atlas-icon + h2.title-lg');
            return errorElement && errorElement.innerText.includes('Something went wrong on our end.');
        });

        if (errorExists) {
            console.log('Error detected on the page. Using only the scraped data so far.');
            await browser.close();
            return;
        }

        // Wait for the "Accept Cookies" button to be visible and click it
        await page.waitForSelector('#onetrust-accept-btn-handler', { timeout: 10000 }).then(async () => {
            console.log('Accept Cookies button found.');
            await page.click('#onetrust-accept-btn-handler');
            console.log('Accept Cookies button clicked.');
        }).catch(() => console.log('No Accept Cookies button found.'));

        // Wait for the "Consent" button to be visible and click it
        await page.waitForSelector('.fc-button.fc-cta-consent.fc-primary-button', { timeout: 10000 }).then(async () => {
            console.log('Consent button found.');
            await page.click('.fc-button.fc-cta-consent.fc-primary-button');
            console.log('Consent button clicked.');
        }).catch(() => console.log('No Consent button found.'));

        let allCards = [];
        let currentPage = 1;

        while (allCards.length < 100) {
            // Wait for the cards to load
            await page.waitForSelector('.geo-places .CardWrapper', { timeout: 10000 });
            console.log('Cards loaded.');

            // Select cards
            const cards = await page.evaluate(() => {
                const cardElements = Array.from(document.querySelectorAll('.geo-places .CardWrapper'));
                return cardElements.map(card => {
                    const name = card.querySelector('.Card__heading span')?.innerText || 'No name';
                    const description = card.querySelector('.Card__content')?.innerText || 'No description';
                    return { name, description };
                });
            });

            allCards = [...allCards, ...cards];
            console.log(`Total cards collected: ${allCards.length}`);

            if (allCards.length >= 100) break;

            // Navigate to the next page
            currentPage++;
            const nextPageUrl = `https://www.atlasobscura.com/things-to-do/${city.toLowerCase()}-${country.toLowerCase()}/places?page=${currentPage}`;
            console.log(`Navigating to ${nextPageUrl}...`);
            await page.goto(nextPageUrl);
            console.log('Navigated to the next page.');

            // Check for error page on next pages
            const nextPageError = await page.evaluate(() => {
                const errorElement = document.querySelector('.col-xs-12 .icon-atlas-icon + h2.title-lg');
                return errorElement && errorElement.innerText.includes('Something went wrong on our end.');
            });

            if (nextPageError) {
                console.log('Error detected on this page. Stopping further scraping.');
                break;
            }
        }

        // Limit to 100 cards
        allCards = allCards.slice(0, 100);

        console.log('Selected cards:', allCards);

        // Write the selected cards to the regular file
        const filePath = path.join(__dirname, 'atlas_data.json');
        fs.writeFileSync(filePath, JSON.stringify(allCards, null, 2));
        console.log('Selected cards written to scrapers/atlas_data.json');

        // Write to cache file
        fs.writeFileSync(cachePath, JSON.stringify(allCards, null, 2));
        console.log(`Cache saved to ${cachePath}`);

        await browser.close();
        console.log('Browser closed.');

        // Calculate and print the elapsed time and CPU usage
        const endTime = performance.now();
        const endCpuUsage = process.cpuUsage(startCpuUsage);
        const elapsedTime = (endTime - startTime) / 1000; // in seconds
        const cpuUsage = (endCpuUsage.user + endCpuUsage.system) / 1000000; // in seconds

        console.log(`Elapsed time: ${elapsedTime.toFixed(2)} seconds`);
        console.log(`CPU usage: ${cpuUsage.toFixed(2)}%`);
    } catch (error) {
        console.error('Error:', error);
    }
})();
