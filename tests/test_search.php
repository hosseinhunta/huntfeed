<?php
/**
 * Test Search Functionality
 */

include __DIR__ . '/vendor/autoload.php';

use Hosseinhunta\Huntfeed\Hub\FeedManager;

// Create manager
$manager = new FeedManager();
$manager->getFetcher()->setVerifySSL(false);

// Register multiple feeds
echo "Registering feeds...\n";
$manager->registerFeeds([
    'semnan_news' => [
        'url' => 'https://www.khabaronline.ir/rss',
        'category' => 'Semnan Province | Local News',
    ],
    'religion_news' => [
        'url' => 'https://www.khabaronline.ir/rss',
        'category' => 'Religion | Islamic Guidance',
    ],
]);

// Get metadata
$metadata = $manager->getMetadata();
echo "\n=== Categories ===\n";
foreach ($metadata['stats']['categories'] as $category => $stats) {
    echo "- $category ({$stats['items_count']} items)\n";
}

// Test 1: Partial category search
echo "\n=== Test 1: Category Search 'Semnan' ===\n";
$items = $manager->getItemsByCategory('Semnan');
echo "Found: " . count($items) . " items\n";
echo "First 3 items:\n";
foreach (array_slice($items, 0, 3) as $item) {
    echo "  - " . $item->title . "\n";
}

// Test 2: Number search
echo "\n=== Test 2: Search '119' ===\n";
$items = $manager->searchItems('119');
echo "Found: " . count($items) . " items\n";
echo "First 3 items:\n";
foreach (array_slice($items, 0, 3) as $item) {
    echo "  - " . $item->title . "\n";
}

// Test 3: Persian text search
echo "\n=== Test 3: Search 'آتش' ===\n";
$items = $manager->searchItems('آتش');
echo "Found: " . count($items) . " items\n";
echo "First 3 items:\n";
foreach (array_slice($items, 0, 3) as $item) {
    echo "  - " . $item->title . "\n";
}

// Save results to file
file_put_contents('search_results.json', json_encode([
    'categories' => $metadata['stats']['categories'],
    'semnan_results' => count($manager->getItemsByCategory('Semnan')),
    'search_119_results' => count($manager->searchItems('119')),
    'search_fire_results' => count($manager->searchItems('آتش')),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n✅ Results saved to search_results.json\n";
