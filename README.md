![](public/logos/logo-transparent.png)

## Project Overview
**TripGenie** is an AI-powered web platform designed to generate custom travel itineraries tailored to each user's preferences. By integrating advanced technologies such as ChatGPT 3.5 for personalised recommendations and the Trip.com API for hotel and flight bookings, the platform provides a seamless, intuitive, and highly customisable travel planning experience. The addition of web scraping techniques enhances the platform's functionality, delivering real-time information on weather, currency conversion, and community-generated travel reviews.

This project is a learning endeavor aimed at extending knowledge in web development, AI integration, and web scraping techniques. It builds on foundational materials from a Computer Science course while introducing new challenges, such as real-time data handling, external API management, and responsive design.

---

## Technologies Used
- **Frontend**: Responsive web design for a seamless user experience.
- **Backend**: Laravel framework for secure and efficient server-side programming.
- **AI Integration**: ChatGPT 3.5 for personalised itinerary generation.
- **Web Scraping**: Playwright and Puppeteer (Node.js) to scrape dynamic web content.
- **External Sources**: OpenWeatherMap.org for weather data and TripAdvisor/Reddit for community reviews.

---

## Success Criteria
The success of **TripGenie** will be measured by:
1. The quality and accuracy of AI-generated itineraries.
2. Effective web scraping techniques for real-time weather updates, currency conversion, and community reviews.
3. A responsive and user-friendly front-end design.
4. Clean, maintainable, and robust code.
5. Positive user feedback from colleagues regarding the platform's functionality and customisability.

---

## Legal and Ethical Considerations
- Ensures compliance with web scraping and data privacy laws.
- Adheres to ethical practices by using reliable sources and providing credit where applicable.

---

## Installation and Setup
1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/tripgenie.git
   ```
2. Navigate to the project directory:
   ```bash
   cd tripgenie
   ```
3. Install dependencies:
   ```bash
   composer install
   npm install
   ```
4. Set up the environment variables:
   - Create a `.env` file and configure the required API keys (e.g., ChatGPT API, Trip.com API, OpenWeatherMap API).
5. Run migrations:
   ```bash
   php artisan migrate
   ```
6. Start the development server:
   ```bash
   php artisan serve
   npm run dev
   ```

---

## Usage
1. Create an account or log in to access personalised travel planning features.
2. Input your travel preferences (destination, budget, duration, etc.).
3. View and customise your AI-generated itinerary.
4. Check real-time weather updates and currency conversions for your destination.
5. Save itineraries to your profile for future reference.

---

## Future Enhancements
- Add local transportation suggestions for eco-friendly options.
- Integrate a booking API for hotels, flights and others.
- Develop an AI-based travel tips chat feature.

---

## Contributing
Contributions are welcome! Please open an issue or submit a pull request to suggest improvements or report bugs.

---

## License
This project is licensed under the [MIT License](LICENSE).

---

## Acknowledgments
- [OpenAI](https://openai.com) for ChatGPT 3.5
- [OpenWeatherMap](https://openweathermap.org) for weather data
- [Node.js](https://nodejs.org) for Playwright and Puppeteer

---

Happy traveling with **TripGenie**! 🌍✈️
