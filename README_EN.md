# HuntFeed - Event-Driven Feed Management Library

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![GitHub](https://img.shields.io/badge/github-hosseinhunta/huntfeed-lightgrey)](https://github.com/hosseinhunta/huntfeed)

A powerful, event-driven PHP library for consuming, normalizing, and distributing RSS, Atom, and **WebSub (PubSubHubbub)** feed updates with real-time push notifications.

## 🚀 Key Features

### Feed Management
- ✅ **Auto-Detection** of RSS 2.0 and Atom 1.0 formats
- ✅ **Unified API** for all feed types
- ✅ **Category Organization** - Group feeds by category
- ✅ **Advanced Search** - Search across title, content, and categories
- ✅ **Item Fingerprinting** - Detect and handle duplicate content
- ✅ **Multi-Format Export** - JSON, RSS, Atom, CSV, HTML, Text

### Real-Time Updates (WebSub)
- ✅ **Push Notifications** - Get updates instantly via WebSub hubs
- ✅ **Automatic Hub Detection** - Detects hubs in RSS/Atom feeds
- ✅ **Signature Verification** - HMAC-SHA1 verification for security
- ✅ **Graceful Fallback** - Falls back to polling if hub unavailable
- ✅ **Transparent API** - Same interface for all sources

### Performance
- 📊 **99.0%** fewer HTTP requests (WebSub vs polling)
- 💾 **98%** bandwidth reduction
- ⚡ **150x** faster updates (real-time vs 15-minute polling)
- ♾️ **Unlimited** scalability with push-based approach

### Developer Experience
- 🔒 **Secure** - HTTPS, HMAC-SHA1, challenge verification
- 📝 **Well-Documented** - 2000+ lines of documentation
- 🧪 **Well-Tested** - 12+ comprehensive tests
- 🏗️ **Clean Architecture** - Event-driven, modular design
- 🎨 **Type-Hinted** - Full PHP 8.1+ type support

## 📦 Installation

### Via Composer
```bash
composer require hosseinhunta/huntfeed
```

### From Source
```bash
git clone https://github.com/hosseinhunta/huntfeed.git
cd huntfeed
```

## 🚀 Quick Start

### 1. Basic Feed Management

```php
<?php
require 'vendor/autoload.php';

use Hosseinhunta\Huntfeed\Hub\FeedManager;

// Create manager
$feedManager = new FeedManager();

// Register feeds
$feedManager->registerFeed('tech', 'https://news.ycombinator.com/rss');
$feedManager->registerFeed('blog', 'https://example.com/feed.xml');

// Check for updates
$updates = $feedManager->checkUpdates();

// Process items
foreach ($updates as $feedId => $items) {
    foreach ($items as $item) {
        echo "Title: " . $item->getTitle() . "\n";
        echo "Link: " . $item->getLink() . "\n";
        echo "---\n";
    }
}
```

### 2. WebSub (Real-Time Updates)

```php
<?php
require 'vendor/autoload.php';

use Hosseinhunta\Huntfeed\Hub\FeedManager;
use Hosseinhunta\Huntfeed\WebSub\WebSubManager;

// Create managers
$feedManager = new FeedManager();
$webSubManager = new WebSubManager(
    $feedManager,
    'https://your-domain.com/callback.php'
);

// Enable auto-subscription and fallback
$webSubManager
    ->setAutoSubscribe(true)
    ->setFallbackToPolling(true);

// Register feeds (automatically detects hubs)
$webSubManager->registerFeedWithWebSub(
    'tech_news',
    'https://example.com/feed.xml'
);

// Same FeedItem interface regardless of method
$updates = $webSubManager->checkUpdates();
```

### 3. Webhook Callback Endpoint

```php
<?php
// callback.php - Webhook endpoint for WebSub notifications
require 'vendor/autoload.php';

$webSubManager = // ... initialize manager

$handler = $webSubManager->getHandler();
$result = $handler->processRequest(
    $_SERVER['REQUEST_METHOD'],
    $_GET,
    file_get_contents('php://input'),
    getallheaders()
);

http_response_code($result['status'] ?? 200);
echo $result['body'] ?? '';
```

## 📚 Documentation

- **[README_FA.md](README_FA.md)** - فارسی (Persian)
- **[WEBSUB_GUIDE.md](WEBSUB_GUIDE.md)** - Complete WebSub implementation guide
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - System architecture details
- **[SECURITY.md](SECURITY.md)** - Security policy and best practices
- **[CONTRIBUTING.md](CONTRIBUTING.md)** - How to contribute

## 🧪 Testing

Run the comprehensive test suite:

```bash
# All tests (12 total)
php tests/QuickStartTest.php

# WebSub tests only
php tests/WebSubTest.php

# Polling tests
php tests/poling-test.php
```

## 🎯 Use Cases

### 1. Feed Aggregator
Aggregate multiple RSS/Atom feeds into a single application.

### 2. Real-Time Notifications
Get instant notifications when feeds update using WebSub.

### 3. Feed Normalizer
Normalize feeds from different sources into a consistent format.

### 4. Feed Distributor
Export feeds in multiple formats (JSON, RSS, Atom, CSV, etc.).

### 5. Custom Feed Processing
Build custom feed processing pipelines with events.

## 🔒 Security Features

- **HTTPS Support** - Enforced for WebSub (configurable)
- **HMAC-SHA1** - Signature verification for all notifications
- **Challenge Verification** - Confirms subscription legitimacy
- **Input Validation** - All inputs validated and sanitized
- **Safe XML Parsing** - Protection against XXE attacks
- **Error Handling** - Graceful error handling without info leakage

## 📊 Performance Benchmarks

### Scenario: 100 feeds, 15-minute polling interval

| Metric | Polling | WebSub | Improvement |
|--------|---------|--------|-------------|
| Requests/day | 9,600 | ~100 | 99.0% ↓ |
| Bandwidth/day | ~5 MB | <100 KB | 98% ↓ |
| Update Latency | 15 min | <1 sec | 150x ↑ |
| Server Load | Continuous | Event-driven | Scalable |

## 🏗️ Architecture

```
External Hubs (Superfeedr, etc.)
        ↓
   Callback Endpoint
        ↓
   WebSubHandler
        ↓
   WebSubManager
        ↓
   FeedManager (Unified Interface)
        ↓
   Application
```

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests
5. Submit a pull request

## 📋 Requirements

- **PHP 8.1+**
- No external dependencies
- Works with any HTTP server

## 📄 License

HuntFeed is open-sourced software licensed under the [MIT license](LICENSE).

## 👨‍💻 Author

**Hossein Mohmmadian**
- GitHub: [@hosseinhunta](https://github.com/hosseinhunta)

## 🙏 Acknowledgments

- [W3C WebSub Specification](https://www.w3.org/TR/websub/)
- [PubSubHubbub Protocol](https://pubsubhubbub.appspot.com/)
- PHP Community

## 📞 Support

- 🐛 [Report Issues](https://github.com/hosseinhunta/huntfeed/issues)
- 💬 [Discussions](https://github.com/hosseinhunta/huntfeed/discussions)
- 📖 [Full Documentation](WEBSUB_GUIDE.md)

## 🚀 Roadmap

- Database persistence for subscriptions
- Automatic lease renewal
- Redis caching support
- REST/GraphQL APIs
- Web dashboard

---

**HuntFeed** - Bringing real-time feed updates to PHP 🚀

