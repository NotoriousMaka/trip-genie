import {chromium} from "playwright";

const from = process.argv[2] || "GBP";
const to = process.argv[3] || "USD";
const amount = process.argv[4] || "1";

async function setBrowser() {
    const browser = await chromium.launch({ headless: false });
    const page = await browser.newPage();
    await page.setViewportSize({ width: 1280, height: 800 });
    return { browser, page };
}

async function navigateAndScrape(page, from, to, amount) {
    const url = `https://www.x-rates.com/calculator/?from=${from}&to=${to}&amount=${amount}`;
    await page.goto(url, { waitUntil: "domcontentloaded" });
    await page.waitForSelector(".ccOutputBx", { timeout: 10000 });

    return await page.evaluate(() => {
        const box = document.querySelector(".ccOutputBx");
        if (!box) return null;
        const txt = box.querySelector(".ccOutputTxt")?.innerText || "";
        const rslt = box.querySelector(".ccOutputRslt")?.cloneNode(true);
        rslt?.querySelector(".ccOutputCode")?.remove();
        const rsltText = rslt?.innerText || "";
        return { text: txt, result: rsltText };
    });
}

async function main(from, to, amount) {
    try {
        const { browser, page } = await setBrowser();
        const data = await navigateAndScrape(page, from, to, amount);
        await browser.close();
        console.log(JSON.stringify(data));
    } catch (error) {
        console.error("Scraping failed:", error);
    }
}

(async () => {
    await main(from, to, amount);
})();
