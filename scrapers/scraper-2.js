import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const city = process.argv[2];
const country = process.argv[3];

const cacheFile = `${city.toLowerCase()}-${country.toLowerCase()}-wikivoyage.json`;
const cachePath = path.join(__dirname, 'cache', cacheFile);

(async () => {
    const startTime = performance.now();
    const startCPU = process.cpuUsage();

    try {
        const cacheDir = path.join(__dirname, 'cache');
        if (!fs.existsSync(cacheDir)) {
            fs.mkdirSync(cacheDir, { recursive: true });
        }

        const browser = await puppeteer.launch({ headless: false });
        const page = await browser.newPage();

        const url = `https://en.wikivoyage.org/wiki/${city.replace(' ', '_')}`;
        await page.goto(url);
        console.log('Navigated to URL.');

        await page.waitForSelector('#mw-content-text', { timeout: 10000 });

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

        const filePath = path.join(__dirname, 'wikivoyage_data.json');
        fs.writeFileSync(filePath, JSON.stringify(data, null, 2));

        fs.writeFileSync(cachePath, JSON.stringify(data, null, 2));
        console.log(`Cache saved.`);

        await browser.close();

        const endTime = performance.now();
        const endCPU = process.cpuUsage(startCPU);
        const totalTime = (endTime - startTime) / 1000; // in seconds
        const cpuUsage = (endCPU.user + endCPU.system) / 1000000; // in seconds

        console.log(`Total time: ${totalTime.toFixed(2)} seconds.`);
        console.log(`CPU usage: ${cpuUsage.toFixed(2)}%.`);
    } catch (error) {
        console.error('Error:', error);
    }
})();
