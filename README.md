# TripGenie

## Project Overview
**TripGenie** is an AI-powered web platform designed to generate custom travel itineraries tailored to each user's preferences. By integrating advanced technologies such as ChatGPT 3.5 for personalized recommendations and the Trip.com API for hotel and flight bookings, the platform provides a seamless, intuitive, and highly customizable travel planning experience. The addition of web scraping techniques enhances the platform's functionality, delivering real-time information on weather, currency conversion, and community-generated travel reviews.

This project is a learning endeavor aimed at extending knowledge in web development, AI integration, and web scraping techniques. It builds on foundational materials from a Computer Science course while introducing new challenges, such as real-time data handling, external API management, and responsive design.

---

## Features

### **Must-Have Features**
1. **AI-Generated Itineraries**  
   - Utilizes ChatGPT 3.5 to create personalized travel itineraries based on user preferences (destination, budget, and duration).  
   - Recommends activities, attractions, restaurants, and hotels tailored to user interests.

2. **Trip.com API Integration**  
   - Provides hotel and flight booking capabilities directly through the platform.  
   - Displays real-time availability and pricing for bookings.

3. **Web Scraping**  
   - Scrapes real-time weather data from reliable sources (e.g., OpenWeatherMap.org).  
   - Gathers reviews and recommendations from community-generated content (e.g., TripAdvisor, Reddit travel forums).  
   - Includes a currency conversion tool to display costs in the user's local currency.

4. **User Profile Management**  
   - Allows users to create profiles to save preferences, past itineraries, and booking details.  
   - Supports preference updates for improved itinerary personalization over time.

### **Should-Have Features**
1. **Customizable Itinerary Interface**  
   - Users can modify AI-generated itineraries by adding or removing activities, adjusting schedules, or selecting alternate attractions.  
   - Provides real-time suggestions to align new recommendations with user interests.

2. **Advanced Filtering Options**  
   - Users can filter recommendations based on travel style (e.g., adventure, history, family-friendly), price range, or specific interests.

### **Could-Have Features**
1. **Local Transportation Suggestions**  
   - Generates transportation options such as bus routes, train schedules, or car rental services.  
   - Includes eco-friendly transport suggestions to appeal to environmentally conscious users.

2. **AI-Based Travel Tips Chat**  
   - Allows users to ask location-specific questions and receive AI-generated tips or recommendations.

### **Won't-Have Features**
1. **Offline Itinerary Generation**  
   - The platform requires an active internet connection to deliver real-time data and recommendations.

2. **Payment Processing for Bookings**  
   - Users will be redirected to the Trip.com website for payments to ensure security and compliance with industry standards.

---

## Technologies Used
- **Frontend**: Responsive web design for a seamless user experience.
- **Backend**: Laravel framework for secure and efficient server-side programming.
- **AI Integration**: ChatGPT 3.5 for personalized itinerary generation.
- **APIs**: Trip.com API for hotel and flight bookings.
- **Web Scraping**: Playwright and Puppeteer (Node.js) to scrape dynamic web content.
- **External Sources**: OpenWeatherMap.org for weather data and TripAdvisor/Reddit for community reviews.

---

## Success Criteria
The success of **TripGenie** will be measured by:
1. The quality and accuracy of AI-generated itineraries.
2. Seamless integration and reliability of the Trip.com API.
3. Effective web scraping techniques for real-time weather updates, currency conversion, and community reviews.
4. A responsive and user-friendly front-end design.
5. Clean, maintainable, and robust code.
6. Positive user feedback from colleagues regarding the platform's functionality and customizability.

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
1. Create an account or log in to access personalized travel planning features.
2. Input your travel preferences (destination, budget, duration, etc.).
3. View and customize your AI-generated itinerary.
4. Explore flight and hotel options via the Trip.com integration.
5. Check real-time weather updates and currency conversions for your destination.
6. Save itineraries to your profile for future reference.

---

## Future Enhancements
- Add local transportation suggestions for eco-friendly options.
- Develop an AI-based travel tips chat feature.
- Expand API integrations to include more travel booking services.

---

## Contributing
Contributions are welcome! Please open an issue or submit a pull request to suggest improvements or report bugs.

---

## License
This project is licensed under the [MIT License](LICENSE).

---

## Acknowledgments
- [OpenAI](https://openai.com) for ChatGPT 3.5
- [Trip.com](https://www.trip.com) for their API
- [OpenWeatherMap](https://openweathermap.org) for weather data
- [Node.js](https://nodejs.org) for Playwright and Puppeteer

---

Happy traveling with **TripGenie**! 🌍✈️
