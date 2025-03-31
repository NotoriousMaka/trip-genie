import { chromium } from "playwright";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const city = process.argv[2];
const country = process.argv[3];

const formatCity = city.toLowerCase().replace(/\s+/g, "-");
const formatCountry = country.toLowerCase().replace(/\s+/g, "-");
const fileName = `${formatCity}-${formatCountry}-atlas.json`;
const cacheDirectory = path.join(__dirname, "cache-play");
const filePath = path.join(cacheDirectory, fileName);

(async () => {
    const startTime = performance.now();

    try {
        if (!fs.existsSync(cacheDirectory)) {
            fs.mkdirSync(cacheDirectory, { recursive: true });
        }

        const browser = await chromium.launch({ headless: false });
        const page = await browser.newPage();

        const url = `https://www.atlasobscura.com/things-to-do/${formatCity}-${formatCountry}/places`;
        await page.goto(url, { waitUntil: "domcontentloaded" });

        if (await page.locator("#onetrust-accept-btn-handler").isVisible()) {
            await page.click("#onetrust-accept-btn-handler");
        }

        if (await page.locator(".fc-button.fc-cta-consent.fc-primary-button").isVisible()) {
            await page.click(".fc-button.fc-cta-consent.fc-primary-button");
        }

        let allCards = [];
        let currentPage = 1;

        while (allCards.length < 100) {
            await page.waitForSelector(".geo-places .CardWrapper", { timeout: 10000 });

            const cards = await page.locator(".geo-places .CardWrapper").evaluateAll(cardElements => {
                return cardElements.map(card => ({
                    name: card.querySelector('.Card__heading span')?.innerText,
                    description: card.querySelector('.Card__content')?.innerText
                }));
            });

            allCards = [...allCards, ...cards];

            if (allCards.length >= 100) break;

            const nextPageUrl = `https://www.atlasobscura.com/things-to-do/${formatCity}-${formatCountry}/places?page=${++currentPage}`;
            await page.goto(nextPageUrl, { waitUntil: "domcontentloaded" });

            if (await page.locator(".col-xs-12 .icon-atlas-icon + h2.title-lg").isVisible()) {
                console.log("Error detected on this page. Stopping further scraping.");
                break;
            }
        }

        allCards = allCards.slice(0, 100);

        fs.writeFileSync(filePath, JSON.stringify(allCards, null, 2));
        console.log("Data saved.");

        await browser.close();
        console.log("Browser closed.");

        console.log(`Total time: ${((performance.now() - startTime) / 1000).toFixed(2)} seconds`);
        console.log("CPU Usage: ", process.cpuUsage());
    } catch (error) {
        console.error("Error:", error);
    }
})();
