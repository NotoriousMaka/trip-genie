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
const fileName = `${formatCity}-${formatCountry}-wikivoyage.json`;
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

        const url = `https://en.wikivoyage.org/wiki/${city.replace(/\s+/g, '_')}`;
        await page.goto(url, { waitUntil: "domcontentloaded" });
        console.log("Navigated to URL.");

        await page.waitForSelector("#mw-content-text", { timeout: 10000 });

        const sections = ["Understand", "Get Around", "See", "Do", "Buy", "Eat", "Drink"];

        const data = await page.evaluate((sections) => {
            const contentDiv = document.querySelector("#mw-content-text");
            const sectionElements = contentDiv.querySelectorAll("h2, h3, p, ul");

            let sectionData = {};
            let currentSection = null;

            sectionElements.forEach(element => {
                if (element.tagName === "H2" || element.tagName === "H3") {
                    const sectionName = element.innerText.trim();
                    if (sections.includes(sectionName)) {
                        currentSection = sectionName;
                        sectionData[currentSection] = [];
                    } else {
                        currentSection = null;
                    }
                } else if ((element.tagName === "P" || element.tagName === "UL") && currentSection) {
                    if (element.tagName === "UL") {
                        const listItems = Array.from(element.querySelectorAll("li")).map(item => item.innerText.trim());
                        sectionData[currentSection].push(...listItems);
                    } else {
                        sectionData[currentSection].push(element.innerText.trim());
                    }
                }
            });

            return sectionData;
        }, sections);

        fs.writeFileSync(filePath, JSON.stringify(data, null, 2));
        console.log("Data saved.");

        await browser.close();
        console.log(`Total time: ${((performance.now() - startTime) / 1000).toFixed(2)} seconds.`);
        console.log("CPU usage: ", process.cpuUsage());
    } catch (error) {
        console.error("Error:", error);
    }
})();
