# HuntFeed PHP Library – Real‑Time RSS & WebSub Feed Manager

> **HuntFeed** is a modern, high‑performance **PHP feed management library** for RSS, Atom, JSON Feed, RDF, and GeoRSS with **native WebSub (PubSubHubbub)** support. It is designed for developers who need **real‑time feed updates**, scalability, and clean APIs.

---

## Why HuntFeed?

* 🚀 **Real‑time updates** via WebSub (push‑based feeds)
* 🔄 Supports **RSS 2.0, Atom 1.0, JSON Feed, RDF, GeoRSS**
* 🧩 Event‑driven architecture (new items, duplicates, errors)
* 📦 Easy Composer installation
* ⚡ Optimized for performance & large feed collections
* 🔍 Built‑in search, export & statistics

Ideal for **news aggregators**, **Telegram bots**, **APIs**, **dashboards**, and **monitoring systems**.

---

## Installation

### Install via Composer (Recommended)

```bash
composer require hosseinhunta/huntfeed
```

### Manual Installation

```bash
git clone https://github.com/hosseinhunta/huntfeed.git
cd huntfeed
composer install
```

---

## Quick Start Example (5 Minutes)

### 1️⃣ Initialize FeedManager

```php
<?php
require 'vendor/autoload.php';

use Hosseinhunta\Huntfeed\Hub\FeedManager;

$manager = new FeedManager();
```

---

### 2️⃣ Register Feeds

```php
$manager->registerFeed('hackernews', 'https://news.ycombinator.com/rss', [
    'category' => 'Technology',
    'interval' => 300
]);
```

Register multiple feeds at once:

```php
$manager->registerFeeds([
    'techcrunch' => [
        'url' => 'https://techcrunch.com/feed/',
        'category' => 'Technology'
    ],
    'bbc_world' => [
        'url' => 'https://bbc.com/news/world/rss.xml',
        'category' => 'News'
    ]
]);
```

---

### 3️⃣ Check for Updates

```php
$updates = $manager->checkUpdates();

foreach ($updates as $feedId => $items) {
    echo "{$feedId}: " . count($items) . " new items\n";
}
```

---

### 4️⃣ Handle Events (New Items, Errors, Duplicates)

```php
$manager->on('item:new', function ($data) {
    $item = $data['item'];
    echo "New article: {$item->title}\n";
});
```

Supported events:

* `feed:registered`
* `feed:updated`
* `item:new`
* `item:duplicate`
* `error`

---

### 5️⃣ Export Feeds (API‑Ready)

```php
$json = $manager->export('json');
$rss  = $manager->export('rss');
$atom = $manager->export('atom');
```

Export a single feed:

```php
$techJson = $manager->export('json', 'techcrunch');
```

Supported formats:

`json`, `rss`, `atom`, `jsonfeed`, `csv`, `html`, `text`

---

## Configuration Options

### Feed Registration Options

```php
$manager->registerFeed('example', 'https://example.com/feed.xml', [
    'category' => 'Example',
    'interval' => 600,
    'enabled' => true,
    'max_items' => 50,
    'keep_history' => true,
    'user_agent' => 'MyApp/1.0',
    'headers' => [
        'Authorization' => 'Bearer TOKEN'
    ]
]);
```

---

### SSL & HTTP Configuration

```php
$fetcher = $manager->getFetcher();

// Development only
$fetcher->setVerifySSL(false);

// Production
$fetcher->setCaBundlePath('/path/to/cacert.pem');
```

---

## Common Use Cases

### 🔍 Search Across Feeds

```php
$results = $manager->searchItems('PHP');
```

### 🗂 Filter by Category

```php
$techItems = $manager->getItemsByCategory('Technology');
```

### 🕒 Get Latest Items

```php
$latest = $manager->getLatestItems(10);
```

---

### 📊 Feed Statistics

```php
$stats = $manager->getStats();
```

Includes:

* Total feeds
* Total items
* Categories
* Most active feed
* New items today

---

### 🔄 Force Updates

```php
$manager->forceUpdateFeed('hackernews');
$manager->forceUpdateAll();
```

---

## SEO & Performance Best Practices

* ✅ Prefer **WebSub** for high‑traffic feeds
* ✅ Use **longer intervals** for slow‑changing sources
* ✅ Cache exported feeds (`json`, `rss`) at API level
* ✅ Use descriptive feed IDs (e.g. `bbc_world_news`)
* ✅ Limit `max_items` for memory efficiency

---

## Troubleshooting

**Feed not updating?**

* Check SSL settings
* Verify feed URL
* Increase timeout

**High memory usage?**

* Reduce `max_items`
* Disable `keep_history`

**Performance issues?**

* Enable WebSub
* Cache export results

---

## Next Steps

* 📘 Read **FeedManager API Reference**
* ⚡ Learn **WebSub integration**
* 🧪 Explore **real‑world examples**
* 🚀 Deploy HuntFeed in production

---

⭐ If you find HuntFeed useful, **star the repository on GitHub** and help the project grow!
