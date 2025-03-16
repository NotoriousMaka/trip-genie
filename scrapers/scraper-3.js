import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Get command line arguments
const city = process.argv[2];
const country = process.argv[3];

// Define cache filename based on city and country
const cacheFileName = `${city.toLowerCase()}-${country.toLowerCase()}-lonelyplanet.json`;
const cachePath = path.join(__dirname, 'cache', cacheFileName);

(async () => {
    const startTime = performance.now();
    const startCpuUsage = process.cpuUsage();

    console.log(`Scraping Lonely Planet attractions for ${city}, ${country}...`);

    try {
        // Create cache directory if it doesn't exist
        const cacheDir = path.join(__dirname, 'cache');
        if (!fs.existsSync(cacheDir)) {
            fs.mkdirSync(cacheDir, { recursive: true });
        }

        console.log('Launching browser...');
        const browser = await puppeteer.launch({
            headless: false,
            defaultViewport: null // Full page view
        });
        console.log('Browser launched.');

        const page = await browser.newPage();
        console.log('New page created.');

        // Construct the URL using the city and country
        const url = `https://www.lonelyplanet.com/${country.toLowerCase()}/${city.toLowerCase()}/attractions`;
        console.log(`Navigating to ${url}...`);

        await page.goto(url, { waitUntil: 'networkidle2' });
        console.log('Navigated to the specified URL.');

        // Handle cookie consent if it appears
        try {
            await page.waitForSelector('#onetrust-accept-btn-handler', { timeout: 5000 });
            await page.click('#onetrust-accept-btn-handler');
            console.log('Cookie consent accepted.');
        } catch (error) {
            console.log('No cookie consent dialog found or could not interact with it.');
        }

        // Wait for the attraction links to appear
        await page.waitForSelector('a.card-link.line-clamp-2');
        console.log('Links loaded.');

        // Get all attraction links
        const links = await page.$$eval('a.card-link.line-clamp-2', (elements) => elements.map(el => el.href));
        console.log(`Found ${links.length} attraction links.`);

        // Array to store attraction data
        const attractionData = [];
        const maxLocations = 20;

        // Loop through each link and scrape the text
        for (let i = 0; i < links.length && i < maxLocations; i++) {
            const link = links[i];
            console.log(`Navigating to attraction: ${link}`);

            // Go to the attraction link
            await page.goto(link, { waitUntil: 'networkidle2' });
            console.log(`Navigated to ${link}`);

            // Check if the "Read more" button exists and click it if it does
            const readMoreButton = await page.$('button.text-blue.font-semibold.mt-2.block.mx-auto');
            if (readMoreButton) {
                await readMoreButton.click();
                console.log('Clicked the "Read more" button.');
            } else {
                console.log('No "Read more" button found.');
            }

            // Wait for content to load
            await page.waitForSelector('.readMore_content__bv7mp', { timeout: 5000 });
            console.log('Waited for content to load.');

            // Scrape <p> elements inside the "readMore_content" div
            const paragraphs = await page.$$eval('.readMore_content__bv7mp p', (elements) => elements.map(el => el.innerText));
            paragraphs.forEach((para, index) => {
                console.log(`Paragraph ${index + 1}: ${para}`);
            });

            // Check if the address exists and scrape it if it does
            const addressElement = await page.$('a[href^="https://www.google.com/maps?q="]');
            const address = addressElement ? await page.evaluate(el => el.href, addressElement) : null;
            if (address) {
                console.log(`Address: ${address}`);
            }

            // Check if the phone number exists and scrape it if it does
            const phoneElement = await page.$('a[href^="tel:"]');
            const phone = phoneElement ? await page.evaluate(el => el.innerText, phoneElement) : null;
            if (phone) {
                console.log(`Phone: ${phone}`);
            }

            // Store the data in the attractionData array
            attractionData.push({
                link: link,
                paragraphs: paragraphs,
                address: address,
                phone: phone
            });
        }

        // Write the collected data to the cache file
        fs.writeFileSync(cachePath, JSON.stringify(attractionData, null, 2));
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
