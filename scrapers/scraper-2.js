import puppeteer from "puppeteer";
import fs from "fs";
import path from "path";
import {fileURLToPath} from "url";

const filename = fileURLToPath(import.meta.url);
const directory = path.dirname(filename);

const country_name = process.argv[3];
const city_name = process.argv[2];

const cache_name =  `${city_name.toLowerCase()}-${country_name.toLowerCase()}-wikivoyage.json`;
const cache_path = path.join(directory, "cache", cache_name);

function startPerformance() {
    return {startTime: performance.now(), startCPU: process.cpuUsage()}
}

async function setBrowser() {
    const browser = await puppeteer.launch({headless: true});
    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 800 });
    await page.setRequestInterception(true);

    page.on("request", (request) => {
        const blockedResources = ["image", "stylesheet", "font", "media", "other"];
        if (blockedResources.includes(request.resourceType())) {
            request.abort();
        } else {
            request.continue();
        }
    });

    return {browser, page};
}

function checkCache() {
    if (fs.existsSync(cache_path)) {
        const cache = fs.readFileSync(cache_path, "utf-8");
        return JSON.parse(cache);
    }

    if (!fs.existsSync(path.join(directory, "cache"))) {
        fs.mkdirSync(path.join(directory, "cache"), { recursive: true });
    }
    return null;
}

async function goToURL(page, city_name) {
    const url = `https://en.wikivoyage.org/wiki/${city_name.replace(/ /g, '_')}`;
    await page.goto(url);
}

async function retrieveData(page) {
    await page.waitForSelector("#mw-content-text");

    const sections = ["Understand", "Get Around", "See", "Do", "Buy", "Eat", "Drink"];

    return page.evaluate((sections) => {
        const div = document.querySelector("#mw-content-text");
        const sectionElement = div.querySelectorAll("h2, h3, p, ul");

        let section = {};
        let currentSection = null;

        sections.forEach(sectionName => {
            section[sectionName] = [];
        });

        sectionElement.forEach(element => {
            if (element.tagName === "H2" || element.tagName === "H3") {
                const section_name = element.innerText.trim().replace(/\[\d+]/g, "");

                if(sections.includes(section_name)) {
                    currentSection = section_name;
                    section[currentSection] = [];
                } else {
                    currentSection = null;
                }
            } else {
                if (currentSection && section[currentSection]) {
                    if (element.tagName === "UL") {
                        const items = Array.from(element.querySelectorAll("li")).map(item => item.innerText.trim());
                        section[currentSection].push(...items);
                    } else {
                        section[currentSection].push(element.innerText.trim());
                    }
                }
            }
        });

        return section;
    }, sections);
}

async function saveCache(cache_path, data) {
    fs.writeFileSync(cache_path, JSON.stringify(data, null, 2));
}

function printPerformance(track) {
    const endTime = performance.now();
    const endCPU = process.cpuUsage(track.startCPU);

    const totalTime = (endTime - track.startTime) / 1000;
    const totalCPU = (endCPU.user + endCPU.system) / 1000000;

    console.log("Total Time Taken (s): ", totalTime.toFixed(2));
    console.log("Total CPU Usage (%): ", totalCPU.toFixed(2));
}

async function main(city_name, country_name, cache_path) {
    const cache = checkCache();
    const track = startPerformance();

    if (cache) {
        console.log(cache);
        return;
    }

    try {
        const {browser, page} = await setBrowser();

        await goToURL(page, city_name);
        const section = await retrieveData(page);
        await saveCache(cache_path, section);

        console.log(section);

        await browser.close();
        printPerformance(track);
    } catch (error) {
        console.error("Error in scraping: ", error);
    }
}

(async () => {
    await main(city_name, country_name, cache_path);
})();
