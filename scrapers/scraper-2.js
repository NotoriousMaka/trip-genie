import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

(async () => {
    const city = process.argv[2];
    const startTime = performance.now();
    const startCpuUsage = process.cpuUsage();

    try {
        console.log('Launching browser...');
        const browser = await puppeteer.launch({ headless: false });
        console.log('Browser launched.');

        const page = await browser.newPage();
        console.log('New page created.');

        // Construct the URL using the city
        const url = `https://en.wikivoyage.org/wiki/${city.replace(' ', '_')}`;
        console.log(`Navigating to ${url}...`);

        await page.goto(url);
        console.log('Navigated to the specified URL.');

        // Wait for the page to load
        await page.waitForSelector('#mw-content-text', { timeout: 10000 });
        console.log('Page content loaded.');

        // Extract sections (Understand, Get Around, See, Do, Buy, Eat, Drink)
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

        // Write the extracted data to a JSON file
        const filePath = path.join(__dirname, 'wikivoyage_data.json');
        fs.writeFileSync(filePath, JSON.stringify(data, null, 2));
        console.log('Data written to wikivoyage_data.json');

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
