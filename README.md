# Church News Hub

A secure and modern church news website built with PHP and Tailwind CSS, featuring a complete admin panel for content management.

## 🌟 Features

### Public Website
- **Responsive Design**: Mobile-first design using Tailwind CSS
- **Featured Content**: Highlighted news articles and announcements
- **Event Management**: Upcoming church events with date/time/location
- **Resource Links**: Quick access to important church resources
- **Category Organization**: Content organized by categories with color coding
- **Featured Images**: Support for featured images on all content types

### Admin Panel
- **Secure Authentication**: Session-based login with CSRF protection
- **Content Management**: Create, edit, and manage news, announcements, and articles
- **Event Management**: Schedule and manage church events
- **Link Management**: Organize external resource links
- **Category System**: Organize content with customizable categories
- **Image Upload**: Secure image upload with validation
- **Dashboard**: Overview of all content with statistics
- **Mobile Responsive**: Admin panel works on all devices

### Security Features
- **Input Sanitization**: All user inputs are sanitized and validated
- **SQL Injection Protection**: Prepared statements for all database queries
- **XSS Prevention**: Output escaping and validation
- **CSRF Protection**: CSRF tokens on all forms
- **Secure File Uploads**: File type validation and secure storage
- **Session Security**: Proper session management with timeouts
- **Password Hashing**: Secure password storage using PHP's password functions

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.2+
- Web server (Apache/Nginx)
- PHP Extensions:
  - PDO MySQL
  - GD or Imagick (for image handling)
  - Session support
  - File upload support

## 🚀 Installation

### 1. Clone/Download the Project
```bash
git clone <your-repo-url>
cd church-news-hub
```

### 2. Database Setup
1. Create a MySQL database named `church_news_hub`
2. Import the database schema:
```bash
mysql -u your_username -p church_news_hub < database/schema.sql
```

### 3. Configuration
1. Edit `includes/config.php` and update the database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'church_news_hub');
define('DB_USER', 'your_db_username');
define('DB_PASS', 'your_db_password');
```

2. Update other configuration settings:
```php
define('SITE_URL', 'http://your-domain.com');
define('SECURE_KEY', 'your-secure-random-key-here'); // Generate a secure key
```

### 4. File Permissions
Set proper permissions for the uploads directory:
```bash
chmod 755 uploads/
chmod 755 uploads/images/
```

### 5. Web Server Configuration

#### Apache
Create/update `.htaccess` file in the root directory:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Content-Security-Policy "default-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com"

# Prevent access to sensitive files
<Files "*.php">
    <RequireAll>
        Require all denied
        <RequireAny>
            Require ip 127.0.0.1
            Require local
        </RequireAny>
    </RequireAll>
</Files>

<FilesMatch "^(index|login)\.php$">
    Require all granted
</FilesMatch>
```

#### Nginx
Add to your server block:
```nginx
location / {
    try_files $uri $uri/ /public/index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}

# Security headers
add_header X-Content-Type-Options nosniff;
add_header X-Frame-Options DENY;
add_header X-XSS-Protection "1; mode=block";
add_header Referrer-Policy "strict-origin-when-cross-origin";

# Prevent access to sensitive directories
location ~ /(database|includes)/ {
    deny all;
}
```

## 🔧 Usage

### Default Admin Credentials
- **Username**: admin
- **Password**: admin123
- **⚠️ Important**: Change these credentials immediately after first login!

### Admin Panel Access
Visit `http://your-domain.com/admin/` to access the admin panel.

### Content Management
1. **News Articles**: Create and manage news articles with featured images
2. **Announcements**: Publish important church announcements
3. **Events**: Schedule upcoming church events with details
4. **Resource Links**: Add helpful external links with icons
5. **Categories**: Organize content with custom categories and colors

### Image Uploads
- Supported formats: JPEG, PNG, GIF, WebP
- Maximum file size: 5MB (configurable)
- Images are automatically secured and renamed

## 📁 File Structure

```
church-news-hub/
├── admin/                  # Admin panel files
│   ├── includes/          # Admin headers/footers
│   ├── dashboard.php      # Main admin dashboard
│   ├── login.php         # Admin login page
│   ├── content.php       # Content management
│   ├── events.php        # Event management
│   └── ...
├── public/               # Public website files
│   └── index.php        # Main public page
├── includes/            # Shared PHP files
│   ├── config.php      # Database configuration
│   ├── functions.php   # Security and utility functions
│   ├── header.php      # Public site header
│   └── footer.php      # Public site footer
├── database/           # Database files
│   └── schema.sql     # Database structure
├── uploads/           # File uploads
│   └── images/       # Uploaded images
├── assets/           # Static assets
└── .github/         # GitHub configuration
    └── copilot-instructions.md
```

## �️ Database Schema

The system uses a MySQL database with the following main tables:

### Core Tables

#### `admins`
Manages admin user accounts with secure authentication.
```sql
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);
```

#### `categories`
Organizes content into categorized groups with color coding.
```sql
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    color VARCHAR(7) DEFAULT '#3B82F6',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### `content`
Main table for news articles, announcements, and general content.
```sql
CREATE TABLE content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    excerpt TEXT,
    type ENUM('news', 'announcement', 'article') NOT NULL,
    category_id INT,
    featured_image VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    is_published BOOLEAN DEFAULT FALSE,
    author_id INT NOT NULL,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES admins(id) ON DELETE CASCADE
);
```

#### `events`
Manages church events with date, time, and location information.
```sql
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME,
    location VARCHAR(200),
    featured_image VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    is_published BOOLEAN DEFAULT FALSE,
    max_attendees INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `links`
Stores external resource links with categorization and click tracking.
```sql
CREATE TABLE links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    url VARCHAR(500) NOT NULL,
    description TEXT,
    category_id INT,
    icon VARCHAR(50), -- Font Awesome icon class
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    click_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);
```

#### `settings`
Configurable site settings with different data types.
```sql
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('text', 'textarea', 'number', 'boolean', 'image') DEFAULT 'text',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Default Data

The schema includes default data:
- **Admin User**: `admin` / `admin123` (change after installation)
- **Categories**: General News, Announcements, Events, Ministry Updates, Community
- **Settings**: Site title, description, contact info, and configuration options

### Indexes

Optimized indexes are created for:
- Username and email lookups
- Content type and publication status
- Featured content queries
- Event date sorting
- Category slug lookups

## �🔒 Security Best Practices

1. **Change Default Credentials**: Always change the default admin credentials
2. **Use HTTPS**: Enable SSL/TLS in production
3. **Regular Updates**: Keep PHP and database software updated
4. **Backup Database**: Regular database backups
5. **Monitor Logs**: Check error logs regularly
6. **File Permissions**: Proper file and directory permissions
7. **Secure Key**: Generate a strong SECURE_KEY value

## 🎨 Customization

### Colors and Branding
Edit the Tailwind configuration in header files to change:
- Primary colors (`church-blue`, `church-gold`)
- Font families
- Component spacing

### Content Types
The system supports three main content types:
- `news`: Regular news articles
- `announcement`: Important announcements  
- `article`: General articles/posts

### Categories
Create custom categories with:
- Custom names and descriptions
- Color coding for visual organization
- Slug-based URLs

## 🚑 Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check database credentials in `includes/config.php`
   - Ensure MySQL service is running
   - Verify database exists

2. **Images Not Uploading**
   - Check file permissions on `uploads/images/` directory
   - Verify PHP file upload settings
   - Check file size limits

3. **Admin Login Issues**
   - Use default credentials: admin/admin123
   - Check session configuration
   - Clear browser cookies

### Error Logging
Check PHP error logs for detailed error information. Common log locations:
- `/var/log/apache2/error.log` (Apache)
- `/var/log/nginx/error.log` (Nginx)
- PHP error log as configured in php.ini

## 📝 License

This project is open source. Feel free to modify and adapt it for your church's needs.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For support and questions:
- Check the troubleshooting section
- Review error logs
- Ensure all requirements are met

---

Built with ❤️ for church communities using PHP and Tailwind CSS.