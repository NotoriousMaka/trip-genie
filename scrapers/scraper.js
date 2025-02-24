import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

(async () => {
    const city = 'Paris';
    const country = 'France';
    const category = 'architecture';
    const startTime = performance.now();
    const startCpuUsage = process.cpuUsage();

    try {
        console.log('Launching browser...');
        const browser = await puppeteer.launch({ headless: false });
        console.log('Browser launched.');

        const page = await browser.newPage();
        console.log('New page created.');

        // Construct the URL using the city, country, and category
        const url = `https://www.atlasobscura.com/things-to-do/${city.toLowerCase()}-${country.toLowerCase()}/${category}`;
        console.log(`Navigating to ${url}...`);

        await page.goto(url);
        console.log('Navigated to the specified URL.');

        // Wait for the "Accept Cookies" button to be visible and click it
        await page.waitForSelector('#onetrust-accept-btn-handler', { timeout: 10000 });
        console.log('Accept Cookies button found.');
        await page.click('#onetrust-accept-btn-handler');
        console.log('Accept Cookies button clicked.');

        // Wait for the "Consent" button to be visible and click it
        await page.waitForSelector('.fc-button.fc-cta-consent.fc-primary-button', { timeout: 10000 });
        console.log('Consent button found.');
        await page.click('.fc-button.fc-cta-consent.fc-primary-button');
        console.log('Consent button clicked.');

        // Wait for the cards to load
        await page.waitForSelector('.geo-places .CardWrapper', { timeout: 10000 });
        console.log('Cards loaded.');

        // Select 5 random cards
        const cards = await page.evaluate(() => {
            const cardElements = Array.from(document.querySelectorAll('.geo-places .CardWrapper'));
            const selectedCards = [];
            while (selectedCards.length < 5 && cardElements.length > 0) {
                const randomIndex = Math.floor(Math.random() * cardElements.length);
                const card = cardElements.splice(randomIndex, 1)[0];
                const name = card.querySelector('.Card__heading span').innerText;
                const description = card.querySelector('.Card__content').innerText;
                selectedCards.push({ name, description });
            }
            return selectedCards;
        });

        console.log('Selected cards:', cards);

        // Write the selected cards to a file in the /scrapers folder
        const filePath = path.join(__dirname, 'selected_cards.json');
        fs.writeFileSync(filePath, JSON.stringify(cards, null, 2));
        console.log('Selected cards written to scrapers/selected_cards.json');

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
