<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Micro SaaS Pricing Analytics - Laravel + Filament</title>
    
    <!-- Meta Tags -->
    <meta name="description" content="Advanced price tracking and analytics platform built with Laravel + Filament. Monitor prices across categories, track historical trends, and analyze market volatility.">
    <meta name="keywords" content="pricing analytics, price tracking, laravel, filament, data analytics, kaggle datasets">
    <meta name="author" content="Micro SaaS Pricing Analytics">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Micro SaaS Pricing Analytics">
    <meta property="og:description" content="Transform your pricing data into actionable insights with our comprehensive analytics platform">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <style>
        /* RTL Support */
        [dir="rtl"] {
            direction: rtl;
        }
        
        /* Arabic Font */
        .font-arabic {
            font-family: 'Noto Sans Arabic', sans-serif;
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Loading animation */
        .loading-spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #4f46e5;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Fallback styles */
        body {
            font-family: 'Figtree', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
            min-height: 100vh;
        }
        
        .app-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
        }
        
        .app-loading h1 {
            color: #1f2937;
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .app-loading p {
            color: #6b7280;
            font-size: 1.125rem;
            margin-bottom: 2rem;
            text-align: center;
            max-width: 500px;
        }
    </style>
    
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body>
    <div id="app">
        <!-- Loading fallback -->
        <div class="app-loading">
            <h1>🚀 Micro SaaS Pricing Analytics</h1>
            <p>Advanced price tracking and analytics platform built with Laravel + Filament</p>
            <div class="loading-spinner"></div>
            <p style="margin-top: 1rem; font-size: 0.875rem;">Loading React application...</p>
        </div>
    </div>

    <!-- Schema.org structured data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Micro SaaS Pricing Analytics",
        "description": "Advanced price tracking and analytics platform built with Laravel + Filament",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        },
        "creator": {
            "@type": "Organization",
            "name": "Micro SaaS Pricing Analytics"
        }
    }
    </script>
</body>
</html> 