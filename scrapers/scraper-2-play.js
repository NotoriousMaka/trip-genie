import {chromium} from "playwright";
import fs from "fs";
import path from "path";
import {fileURLToPath} from "url";

const filename = fileURLToPath(import.meta.url);
const directory = path.dirname(filename);

const city_name = process.argv[2];
const country_name = process.argv[3];

const city_formatted = city_name.toLowerCase().replace(/\s+/g, "-");
const country_formatted = country_name.toLowerCase().replace(/\s+/g, "-");
const cache_name = `${city_formatted}-${country_formatted}-wikivoyage.json`;
const cache_path = path.join(directory, "cache-play", cache_name);

function startPerformance() {
    return { startTime: performance.now(), startCPU: process.cpuUsage() };
}

function checkCache() {
    if (fs.existsSync(cache_path)) {
        const cache = fs.readFileSync(cache_path, "utf-8");
        return JSON.parse(cache);
    }

    const cacheDir = path.dirname(cache_path);
    if (!fs.existsSync(cacheDir)) {
        fs.mkdirSync(cacheDir, { recursive: true });
    }

    return null;
}

async function setBrowser() {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    await page.setViewportSize({ width: 1280, height: 800 });
    return { browser, page };
}

async function navigateAndScrape(page, city_name) {
    const city_formatted = city_name.replace(/\s+/g, "_");
    const url = `https://en.wikivoyage.org/wiki/${city_formatted}`;

    await page.goto(url, { waitUntil: "domcontentloaded" });
    console.log(`Navigated to: ${url}`);

    await page.waitForSelector("#mw-content-text", { timeout: 10000 });

    const sections = ["Understand", "Get Around", "See", "Do", "Buy", "Eat", "Drink"];

    return await page.evaluate((sections) => {
        const div = document.querySelector("#mw-content-text");
        const elements = div.querySelectorAll("h2, h3, p, ul");

        let attraction_data = {};
        let current_section = null;

        elements.forEach((element) => {
            if (element.tagName === "H2" || element.tagName === "H3") {
                const section = element.innerText.trim();
                if (sections.includes(section)) {
                    current_section = section;
                    attraction_data[current_section] = [];
                } else {
                    current_section = null;
                }
            } else if ((element.tagName === "P" || element.tagName === "UL") && current_section) {
                if (element.tagName === "UL") {
                    const listItems = Array.from(element.querySelectorAll("li")).map((item) =>
                        item.innerText.trim()
                    );
                    attraction_data[current_section].push(...listItems);
                } else {
                    attraction_data[current_section].push(element.innerText.trim());
                }
            }
        });

        return attraction_data;
    }, sections);
}

async function saveCache(data) {
    fs.writeFileSync(cache_path, JSON.stringify(data, null, 2));
}

function printPerformance(track) {
    const endTime = performance.now();
    const endCPU = process.cpuUsage(track.startCPU);
    const totalTime = (endTime - track.startTime) / 1000;
    const totalCPU = (endCPU.user + endCPU.system) / 1000000;

    console.log("Total Time Taken (s):", totalTime);
    console.log("Total CPU Usage (%):", totalCPU);
}

async function main(city_name) {
    const cache = checkCache();
    const track = startPerformance();

    if (cache) {
        console.log(cache);
        return;
    }

    try {
        const { browser, page } = await setBrowser();
        const data = await navigateAndScrape(page, city_name);
        await saveCache(data);
        await browser.close();
        printPerformance(track);
    } catch (error) {
        console.error("Scraping failed:", error);
    }
}

(async () => {
    await main(city_name, country_name);
})();
