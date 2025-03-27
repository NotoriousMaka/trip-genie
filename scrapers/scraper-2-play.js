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
    console.error('Usage: node scraper-play-wikivoyage.js <city> <country>');
    process.exit(1);
}

// Format file paths
const formattedCity = city.toLowerCase().replace(/\s+/g, '-');
const formattedCountry = country.toLowerCase().replace(/\s+/g, '-');
const fileName = `${formattedCity}-${formattedCountry}-wikivoyage.json`;
const cacheDir = path.join(__dirname, 'cache-play');
const filePath = path.join(cacheDir, fileName);

(async () => {
    const startTime = performance.now();
    console.log(`Scraping Wikivoyage for ${city}...`);

    try {
        // Create cache-play directory if it doesn't exist
        if (!fs.existsSync(cacheDir)) {
            fs.mkdirSync(cacheDir, { recursive: true });
        }

        console.log('Launching browser...');
        const browser = await chromium.launch({ headless: true });
        const page = await browser.newPage();

        // Construct the URL
        const url = `https://en.wikivoyage.org/wiki/${city.replace(/\s+/g, '_')}`;
        console.log(`Navigating to ${url}...`);
        await page.goto(url, { waitUntil: 'domcontentloaded' });

        // Wait for content
        await page.waitForSelector('#mw-content-text', { timeout: 10000 });

        // Sections to extract
        const sections = ['Understand', 'Get Around', 'See', 'Do', 'Buy', 'Eat', 'Drink'];

        const data = await page.evaluate((sections) => {
            const contentDiv = document.querySelector('#mw-content-text');
            const sectionElements = contentDiv.querySelectorAll('h2, h3, p, ul');

            let sectionData = {};
            let currentSection = null;

            sectionElements.forEach(element => {
                if (element.tagName === 'H2' || element.tagName === 'H3') {
                    const sectionName = element.innerText.trim();
                    if (sections.includes(sectionName)) {
                        currentSection = sectionName;
                        sectionData[currentSection] = [];
                    } else {
                        currentSection = null;
                    }
                } else if ((element.tagName === 'P' || element.tagName === 'UL') && currentSection) {
                    if (element.tagName === 'UL') {
                        const listItems = Array.from(element.querySelectorAll('li')).map(item => item.innerText.trim());
                        sectionData[currentSection].push(...listItems);
                    } else {
                        sectionData[currentSection].push(element.innerText.trim());
                    }
                }
            });

            return sectionData;
        }, sections);

        // Save data to JSON file
        fs.writeFileSync(filePath, JSON.stringify(data, null, 2));
        console.log(`Data saved to ${filePath}`);

        await browser.close();
        console.log(`Elapsed time: ${((performance.now() - startTime) / 1000).toFixed(2)} seconds`);
    } catch (error) {
        console.error('Error:', error);
    }
})();
