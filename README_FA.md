# 🚀 HuntFeed - Advanced Feed Management System

یک سیستم جامع و حرفه‌ای برای دریافت، تحلیل، مدیریت و صادرات فیدهای RSS/Atom و سایر فرمت‌های خبری.

## 📋 مشخصات

### ۱. **پارسرهای خودکار** 🔍
- **RSS 2.0** - فرمت استاندارد RSS
- **Atom** - فرمت سنجش شده‌شده Atom
- **JSON Feed** - فرمت JSON جدید
- **RDF/RSS 1.0** - فرمت RDF قدیمی
- **GeoRSS** - فیدهای جغرافیایی

**شناسایی خودکار فرمت و پارسینگ بدون نیاز به مشخص‌کردن نوع!**

### ۲. **دریافت و Polling** 📥
- **FeedFetcher** - دریافت فیدها از URL
  - پشتیبانی از cURL
  - مدیریت redirectها
  - Custom Headers و User-Agent
  - Timeout کنترل‌شده

- **FeedScheduler** - Polling دوره‌ای
  - بررسی خودکار فیدها
  - شناسایی خبرهای جدید
  - تاریخچه تغییرات
  - بروزرسانی به‌صورت دوره‌ای

### ۳. **مدیریت داده‌ها** 📦
- **FeedItem** - هر خبر/آیتم
  - 3 استراتژی Fingerprinting برای تشخیص تکراری
  - دسترسی nested به extra fields (dot notation)
  - تبدیل به JSON/Array
  - Metadata شامل

- **Feed** - مجموعه آیتم‌های یک فید
  - جستجو و فیلتر
  - مرتب‌سازی
  - Pagination
  - Duplicate detection

- **FeedCollection** - مدیریت چند فید و دسته‌بندی‌ها
  - سازمان‌دهی برحسب category
  - جستجو در تمام فیدها
  - آماریات جامع

### ۴. **صادرات به فرمت‌های مختلف** 📤
- **JSON** - برای API و JavaScript
- **RSS 2.0** - فرمت استاندارد RSS
- **Atom** - فرمت Atom
- **JSON Feed** - فرمت JSON جدید
- **CSV** - برای Excel و پایگاه‌داده
- **HTML** - نمایش وبی
- **Text** - متن ساده

### ۵. **مدیریت یکپارچه** 🎯
- **FeedManager** - کلاس مرکزی
  - ثبت و مدیریت چند فید
  - بررسی دوره‌ای
  - Event-based architecture
  - Export in any format

## 🔧 نصب و استفاده

### نصب

```bash
composer require hosseinhunta/huntfeed
```

### استفاده اساسی

```php
use Hosseinhunta\Huntfeed\Hub\FeedManager;

// ایجاد مدیر
$manager = new FeedManager();

// ثبت یک فید
$manager->registerFeed('tech_news', 'https://news.ycombinator.com/rss', [
    'category' => 'Technology',
    'interval' => 600, // 10 دقیقه
]);

// بررسی برای خبرهای جدید
$updates = $manager->checkUpdates();

// صادرات به JSON
$json = $manager->export('json');
file_put_contents('feeds.json', $json);

// صادرات به RSS
$rss = $manager->export('rss');
file_put_contents('feeds.rss', $rss);
```

## 📚 مثال‌های پیشرفته

### ثبت چند فید در دسته‌بندی‌های مختلف

```php
$manager->registerFeeds([
    'hn' => [
        'url' => 'https://news.ycombinator.com/rss',
        'category' => 'Technology',
        'interval' => 600,
    ],
    'medium' => [
        'url' => 'https://medium.com/feed',
        'category' => 'Blogging',
        'interval' => 1800,
    ],
    'persian_news' => [
        'url' => 'https://news.fa/rss',
        'category' => 'News',
        'interval' => 300,
    ],
]);
```

### Event-Based Handling

```php
// هنگام ثبت فید جدید
$manager->on('feed:registered', function($data) {
    echo "Feed registered: {$data['feedId']}\n";
});

// هنگام یافتن خبر جدید
$manager->on('item:new', function($data) {
    $item = $data['item'];
    $feedId = $data['feedId'];
    
    // ارسال به Telegram
    sendToTelegram($feedId, $item);
    
    // ارسال ایمیل
    sendEmail($item);
});

// هنگام بروزرسانی فید
$manager->on('feed:updated', function($data) {
    echo "Feed {$data['feedId']} updated with {$data['new_items_count']} new items\n";
});
```

### جستجو و فیلتر

```php
// آخرین 10 خبر
$latest = $manager->getLatestItems(10);

// خبرهای دسته‌ی Technology
$techItems = $manager->getItemsByCategory('Technology');

// جستجوی خبرها
$results = $manager->searchItems('PHP');

// خبرهای جدید از یک دسته‌بندی
$latest = $manager->getLatestItemsByCategory('Technology', 5);
```

### دریافت آمار و Metadata

```php
// آمار کل
$stats = $manager->getStats();
echo "Total Feeds: {$stats['total_feeds']}\n";
echo "Total Categories: {$stats['total_categories']}\n";
echo "Total Items: {$stats['total_items']}\n";

// وضعیت تمام فیدها
$status = $manager->getAllFeedsStatus();
foreach ($status as $feedId => $feed) {
    echo "$feedId: Last updated {$feed['seconds_since_update']}s ago\n";
}

// Metadata جامع
$metadata = $manager->getMetadata();
```

## 🔄 کنترل Fingerprinting

هر آیتم سه نوع Fingerprint دارد برای شناسایی تکراری‌ها:

```php
// Fingerprint اساسی: ID + Link
$item->fingerprint('default');

// Fingerprint محتوا: Title + Content + Date
// (برای شناسایی تکراری‌های cross-source)
$item->fingerprint('content');

// Fingerprint فازی: Title + Date
// (برای گروه‌بندی خبرهای مشابه)
$item->fingerprint('fuzzy');
```

## 💾 صادرات

### صادرات کل فیدها

```php
// JSON
$manager->export('json');

// RSS
$manager->export('rss');

// Atom
$manager->export('atom');

// JSON Feed
$manager->export('jsonfeed');

// CSV
$manager->export('csv');

// HTML
$manager->export('html');

// Text
$manager->export('text');
```

### صادرات یک فید خاص

```php
// صادرات فید خاص به JSON
$manager->export('json', 'tech_news');

// صادرات فید خاص به RSS
$manager->export('rss', 'tech_news');
```

### صادرات Metadata

```php
// Metadata به JSON
$metadata = $manager->exportMetadata('json');

// Metadata به CSV
$metadata = $manager->exportMetadata('csv');

// Metadata به Text
$metadata = $manager->exportMetadata('text');
```

## 🤖 통합 با سرویس‌های خارجی

### ارسال به Telegram

```php
$manager->on('item:new', function($data) {
    $item = $data['item'];
    
    $telegramToken = 'YOUR_BOT_TOKEN';
    $chatId = 'YOUR_CHAT_ID';
    
    $message = "*{$item->title}*\n";
    $message .= "{$item->publishedAt->format('Y-m-d H:i')}\n";
    $message .= "[Read More]({$item->link})";
    
    $url = "https://api.telegram.org/bot{$telegramToken}/sendMessage";
    
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'Markdown',
    ];
    
    // Send via cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_exec($ch);
});
```

### ارسال ایمیل

```php
$manager->on('item:new', function($data) {
    $item = $data['item'];
    
    $to = 'user@example.com';
    $subject = "New Item: {$item->title}";
    $body = "{$item->title}\n{$item->link}\n{$item->content}";
    
    mail($to, $subject, $body);
});
```

## 📊 ساختار پروژه

```
src/
├── Parser/              # Parsers برای فرمت‌های مختلف
│   ├── ParserInterface.php
│   ├── Rss20Parser.php
│   ├── AtomParser.php
│   ├── JsonFeedParser.php
│   ├── RdfParser.php
│   ├── GeoRssParser.php
│   └── AutoDetectParser.php
├── Transport/           # دریافت فیدها
│   ├── PollingTransport.php
│   └── FeedFetcher.php
├── Engine/              # موتور اصلی
│   ├── UpdateDetector.php
│   └── FeedScheduler.php
├── Hub/                 # مدیریت یکپارچه
│   ├── FeedManager.php
│   ├── FeedCollection.php
│   ├── FeedExporter.php
│   └── NotificationService.php
└── Feed/                # مدل‌های داده
    ├── Feed.php
    └── FeedItem.php
```

## 🎯 مموارد خاص

### Extra Fields (فیلدهای اضافی)

هر Parser فیلدهای اضافی را بسته به فرمت فید استخراج می‌کند:

```php
// RSS: Dublin Core, Media RSS, Content Encoded, etc.
$creator = $item->getExtra('creator');
$mediaContent = $item->getExtra('media_content');

// Atom: Author, Links, Contributors, Rights
$author = $item->getExtra('author');
$links = $item->getExtra('links');

// JSON Feed: Attachments, Tags, Image
$attachments = $item->getExtra('attachments');
$tags = $item->getExtra('tags');

// GeoRSS: Geo Data
$geo = $item->getExtra('geo');
if ($geo) {
    echo $geo['latitude'] . ', ' . $geo['longitude'];
}
```

### Nested Access

```php
// از Dot notation استفاده کنید
$authorName = $item->getExtra('author.name');

// بررسی وجود
if ($item->hasExtra('author.email')) {
    $email = $item->getExtra('author.email');
}
```

## 🔐 Duplicate Management

```php
// بررسی تکراری‌ها
if ($item1->equals($item2)) {
    // همان item
}

// بررسی شباهت محتوا
if ($item1->isSimilar($item2)) {
    // محتوای مشابه
}
```

## 📈 Configuration

```php
// تنظیم Polling Interval
$manager->setConfig('poll_interval', 300); // 5 دقیقه

// تنظیم Keep History
$manager->setConfig('keep_history', true);

// تنظیم Max Items
$manager->setConfig('max_items', 100);

// دریافت config
$interval = $manager->getConfig('poll_interval');
$allConfig = $manager->getConfig();
```

## 📄 لایسنس

MIT License


---

**آماده برای تولید محتوا و ارسال به پلتفرم‌های مختلف! 🚀**
