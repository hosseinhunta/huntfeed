# Installation Guide

## Requirements

- **PHP 8.1** or higher
- **Composer** for dependency management
- Any web server (Apache, Nginx, etc.)

## Installation Methods

### Method 1: Via Composer (Recommended)

```bash
composer require hosseinhunta/huntfeed
```

Then in your PHP file:

```php
require 'vendor/autoload.php';

use Hosseinhunta\Huntfeed\Hub\FeedManager;

$feedManager = new FeedManager();
```

### Method 2: From Source

```bash
git clone https://github.com/hosseinhunta/huntfeed.git
cd huntfeed
composer install
```

## Quick Setup

### 1. Create Configuration File

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

Edit `.env` with your settings:

```env
WEBSUB_ENABLED=true
WEBSUB_CALLBACK_URL=https://your-domain.com/callback.php
WEBSUB_AUTO_SUBSCRIBE=true
WEBSUB_FALLBACK_POLLING=true
```

### 2. Create Callback Endpoint (for WebSub)

Copy the template and create `callback.php`:

```bash
cp examples/callback.php /path/to/webroot/callback.php
```

### 3. Test Installation

Run the test suite:

```bash
php tests/QuickStartTest.php
```

Expected output: ✅ All 12 tests passing

## Production Deployment

### 1. Environment Setup

```bash
# Create production .env
cp .env.example .env

# Edit .env
nano .env
```

Set for production:
```env
APP_ENV=production
APP_DEBUG=false
HTTPS_REQUIRED=true
```

### 2. SSL Certificate

Ensure you have a valid SSL certificate:

```bash
# For Let's Encrypt (free)
certbot certonly --webroot -w /path/to/webroot -d your-domain.com
```

### 3. Callback Endpoint

Make callback endpoint publicly accessible:

```bash
# Configure in your web server
# Ensure callback.php is reachable at:
# https://your-domain.com/callback.php
```

### 4. Permissions

Set proper directory permissions:

```bash
chmod -R 755 /path/to/huntfeed
chmod -R 775 /path/to/huntfeed/logs
```

### 5. Cron Job (Optional - for polling)

Add to crontab for periodic polling:

```bash
*/15 * * * * php /path/to/update-feeds.php
```

## Docker Setup (Optional)

### Dockerfile

```dockerfile
FROM php:8.3-apache

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    curl \
    libxml2 \
    && docker-php-ext-enable curl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
```

### Docker Compose

```yaml
version: '3'
services:
  web:
    build: .
    ports:
      - "80:80"
    volumes:
      - ./logs:/var/www/html/logs
    environment:
      - WEBSUB_ENABLED=true
      - WEBSUB_CALLBACK_URL=https://your-domain.com/callback.php
```

Run:
```bash
docker-compose up -d
```

## Troubleshooting

### PHP Version Issue

```
Fatal error: Unsupported operand types
```

**Solution:** Ensure PHP 8.1+

```bash
php -v
```

### Composer Not Found

```
command not found: composer
```

**Solution:** Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Permission Denied

```
Permission denied: /path/to/callback.php
```

**Solution:** Fix permissions

```bash
chmod 755 /path/to/callback.php
chown www-data:www-data /path/to/callback.php
```

### SSL Certificate Error

```
SSL certificate problem: unable to get local issuer certificate
```

**Solution:** Either:

1. Get a valid certificate
2. Disable SSL verification (development only):

```php
$fetcher->setVerifySSL(false);
```

### Feed Not Fetching

```
Failed to fetch feed from URL
```

**Troubleshooting:**

1. Check URL is valid:
```bash
curl https://example.com/feed.xml
```

2. Check network access:
```bash
php -r "echo file_get_contents('https://example.com/feed.xml');"
```

3. Check PHP cURL extension:
```bash
php -m | grep curl
```

## Next Steps

After installation:

1. Read [README.md](README.md) or [README_EN.md](README_EN.md)
2. Review [WEBSUB_GUIDE.md](WEBSUB_GUIDE.md) for WebSub setup
3. Check [examples/](examples/) for usage examples
4. Read [SECURITY.md](SECURITY.md) for security best practices

## Getting Help

- 🐛 [Report Issues](https://github.com/hosseinhunta/huntfeed/issues)
- 📖 [Documentation](WEBSUB_GUIDE.md)
- 💬 [Discussions](https://github.com/hosseinhunta/huntfeed/discussions)

---

**Installation Complete!** 🎉

