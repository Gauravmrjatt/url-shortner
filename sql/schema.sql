-- URL Shortener Database Schema

-- CREATE DATABASE IF NOT EXISTS url_shortener;
-- USE url_shortener;

-- Main URLs table
CREATE TABLE IF NOT EXISTS urls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_url VARCHAR(2048) NOT NULL,
    short_code VARCHAR(20) UNIQUE NOT NULL,
    custom_alias VARCHAR(50) UNIQUE,
    clicks INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NULL,
    is_active TINYINT DEFAULT 1,
    user_id VARCHAR(255),
    INDEX idx_short_code (short_code),
    INDEX idx_custom_alias (custom_alias),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Click logs for analytics
CREATE TABLE IF NOT EXISTS click_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    url_id INT NOT NULL,
    clicked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    referer VARCHAR(500),
    user_agent VARCHAR(500),
    ip_address VARCHAR(45),
    FOREIGN KEY (url_id) REFERENCES urls(id) ON DELETE CASCADE,
    INDEX idx_url_id (url_id),
    INDEX idx_clicked_at (clicked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API Keys table (for API authentication)
CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_key VARCHAR(64) UNIQUE NOT NULL,
    user_id VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT DEFAULT 1,
    INDEX idx_api_key (api_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
