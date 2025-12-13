# 🚀 HuntFeed - Event-Driven Feed Management Library

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![GitHub](https://img.shields.io/badge/github-hosseinhunta/huntfeed-lightgrey)](https://github.com/hosseinhunta/huntfeed)

**[English](README_EN.md) | [فارسی](README_FA.md) | [Architecture](ARCHITECTURE.md) | [WebSub Guide](WEBSUB_GUIDE.md)**

A kickass, **production-ready** system for fetching, parsing, managing, and exporting RSS/Atom feeds with **WebSub (PubSubHubbub)** support for real-time, push-based updates! 🚀

---

## ⚡ Quick Start (Let's Get This Party Started! 🎉)

### Installation (Grab the Goodies)
```bash
composer require hosseinhunta/huntfeed
```

### Basic Usage (Watch the Magic Happen ✨)
```php
use Hosseinhunta\Huntfeed\Hub\FeedManager;

// Create your feed master
$manager = new FeedManager();

// Fix SSL (for when certificates are being drama queens 👑)
$manager->getFetcher()->setVerifySSL(false);

// Register some awesome feeds
$manager->registerFeeds([
    'tech' => [
        'url' => 'https://news.ycombinator.com/rss',
        'category' => 'Technology'
    ]
]);

// Check for fresh content
$updates = $manager->checkUpdates();

// Export like a boss
$json = $manager->export('json');
$rss = $manager->export('rss');
```

---

## 📋 Features (The Good Stuff 🎁)

### ✅ 5 Feed Parsers (Auto-Detection Magic)
- **RSS 2.0** - Dublin Core, Media RSS, Content Encoded
- **Atom** - Links, Contributors, Rights
- **JSON Feed** - Attachments, Tags, Custom Fields
- **RDF/RSS 1.0** - Dublin Core Metadata
- **GeoRSS** - Geographic Data (for location-aware feeds 📍)

### ✅ Feed Management (Keeping Things Organized)
- **FeedFetcher** - Fetch feeds via HTTP (like a ninja 🥷)
- **FeedScheduler** - Periodic polling (your personal reminder bot ⏰)
- **FeedCollection** - Manage multiple feeds & categories
- **FeedManager** - Unified control center (mission control 🚀)

### ✅ 7 Export Formats (Choose Your Weapon ⚔️)
```php
$manager->export('json');      // For API responses
$manager->export('rss');       // Classic RSS feeds
$manager->export('atom');      // Modern Atom feeds
$manager->export('jsonfeed');  // JSON Feed (the cool kid 😎)
$manager->export('csv');       // Excel/Database friendly
$manager->export('html');      // Web display ready
$manager->export('text');      // Plain and simple
```

### ✅ Duplicate Prevention (No Copycats! 🐱)
- 3 Fingerprinting strategies
- Content-based detection
- Fuzzy matching (smart enough to know twins apart 👯)

### ✅ Event System (When Stuff Happens! 🎭)
```php
$manager->on('item:new', function($data) {
    // Send to Telegram, Email, Slack, carrier pigeon... you name it!
});
```

### ✅ Search & Filter (Find That Needle! 🧵)
```php
$manager->getLatestItems(10);
$manager->getItemsByCategory('Tech');
$manager->searchItems('PHP');
```

---

## 🔒 SSL Certificate Issues (The Drama Zone 🎭)

### Quick Fix (Development - Don't Tell Security! 🤫)
```php
$manager->getFetcher()->setVerifySSL(false);
```

### Proper Fix (The Right Way ✅)
```php
// Run diagnostic (let's diagnose this patient 🏥)
php ssl_test.php

// Use CA bundle path
$manager->getFetcher()->setCaBundlePath('/path/to/cacert.pem');
```

**More deets**: [QUICK_SSL_FIX.md](QUICK_SSL_FIX.md)

---

## 📚 Documentation (Read Me Maybe? 📖)

| Document | Content |
|----------|---------|
| [README_FA.md](README_FA.md) | Full Persian Docs 🇮🇷 |
| [ARCHITECTURE.md](ARCHITECTURE.md) | System Architecture (Behind the scenes 🎬) |
| [QUICK_SSL_FIX.md](QUICK_SSL_FIX.md) | SSL Certificate Solutions |
| [SUMMARY.md](SUMMARY.md) | TL;DR Summary (For the impatient ones ⚡) |
| [SSL_CERTIFICATE_FIX.md](SSL_CERTIFICATE_FIX.md) | Detailed SSL Guide |

---

## 🎯 Examples (Copy-Paste Heaven 😇)

### Register Multiple Feeds (Feed Your App! 🍽️)
```php
$manager->registerFeeds([
    'hn' => [
        'url' => 'https://news.ycombinator.com/rss',
        'category' => 'Technology',
        'interval' => 600, // 10 minutes
    ],
    'medium' => [
        'url' => 'https://medium.com/feed',
        'category' => 'Blogging',
        'interval' => 1800, // 30 minutes
    ],
]);
```

### Handle New Items (Get Notified! 📢)
```php
$manager->on('item:new', function($data) {
    $item = $data['item'];
    $feedId = $data['feedId'];
    
    echo "🎉 New Item Alert: {$item->title}\n";
    sendToTelegram($item);
    sendEmail($item);
    doACoolDance(); // Optional but recommended 💃
});

$updates = $manager->checkUpdates();
```

### Get Statistics (Be Data-Driven! 📊)
```php
$stats = $manager->getStats();
echo "Total feeds: {$stats['total_feeds']}\n";
echo "Total items: {$stats['total_items']}\n";
echo "Categories: " . implode(', ', $stats['categories_list']) . "\n";
```

### Search Items (Find Your Treasure! 🗺️)
```php
$results = $manager->searchItems('PHP');
$techItems = $manager->getItemsByCategory('Technology');
$latest = $manager->getLatestItems(5);
```

---

## 🔧 Advanced Configuration (For Power Users ⚡)

### SSL Certificate (Security First! 🛡️)
```php
$fetcher = $manager->getFetcher();

// Disable verification (for dev only - we won't judge 👀)
$fetcher->setVerifySSL(false);

// Set custom CA bundle
$fetcher->setCaBundlePath('/path/to/cacert.pem');

// Custom headers (dress up your requests! 👔)
$fetcher->setUserAgent('My Awesome App/1.0');
$fetcher->addHeader('Authorization', 'Bearer my-super-secret-token');
```

### Polling Settings (Timing is Everything! ⏱️)
```php
$manager->setConfig('poll_interval', 300);    // 5 minutes
$manager->setConfig('keep_history', true);    // Remember everything
$manager->setConfig('max_items', 100);        // Don't get greedy!
```

### Extra Fields (The Secret Sauce! 🍝)
```php
// Dot notation for nested access (like JavaScript but better 😏)
$author = $item->getExtra('author.name');

// Check existence
if ($item->hasExtra('media_content')) {
    $media = $item->getExtra('media_content');
    // Do something cool with media!
}
```

---

## 🏗️ Architecture (How the Sausage is Made 🏭)

```
FeedManager (The Boss 👑)
├── FeedScheduler (The Timekeeper ⏰)
├── FeedFetcher (The Collector 📥)
├── FeedCollection (The Organizer 📂)
└── FeedExporter (The Translator 🌍)
    └── AutoDetectParser (The Detective 🕵️‍♂️)
        ├── RSS2Parser (Old but Gold 🥇)
        ├── AtomParser (Modern & Fancy 💎)
        ├── JsonFeedParser (The New Kid 🆕)
        ├── RdfParser (The Academic 🎓)
        └── GeoRssParser (The Traveler 🌎)
```

---

## 🧪 Testing (Break Things Safely! 🧪)

### Run Quick Start Test
```bash
php examples/quick_start.php
```

### Run SSL Diagnostic
```bash
php ssl_test.php
```

### Run Full Tests
```php
use Hosseinhunta\Huntfeed\Tests\QuickStartTest;

QuickStartTest::runAll();
```

---

## 🤝 Integration Examples (Play Nice with Others! 🤗)

### Telegram Bot (Ping Your Phone! 📱)
```php
$manager->on('item:new', function($data) {
    $item = $data['item'];
    
    $message = "*📰 {$item->title}*\n";
    $message .= "[Read Now!]({$item->link})";
    
    // Send via Telegram API
    sendToTelegramAPI($chatId, $message);
});
```

### Email Notification (Old School Cool! 📧)
```php
$manager->on('item:new', function($data) {
    mail(
        'user@example.com',
        "📬 New Feed Item: {$data['item']->title}",
        $data['item']->content
    );
});
```

### REST API (Serve it Fresh! 🍽️)
```php
$app->get('/api/feeds', function() use ($manager) {
    return json_encode($manager->getMetadata());
});

$app->get('/api/items', function() use ($manager) {
    return $manager->export('json');
});
```

### Database Storage (Keep it Forever! 🗄️)
```php
$items = $manager->getAllItems();
foreach ($items as $item) {
    $db->insert('items', $item->toArray());
}
```

---

## 📊 Data Models (The Blueprints 📐)

### FeedItem (The Star of the Show 🌟)
```php
id: string           // Unique identifier (like a fingerprint 👆)
title: string        // Article title (the headline 📰)
link: string         // Article URL (where the magic is 🪄)
content: ?string     // Article content (the meat 🍖)
enclosure: ?string   // Media URL (pics or it didn't happen 📸)
publishedAt: DateTimeImmutable (when it happened 🗓️)
category: ?string    (what's it about? 🏷️)
extra: array         // Additional fields (the secret stash 🤫)
```

### Feed (The Collection 📚)
```php
url: string          // Where it lives 🌐
title: string        // Feed name
items: FeedItem[]    // All the goodies inside 🎁
```

### FeedCollection (The Library 📚)
```php
feeds: Feed[]        // All your feeds
categories: array<string, Feed[]>  // Organized by category
```

---

## 🔐 Security Notes (Don't Get Hacked! 🔓)

1. **SSL Verification**: Always verify SSL in production (be safe! 🛡️)
2. **Error Handling**: All exceptions are caught and logged (no surprises! 🎭)
3. **Timeout Protection**: Default 30 seconds timeout (don't wait forever! ⏳)
4. **Input Validation**: URLs are validated before fetching (trust but verify! ✅)

---

## 📦 Requirements (What You Need 🛒)

- PHP >= 8.0 (Get with the times! 🕰️)
- cURL extension (For fetching stuff 🌐)
- SimpleXML extension (XML parsing magic 🧙)
- JSON extension (For JSON things 📝)

---

## 📝 License

MIT License (Do whatever you want with it! 🎈)

---

## 👨‍💻 Author

Hossein Mohmmadian (That's me! 👋)

---

## 🚀 Ready to Use! (Let's Go! 🏃‍♂️)

Start using HuntFeed now and never miss an update again!

```bash
composer require hosseinhunta/huntfeed
php examples/quick_start.php
```

---

**Need Help? Stuck? Confused?** 🤔
- 📖 [Full Documentation](README_FA.md)
- 🔒 [SSL Issues?](QUICK_SSL_FIX.md)
- 🏗️ [Architecture](ARCHITECTURE.md)
- 💡 [Examples](examples/)
- 🐛 [Found a bug? Open an issue!](https://github.com/hosseinhunta/huntfeed/issues)

Happy feeding! 🎣