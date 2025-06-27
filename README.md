# 🚀  Pricing Analytics System

[![Laravel](https://img.shields.io/badge/Laravel-11-red.svg)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3-orange.svg)](https://filamentphp.com)
[![React](https://img.shields.io/badge/React-18-blue.svg)](https://reactjs.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3-blue.svg)](https://tailwindcss.com)

A comprehensive ** Pricing Analytics System** built with Laravel 11 + Filament 3, featuring a beautiful React landing page with Arabic/English support, advanced data analytics, and ETL pipeline for processing large datasets.

## ✨ **Live Demo**

- **🏠 Landing Page**: Beautiful bilingual homepage with project showcase
- **📊 Admin Dashboard**: `/admin` - Complete analytics dashboard with 5+ widgets
- **🎯 Demo Data**: 60+ products, 10 categories, 23,000+ price records over 3 years

## 🌟 **Key Features**

### 🎨 **Beautiful Landing Page**
- **🌐 Bilingual Support**: English & Arabic with RTL layout
- **📱 Fully Responsive**: Mobile-first design
- **🎯 SEO Optimized**: Meta tags, structured data, Open Graph
- **⚡ Fast Loading**: Vite-powered build system

### 📊 **Advanced Analytics Dashboard**
- **📈 Price History Trends**: Multi-line charts with time filtering
- **📋 Category Comparison**: Bar charts for price analysis
- **🎯 Volatility Analysis**: Doughnut charts using standard deviation
- **📊 Metrics Overview**: KPIs and real-time statistics
- **📁 Import Management**: Progress tracking and error handling

### 🔄 **Data Processing & ETL**
- **📥 Kaggle Dataset Support**: Process CSV files with millions of records
- **🔄 ETL Pipeline**: Automated data transformation and validation
- **🏪 Data Warehouse**: Star schema with dimension and fact tables
- **⚡ Queue Processing**: Background jobs with Laravel Queues
- **🗺️ Province Mapping**: Canadian provinces to store mapping
- **🏷️ SKU Generation**: Automatic product identification

### 🛠️ **Technical Stack**
- **Backend**: Laravel 11, MySQL, Queue Jobs
- **Frontend**: React 18, Tailwind CSS, Heroicons
- **Admin**: Filament 3 with custom widgets
- **Build Tools**: Vite, NPM
- **Analytics**: Custom widgets and charts

## 📁 **Project Structure**

```
├── app/
│   ├── Filament/
│   │   ├── Resources/         # Product, Category, Brand, Store resources
│   │   └── Widgets/           # 5+ custom analytics widgets
│   ├── Jobs/                  # ETL and import processing jobs
│   ├── Models/                # Eloquent models with relationships
│   └── Services/              # KaggleEtlService for data transformation
├── resources/
│   ├── js/
│   │   ├── Components/
│   │   │   └── LandingPage.jsx  # Bilingual React landing page
│   │   └── app.jsx             # React app entry point
│   ├── css/app.css             # Tailwind CSS + custom styles
│   └── views/landing.blade.php  # Laravel Blade template
├── database/
│   ├── migrations/            # Database schema
│   └── seeders/              # Demo data generation
├── storage/app/
│   ├── imports/              # CSV import directory
│   └── exports/              # Data export directory
└── routes/web.php            # Application routes
```

## 🚀 **Quick Start**

### 1. **Clone Repository**
```bash
git clone https://github.com/i2vc7/Pricing_system.git
cd Pricing_system
```

### 2. **Install Dependencies**
```bash
# PHP dependencies
composer install

# Node.js dependencies
npm install
```

### 3. **Environment Setup**
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env file
DB_DATABASE=pricing_system
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. **Database Setup**
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE pricing_system"

# Run migrations
php artisan migrate

# Seed demo data (60 products, 23K+ records)
php artisan db:seed --class=KaggleDemoSeeder
```

### 5. **Build Assets**
```bash
# Build React landing page
npm run build

# Or for development with hot reload
npm run dev
```

### 6. **Start Application**
```bash
# Start Laravel server
php artisan serve

# Start queue worker (for data processing)
php artisan queue:work
```

### 7. **Access Application**
- **Landing Page**: http://localhost:8000
- **Admin Dashboard**: http://localhost:8000/admin

## 🎯 **System Commands**

### **Demo Data Management**
```bash
# Generate demo dataset (60 products)
php artisan db:seed --class=KaggleDemoSeeder

# Import CSV data
php artisan kaggle:import path/to/file.csv --max-rows=1000

# Process background jobs
php artisan queue:work
```

### **Data Warehouse Operations**
```bash
# Run ETL pipeline
php artisan warehouse:sync

# Clear import cache
php artisan cache:clear
```

## 📊 **Dashboard Widgets**

### 1. **📈 Pricing Metrics Overview**
- Total products count
- Average price across categories
- Daily price updates
- Highest/lowest prices
- Market volatility metrics
- Data coverage statistics

### 2. **📊 Price History Trends**
- Multi-line charts for top 5 products
- Time-based filtering (7 days, 30 days, 90 days, 1 year)
- Interactive hover tooltips
- Trend analysis

### 3. **📋 Category Price Comparison**
- Bar charts with multiple metrics
- Average, highest, lowest prices
- Price range analysis
- Category-wise comparison

### 4. **🎯 Price Volatility Analysis**
- Doughnut charts showing volatility distribution
- Standard deviation calculations
- High/medium/low volatility products
- Risk assessment metrics

### 5. **📁 Data Import Management**
- Import history tracking
- Processing progress monitoring
- Error handling and reporting
- File upload management

## 🌐 **Landing Page Features**

### **Bilingual Support**
- **English**: Default language with LTR layout
- **Arabic**: RTL layout with proper Arabic typography
- **Language Toggle**: One-click switching
- **Font Integration**: Noto Sans Arabic for beautiful typography

### **Content Sections**
1. **Hero Section**: Compelling headline with stats
2. **Features Overview**: 6 key features explained
3. **Dashboard Features**: 5 widgets showcased
4. **Data Warehouse**: ETL pipeline explanation
5. **Tech Stack**: Technology showcase
6. **System Commands**: Developer-friendly commands
7. **Call-to-Action**: Direct links to admin dashboard

## 🗃️ **Database Schema**

### **Core Tables**
- `products`: Product information and current prices
- `categories`: Product categorization
- `brands`: Brand management
- `stores`: Store/location data
- `price_histories`: Historical price tracking

### **Data Warehouse**
- `dim_products`: Product dimensions
- `dim_dates`: Date dimensions
- `fact_price_changes`: Price change facts

### **Import Management**
- `data_imports`: Import tracking and status
- `failed_jobs`: Failed job monitoring

## 📈 **Sample Data**

The system comes with comprehensive demo data:
- **60 Products** across 10 categories
- **23,000+ Price Records** over 3 years
- **10 Categories**: Electronics, Clothing, Food, Books, Toys, etc.
- **Realistic Price Variations**: Market-like fluctuations
- **13 Canadian Provinces**: Geographic distribution

## 🔧 **Customization**

### **Adding New Widgets**
1. Create widget class in `app/Filament/Widgets/`
2. Register in `AdminPanelProvider`
3. Add to dashboard configuration

### **Extending ETL Pipeline**
1. Modify `KaggleEtlService` for data transformation
2. Update `ProcessKaggleDatasetJob` for processing logic
3. Add new validation rules

### **Landing Page Customization**
1. Edit `resources/js/Components/LandingPage.jsx`
2. Update translations for new languages
3. Modify styling in `resources/css/app.css`

## 🛡️ **Security Features**

- **CSRF Protection**: Laravel's built-in CSRF tokens
- **SQL Injection Prevention**: Eloquent ORM protection
- **XSS Protection**: Input sanitization
- **Authentication**: Filament admin authentication
- **Rate Limiting**: API rate limiting

## 🚀 **Performance Optimization**

- **Database Indexing**: Optimized queries
- **Eager Loading**: Reduced N+1 queries
- **Queue Processing**: Background job processing
- **Asset Optimization**: Vite build optimization
- **Caching**: Application and query caching

## 📝 **API Documentation**

The system includes RESTful APIs for:
- Product data retrieval
- Price history access
- Category information
- Import status tracking

## 🤝 **Contributing**

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📄 **License**

This project is open-source and available under the [MIT License](LICENSE).

## 🙏 **Acknowledgments**

- **Laravel Team**: Amazing PHP framework
- **Filament**: Beautiful admin interface
- **React Team**: Powerful frontend library
- **Tailwind CSS**: Utility-first CSS framework
- **Heroicons**: Beautiful SVG icons

## 📞 **Support**

- **Issues**: [GitHub Issues](https://github.com/i2vc7/Pricing_system/issues)
- **Discussions**: [GitHub Discussions](https://github.com/i2vc7/Pricing_system/discussions)
- **Email**: Support via GitHub

## 🌟 **Show Your Support**

Give a ⭐️ if this project helped you!

---

**Built with ❤️ by [i2vc7](https://github.com/i2vc7) for the developer community**
