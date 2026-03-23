<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartFarm - Professional Farm Record Keeping System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Landing Page Specific Styles */
        .landing-page {
            background: linear-gradient(135deg, #4a7c59 0%, #8b7355 100%);
            min-height: 100vh;
            color: white;
        }
        
        .nav-landing {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        .nav-landing .nav-brand {
            font-size: 1.8rem;
            font-weight: bold;
            color: white;
        }
        
        .nav-landing .nav-actions {
            display: flex;
            gap: 1rem;
        }
        
        .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-outline:hover {
            background: white;
            color: var(--primary);
        }
        
        .hero-section {
            padding: 8rem 2rem 4rem;
            text-align: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .hero-title {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            animation: fadeInUp 0.8s ease;
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            animation: fadeInUp 0.8s ease 0.2s both;
        }
        
        .hero-description {
            font-size: 1.1rem;
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            opacity: 0.8;
            animation: fadeInUp 0.8s ease 0.4s both;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease 0.6s both;
        }
        
        .btn-hero {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 30px;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-primary-hero {
            background: white;
            color: var(--primary);
        }
        
        .btn-primary-hero:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .btn-secondary-hero {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-secondary-hero:hover {
            background: white;
            color: var(--primary);
        }
        
        .features-section {
            background: white;
            padding: 5rem 2rem;
            color: var(--text);
        }
        
        .features-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .features-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .features-title {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .features-subtitle {
            font-size: 1.2rem;
            color: var(--text-light);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .feature-card {
            background: var(--bg);
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .feature-title {
            font-size: 1.3rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .feature-description {
            color: var(--text-light);
            line-height: 1.6;
        }
        
        .stats-section {
            background: var(--primary);
            padding: 4rem 2rem;
            color: white;
        }
        
        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }
        
        .stat-item {
            padding: 1rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .cta-section {
            background: var(--secondary);
            padding: 4rem 2rem;
            text-align: center;
            color: white;
        }
        
        .cta-title {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .cta-description {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .footer {
            background: #333;
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.2rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .nav-landing {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body class="landing-page">
    <!-- Navigation -->
    <nav class="nav-landing">
        <div class="nav-brand">🌾 SmartFarm</div>
        <div class="nav-actions">
            <a href="login.php" class="btn-outline">Sign In</a>
            <a href="register.php" class="btn btn-primary">Get Started</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <h1 class="hero-title">Transform Your Farm Management</h1>
        <p class="hero-subtitle">Digital Solutions for Modern Agriculture</p>
        <p class="hero-description">
            SmartFarm is your complete farm record keeping system. Track livestock, manage crops, 
            monitor finances, and analyze your farm's performance - all in one powerful platform.
        </p>
        <div class="hero-buttons">
            <a href="register.php" class="btn-hero btn-primary-hero">Start Free Trial</a>
            <a href="#features" class="btn-hero btn-secondary-hero">Learn More</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="features-container">
            <div class="features-header">
                <h2 class="features-title">Everything You Need to Manage Your Farm</h2>
                <p class="features-subtitle">Powerful features designed for farmers, by farmers</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <span class="feature-icon">🐄</span>
                    <h3 class="feature-title">Livestock Management</h3>
                    <p class="feature-description">
                        Track your animals' health, breeding records, vaccination schedules, and performance metrics. 
                        Monitor individual animals or entire herds with detailed analytics.
                    </p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">🌱</span>
                    <h3 class="feature-title">Crop Tracking</h3>
                    <p class="feature-description">
                        Manage planting schedules, monitor growth stages, track yields, and optimize your 
                        crop rotation. Get insights into soil health and weather patterns.
                    </p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">✓</span>
                    <h3 class="feature-title">Task Management</h3>
                    <p class="feature-description">
                        Organize daily farm activities, set reminders for important tasks, 
                        and track work progress. Never miss feeding times or maintenance schedules.
                    </p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">💰</span>
                    <h3 class="feature-title">Financial Tracking</h3>
                    <p class="feature-description">
                        Monitor income and expenses, track profit margins, and generate financial reports. 
                        Make data-driven decisions to maximize your farm's profitability.
                    </p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">📊</span>
                    <h3 class="feature-title">Analytics Dashboard</h3>
                    <p class="feature-description">
                        Visualize your farm's performance with interactive charts and reports. 
                        Identify trends, track growth, and make informed decisions.
                    </p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">📱</span>
                    <h3 class="feature-title">Mobile Friendly</h3>
                    <p class="feature-description">
                        Access your farm data from anywhere. Works seamlessly on desktop, tablet, 
                        and mobile devices. Manage your farm on the go.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="stats-container">
            <div class="stat-item">
                <span class="stat-number">Track Yours</span>
                <span class="stat-label">Active Farms</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">Monitor All</span>
                <span class="stat-label">Your Animals</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">Your Land</span>
                <span class="stat-label">Acres Managed</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">100%</span>
                <span class="stat-label">Reliable</span>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <h2 class="cta-title">Ready to Modernize Your Farm?</h2>
        <p class="cta-description">
            Join thousands of farmers who have already transformed their operations with SmartFarm
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="register.php" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem; border-radius: 30px;">
                Sign Up Free
            </a>
            <a href="login.php" class="btn-outline" style="padding: 1rem 2rem; font-size: 1.1rem; border-radius: 30px; background: transparent; color: white; border: 2px solid white;">
                Already Have Account
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 SmartFarm. Professional Farm Record Keeping System. All rights reserved.</p>
    </footer>
</body>
</html>
