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
    if (fs.existsSync(cachePath)) {
        console.log(fs.readFileSync(cachePath, "utf-8"));
        return;
    }
    const startTime = performance.now();
    const startCPU = process.cpuUsage();

    try {
        const cacheDir = path.join(__dirname, "cache");
        if (!fs.existsSync(cacheDir)) {
            fs.mkdirSync(cacheDir, { recursive: true });
        }

        const browser = await puppeteer.launch({ headless: "true" });
        const page = await browser.newPage();
        await page.setViewport({ width: 800, height: 600 });
        await page.setRequestInterception(true);

        page.on("request", (request) => {
            const blockedResources = ["image", "stylesheet", "font", "media", "other"];
            if (blockedResources.includes(request.resourceType())) {
                request.abort();
            } else {
                request.continue();
            }
        });

        const url = `https://www.atlasobscura.com/things-to-do/${city.toLowerCase()}-${country.toLowerCase()}/places`;
        await page.goto(url);

        await page.waitForSelector("#onetrust-accept-btn-handler", { timeout: 5000 }).then(async () => {
            await page.click("#onetrust-accept-btn-handler");
        })

        await page.waitForSelector(".fc-button.fc-cta-consent.fc-primary-button", { timeout: 10000 }).then(async () => {
            await page.click(".fc-button.fc-cta-consent.fc-primary-button");
        })

        let allCards = [];
        let currentPage = 1;

        while (allCards.length < 50) {
            await page.waitForSelector(".geo-places .CardWrapper", { timeout: 5000 });

            const cards = await page.evaluate(() => {
                return Array.from(document.querySelectorAll(".geo-places .CardWrapper")).map(card => ({
                    name: card.querySelector(".Card__heading span")?.textContent.trim(),
                    description: card.querySelector(".Card__content")?.textContent.trim()
                }));
            });

            allCards = allCards.concat(cards);

            if (allCards.length >= 50) {
                break;
            }

            currentPage++;
            const nextPageUrl = `https://www.atlasobscura.com/things-to-do/${city.toLowerCase()}-${country.toLowerCase()}/places?page=${currentPage}`;
            await page.goto(nextPageUrl);

            const nextPageError = await page.evaluate(() => {
                const errorElement = document.querySelector(".col-xs-12 .icon-atlas-icon + h2.title-lg");
                return errorElement && errorElement.innerText.includes("Something went wrong on our end.");
            });

            if (nextPageError) {
                break;
            }
        }

        allCards = allCards.slice(0, 50);

        fs.writeFileSync(cachePath, JSON.stringify(allCards, null, 2));

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
