-- Church News Hub Database Schema
-- Create database and tables for the church news hub

-- Create database (run this separately if needed)
-- CREATE DATABASE church_news_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE church_news_hub;

-- Admin users table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_username (username),
    INDEX idx_email (email)
);

-- Categories for organizing content
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    color VARCHAR(7) DEFAULT '#3B82F6', -- Tailwind blue-500
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
);

-- Main content table for news, announcements, and articles
CREATE TABLE content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    excerpt TEXT,
    type ENUM('news', 'announcement', 'article') NOT NULL,
    category_id INT,
    featured_image VARCHAR(255),
    facebook_post_url VARCHAR(500),
    is_featured BOOLEAN DEFAULT FALSE,
    is_published BOOLEAN DEFAULT FALSE,
    author_id INT NOT NULL,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    INDEX idx_slug (slug),
    INDEX idx_type (type),
    INDEX idx_published (is_published),
    INDEX idx_featured (is_featured),
    INDEX idx_created (created_at),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- Links table for external resources
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
    INDEX idx_featured (is_featured),
    INDEX idx_active (is_active),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Events table for church events
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_date (event_date),
    INDEX idx_published (is_published),
    INDEX idx_featured (is_featured)
);

-- Settings table for site configuration
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('text', 'textarea', 'number', 'boolean', 'image') DEFAULT 'text',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
);

-- Insert default admin user (password: admin123 - CHANGE THIS!)
INSERT INTO admins (username, email, password_hash, full_name) VALUES 
('admin', 'admin@church.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Site Administrator');

-- Insert default categories
INSERT INTO categories (name, slug, description, color) VALUES 
('General News', 'general-news', 'General church news and updates', '#3B82F6'),
('Announcements', 'announcements', 'Important church announcements', '#EF4444'),
('Events', 'events', 'Upcoming church events and activities', '#10B981'),
('Ministry Updates', 'ministry-updates', 'Updates from various ministries', '#8B5CF6'),
('Community', 'community', 'Community-related news and information', '#F59E0B');

-- Insert default site settings
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES 
('site_title', 'Church News Hub', 'text', 'Main site title'),
('site_description', 'Stay updated with the latest news, announcements, and events from our church community.', 'textarea', 'Site meta description'),
('contact_email', 'info@church.local', 'text', 'Main contact email'),
('church_name', 'Community Church', 'text', 'Official church name'),
('church_address', '123 Church Street, City, State 12345', 'textarea', 'Church physical address'),
('church_phone', '(555) 123-4567', 'text', 'Church phone number'),
('posts_per_page', '10', 'number', 'Number of posts to display per page'),
('enable_comments', '0', 'boolean', 'Enable comments on posts'),
('maintenance_mode', '0', 'boolean', 'Enable maintenance mode');

-- Telegram Officers table
CREATE TABLE telegram_officers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    telegram_username VARCHAR(50) NOT NULL,
    description TEXT,
    icon_color VARCHAR(20) DEFAULT 'blue',
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default telegram officers
INSERT INTO telegram_officers (name, position, telegram_username, description, icon_color, display_order) VALUES
('Pastor', 'Senior Pastor', 'pastor_username', 'Spiritual guidance and pastoral care', 'blue', 1),
('Secretary', 'Church Secretary', 'secretary_username', 'Administrative matters and inquiries', 'green', 2),
('Treasurer', 'Church Treasurer', 'treasurer_username', 'Financial matters and donations', 'purple', 3);

-- Featured Images table
CREATE TABLE featured_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    link_url VARCHAR(500),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX idx_content_type_published ON content(type, is_published);
CREATE INDEX idx_content_featured_published ON content(is_featured, is_published);
CREATE INDEX idx_links_featured_active ON links(is_featured, is_active);
CREATE INDEX idx_telegram_officers_active_order ON telegram_officers(is_active, display_order);
CREATE INDEX idx_featured_images_active_order ON featured_images(is_active, display_order);