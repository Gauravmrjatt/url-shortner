# URL Shortener - Specification Document

## 1. Project Overview

- **Project Name**: URL Shortener
- **Type**: Full-stack PHP Web Application with REST API
- **Core Functionality**: Create shortened URLs with custom aliases, track click counts, and provide API access
- **Target Users**: General users needing URL shortening services

## 2. Features

### Core Features
- **URL Shortening**: Convert long URLs to short, memorable links
- **Custom Alias**: Allow users to create custom short codes (e.g., mylink instead of random6 chars)
- **Click Tracking**: Track number of clicks for each shortened URL
- **API Access**: RESTful API for programmatic URL shortening
- **Analytics**: View click statistics per URL

### Technical Features
- **Short Code Generation**: Configurable length (default 6 characters)
- **Unique Constraint**: Ensure custom aliases are unique
- **Expiration**: Optional URL expiration date
- **QR Code**: Generate QR code for shortened URLs
- **URL Validation**: Validate URLs before shortening

### Security Features
- **SQL Injection Prevention**: Prepared statements
- **XSS Prevention**: Input sanitization
- **CSRF Protection**: Token-based protection for web forms
- **Rate Limiting**: Prevent abuse (optional)

## 3. Database Schema

### Table: urls
| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| original_url | VARCHAR(2048) | Long URL |
| short_code | VARCHAR(20) UNIQUE | Short code |
| custom_alias | VARCHAR(50) UNIQUE | User-defined alias |
| clicks | INT DEFAULT 0 | Click count |
| created_at | DATETIME | Creation timestamp |
| expires_at | DATETIME | Expiration timestamp (nullable) |
| is_active | TINYINT DEFAULT 1 | Active status |
| user_id | VARCHAR(255) | User identifier (for API) |

### Table: click_logs
| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT | Primary key |
| url_id | INT | Foreign key to urls |
| clicked_at | DATETIME | Click timestamp |
| referer | VARCHAR(255) | Referring URL |
| user_agent | VARCHAR(500) | User agent string |
| ip_address | VARCHAR(45) | Visitor IP |

## 4. API Endpoints

### POST /api/shorten
Create a new shortened URL.

**Request:**
```json
{
  "url": "https://example.com/very-long-url",
  "custom_alias": "mylink" // optional
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "original_url": "https://example.com/very-long-url",
    "short_url": "http://localhost/s/mylink",
    "short_code": "mylink",
    "clicks": 0,
    "created_at": "2026-03-27 10:00:00"
  }
}
```

### GET /api/stats/{short_code}
Get URL statistics.

**Response:**
```json
{
  "success": true,
  "data": {
    "short_code": "abc123",
    "original_url": "https://example.com",
    "clicks": 150,
    "created_at": "2026-03-27 10:00:00",
    "last_clicked": "2026-03-27 12:30:00"
  }
}
```

### GET /s/{short_code}
Redirect to original URL and track click.

## 5. Project Structure

```
urlshortner/
├── config/
│   └── config.php
├── src/
│   ├── Database.php
│   ├── Url.php
│   ├── UrlService.php
│   └── Logger.php
├── api/
│   ├── shorten.php
│   └── stats.php
├── public/
│   ├── index.php
│   ├── redirect.php
│   └── css/
│       └── style.css
├── js/
│   └── app.js
├── sql/
│   └── schema.sql
└── .htaccess
```

## 6. Acceptance Criteria

1. ✓ Users can shorten any valid URL
2. ✓ Users can create custom aliases (must be unique)
3. ✓ Click count increments on each redirect
4. ✓ API returns proper JSON responses
5. ✓ Custom aliases are validated for uniqueness
6. ✓ Invalid URLs are rejected
7. ✓ Expired URLs redirect to error page
8. ✓ Production-ready code with error handling
