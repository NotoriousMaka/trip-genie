import puppeteer from "puppeteer";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const city = process.argv[2];
const country = process.argv[3];

const cacheFile = `${city.toLowerCase()}-${country.toLowerCase()}-atlas.json`;
const cachePath = path.join(__dirname, "cache", cacheFile);

(async () => {
    const startTime = performance.now();
    const startCPU = process.cpuUsage();

    try {
        const cacheDir = path.join(__dirname, "cache");
        if (!fs.existsSync(cacheDir)) {
            fs.mkdirSync(cacheDir, { recursive: true });
        }

        const browser = await puppeteer.launch({ headless: false });
        const page = await browser.newPage();

        const url = `https://www.atlasobscura.com/things-to-do/${city.toLowerCase()}-${country.toLowerCase()}/places`;
        await page.goto(url);
        console.log("Navigated to URL.");

        const errorExists = await page.evaluate(() => {
            const errorElement = document.querySelector('.col-xs-12 .icon-atlas-icon + h2.title-lg');
            return errorElement && errorElement.innerText.includes("Something went wrong on our end.");
        });

        if (errorExists) {
            await browser.close();
            return;
        }

        await page.waitForSelector("#onetrust-accept-btn-handler", { timeout: 10000 }).then(async () => {
            await page.click("#onetrust-accept-btn-handler");
        }).catch(() => console.log("No Accept Cookies button found."));

        await page.waitForSelector(".fc-button.fc-cta-consent.fc-primary-button", { timeout: 10000 }).then(async () => {
            await page.click(".fc-button.fc-cta-consent.fc-primary-button");
        }).catch(() => console.log("No Consent button found."));

        let allCards = [];
        let currentPage = 1;

        while (allCards.length < 100) {
            await page.waitForSelector(".geo-places .CardWrapper", { timeout: 10000 });

            const cards = await page.evaluate(() => {
                const cardElements = Array.from(document.querySelectorAll(".geo-places .CardWrapper"));
                return cardElements.map(card => {
                    const name = card.querySelector(".Card__heading span")?.innerText || "No name";
                    const description = card.querySelector(".Card__content")?.innerText || "No description";
                    return { name, description };
                });
            });

            allCards = allCards.concat(cards);

            if (allCards.length >= 100) {
                break;
            }

            currentPage++;
            const nextPageUrl = `https://www.atlasobscura.com/things-to-do/${city.toLowerCase()}-${country.toLowerCase()}/places?page=${currentPage}`;
            await page.goto(nextPageUrl);
            console.log("Navigated to the next page.");

            const nextPageError = await page.evaluate(() => {
                const errorElement = document.querySelector(".col-xs-12 .icon-atlas-icon + h2.title-lg");
                return errorElement && errorElement.innerText.includes("Something went wrong on our end.");
            });

            if (nextPageError) {
                console.log("Error detected on this page. Stopping further scraping.");
                break;
            }
        }

        allCards = allCards.slice(0, 100);

        const filePath = path.join(__dirname, "atlas_data.json");
        fs.writeFileSync(filePath, JSON.stringify(allCards, null, 2));

        fs.writeFileSync(cachePath, JSON.stringify(allCards, null, 2));
        console.log("Cache saved.");

        await browser.close();

        const endTime = performance.now();
        const endCPU = process.cpuUsage(startCPU);
        const totalTime = (endTime - startTime) / 1000;
        const cpuUsage = (endCPU.user + endCPU.system) / 1000000;

        console.log(`Total time: ${totalTime.toFixed(2)} seconds.`);
        console.log(`CPU usage: ${cpuUsage.toFixed(2)}%.`);
    } catch (error) {
        console.error("Error:", error);
    }
})();
