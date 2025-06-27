# TripGenie - AI-Powered Travel Planner

![TripGenie Logo](public/logos/logo-transparent-white.png)

## 📋 Project Overview

**TripGenie** is an intelligent web application that generates personalized travel itineraries using cutting-edge AI technology. The platform combines ChatGPT 3.5 integration with sophisticated web scraping to deliver comprehensive travel planning experiences, including real-time weather data, currency conversion, and community-sourced travel recommendations.

### 🎯 Key Features

- **AI-Powered Itineraries**: Generate custom travel plans using OpenAI's GPT-3.5
- **Real-Time Data**: Live weather updates and currency conversion
- **Web Scraping Integration**: Automatically gather travel information from multiple sources
- **User Authentication**: Secure user accounts with profile management
- **Responsive Design**: Mobile-friendly interface built with Tailwind CSS
- **Multi-Source Data**: Information from Atlas Obscura, Wikivoyage, Lonely Planet, and Yelp

## 🛠 Tech Stack

### Backend
- **Framework**: Laravel 11.x (PHP 8.2+)
- **Database**: SQLite (default) / MySQL / PostgreSQL
- **Authentication**: Laravel Breeze with Jetstream
- **AI Integration**: OpenAI PHP Client

### Frontend
- **Styling**: Tailwind CSS, Bootstrap 5.3
- **JavaScript**: Vue.js 3.x, Alpine.js
- **Build Tool**: Vite

### Web Scraping
- **Node.js Tools**: Puppeteer, Playwright
- **Data Sources**: 
  - Atlas Obscura (attractions)
  - Wikivoyage (travel guides)
  - Lonely Planet (destinations)
  - Yelp (restaurants)
  - OpenWeatherMap (weather data)

## 📦 Installation

### Prerequisites
- PHP 8.2 or higher
- Node.js 18+ and npm
- Composer
- Git

### 1. Clone the Repository
```bash
git clone https://github.com/yourusername/tripgenie.git
cd tripgenie
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node.js Dependencies
```bash
npm install
```

### 4. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Configure Environment Variables
Edit `.env` file and add your API keys:

```env
# OpenAI Configuration
OPENAI_API_KEY=your_openai_api_key_here

# Database Configuration
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=tripgenie
# DB_USERNAME=root
# DB_PASSWORD=

# Application Settings
APP_NAME="TripGenie"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### 6. Database Setup
```bash
# Create database tables
php artisan migrate

# (Optional) Seed database with sample data
php artisan db:seed
```

### 7. Build Assets
```bash
# Development build
npm run dev

# Production build
npm run build
```

### 8. Start Development Server
```bash
# Start Laravel server
php artisan serve

# In another terminal, start Vite dev server
npm run dev
```

Visit `http://localhost:8000` to access the application.

## 🚀 Usage

### Creating Your First Trip Plan

1. **Register/Login**: Create an account or sign in
2. **Access Dashboard**: Navigate to your personal dashboard
3. **Plan a Trip**: 
   - Enter destination (city and country)
   - Select travel dates
   - Choose preferences (adventure, culture, food, etc.)
4. **Generate Itinerary**: AI creates a detailed day-by-day plan
5. **View Results**: 
   - Detailed itinerary with timings
   - Real-time weather forecast
   - Currency conversion rates
   - Local attractions and restaurants

### Features Overview

- **Personalized Itineraries**: AI-generated plans based on your preferences
- **Weather Integration**: 5-day weather forecasts for your destination
- **Currency Converter**: Real-time exchange rates
- **Local Insights**: Curated recommendations from multiple travel sources
- **Profile Management**: Save and manage your travel plans

## 🔧 Configuration

### API Keys Required

1. **OpenAI API Key**: 
   - Visit [OpenAI Platform](https://platform.openai.com/)
   - Generate API key and add to `.env`

2. **Optional APIs**:
   - OpenWeatherMap (for enhanced weather data)
   - Google OAuth (for social login)

### Web Scraping Configuration

The application includes several scraping modules in the `/scrapers` directory:

- `scraper.js` - Atlas Obscura attractions
- `scraper-2.js` - Wikivoyage travel guides  
- `scraper-3.js` - Lonely Planet destinations
- `scraper-4-play.js` - Yelp restaurant data
- `weather-play.js` - Weather information
- `currency-play.js` - Currency conversion

### Database Configuration

Default: SQLite (no additional setup required)

For MySQL/PostgreSQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tripgenie
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## 📁 Project Structure

```
TripGenie/
├── app/
│   ├── Http/Controllers/
│   │   ├── Auth/          # Authentication controllers
│   │   ├── TripController.php
│   │   └── ProfileController.php
│   └── Models/
│       ├── User.php
│       └── Trip.php
├── resources/
│   ├── views/
│   │   ├── auth/          # Authentication views
│   │   ├── trip/          # Trip planning views
│   │   └── layouts/       # Layout templates
│   ├── css/
│   └── js/
├── scrapers/              # Web scraping scripts
│   ├── cache/            # Scraped data cache
│   ├── cache-play/       # Additional cache
│   └── *.js             # Scraping modules
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── logos/
│   └── images/
└── routes/
    ├── web.php
    └── auth.php
```

## 🧪 Testing

```bash
# Run PHP tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run tests with coverage
php artisan test --coverage
```

## 🚀 Deployment

### Production Setup

1. **Server Requirements**:
   - PHP 8.2+
   - Web server (Apache/Nginx)
   - Node.js (for build process)
   - Database (MySQL/PostgreSQL recommended for production)

2. **Environment Configuration**:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

3. **Build Assets**:
```bash
npm run build
```

4. **Optimize Application**:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Docker Deployment

```dockerfile
# Example Dockerfile structure
FROM php:8.2-fpm
# Add your Docker configuration
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards for PHP
- Use ESLint configuration for JavaScript
- Write tests for new features
- Update documentation for significant changes

## 📝 License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **OpenAI** - For GPT-3.5 integration
- **Laravel Community** - For the amazing framework
- **Data Sources**:
  - Atlas Obscura
  - Wikivoyage
  - Lonely Planet
  - Yelp
  - OpenWeatherMap

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/NotoriousMaka/trip-genie/issues)
- **Discussions**: [GitHub Discussions](https://github.com/NotoriousMaka/trip-genie/discussions)

## 🔮 Future Enhancements

- [ ] Integration with booking APIs (hotels, flights)
- [ ] Social features (trip sharing, reviews)
- [ ] Multi-language support
- [ ] Advanced AI chat features
- [ ] Calendar integration
- [ ] Expense tracking
- [ ] Group trip planning

---

**Happy traveling with TripGenie!** 🌍✈️

*Made with love for travelers by travelers*
