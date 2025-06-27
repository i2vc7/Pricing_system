import React, { useState } from 'react';
import { 
    ChartBarIcon, 
    CubeIcon, 
    ArrowTrendingUpIcon, 
    DocumentChartBarIcon, 
    GlobeAltIcon, 
    CloudArrowUpIcon,
    PresentationChartLineIcon,
    TableCellsIcon
} from '@heroicons/react/24/outline';

const LandingPage = () => {
    const [language, setLanguage] = useState('en');

    const translations = {
        en: {
            title: "Micro SaaS Pricing Analytics",
            subtitle: "Advanced price tracking and analytics platform built with Laravel + Filament",
            
            // Hero Section
            heroTitle: "Transform Your Pricing Data Into Actionable Insights",
            heroDescription: "Monitor prices across categories, track historical trends, and analyze market volatility with our powerful analytics dashboard featuring 60+ products and 23,000+ price records.",
            viewDashboard: "View Admin Dashboard",
            learnMore: "Learn More",
            
            // Stats
            statsProducts: "60+ Products",
            statsCategories: "10 Categories", 
            statsPriceRecords: "23,000+ Records",
            statsTimeRange: "3 Years Data",
            
            // Features
            featuresTitle: "Powerful Features",
            featuresSubtitle: "Everything you need for comprehensive pricing analytics",
            
            feature1Title: "Real-time Analytics",
            feature1Desc: "Live dashboard with comprehensive pricing metrics, KPIs, and volatility tracking",
            
            feature2Title: "Kaggle Data Processing",
            feature2Desc: "ETL pipeline for processing large CSV datasets with automated transformation and validation",
            
            feature3Title: "Advanced Charts",
            feature3Desc: "5 widget types: metrics overview, price trends, category comparison, volatility analysis",
            
            feature4Title: "Multi-category Support", 
            feature4Desc: "Track 10+ product categories: Electronics, Clothing, Food, Books, Toys, and more",
            
            feature5Title: "Historical Analysis",
            feature5Desc: "3+ years of price history with time-based filtering and trend analysis",
            
            feature6Title: "Data Import System",
            feature6Desc: "Queue-based CSV processing with progress tracking and error handling",
            
            // Dashboard Features
            dashboardTitle: "Admin Dashboard Features",
            dashboardSubtitle: "Built with Filament 3 - Comprehensive analytics and management interface",
            
            widget1: "📊 Pricing Metrics Overview",
            widget1Desc: "Total products, average prices, daily updates, highest prices, volatility metrics, data coverage statistics",
            
            widget2: "📈 Price History Trends", 
            widget2Desc: "Multi-line charts showing price trends for top 5 products with time filtering options",
            
            widget3: "📋 Category Price Comparison",
            widget3Desc: "Bar charts comparing average, highest, lowest, and price range across all categories",
            
            widget4: "🎯 Price Volatility Analysis",
            widget4Desc: "Doughnut charts identifying products with highest price fluctuations using standard deviation",
            
            widget5: "📁 Data Import Management",
            widget5Desc: "Track import history, monitor processing progress, handle errors, manage file uploads",
            
            // Data Warehouse
            warehouseTitle: "Data Warehouse & ETL",
            warehouseSubtitle: "Star schema design for scalable analytics",
            
            warehouse1: "🏪 Dimension Tables",
            warehouse1Desc: "DimProduct, DimDate for optimized query performance",
            
            warehouse2: "📊 Fact Tables", 
            warehouse2Desc: "FactPriceChange tracking all price movements and changes",
            
            warehouse3: "🔄 ETL Pipeline",
            warehouse3Desc: "Automated data transformation with province mapping and SKU generation",
            
            // Tech Stack
            techTitle: "Built With Modern Technology",
            techSubtitle: "Scalable architecture for processing millions of records",
            
            // Dataset Info
            datasetTitle: "Demo Dataset Information",
            datasetSubtitle: "Synthetic data designed to showcase system capabilities",
            
            datasetInfo: "Our demo includes 60 products across 10 categories with realistic price variations over 3 years. The system is designed to work with real Kaggle datasets like 'Product Retail Prices per month (2017–2025)' containing millions of records.",
            
            // Commands
            commandsTitle: "Key System Commands",
            commandsSubtitle: "Essential commands for data management",
            
            // CTA
            ctaTitle: "Ready to Analyze Your Pricing Data?",
            ctaDesc: "Start monitoring prices and generating insights with our comprehensive analytics platform",
            getStarted: "Access Dashboard",
            
            // Footer
            footerText: "Micro SaaS Pricing Analytics - Open source solution for the community",
        },
        ar: {
            title: "نظام تحليل الأسعار الذكي",
            subtitle: "منصة متقدمة لتتبع الأسعار والتحليلات مبنية بـ Laravel + Filament",
            
            // Hero Section
            heroTitle: "حول بيانات الأسعار إلى رؤى قابلة للتنفيذ",
            heroDescription: "راقب الأسعار عبر الفئات، تتبع الاتجاهات التاريخية، وحلل تقلبات السوق مع لوحة التحليلات القوية التي تضم أكثر من 60 منتج و 23,000 سجل سعر.",
            viewDashboard: "عرض لوحة الإدارة",
            learnMore: "تعلم المزيد",
            
            // Stats
            statsProducts: "60+ منتج",
            statsCategories: "10 فئات",
            statsPriceRecords: "23,000+ سجل",
            statsTimeRange: "3 سنوات بيانات",
            
            // Features
            featuresTitle: "ميزات قوية",
            featuresSubtitle: "كل ما تحتاجه لتحليل شامل للأسعار",
            
            feature1Title: "تحليلات فورية",
            feature1Desc: "لوحة معلومات مباشرة مع مقاييس شاملة للأسعار ومؤشرات الأداء وتتبع التقلبات",
            
            feature2Title: "معالجة بيانات Kaggle",
            feature2Desc: "خط أنابيب ETL لمعالجة مجموعات بيانات CSV كبيرة مع التحويل والتحقق الآلي",
            
            feature3Title: "مخططات متقدمة",
            feature3Desc: "5 أنواع أدوات: نظرة عامة على المقاييس، اتجاهات الأسعار، مقارنة الفئات، تحليل التقلبات",
            
            feature4Title: "دعم متعدد الفئات",
            feature4Desc: "تتبع أكثر من 10 فئات منتجات: الإلكترونيات، الملابس، الطعام، الكتب، الألعاب، والمزيد",
            
            feature5Title: "التحليل التاريخي", 
            feature5Desc: "أكثر من 3 سنوات من تاريخ الأسعار مع التصفية الزمنية وتحليل الاتجاهات",
            
            feature6Title: "نظام استيراد البيانات",
            feature6Desc: "معالجة CSV قائمة على الطوابير مع تتبع التقدم ومعالجة الأخطاء",
            
            // Dashboard Features
            dashboardTitle: "ميزات لوحة الإدارة",
            dashboardSubtitle: "مبنية بـ Filament 3 - واجهة شاملة للتحليلات والإدارة",
            
            widget1: "📊 نظرة عامة على مقاييس الأسعار",
            widget1Desc: "إجمالي المنتجات، متوسط الأسعار، التحديثات اليومية، أعلى الأسعار، مقاييس التقلبات، إحصائيات تغطية البيانات",
            
            widget2: "📈 اتجاهات تاريخ الأسعار",
            widget2Desc: "مخططات متعددة الخطوط تُظهر اتجاهات الأسعار لأفضل 5 منتجات مع خيارات التصفية الزمنية",
            
            widget3: "📋 مقارنة أسعار الفئات",
            widget3Desc: "مخططات شريطية تقارن متوسط وأعلى وأدنى ونطاق الأسعار عبر جميع الفئات",
            
            widget4: "🎯 تحليل تقلبات الأسعار",
            widget4Desc: "مخططات دائرية تحدد المنتجات ذات أعلى تقلبات في الأسعار باستخدام الانحراف المعياري",
            
            widget5: "📁 إدارة استيراد البيانات",
            widget5Desc: "تتبع تاريخ الاستيراد، مراقبة تقدم المعالجة، معالجة الأخطاء، إدارة تحميل الملفات",
            
            // Data Warehouse
            warehouseTitle: "مستودع البيانات والـ ETL",
            warehouseSubtitle: "تصميم المخطط النجمي للتحليلات القابلة للتطوير",
            
            warehouse1: "🏪 جداول الأبعاد",
            warehouse1Desc: "DimProduct، DimDate لأداء استعلام محسّن",
            
            warehouse2: "📊 جداول الحقائق",
            warehouse2Desc: "FactPriceChange لتتبع جميع حركات وتغييرات الأسعار",
            
            warehouse3: "🔄 خط أنابيب ETL",
            warehouse3Desc: "تحويل البيانات الآلي مع رسم خرائط المقاطعات وتوليد SKU",
            
            // Tech Stack
            techTitle: "مبني بالتكنولوجيا الحديثة",
            techSubtitle: "هندسة قابلة للتطوير لمعالجة ملايين السجلات",
            
            // Dataset Info
            datasetTitle: "معلومات مجموعة البيانات التجريبية",
            datasetSubtitle: "بيانات اصطناعية مصممة لعرض قدرات النظام",
            
            datasetInfo: "تشمل نسختنا التجريبية 60 منتجًا عبر 10 فئات مع تغييرات أسعار واقعية على مدى 3 سنوات. النظام مصمم للعمل مع مجموعات بيانات Kaggle الحقيقية مثل 'أسعار التجزئة للمنتجات شهريًا (2017-2025)' التي تحتوي على ملايين السجلات.",
            
            // Commands
            commandsTitle: "أوامر النظام الرئيسية",
            commandsSubtitle: "الأوامر الأساسية لإدارة البيانات",
            
            // CTA
            ctaTitle: "جاهز لتحليل بيانات الأسعار؟",
            ctaDesc: "ابدأ في مراقبة الأسعار وتوليد الرؤى مع منصة التحليلات الشاملة",
            getStarted: "الوصول للوحة التحكم",
            
            // Footer
            footerText: "نظام تحليل الأسعار الذكي - حل مفتوح المصدر للمجتمع",
        }
    };

    const t = translations[language];

    const features = [
        {
            icon: <ChartBarIcon className="w-8 h-8" />,
            titleKey: 'feature1Title',
            descKey: 'feature1Desc'
        },
        {
            icon: <CloudArrowUpIcon className="w-8 h-8" />,
            titleKey: 'feature2Title',
            descKey: 'feature2Desc'
        },
        {
            icon: <DocumentChartBarIcon className="w-8 h-8" />,
            titleKey: 'feature3Title',
            descKey: 'feature3Desc'
        },
        {
            icon: <CubeIcon className="w-8 h-8" />,
            titleKey: 'feature4Title',
            descKey: 'feature4Desc'
        },
        {
            icon: <ArrowTrendingUpIcon className="w-8 h-8" />,
            titleKey: 'feature5Title',
            descKey: 'feature5Desc'
        },
        {
            icon: <GlobeAltIcon className="w-8 h-8" />,
            titleKey: 'feature6Title',
            descKey: 'feature6Desc'
        }
    ];

    const dashboardFeatures = [
        { titleKey: 'widget1', descKey: 'widget1Desc' },
        { titleKey: 'widget2', descKey: 'widget2Desc' },
        { titleKey: 'widget3', descKey: 'widget3Desc' },
        { titleKey: 'widget4', descKey: 'widget4Desc' },
        { titleKey: 'widget5', descKey: 'widget5Desc' }
    ];

    const warehouseFeatures = [
        { titleKey: 'warehouse1', descKey: 'warehouse1Desc' },
        { titleKey: 'warehouse2', descKey: 'warehouse2Desc' },
        { titleKey: 'warehouse3', descKey: 'warehouse3Desc' }
    ];

    const commands = [
        "php artisan db:seed --class=KaggleDemoSeeder",
        "php artisan kaggle:import file.csv --max-rows=1000",
        "php artisan queue:work"
    ];

    return (
        <div className={`min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 ${language === 'ar' ? 'rtl font-arabic' : 'ltr'}`}>
            {/* Header */}
            <header className="bg-white shadow-sm sticky top-0 z-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center py-4">
                        <div className="flex items-center">
                            <ChartBarIcon className="w-8 h-8 text-indigo-600 mr-3" />
                            <h1 className="text-lg font-bold text-gray-900">{t.title}</h1>
                        </div>
                        <div className="flex items-center space-x-4">
                            <button
                                onClick={() => setLanguage(language === 'en' ? 'ar' : 'en')}
                                className="px-3 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors border rounded-md"
                            >
                                {language === 'en' ? 'العربية' : 'English'}
                            </button>
                            <a 
                                href="/admin" 
                                className="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors font-medium"
                            >
                                {t.viewDashboard}
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            {/* Hero Section */}
            <section className="py-20">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 className="text-4xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                        {t.heroTitle}
                    </h2>
                    <p className="text-xl text-gray-600 mb-8 max-w-4xl mx-auto leading-relaxed">
                        {t.heroDescription}
                    </p>
                    
                    {/* Stats */}
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-6 mb-12 max-w-2xl mx-auto">
                        <div className="bg-white p-4 rounded-lg shadow-sm">
                            <div className="text-2xl font-bold text-indigo-600">{t.statsProducts}</div>
                        </div>
                        <div className="bg-white p-4 rounded-lg shadow-sm">
                            <div className="text-2xl font-bold text-green-600">{t.statsCategories}</div>
                        </div>
                        <div className="bg-white p-4 rounded-lg shadow-sm">
                            <div className="text-2xl font-bold text-purple-600">{t.statsPriceRecords}</div>
                        </div>
                        <div className="bg-white p-4 rounded-lg shadow-sm">
                            <div className="text-2xl font-bold text-orange-600">{t.statsTimeRange}</div>
                        </div>
                    </div>

                    <div className="flex flex-col sm:flex-row gap-4 justify-center">
                        <a 
                            href="/admin" 
                            className="bg-indigo-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-indigo-700 transition-colors inline-flex items-center justify-center"
                        >
                            <PresentationChartLineIcon className="w-5 h-5 mr-2" />
                            {t.getStarted}
                        </a>
                        <button 
                            onClick={() => document.getElementById('features').scrollIntoView({ behavior: 'smooth' })}
                            className="border border-gray-300 text-gray-700 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-50 transition-colors"
                        >
                            {t.learnMore}
                        </button>
                    </div>
                </div>
            </section>

            {/* Features Section */}
            <section id="features" className="py-20 bg-white">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h3 className="text-3xl font-bold text-gray-900 mb-4">{t.featuresTitle}</h3>
                        <p className="text-xl text-gray-600">{t.featuresSubtitle}</p>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {features.map((feature, index) => (
                            <div key={index} className="p-6 rounded-lg border border-gray-200 hover:shadow-lg transition-all duration-300 hover:border-indigo-300">
                                <div className="text-indigo-600 mb-4">{feature.icon}</div>
                                <h4 className="text-xl font-semibold mb-3">{t[feature.titleKey]}</h4>
                                <p className="text-gray-600 leading-relaxed">{t[feature.descKey]}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Dashboard Features */}
            <section className="py-20 bg-gray-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h3 className="text-3xl font-bold text-gray-900 mb-4">{t.dashboardTitle}</h3>
                        <p className="text-xl text-gray-600">{t.dashboardSubtitle}</p>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {dashboardFeatures.map((feature, index) => (
                            <div key={index} className="bg-white p-6 rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                                <h4 className="text-lg font-semibold mb-3 text-indigo-600">{t[feature.titleKey]}</h4>
                                <p className="text-gray-600 leading-relaxed">{t[feature.descKey]}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Data Warehouse Section */}
            <section className="py-20 bg-white">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h3 className="text-3xl font-bold text-gray-900 mb-4">{t.warehouseTitle}</h3>
                        <p className="text-xl text-gray-600">{t.warehouseSubtitle}</p>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {warehouseFeatures.map((feature, index) => (
                            <div key={index} className="p-6 rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 border hover:shadow-lg transition-shadow">
                                <h4 className="text-lg font-semibold mb-3 text-gray-800">{t[feature.titleKey]}</h4>
                                <p className="text-gray-600 leading-relaxed">{t[feature.descKey]}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Dataset Info */}
            <section className="py-20 bg-indigo-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h3 className="text-3xl font-bold text-gray-900 mb-4">{t.datasetTitle}</h3>
                    <p className="text-xl text-gray-600 mb-8">{t.datasetSubtitle}</p>
                    <p className="text-lg text-gray-700 max-w-4xl mx-auto leading-relaxed">{t.datasetInfo}</p>
                </div>
            </section>

            {/* Commands Section */}
            <section className="py-20 bg-gray-900 text-white">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h3 className="text-3xl font-bold mb-4">{t.commandsTitle}</h3>
                        <p className="text-xl text-gray-300">{t.commandsSubtitle}</p>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {commands.map((command, index) => (
                            <div key={index} className="bg-gray-800 p-4 rounded-lg border border-gray-700">
                                <code className="text-green-400 text-sm font-mono break-all">{command}</code>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Tech Stack */}
            <section className="py-20 bg-white">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h3 className="text-3xl font-bold text-gray-900 mb-4">{t.techTitle}</h3>
                    <p className="text-xl text-gray-600 mb-12">{t.techSubtitle}</p>
                    <div className="grid grid-cols-2 md:grid-cols-5 gap-8">
                        <div className="p-6 bg-gradient-to-br from-red-50 to-red-100 rounded-lg">
                            <div className="text-2xl font-bold text-red-600 mb-2">Laravel 11</div>
                            <p className="text-gray-600 text-sm">Backend Framework</p>
                        </div>
                        <div className="p-6 bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg">
                            <div className="text-2xl font-bold text-amber-600 mb-2">Filament 3</div>
                            <p className="text-gray-600 text-sm">Admin Interface</p>
                        </div>
                        <div className="p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg">
                            <div className="text-2xl font-bold text-blue-600 mb-2">React</div>
                            <p className="text-gray-600 text-sm">Frontend Framework</p>
                        </div>
                        <div className="p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-lg">
                            <div className="text-2xl font-bold text-green-600 mb-2">MySQL</div>
                            <p className="text-gray-600 text-sm">Database</p>
                        </div>
                        <div className="p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg">
                            <div className="text-2xl font-bold text-purple-600 mb-2">Queue Jobs</div>
                            <p className="text-gray-600 text-sm">Background Processing</p>
                        </div>
                    </div>
                </div>
            </section>

            {/* CTA Section */}
            <section className="py-20 bg-gradient-to-r from-indigo-600 to-purple-600">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h3 className="text-3xl font-bold text-white mb-4">{t.ctaTitle}</h3>
                    <p className="text-xl text-indigo-100 mb-8 max-w-2xl mx-auto">{t.ctaDesc}</p>
                    <a 
                        href="/admin" 
                        className="bg-white text-indigo-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-100 transition-colors inline-flex items-center"
                    >
                        <TableCellsIcon className="w-5 h-5 mr-2" />
                        {t.getStarted}
                    </a>
                </div>
            </section>

            {/* Footer */}
            <footer className="bg-gray-900 py-12">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center">
                        <div className="flex items-center justify-center mb-4">
                            <ChartBarIcon className="w-8 h-8 text-indigo-400 mr-3" />
                            <span className="text-xl font-bold text-white">{t.title}</span>
                        </div>
                        <p className="text-gray-400 mb-6">{t.footerText}</p>
                        <div className="flex justify-center space-x-6">
                            <a href="/admin" className="text-gray-400 hover:text-white transition-colors">Dashboard</a>
                            <a href="https://github.com" className="text-gray-400 hover:text-white transition-colors">GitHub</a>
                            <a href="https://laravel.com" className="text-gray-400 hover:text-white transition-colors">Laravel</a>
                            <a href="https://filamentphp.com" className="text-gray-400 hover:text-white transition-colors">Filament</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    );
};

export default LandingPage; 