<?php
/**
 * Complete Search Tests (Manual)
 */

include __DIR__ . '/vendor/autoload.php';

use Hosseinhunta\Huntfeed\Hub\FeedManager;
use Hosseinhunta\Huntfeed\Feed\FeedItem;
use Hosseinhunta\Huntfeed\Feed\Feed;

// Manual test data
$testData = [
    'آیین استانی هفته پژوهش و فناوری در سمنان برگزار شد / رشد ۱۳ درصدی مشارکت معلمان',
    'مدیرکل آموزش و پرورش استان سمنان: آغاز توزیع شیر رایگان برای دانش‌آموزان ابتدایی سمنان از فردا',
    'حضور آقاسی در باشگاه استقلال؛ درخواست مدافع جنجالی از مدیران باشگاه',
    'هزینه بیمه تکمیلی بازنشستگان ۱۱۹ درصد افزایش یافت/ پشت بازنشستگان را خالی کردند',
    'ببینید | دود غلیظ در کمربندی اسالم بر اثر آتش‌سوزی بنزین نشت‌کرده در جوی آب',
    'شوهرکشی هولناک در اشتهارد/ اذیت می‌کرد، وقتی خواب بود با تیشه او را کشتم!',
    'جشن بزرگ یلدا در سنندج برگزار می‌شود',
    'بارش چشمگیر در راه کرمانشاه نیست؛ افت دما از جمعه آغاز می‌شود',
];

// Create manager
$manager = new FeedManager();
$manager->getFetcher()->setVerifySSL(false);

// Create test feed manually
$feed = new Feed('test', 'Test Feed');
foreach ($testData as $i => $title) {
    $item = new FeedItem(
        "test-$i",
        $title,
        "https://example.com/$i",
        "Test content for: $title",
        null,
        new \DateTimeImmutable(),
        'Test | Category',
        []
    );
    $feed->addItem($item);
}

// Add directly to collection without registering
$manager->getCollection()->addFeed('test_feed', $feed, 'Local News | Semnan');

// Test 1: Category search
echo "=== Test 1: Search Category 'Semnan' ===\n";
$results = $manager->getItemsByCategory('Semnan');
echo "Found: " . count($results) . " items\n";
foreach ($results as $item) {
    echo "  ✓ " . $item->title . "\n";
}

// Test 2: Number search (Persian digits)
echo "\n=== Test 2: Search '۱۱۹' (Persian digits) ===\n";
$results = $manager->searchItems('۱۱۹');
echo "Found: " . count($results) . " items\n";
foreach ($results as $item) {
    echo "  ✓ " . $item->title . "\n";
}

// Test 3: Persian text search
echo "\n=== Test 3: Search 'آتش' (Fire) ===\n";
$results = $manager->searchItems('آتش');
echo "Found: " . count($results) . " items\n";
foreach ($results as $item) {
    echo "  ✓ " . $item->title . "\n";
}

// Test 4: Partial text search
echo "\n=== Test 4: Search 'سمنان' ===\n";
$results = $manager->searchItems('سمنان');
echo "Found: " . count($results) . " items\n";
foreach ($results as $item) {
    echo "  ✓ " . $item->title . "\n";
}

// Test 5: Another word
echo "\n=== Test 5: Search 'یلدا' ===\n";
$results = $manager->searchItems('یلدا');
echo "Found: " . count($results) . " items\n";
foreach ($results as $item) {
    echo "  ✓ " . $item->title . "\n";
}

echo "\n✅ All tests completed!\n";
