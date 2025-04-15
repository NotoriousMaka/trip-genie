import { chromium } from "playwright";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const city = process.argv[2];
const country = process.argv[3];

const formatCity = city.toLowerCase().replace(/\s+/g, '-');
const formatCountry = country.toLowerCase().replace(/\s+/g, '-');
const fileName = `${formatCity}-${formatCountry}-lonelyplanet.json`;
const cacheDirectory = path.join(__dirname, "cache-play");
const filePath = path.join(cacheDirectory, fileName);

(async () => {
    const startTime = performance.now();

    try {
        if (!fs.existsSync(cacheDirectory)) {
            fs.mkdirSync(cacheDirectory, { recursive: true });
        }

        const browser = await chromium.launch({ headless: true });
        const page = await browser.newPage();

        const searchUrl = `https://www.lonelyplanet.com/search?q=${encodeURIComponent(city)}`;
        await page.goto(searchUrl, { waitUntil: "networkidle" });

        try {
            await page.waitForSelector("#onetrust-accept-btn-handler", { timeout: 5000 });
            await page.click("#onetrust-accept-btn-handler");
        } catch (error) {
            console.log("Cookie consent button not found or already accepted.");
        }

        try {
            const linkSelector = 'a.lg\\:text-lg.text-black.hover\\:text-black.font-semibold.card-link.mb-1.line-clamp-4';
            await page.waitForSelector(linkSelector, { timeout: 10000 });

            const firstLink = await page.locator(linkSelector).first();
            await firstLink.click();
        } catch (error) {
            console.error("Error clicking the <a> element.");
        }

        await page.waitForSelector("a.card-link.line-clamp-2");
        const links = await page.$$eval("a.card-link.line-clamp-2", (elements) => elements.map(el => el.href));

        const attractionData = [];
        const maxLocations = 20;

        for (let i = 0; i < links.length && i < maxLocations; i++) {
            const link = links[i];

            await page.goto(link, { waitUntil: "networkidle" });

            const readMoreButton = await page.$("button.text-blue.font-semibold.mt-2.block.mx-auto");
            if (readMoreButton) {
                await readMoreButton.click();
            } else {
                console.log("No Read more button found.");
            }

            try {
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
            } catch (error) {
                console.log("Error processing attraction.");

            }
        }

        fs.writeFileSync(filePath, JSON.stringify(attractionData, null, 2));
        await browser.close();

        const endTime = performance.now();
        const totalTime = (endTime - startTime) / 1000;
        console.log(`Total time: ${totalTime.toFixed(2)} seconds`);
        console.log("CPU usage: ", process.cpuUsage());
    } catch (error) {
        console.error('Error:', error);
    }
})();
