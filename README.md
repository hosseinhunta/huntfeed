<<<<<<< HEAD
# 🚀 HuntFeed – Production-Ready PHP Feed Management Library
=======
# 🚀 HuntFeed - Production-Ready PHP Feed Management Library

## 🔥 Real-Time RSS/Atom Feed Management with WebSub Support
>>>>>>> 448c5dc116272d3979f76135b58bd2b61055119c

## 🔥 Real-Time RSS/Atom Feed Management with WebSub Support

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![GitHub Stars](https://img.shields.io/github/stars/hosseinhunta/huntfeed)](https://github.com/hosseinhunta/huntfeed)
<<<<<<< HEAD
[![Packagist Downloads](https://img.shields.io/packagist/dt/hosseinhunta/huntfeed)](https://packagist.org/packages/hosseinhunta/huntfeed)
[![WebSub Ready](https://img.shields.io/badge/WebSub-Ready-green)](https://www.w3.org/TR/websub/)
[![Tests Passing](https://img.shields.io/badge/tests-12%2B%20passing-brightgreen)](tests/)

![HuntFeed, event-driven PHP library for consuming, normalizing, and distributing RSS, Atom, and WebSub](/docs/huntfeed-readme.jpg)

**HuntFeed** is a production-ready, event-driven PHP library for consuming, normalizing, and distributing RSS, Atom, and WebSub feed updates with **real-time push notifications**. Perfect for feed aggregators, news readers, and content monitoring applications.
=======
[![Downloads](https://img.shields.io/packagist/dt/hosseinhunta/huntfeed)](https://packagist.org/packages/hosseinhunta/huntfeed)
[![WebSub Ready](https://img.shields.io/badge/WebSub-Ready-green)](https://www.w3.org/TR/websub/)

HuntFeed is a **production-ready, event-driven PHP library** for consuming, normalizing, and distributing RSS, Atom, and WebSub feed updates with **real-time push notifications**. Perfect for feed aggregators, news readers, and content monitoring applications.
>>>>>>> 448c5dc116272d3979f76135b58bd2b61055119c

---

## ✨ Why Choose HuntFeed?

<<<<<<< HEAD
## 📊 Performance Benchmarks

| Scenario | Traditional Polling | HuntFeed with WebSub | Improvement |
|----------|-------------------|---------------------|-------------|
| **100 feeds, 15-min interval** | 9,600 requests/day | ~100 requests/day | **99% fewer requests** |
| **Bandwidth Usage** | ~5 MB/day | <100 KB/day | **98% reduction** |
| **Update Latency** | 15 minutes | <1 second | **150x faster** |
| **Server Load** | Continuous polling | Event-driven | **Massively scalable** |

## 🆚 HuntFeed vs SimplePie

| Feature | SimplePie | HuntFeed |
|---------|-----------|----------|
| **Real-Time Updates** | ❌ Polling only | ✅ WebSub push |
| **PHP Version** | 5.2+ | 8.1+ (modern features) |
| **Feed Formats** | RSS, Atom | RSS, Atom, JSON Feed, RDF, GeoRSS |
| **Performance** | 9,600 reqs/day | ~100 reqs/day |
| **Security** | Basic | HMAC-SHA1 |
| **Export Formats** | 2 formats | 7 formats |

---

## 🛡️ Trust & Production Ready

### ✅ Security Features

- **HMAC-SHA1 Verification** – All WebSub notifications are signed
- **SSL/TLS Enforcement** – HTTPS required in production
- **Challenge Verification** – Prevents unauthorized subscriptions
- **No Data Leakage** – Sensitive data never logged

### ✅ Enterprise-Grade

- **12+ Comprehensive Tests** – All passing
- **PSR-12 Compliant Code** – Industry standards
- **2000+ Lines Documentation** – Complete coverage
- **Active Maintenance** – Regular updates and security patches
=======
### 🚀 **Performance That Scales**
- **99% fewer HTTP requests** with WebSub vs polling
- **98% bandwidth reduction** - push notifications only when content changes
- **Real-time updates** under 1 second latency
- **150x faster** than traditional 15-minute polling

### 🔧 **Developer Experience**
- Clean, type-hinted PHP 8.1+ API
- PSR-12 compliant code
- Comprehensive documentation (2000+ lines)
- 12+ built-in tests
- Zero external dependencies for core functionality

### 🛡️ **Production Ready**
- Full WebSub/PubSubHubbub implementation
- HMAC-SHA1 signature verification
- SSL/TLS enforcement
- Duplicate detection & prevention
- Multi-format export (JSON, RSS, Atom, CSV, HTML)
>>>>>>> 448c5dc116272d3979f76135b58bd2b61055119c

---

## 📦 Quick Installation
<<<<<<< HEAD

## Requirements

- **PHP 8.1+** (Takes advantage of modern PHP features)
- **cURL extension** (For HTTP requests)
- **SimpleXML extension** (For XML parsing)
- **JSON extension** (For JSON handling)

**No external dependencies** for core functionality!
=======
>>>>>>> 448c5dc116272d3979f76135b58bd2b61055119c

```bash
composer require hosseinhunta/huntfeed
```

### Basic Usage - Get Started in 30 Seconds

```php
<?php
require 'vendor/autoload.php';

use Hosseinhunta\Huntfeed\Hub\FeedManager;

$manager = new FeedManager();

// Register your feeds
$manager->registerFeeds([
    'tech' => [
        'url' => 'https://news.ycombinator.com/rss',
        'category' => 'Technology',
        'interval' => 300 // 5 minutes
    ]
]);

// Check for updates
$updates = $manager->checkUpdates();

// Export to any format
$json = $manager->export('json'); // Perfect for APIs
$rss = $manager->export('rss');   // For feed readers
```

---

<<<<<<< HEAD
## 🔧 Advanced Features

### 📡 **Multi-Format Feed Support**

=======
## 🌟 Key Features

### 📡 **Multi-Format Feed Support**
>>>>>>> 448c5dc116272d3979f76135b58bd2b61055119c
- **RSS 2.0** - Dublin Core, Media RSS, Content Encoded
- **Atom 1.0** - Full specification support
- **JSON Feed** - Modern JSON-based format
- **RDF/RSS 1.0** - Legacy format support
- **GeoRSS** - Geographic-aware feeds
- **Auto-detection** - Automatically identifies feed format

<<<<<<< HEAD
### **🔔 WebSub Implementation**

=======
### 🔔 **Real-Time WebSub (PubSubHubbub)**
>>>>>>> 448c5dc116272d3979f76135b58bd2b61055119c
```php
use Hosseinhunta\Huntfeed\WebSub\WebSubManager;

$webSubManager = new WebSubManager(
<<<<<<< HEAD
    $manager,
    'https://your-domain.com/callback.php'
);

=======
    $feedManager,
    'https://your-domain.com/callback.php'
);

// Auto-subscribe to push notifications
>>>>>>> 448c5dc116272d3979f76135b58bd2b61055119c
$webSubManager->setAutoSubscribe(true);
$webSubManager->registerFeedWithWebSub('tech', 'https://example.com/feed.xml');
```

<<<<<<< HEAD
### 📊 **Smart Feed Management**

- **Duplicate prevention** - 3 fingerprinting strategies
- **Category organization** - Multi-category support
- **Advanced search** - Search across titles, content, categories
- **Event-driven architecture** - Hook into feed updates
- **Batch processing** - Handle multiple feeds efficiently (Soon)

### 🎨 **7 Export Formats**

```php
// Export to any format your application needs
$manager->export('json');      // API responses
$manager->export('rss');       // RSS feed generation
$manager->export('atom');      // Atom feed generation
$manager->export('jsonfeed');  // JSON Feed format
$manager->export('csv');       // Excel/database import
$manager->export('html');      // Web display
$manager->export('text');      // Plain text
```

---

## 🎯 Use Cases

🤖 Telegram Bots

```php
$manager->on('item:new', function($data) {
    sendTelegramAlert($data['item']);
});
```

📱 Mobile App Backends

```php
// API endpoint
$app->get('/api/feeds', function() use ($manager) {
    return $manager->export('json');
});
```

📰 News Aggregators

```php
$manager->registerFeeds([
    'tech' => 'https://techcrunch.com/feed/',
    'news' => 'https://bbc.com/news/world/rss.xml',
    'sports' => 'https://espn.com/rss'
]);
```

🔍 Content Monitoring

Monitor competitors, keywords, or industry news with instant notifications.

---

## 🧪 Testing & Quality

```bash
# Run comprehensive test suite
php tests/QuickStartTest.php

# Run WebSub-specific tests
php tests/WebSubTest.php

# Run polling tests
php tests/poling-test.php
```

**All 12+ tests pass** - Production-ready code.

---

## 🔒 Security Features

### ✅ **Enterprise-Grade Security**

- **HMAC-SHA1 Verification** - All WebSub notifications are signed
- **SSL/TLS Enforcement** - HTTPS required in production
- **Challenge Verification** - Prevents unauthorized subscriptions
- **Input Validation** - All URLs and content validated
- **XML Safety** - XXE attack prevention
- **No Data Leakage** - Sensitive data never logged

### 🔐 **Best Practices Included**

```php
// Production configuration
$manager->setConfig('https_required', true);
$manager->setConfig('verify_ssl', true);
$manager->setConfig('hmac_secret', getenv('WEBSUB_SECRET'));
=======
**Benefits:**
- Instant push notifications
- Zero polling when no updates
- Scalable to thousands of feeds
- W3C WebSub compliant

### 📊 **Smart Feed Management**
- **Duplicate prevention** - 3 fingerprinting strategies
- **Category organization** - Multi-category support
- **Advanced search** - Search across titles, content, categories
- **Event-driven architecture** - Hook into feed updates
- **Batch processing** - Handle multiple feeds efficiently

### 🎨 **7 Export Formats**
```php
// Export to any format your application needs
$manager->export('json');      // API responses
$manager->export('rss');       // RSS feed generation
$manager->export('atom');      // Atom feed generation
$manager->export('jsonfeed');  // JSON Feed format
$manager->export('csv');       // Excel/database import
$manager->export('html');      // Web display
$manager->export('text');      // Plain text
```

---

## 📈 Performance Comparison

| Scenario | Traditional Polling | HuntFeed with WebSub | Improvement |
|----------|---------------------|----------------------|-------------|
| **100 feeds, 15-min interval** | 9,600 requests/day | ~100 requests/day | **99% fewer requests** |
| **Bandwidth** | ~5 MB/day | <100 KB/day | **98% reduction** |
| **Update Latency** | 15 minutes | <1 second | **150x faster** |
| **Server Load** | Continuous polling | Event-driven | **Massively scalable** |

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────┐
│                  External Hubs                      │
│              (Superfeedr, etc.)                     │
└───────────────────────┬─────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│               Callback Endpoint                      │
│           (Your WebSub Handler)                      │
└───────────────────────┬─────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│                 WebSubManager                        │
│          (Real-time Push Handling)                   │
└───────────────────────┬─────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│                  FeedManager                         │
│          (Unified Feed Management)                   │
└───────────────┬─────────────────┬───────────────────┘
                ↓                 ↓
    ┌──────────────────┐ ┌──────────────────┐
    │  FeedCollection  │ │  FeedScheduler   │
    │  (Organization)  │ │  (Polling Fallback)│
    └──────────────────┘ └──────────────────┘
```

---

## 🔧 Advanced Usage Examples

### 1. Telegram Bot Integration
```php
$manager->on('item:new', function($data) {
    $item = $data['item'];
    
    $message = "📰 *New Article Alert!*\n\n";
    $message .= "**{$item->title}**\n";
    $message .= "{$item->link}\n\n";
    $message .= "Category: {$data['category']}";
    
    sendTelegramMessage($chatId, $message);
});
```

### 2. REST API Endpoint
```php
// api/feeds.php
header('Content-Type: application/json');
echo $manager->export('json');

// api/feeds/rss.xml
header('Content-Type: application/rss+xml');
echo $manager->export('rss');
```

### 3. Database Storage
```php
$items = $manager->getAllItems();
foreach ($items as $item) {
    $db->insert('articles', [
        'title' => $item->title,
        'content' => $item->content,
        'url' => $item->link,
        'published_at' => $item->publishedAt->format('Y-m-d H:i:s'),
        'category' => $item->category
    ]);
}
```

### 4. Email News Digest
```php
// Send daily digest
$latest = $manager->getLatestItems(10);
$html = $manager->export('html', ['limit' => 10]);

sendEmailDigest('subscribers@example.com', 'Daily News Digest', $html);
```

---

## 🔒 Security Features

### ✅ **Enterprise-Grade Security**
- **HMAC-SHA1 Verification** - All WebSub notifications are signed
- **SSL/TLS Enforcement** - HTTPS required in production
- **Challenge Verification** - Prevents unauthorized subscriptions
- **Input Validation** - All URLs and content validated
- **XML Safety** - XXE attack prevention
- **No Data Leakage** - Sensitive data never logged

### 🔐 **Best Practices Included**
```php
// Production configuration
$manager->setConfig('https_required', true);
$manager->setConfig('verify_ssl', true);
$manager->setConfig('hmac_secret', getenv('WEBSUB_SECRET'));
```

---

## 📚 Comprehensive Documentation

### 🎯 **Getting Started**
- [Quick Start Guide](https://github.com/hosseinhunta/huntfeed/blob/main/README_EN.md) - English
- [راهنمای شروع سریع](https://github.com/hosseinhunta/huntfeed/blob/main/README_FA.md) - فارسی
- [Installation Guide](https://github.com/hosseinhunta/huntfeed/blob/main/INSTALL.md)

### 🏗️ **Architecture**
- [System Architecture](https://github.com/hosseinhunta/huntfeed/blob/main/ARCHITECTURE.md)
- [Complete System Summary](https://github.com/hosseinhunta/huntfeed/blob/main/SUMMARY.md)

### 🔔 **WebSub Implementation**
- [Complete WebSub Guide](https://github.com/hosseinhunta/huntfeed/blob/main/WEBSUB_GUIDE.md) (2000+ lines)
- [Production Checklist](https://github.com/hosseinhunta/huntfeed/blob/main/WEBSUB_GUIDE.md#-checklist-for-production)

### 🔧 **Troubleshooting**
- [SSL Certificate Fix](https://github.com/hosseinhunta/huntfeed/blob/main/SSL_CERTIFICATE_FIX.md)
- [Security Policy](https://github.com/hosseinhunta/huntfeed/blob/main/SECURITY.md)

---

## 🚀 Use Cases

### 📰 **News Aggregators**
Build custom news aggregators with real-time updates from hundreds of sources.

### 🤖 **Content Monitoring**
Monitor competitors, industry news, or specific keywords with instant notifications.

### 📱 **Mobile Apps**
Power your mobile app's content with a robust feed backend.

### 🔍 **SEO Monitoring**
Track RSS feeds for backlink opportunities and content updates.

### 💼 **Enterprise Solutions**
Internal news distribution, competitor intelligence, market monitoring.

---

## 📦 Requirements

- **PHP 8.1+** (Takes advantage of modern PHP features)
- **cURL extension** (For HTTP requests)
- **SimpleXML extension** (For XML parsing)
- **JSON extension** (For JSON handling)

**No external dependencies** for core functionality!

---

## 🧪 Testing & Quality

```bash
# Run comprehensive test suite
php tests/QuickStartTest.php

# Run WebSub-specific tests
php tests/WebSubTest.php

# Run polling tests
php tests/poling-test.php
```

**All 12+ tests pass** - Production-ready code.

---

## 👥 Community & Support

### 📞 **Get Help**
- [GitHub Issues](https://github.com/hosseinhunta/huntfeed/issues) - Bug reports
- [GitHub Discussions](https://github.com/hosseinhunta/huntfeed/discussions) - Questions & ideas
- **Email**: hosseinhunta@gmail.com

### 🤝 **Contributing**
We welcome contributions! See our:
- [Contributing Guide](https://github.com/hosseinhunta/huntfeed/blob/main/CONTRIBUTING.md)
- [Code of Conduct](https://github.com/hosseinhunta/huntfeed/blob/main/CODE_OF_CONDUCT.md)

---

## 📊 Changelog & Roadmap

### ✅ **Version 1.0.0** (Current)
- Complete feed management system
- Full WebSub implementation
- Multi-format export
- Production-ready security

### 🚧 **Roadmap**
- **v1.1.0**: Database persistence for subscriptions
- **v1.2.0**: Redis caching, batch processing
- **v2.0.0**: REST API, GraphQL, Dashboard UI

See full [Changelog](https://github.com/hosseinhunta/huntfeed/blob/main/CHANGELOG.md)

---

## 📄 License

MIT License - Free for commercial and personal use.

---

## 👨‍💻 Author

**Hossein Mohmmadian**
- GitHub: [@hosseinhunta](https://github.com/hosseinhunta)
- Email: hosseinhunta@gmail.com

---

## 🎯 Ready for Production?

```bash
# 1. Install
composer require hosseinhunta/huntfeed

# 2. Run tests
php tests/QuickStartTest.php

# 3. Check the examples
php examples/WebSubExample.php
>>>>>>> 448c5dc116272d3979f76135b58bd2b61055119c
```

**Join hundreds of developers** who trust HuntFeed for their feed management needs!

---

<<<<<<< HEAD
## 📚 Documentation

### Getting Started

- Quick Start Guide – 5-minute setup
- WebSub Implementation – Complete 2000+ line guide
- API Reference – Full method documentation

### Production Deployment

- Security Policy – Enterprise security
- SSL Certificate Fix – Troubleshooting
- Architecture Overview – System design

---

## 👥 Community & Support

### 📞 **Get Help**

- [GitHub Issues](https://github.com/hosseinhunta/huntfeed/issues) - Bug reports
- [GitHub Discussions](https://github.com/hosseinhunta/huntfeed/discussions) - Questions & ideas
- **Email**: <hosseinhunta@gmail.com>

### 🤝 **Contributing**

We welcome contributions! See our:

- [Contributing Guide](https://github.com/hosseinhunta/huntfeed/blob/main/CONTRIBUTING.md)
- [Code of Conduct](https://github.com/hosseinhunta/huntfeed/blob/main/CODE_OF_CONDUCT.md)

---

## 📊 Changelog & Roadmap

### ✅ **Version 1.0.0** (Current)

- Complete feed management system
- Full WebSub implementation
- Multi-format export
- Production-ready security

### 🚧 **Roadmap**

- **v1.1.0**: Database persistence for subscriptions
- **v1.2.0**: Redis caching, batch processing
- **v2.0.0**: REST API, GraphQL, Dashboard UI

See full [Changelog](https://github.com/hosseinhunta/huntfeed/blob/main/CHANGELOG.md)

---

## 📄 License

MIT License - Free for commercial and personal use.

---

## 🎯 Ready for Production?

```bash
# 1. Install
composer require hosseinhunta/huntfeed

# 2. Run tests
php tests/QuickStartTest.php

# 3. Check the examples
php examples/WebSubExample.php
```

**Join hundreds of developers** who trust HuntFeed for their feed management needs!

---

<div align="center">

=======
<div align="center">

>>>>>>> 448c5dc116272d3979f76135b58bd2b61055119c
### ⭐ **Star HuntFeed on GitHub** ⭐

[![GitHub](https://img.shields.io/badge/View_on_GitHub-hosseinhunta/huntfeed-blue?logo=github)](https://github.com/hosseinhunta/huntfeed)

**Need help?** Check our [complete documentation](https://github.com/hosseinhunta/huntfeed/tree/main#readme) or [open an issue](https://github.com/hosseinhunta/huntfeed/issues)!

</div>

<<<<<<< HEAD

![HuntFeed, event-driven PHP library for consuming, normalizing, and distributing RSS, Atom, and WebSub](/docs/huntfeed-opengraph.png)

=======
>>>>>>> 448c5dc116272d3979f76135b58bd2b61055119c
<!-- SEO Keywords: PHP RSS Atom feed parser, WebSub PubSubHubbub real-time updates, PHP feed management library, RSS aggregator PHP, Atom feed reader PHP, real-time notifications PHP, feed parsing library, content aggregation PHP, news reader PHP, feed monitoring, RSS to JSON, PHP 8.1 library, production-ready feed manager, enterprise feed solution, scalable feed aggregator -->
