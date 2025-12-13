<?php
include __DIR__ . '/vendor/autoload.php';

use Hosseinhunta\Huntfeed\Hub\FeedManager;

$manager = new FeedManager();
$manager->getFetcher()->setVerifySSL(false);

$manager->registerFeeds([
    'news' => [
        'url' => 'https://www.khabaronline.ir/rss',
        'category' => 'News',
    ],
]);

// Get all items
$items = $manager->getAllItems();
echo "Total items: " . count($items) . "\n\n";

echo "=== Checking item contents ===\n";
foreach (array_slice($items, 0, 5) as $i => $item) {
    echo "\nItem $i:\n";
    echo "  Title: " . substr($item->title, 0, 50) . "...\n";
    echo "  Category: " . ($item->category ?? 'null') . "\n";
    echo "  Content length: " . strlen((string)$item->content) . "\n";
    echo "  Has '119': " . (str_contains($item->title, '119') ? 'YES' : 'NO') . "\n";
    echo "  Has 'آتش': " . (str_contains(strtolower($item->title), strtolower('آتش')) ? 'YES' : 'NO') . "\n";
}

echo "\n=== Searching for items ===\n";
$all = $manager->getAllItems();
echo "Total items: " . count($all) . "\n";

// Check for '119' manually
$count_119 = 0;
foreach ($all as $item) {
    if (str_contains($item->title, '119')) $count_119++;
}
echo "Items with '119' in title: $count_119\n";

// Check for 'آتش'
$count_fire = 0;
foreach ($all as $item) {
    if (str_contains(strtolower($item->title), strtolower('آتش'))) $count_fire++;
}
echo "Items with 'آتش' in title: $count_fire\n";
