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
        const searchUrl = "https://www.lonelyplanet.com/search";
        await page.goto(searchUrl, { waitUntil: "networkidle2" });
        console.log("Navigated to search page.");

        try {
            await page.waitForSelector("#onetrust-accept-btn-handler", { timeout: 5000 });
            await page.click("#onetrust-accept-btn-handler");
            console.log("Cookie consent accepted.");
        } catch (error) {
            console.log("No cookie consent dialog found or could not interact with it.");
        }

        await page.evaluate(() => {
            const button = document.querySelector('button[data-tagular*="ElementClicked"][data-tagular*="Search"]');
            if (button) {
                button.click();
            }
        });

        const searchSelector = "input#searchInputComponent";
        await page.waitForSelector(searchSelector, { visible: true, timeout: 10000 });
        await page.focus(searchSelector);
        await page.click(searchSelector);
        await page.keyboard.type(city);
        await page.keyboard.press("Enter");

        await page.waitForNavigation({ waitUntil: "networkidle2" });

        await page.waitForSelector('a.lg\\:text-lg.text-black.hover\\:text-black.font-semibold.card-link.mb-1.line-clamp-4', { timeout: 60000 });
        await page.click('a.lg\\:text-lg.text-black.hover\\:text-black.font-semibold.card-link.mb-1.line-clamp-4');
        console.log("Clicked on the search result link.");
        console.log("Navigated URL.");

        try {
            await page.waitForSelector("a.card-link.line-clamp-2", { timeout: 60000 });
        } catch (error) {
            console.error("Error waiting for selector:", error);
            await browser.close();
            process.exit(1);
        }

        const links = await page.$$eval("a.card-link.line-clamp-2", (elements) => elements.map(el => el.href));

        const attractionData = [];
        const maxLocations = 20;

        for (let i = 0; i < links.length && i < maxLocations; i++) {
            const link = links[i];

            await page.goto(link, { waitUntil: "networkidle2" });
            console.log("Navigated to attraction.");

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

            const phoneElement = await page.$("a[href^=\"tel:\"]");
            const phone = phoneElement ? await page.evaluate(el => el.innerText, phoneElement) : null;

            attractionData.push({
                link: link,
                paragraphs: paragraphs,
                address: address,
                phone: phone
            });
        }

        fs.writeFileSync(cachePath, JSON.stringify(attractionData, null, 2));
        console.log("Cache saved.");

        await browser.close();
        console.log("Browser closed.")

        const endTime = performance.now();
        const endCPU = process.cpuUsage(startCPU);
        const totalTime = (endTime - startTime) / 1000;
        const cpuUsage = (endCPU.user + endCPU.system) / 1000000;

        console.log(`Total time: ${totalTime.toFixed(2)} seconds`);
        console.log(`CPU usage: ${cpuUsage.toFixed(2)}%`);
    } catch (error) {
        console.error("Error:", error);
    }
})();
