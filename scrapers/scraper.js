import puppeteer from "puppeteer";
import fs from "fs";
import path from "path";
import {fileURLToPath} from "url";

const filename = fileURLToPath(import.meta.url);
const directory = path.dirname(filename);

const country_name = process.argv[3];
const city_name = process.argv[2];

const cache_name =  `${city_name.toLowerCase()}-${country_name.toLowerCase()}-atlas.json`;
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
    return null;
}

async function goToURL(page, city_name, country_name, pageNo = 1){
    const url = `https://www.atlasobscura.com/things-to-do/${city_name.toLowerCase()}-${country_name.toLowerCase()}/places`;
    let nextUrl = ``;

    if (pageNo === 1) {
        nextUrl = url;
    } else {
        nextUrl = `${url}?page=${pageNo}`;
    }

    await page.goto(nextUrl);
}

async function resolveCookie(page) {
    try {
        await page.waitForSelector("#onetrust-accept-btn-handler").then(
            async () => {
                await page.click("#onetrust-accept-btn-handler");
            }
        );

        await page.waitForSelector(".fc-button.fc-cta-consent.fc-primary-button").then(
            async () => {
                await page.click(".fc-button.fc-cta-consent.fc-primary-button");
            }
        );
    } catch (error) {
        console.log("Cookie not present.");
    }
}

async function retrievePlaces(page){
    await page.waitForSelector(".geo-places .CardWrapper")

    return page.evaluate(() => {
        return Array.from(document.querySelectorAll(".geo-places .CardWrapper"))
            .map(card => ({
                name: card.querySelector(".Card__heading span")?.textContent.trim(),
                description: card.querySelector(".Card__content")?.textContent.trim()
            }))
    })
}

async function seeError(page) {
    return page.evaluate(() => {
        const errorElement = document.querySelector(".col-xs-12 .icon-atlas-icon + h2.title-lg");
        return errorElement && errorElement.innerText.includes("Something went wrong on our end.");
    });
}

async function saveCache(cache_path, data){
    fs.writeFileSync(cache_path, JSON.stringify(data, null, 2));
}

function  printPerformance(track) {
    const endTime = performance.now();
    const endCPU = process.cpuUsage(track.startCPU);

    const totalTime = (endTime - track.startTime) / 1000;
    const totalCPU = (endCPU.user + endCPU.system) / 1000000;

    console.log("Total Time Taken (s): ", totalTime)
    console.log("Total CPU Usage (%): ", totalCPU)
}

async function main(city_name, country_name, cache_path, cache_name){
    const cache = checkCache();
    const track = startPerformance();
    const {browser, page} = await setBrowser();

    let totalPlaces = [];
    let currentPage = 1;

    if (cache) {
        console.log(JSON.stringify(cache, null, 2));
    }

    try {
        const cache_directory = path.join(directory, "cache")

        if (!fs.existsSync(cache_directory)) {
            fs.mkdirSync(cache_directory, { recursive: true });
        }

        await goToURL(page, city_name, country_name);
        await resolveCookie(page);

        while (totalPlaces.length < 50) {
            const places = await retrievePlaces(page);
            totalPlaces = totalPlaces.concat(places);

            if (totalPlaces.length >= 50) {
                break;
            }

            currentPage++;
            await goToURL(page, city_name, country_name, currentPage);

            const error = await seeError(page);
            if (error) {
                break;
            }
        }

        totalPlaces = totalPlaces.slice(0, 50);

        await saveCache(cache_path, totalPlaces);

        await browser.close();
        printPerformance(track);

    } catch (error) {
        console.error("Error in scraping: ", error);
    }
}

(async () => {
    await main(city_name, country_name, cache_path, cache_name);
})();


