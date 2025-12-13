# 🎉 HuntFeed - System Architecture Overview

## 📐 Complete System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        FeedManager (Hub)                         │
│                    مدیر یکپارچه و مرکزی                          │
└──────────────┬──────────────────────────────┬────────────────────┘
               │                              │
       ┌───────▼────────┐          ┌──────────▼─────────┐
       │  FeedScheduler │          │ FeedCollection     │
       │  (Polling)     │          │ (Management)       │
       └───────┬────────┘          └──────────┬─────────┘
               │                              │
       ┌───────▼────────┐          ┌──────────▼─────────┐
       │ FeedFetcher    │          │ FeedExporter       │
       │ (HTTP Fetch)   │          │ (Multi-Format)     │
       └───────┬────────┘          └────────────────────┘
               │
       ┌───────▼────────┐
       │AutoDetectParser│
       │ (Format Auto   │
       │  Detection)    │
       └───────┬────────┘
               │
      ┌────────┴─────────────────┬───────────┬─────────┐
      │        │        │        │     │         │
    ┌─▼──┐ ┌──▼──┐ ┌──▼───┐ ┌─▼──┐ ┌─▼──┐ ┌──▼──┐
    │RSS2│ │Atom │ │JSON  │ │RDF │ │GeoRSS│
    └────┘ └─────┘ └──────┘ └────┘ └──────┘
```

## 🏗️ Core Components

### 1. **Parser Layer** (پارسرها)
تشخیص و تحلیل فرمت‌های مختلف فیدها

| Parser | Format | Features |
|--------|--------|----------|
| `Rss20Parser` | RSS 2.0 | Dublin Core, Media RSS, Content Encoded |
| `AtomParser` | Atom 1.0 | Multiple links, Contributors, Rights |
| `JsonFeedParser` | JSON Feed | Attachments, Tags, Custom fields |
| `RdfParser` | RDF/RSS 1.0 | Dublin Core metadata |
| `GeoRssParser` | GeoRSS | Geographic data (point, polygon, box) |
| `AutoDetectParser` | Auto | خودکار شناسایی و انتخاب پارسر مناسب |

### 2. **Transport Layer** (انتقال و دریافت)

#### FeedFetcher
```php
- fetch($url): Fetch and parse a single feed
- fetchMultiple($urls): Fetch multiple feeds at once
- hasNewItems(): Check for new content
- getNewItems(): Extract only new items
```

**Features:**
- cURL-based HTTP requests
- Redirect handling
- Custom headers and User-Agent
- Timeout management
- Error handling

### 3. **Scheduling Layer** (برنامه‌ریزی)

#### FeedScheduler
```php
- register($feedId, $url, $interval)
- checkUpdates(): بررسی تمام فیدها
- forceUpdate($feedId): اجبار بروزرسانی
- getStatus($feedId): وضعیت فید
- getHistory($feedId): تاریخچه تغییرات
```

**Features:**
- Periodic polling
- Interval management per feed
- New items detection using fingerprint
- Feed history tracking
- Status monitoring

### 4. **Data Model Layer** (مدل داده‌ها)

#### FeedItem
- **Properties:** id, title, link, content, enclosure, publishedAt, category, extra
- **Methods:**
  - `fingerprint($strategy)`: 3 strategies (default, content, fuzzy)
  - `getExtra($key)`: دسترسی nested با dot notation
  - `toArray()`: تبدیل به Array
  - `equals()`: مقایسه آیتم‌ها
  - `isSimilar()`: شباهت محتوا

#### Feed
- **Duplicate prevention** با fingerprint
- **Methods:**
  - `findByCategory()`, `searchByTitle()`
  - `findAfterDate()`, `findBeforeDate()`
  - `getItemsSorted()`, `paginate()`
  - `getLatest()`

### 5. **Collection Layer** (مدیریت دسته‌بندی‌ها)

#### FeedCollection
```php
- addFeed($feedId, $feed, $categories)
- getFeedsByCategory($category)
- getItemsByCategory($category)
- searchItems($query)
- getLatestItems($limit)
```

**Features:**
- Multi-category support
- Search across all feeds
- Sorting and pagination
- Statistics and metadata

### 6. **Export Layer** (صادرات)

#### FeedExporter
Supported formats:
- **JSON** - برای API و JavaScript
- **RSS 2.0** - فرمت استاندارد
- **Atom 1.0** - فرمت Atom
- **JSON Feed** - فرمت JSON جدید
- **CSV** - برای Excel
- **HTML** - نمایش وبی
- **Text** - متن ساده

### 7. **Management Layer** (مدیریت مرکزی)

#### FeedManager
مدیر یکپارچه تمام سیستم

**Methods:**
```php
- registerFeed($feedId, $url, $options)
- registerFeeds($feedsArray)
- checkUpdates()
- forceUpdateFeed($feedId)
- forceUpdateAll()
- getAllItems(), getLatestItems()
- getItemsByCategory()
- searchItems()
- export($format, $feedId)
- on($event, $handler) // Event listeners
```

**Events:**
- `feed:registered` - فید جدید ثبت شد
- `feed:updated` - فید بروزرسانی شد
- `feed:removed` - فید حذف شد
- `item:new` - خبر جدید یافت شد

## 📊 Data Flow

```
URL → FeedFetcher → HTTP GET → Raw Content
                        ↓
                   AutoDetectParser
                        ↓
                    ↙  ↓  ↖  ↙  ↖
                  RSS Atom JSON RDF GeoRSS
                        ↓
                    FeedItem[]
                        ↓
                      Feed
                        ↓
                  FeedScheduler (Polling)
                        ↓
                   Check for Updates
                        ↓
                 Fingerprint Comparison
                        ↓
              New Items Found? → Event Handler
                        ↓
                  FeedCollection
                        ↓
            ┌───────────┴──────────┐
            ↓                      ↓
         JSON Export         RSS Export → ...
            ↓                      ↓
        API Response         File Output
```

## 🔄 Workflow Example

```php
// 1. Initialize
$manager = new FeedManager();

// 2. Register Feeds
$manager->registerFeeds([
    'tech' => ['url' => '...', 'category' => 'Tech'],
    'news' => ['url' => '...', 'category' => 'News'],
]);

// 3. Setup Event Handlers
$manager->on('item:new', function($data) {
    sendToTelegram($data['item']);
});

// 4. Check Updates (in loop/cron)
$updates = $manager->checkUpdates();

// 5. Get Data
$latest = $manager->getLatestItems(10);

// 6. Export
$json = $manager->export('json');
$rss = $manager->export('rss');
```

## 🎯 Key Features

### ✅ Multi-Format Support
- خودکار شناسایی فرمت
- پارسینگ صحیح هر فرمت
- استخراج فیلدهای خاص هر فرمت

### ✅ Duplicate Prevention
- سه استراتژی Fingerprinting
- تشخیص تکراری‌های دقیق
- تشخیص محتوای مشابه

### ✅ Flexible Export
- 7 فرمت صادرات
- Custom configuration
- API-ready JSON

### ✅ Event-Driven
- Real-time notifications
- Custom event handlers
- Integration with external services

### ✅ Multi-Category Management
- تنظیم دسته‌بندی‌های متعدد
- جستجو و فیلتر
- آمار و Metadata

### ✅ Scheduling & Polling
- بروزرسانی دوره‌ای
- مدیریت Interval
- تاریخچه تغییرات

## 🚀 Integration Points

### تلگرام
```php
$manager->on('item:new', function($data) {
    // Send to Telegram Bot API
});
```

### ایمیل
```php
$manager->on('item:new', function($data) {
    // Send email
});
```

### پایگاه‌داده
```php
$items = $manager->getAllItems();
// Save to database
foreach ($items as $item) {
    $db->insert('items', $item->toArray());
}
```

### API REST
```php
$app->get('/api/feeds', function() {
    return json_encode($manager->getMetadata());
});

$app->get('/api/items', function() {
    return $manager->export('json');
});
```

### موبایل Apps
```php
// Export as JSON for mobile apps
$manager->export('json'); // Ready for iOS/Android/Flutter
```

## 📈 Performance Considerations

### Fingerprinting Strategies
1. **default** - سریع، برای شناسایی دقیق
2. **content** - متوسط، برای cross-source detection
3. **fuzzy** - سریع، برای grouping

### Caching
```php
// Cache exports
$json = apcu_fetch('feeds:json');
if (!$json) {
    $json = $manager->export('json');
    apcu_store('feeds:json', $json, 300); // 5 min
}
```

### Pagination
```php
$page = 1;
$items = $manager->getCollection()->paginate($page, 20);
```

## 🔐 Security Considerations

1. **URL Validation** - تمام URLs بررسی می‌شوند
2. **HTML Escaping** - در صادرات HTML
3. **Error Handling** - مدیریت خطاهای مختلف
4. **Timeout Protection** - جلوگیری از Hang

## 📦 Dependency Requirements

```
PHP >= 8.0
- cURL extension
- SimpleXML extension
- JSON extension
```

---

**نتیجه: یک سیستم کامل، قابل‌توسعه و پروداکشن-رادی برای مدیریت فیدها!** 🎉
