# 🌾 SmartFarm - Professional Farm Record Keeping System

**Enterprise-grade farm management platform for modern agricultural operations**

![Version](https://img.shields.io/badge/version-2.0.0-blue)
![Language](https://img.shields.io/badge/language-PHP-purple)
![License](https://img.shields.io/badge/license-MIT-green)
![Status](https://img.shields.io/badge/status-Production%20Ready-brightgreen)

> A professional, responsive web application for managing agricultural operations with multi-farm support, advanced analytics, and comprehensive reporting tools.

---

## ✨ Key Features

### 🔐 Authentication & Security
- Secure user registration and login system
- Bcrypt password hashing (cost factor 10)
- Secure session management
- CSRF token protection
- Input sanitization & output encoding
- SQL injection prevention with prepared statements

### 🏢 Multi-Farm Management
- Manage multiple farms from single account
- Farm-specific data isolation
- Easy farm switching
- Farm settings & configuration
- Scalable architecture for enterprise use

### 📊 Dashboard
- Real-time farm overview with key metrics
- Performance indicators
- Quick statistics summary
- Navigation to all modules
- At-a-glance farm status

### 🌾 Crop Management
- Track planting and harvest cycles
- Monitor crop status (Seedling, Growing, Flowering, Harvested, Failed)
- Detailed crop information & history
- Crop performance analytics
- Field mapping & area tracking

### 🐄 Livestock Tracking
- Comprehensive animal inventory management
- Health status monitoring (Healthy, Sick, Quarantined)
- Breed and age tracking
- Animal value estimation
- Livestock performance metrics

### ✅ Task Management
- Schedule farm activities
- Set task priorities & deadlines
- Track task completion
- Activity reminders & notifications
- Comprehensive activity history

### 📅 Calendar View
- Visual task scheduling interface
- Date-based event planning
- Interactive calendar management
- Export calendar data
- Event management & tracking

### 💰 Financial Records
- **Income Tracking:** Crops, livestock sales, grants, subsidies
- **Expense Tracking:** Feed, seeds, labor, equipment, utilities
- Automatic balance calculation
- Transaction history & filtering
- Financial analytics & trend analysis
- Revenue vs. expense charts

### 📈 Analytics Dashboard
- Revenue & expense analysis
- Profit/loss calculations
- Performance trends & comparisons
- Custom report generation
- Data visualization & insights

---

## 🎨 Design Features

### 🌐 Responsive Design
- Mobile-first approach
- Works seamlessly on all devices
- Touch-optimized interface
- Mobile-friendly forms & navigation
- PWA ready (installable as mobile app)

### 🎯 Professional UI
- Clean, intuitive interface
- Dark mode toggle support
- Color-coded status indicators
- Smooth animations & transitions
- Enterprise-grade styling

---

## 🛠️ Technology Stack

| Component | Technology |
|-----------|-----------|
| **Backend** | PHP 7.4+ |
| **Database** | SQLite 3 |
| **Frontend** | HTML5, CSS3, JavaScript |
| **Authentication** | Bcrypt password hashing |
| **Database Ext** | SQLite3 PHP Extension |
| **Templating** | PHP native templating |
| **Styling** | Custom CSS + Dark Mode |
| **Security** | Prepared statements, CSRF tokens |

---

## 📁 Project Structure

```
smart-record/
├── index.php                    # Redirect to landing page
├── home.php                     # Professional landing page
├── dashboard.php                # Main dashboard
├── livestock.php                # Livestock management
├── crops.php                    # Crop management
├── tasks.php                    # Task management
├── calendar.php                 # Calendar view
├── finances.php                 # Financial records
├── analytics.php                # Analytics dashboard
├── farms.php                    # Multi-farm management
├── profile.php                  # User profile
├── login.php                    # User login
├── register.php                 # User registration
├── logout.php                   # User logout
│
├── includes/
│   ├── db.php                  # Database configuration
│   ├── auth.php                # Authentication functions
│   └── header.php              # Site header/navigation
│
├── css/
│   └── style.css               # Main stylesheet (with dark mode)
│
├── js/
│   └── app.js                  # JavaScript functionality
│
├── db/
│   └── database.sqlite         # SQLite database file
│
├── images/                      # Static images & assets
│
└── README.md                    # Documentation
```

---

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- SQLite 3
- PHP SQLite extension (usually enabled by default)
- Modern web browser

### Local Development

```bash
# Navigate to project directory
cd smart-record

# Start PHP development server
php -S localhost:3000

# Open browser to http://localhost:3000
```

**First Login:**
- Email: `admin@example.com`
- Password: `admin123`

Or register a new account

---

## 🌐 Production Deployment

### Option 1: Shared Hosting (cPanel) - RECOMMENDED

1. Upload all files to `public_html` directory
2. Ensure PHP 7.4+ is available
3. Set directory permissions to 755, file permissions to 644
4. Create SQLite database through file manager
5. Update database path in `includes/db.php`
6. Access via your domain

**Note:** Store database outside public_html for security

### Option 2: VPS/Cloud Server (Ubuntu/Debian)

```bash
# Install dependencies
sudo apt update
sudo apt install apache2 php php-sqlite3 php-mbstring php-curl

# Clone repository
cd /var/www
git clone https://github.com/tech-vicker/smart-record.git smartfarm
cd smartfarm

# Set permissions
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod 644 db/database.sqlite

# Enable Apache modules
sudo a2enmod rewrite

# Configure virtual host (optional)
sudo nano /etc/apache2/sites-available/smartfarm.conf
```

Add to virtual host:
```apache
<Directory /var/www/smartfarm>
    AllowOverride All
    Require all granted
</Directory>
```

Then:
```bash
sudo a2ensite smartfarm.conf
sudo systemctl restart apache2
```

### Option 3: Docker Deployment

```bash
# Build Docker image
docker build -t smartfarm .

# Run container
docker run -d \
  -p 80:80 \
  -v $(pwd)/db:/var/www/html/db \
  --name smartfarm \
  smartfarm

# Access at http://localhost
```

### Option 4: Platform-as-a-Service

**Heroku:**
```bash
heroku create your-app-name
git push heroku main
heroku logs --tail
```

**DigitalOcean, AWS, Azure:** Follow provider's PHP deployment guides

---

## 📱 Usage Guide

### 👤 User Account Management
1. **Create Account** → Click "Register" or "Sign up here"
2. **Login** → Use credentials to access dashboard
3. **Profile** → Update profile settings & preferences
4. **Farms** → Switch between multiple farms

### 🏢 Farm Management
1. **Create Farm** → Add farm details (name, location, size)
2. **Switch Farms** → Select farm from dropdown
3. **Farm Settings** → Configure farm-specific settings
4. **Export Data** → Export farm data for analysis

### 🌾 Crop Management
1. Go to **Crops** section
2. Click **"+ Add New Crop"**
3. Enter:
   - Crop name & variety
   - Field area & location
   - Planting & expected harvest dates
   - Status & notes
4. Track growth & update status
5. Harvest & generate reports

### 🐄 Livestock Tracking
1. Go to **Livestock** section
2. Click **"+ Add New Animal"**
3. Select:
   - Animal category & breed
   - Age, health status
   - Quantity & value
4. Monitor health changes
5. Track vaccinations & veterinary records

### ✅ Task Scheduling
1. Go to **Tasks** section
2. Click **"+ Add Task"**
3. Set:
   - Task name & description
   - Due date & priority
   - Assigned to
4. Update status as completed
5. View task history

### 📅 Calendar Planning
1. Open **Calendar** view
2. Click date to add event
3. Set event details & reminders
4. Drag to reschedule events
5. Export calendar for sharing

### 💰 Financial Management
1. Go to **Finances** section
2. **Add Income:**
   - Type: Crop Sales, Livestock Sales, Grants, etc.
   - Amount & date
3. **Add Expense:**
   - Category: Feed, Seeds, Labor, Equipment, etc.
   - Amount & date
4. View balance sheet & trends
5. Generate financial reports

### 📊 Analytics Dashboard
1. Open **Analytics** section
2. View:
   - Revenue vs. expenses chart
   - Profit/loss analysis
   - Performance trends
   - Comparative analytics
3. Generate custom reports
4. Export data for external analysis

---

## 🔒 Security Features

| Feature | Implementation |
|---------|-----------------|
| **SQL Injection** | Prepared statements for all queries |
| **XSS Protection** | Input sanitization & output encoding |
| **CSRF Protection** | Form tokens for state-changing operations |
| **Password Security** | Bcrypt hashing (cost: 10) |
| **Session Security** | Secure session handling & timeouts |
| **Input Validation** | Server-side validation on all inputs |
| **Error Handling** | No sensitive data in error messages |
| **Access Control** | Farm-specific data isolation |

---

## ⚡ Performance Features

- **Database Indexes** - Optimized for large datasets
- **WAL Mode** - Better database concurrency
- **Query Optimization** - Efficient database queries
- **Asset Minification** - Smaller CSS/JS files
- **Lazy Loading** - On-demand asset loading
- **Browser Cache** - Cache headers configured
- **Connection Pooling** - Efficient DB connections

---

## 🌐 Browser Support

| Browser | Support |
|---------|---------|
| Chrome | 90+ ✅ |
| Firefox | 88+ ✅ |
| Safari | 14+ ✅ |
| Edge | 90+ ✅ |
| Mobile Chrome | Latest ✅ |
| Mobile Safari | Latest ✅ |

---

## 🔧 Configuration

### Database Setup
SQLite with automatic table creation. No manual setup required!

### Environment Configuration
Create `.env` file (optional):
```env
APP_ENV=production
APP_DEBUG=false
DB_PATH=/secure/path/to/database.sqlite
TIMEZONE=UTC
```

### Database Security
- Store database outside web root
- Restrict database file permissions (640)
- Regular database backups
- Connection encryption for remote access

---

## 🐛 Troubleshooting

| Problem | Solution |
|---------|----------|
| **PHP not found** | Install PHP: `sudo apt install php` |
| **Permission denied** | Fix: `chmod 755 -R .` and `chmod 644 db/database.sqlite` |
| **Database locked** | Restart server or delete & reinitialize database |
| **Dark mode not working** | Clear browser cache (Ctrl+Shift+Del) |
| **SQLite extension missing** | Install: `sudo apt install php-sqlite3` |
| **Login issues** | Clear cookies & check database permissions |
| **Slow queries** | Check database indexes & add missing ones |

### Debug Mode
In `includes/db.php` (uncomment for debugging):
```php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
```

---

## 🌟 Version History

### v2.0.0 (Current)
- ✅ Multi-farm support
- ✅ Dark mode toggle
- �� Calendar view for task scheduling
- ✅ CSV export functionality
- ✅ Performance optimizations
- ✅ Enhanced form validation
- ✅ Advanced analytics dashboard
- ✅ Professional UI/UX improvements

### v1.0.0 (Legacy)
- ✅ Basic farm management
- ✅ User authentication
- ✅ CRUD operations
- ✅ Responsive design

---

## 🚀 Roadmap

- [ ] REST API for mobile app integration
- [ ] Weather API integration & forecasting
- [ ] Mobile app (iOS/Android)
- [ ] Real-time notifications
- [ ] Advanced PDF reports
- [ ] Multi-language support
- [ ] Two-factor authentication (2FA)
- [ ] Data backup & restore
- [ ] Machine learning crop predictions
- [ ] Blockchain transaction records

---

## 🤝 Contributing

We welcome contributions! To contribute:

1. **Fork** the repository
2. **Create** feature branch: `git checkout -b feature/YourFeature`
3. **Commit** changes: `git commit -m 'Add YourFeature'`
4. **Push** to branch: `git push origin feature/YourFeature`
5. **Open** Pull Request

### Contribution Guidelines
- Follow PSR-12 coding standards
- Add comments for complex logic
- Test thoroughly
- Update documentation

---

## 📄 License

Open source under the **MIT License** - See LICENSE file for details

---

## 📞 Support

- **Issues:** [GitHub Issues](https://github.com/tech-vicker/smart-record/issues)
- **Discussions:** GitHub Discussions available
- **Contact:** Reach out via GitHub profile

---

## 🙏 Credits

Built with passion for modern agriculture 🚜

**Technologies:**
- PHP Community
- SQLite Project
- Web Standards (HTML5, CSS3, JavaScript)

---

## 📊 Project Stats

- **Created:** March 23, 2026
- **Last Updated:** August 17, 2026
- **Language:** PHP
- **Database:** SQLite
- **Version:** 2.0.0
- **Status:** Active Development

---

**Made with love for farmers worldwide** 🌾🚜

*Professional Farm Management Solution*
