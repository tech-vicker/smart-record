# SmartFarm - Farm Record Keeping System

## 🌾 Professional Farm Management System

A modern, responsive web application for managing agricultural operations with multi-farm support, analytics, and advanced features.

## ✨ Features

### 🎯 Core Features
- **Multi-Farm Management** - Manage multiple farms from one account
- **Livestock Tracking** - Monitor animal health, count, and value
- **Crop Management** - Track planting, growth, and harvest cycles
- **Task Management** - Schedule and track farm activities
- **Financial Records** - Income, expense tracking with analytics
- **Calendar View** - Visual task scheduling and planning
- **Data Export** - CSV export for reports and analysis

### 🎨 Advanced Features
- **Dark Mode** - Toggle between light and dark themes
- **Real-time Form Validation** - Enhanced user experience
- **Loading Animations** - Professional loading states
- **Responsive Design** - Works on all devices
- **Performance Optimized** - Fast database queries with indexes
- **Secure Authentication** - Password hashing and session management

## 🚀 Quick Start

### Local Development
```bash
# Clone the repository
git clone <repository-url>
cd smartfarm

# Start the development server
php -S localhost:3000

# Visit http://localhost:3000
```

### Production Deployment

#### Option 1: Shared Hosting (cPanel)
1. Upload all files to public_html directory
2. Ensure PHP 7.4+ is available
3. Set file permissions (755 for directories, 644 for files)
4. Create database through cPanel
5. Update database credentials in `includes/db.php`

#### Option 2: VPS/Cloud Server
```bash
# Install requirements (Ubuntu/Debian)
sudo apt update
sudo apt install apache2 php php-sqlite3 php-mbstring

# Clone repository
git clone <repository-url> /var/www/smartfarm
cd /var/www/smartfarm

# Set permissions
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod 644 db/database.sqlite

# Configure Apache
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Option 3: Docker Deployment
```bash
# Build and run
docker build -t smartfarm .
docker run -d -p 80:80 -v $(pwd)/db:/app/db smartfarm
```

## 📁 Project Structure

```
smartfarm/
├── 📄 index.php          # Redirect to landing page
├── 🏠 home.php           # Professional landing page
├── 📊 dashboard.php      # Main dashboard
├── 🐄 livestock.php      # Livestock management
├── 🌱 crops.php          # Crop management
├── ✅ tasks.php          # Task management
├── 📅 calendar.php       # Calendar view
├── 💵 finances.php       # Financial records
├── 📈 analytics.php      # Analytics dashboard
├── 🏡 farms.php          # Multi-farm management
├── 👤 profile.php        # User profile
├── 🔐 login.php          # User login
📝 register.php          # User registration
🚪 logout.php            # User logout
├── 📁 includes/
│   ├── 🔧 db.php         # Database configuration
│   ├── 🔐 auth.php       # Authentication functions
│   └── 📄 header.php     # Site header/navigation
├── 📁 css/
│   └── 🎨 style.css      # Stylesheets with dark mode
├── 📁 js/
│   └── ⚡ app.js         # JavaScript functionality
├── 📁 db/
│   └── 💾 database.sqlite # SQLite database
└── 📁 images/           # Static images
```

## 🔧 Configuration

### Database Setup
The system uses SQLite with automatic table creation. No manual database setup required.

### Environment Variables
Create `.env` file for production:
```env
# Production environment
APP_ENV=production
APP_DEBUG=false
DB_PATH=/path/to/secure/database.sqlite
```

## 🔒 Security Features

- **SQL Injection Prevention** - Prepared statements for all queries
- **XSS Protection** - Input sanitization and output encoding
- **Session Security** - Secure session management
- **Password Security** - Bcrypt hashing for passwords
- **CSRF Protection** - Form tokens for state-changing operations

## 📊 Performance Features

- **Database Indexes** - Optimized queries for large datasets
- **WAL Journal Mode** - Better concurrency performance
- **Memory Caching** - Efficient data retrieval
- **Lazy Loading** - Optimized asset loading
- **Minified Assets** - Production-ready optimizations

## 🌐 Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Android Chrome)

## 📱 Mobile Features

- **Responsive Design** - Works on all screen sizes
- **Touch Optimized** - Mobile-friendly interactions
- **PWA Ready** - Can be installed as mobile app

## 🔄 Version History

### v2.0.0 (Current)
- ✅ Multi-farm support
- ✅ Dark mode toggle
- ✅ Calendar view
- ✅ Export functionality
- ✅ Performance optimizations
- ✅ Enhanced form validation

### v1.0.0
- ✅ Basic farm management
- ✅ User authentication
- ✅ CRUD operations
- ✅ Responsive design

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:
- 📧 Email: support@smartfarm.com
- 📱 Phone: +1-234-567-8900
- 💬 Live Chat: Available on website

## 🌟 Star History

[![Star History Chart](https://api.star-history.com/svg?repos=username/smartfarm&type=Date)](https://star-history.com/#username/smartfarm&Date)

---

**Made with ❤️ for farmers worldwide** 🌾
