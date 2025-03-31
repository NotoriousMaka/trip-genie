import puppeteer from "puppeteer";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const city = process.argv[2];
const country = process.argv[3];

const cacheFile = `${city.toLowerCase()}-${country.toLowerCase()}-lonelyplanet.json`;
const cachePath = path.join(__dirname, "cache", cacheFile);

(async () => {
    const startTime = performance.now();
    const startCPU = process.cpuUsage();

    try {
        const cacheDirectory = path.join(__dirname, "cache");
        if (!fs.existsSync(cacheDirectory)) {
            fs.mkdirSync(cacheDirectory, { recursive: true });
        }

        const browser = await puppeteer.launch({
            headless: false,
            defaultViewport: null
        });
        console.log("Browser launched.");

        const page = await browser.newPage();
        const url = `https://www.lonelyplanet.com/${country.toLowerCase()}/${city.toLowerCase()}/attractions`;
        await page.goto(url, { waitUntil: "networkidle2" });
        console.log("Navigated URL.");

        try {
            await page.waitForSelector("#onetrust-accept-btn-handler", { timeout: 5000 });
            await page.click("#onetrust-accept-btn-handler");
            console.log("Cookie consent accepted.");
        } catch (error) {
            console.log("No cookie consent dialog found or could not interact with it.");
        }

        await page.waitForSelector("a.card-link.line-clamp-2");

        const links = await page.$$eval("a.card-link.line-clamp-2", (elements) => elements.map(el => el.href));

        const attractionData = [];
        const maxLocations = 20;

        for (let i = 0; i < links.length && i < maxLocations; i++) {
            const link = links[i];

            await page.goto(link, { waitUntil: "networkidle2" });
            console.log(`Navigated to attraction.`);

            const readMoreButton = await page.$("button.text-blue.font-semibold.mt-2.block.mx-auto");
            if (readMoreButton) {
                await readMoreButton.click();
                console.log("Clicked the \"Read more\" button.");
            } else {
                console.log("No \"Read more\" button found.");
            }

            await page.waitForSelector(".readMore_content__bv7mp", { timeout: 5000 });

            const paragraphs = await page.$$eval(".readMore_content__bv7mp p", (elements) => elements.map(el => el.innerText));

            const addressElement = await page.$("a[href^=\"https://www.google.com/maps?q=\"]");
            const address = addressElement ? await page.evaluate(el => el.href, addressElement) : null;

            const phoneElement = await page.$('a[href^="tel:"]');
            const phone = phoneElement ? await page.evaluate(el => el.innerText, phoneElement) : null;

            attractionData.push({
                link: link,
                paragraphs: paragraphs,
                address: address,
                phone: phone
            });
        }

        fs.writeFileSync(cachePath, JSON.stringify(attractionData, null, 2));
        console.log(`Cache saved.`);

        await browser.close();

        const endTime = performance.now();
        const endCPU = process.cpuUsage(startCPU);
        const totalTime = (endTime - startTime) / 1000;
        const cpuUsage = (endCPU.user + endCPU.system) / 1000000;

        console.log(`Total time: ${totalTime.toFixed(2)} seconds`);
        console.log(`CPU usage: ${cpuUsage.toFixed(2)}%`);
    } catch (error) {
        console.error('Error:', error);
    }
})();
