<?php
/**
 * Example: Simulating Multiple Feed Updates Over Time
 * 
 */

namespace Hosseinhunta\Huntfeed\Examples;

include __DIR__ . '/../vendor/autoload.php';

use Hosseinhunta\Huntfeed\Hub\FeedManager;
use Hosseinhunta\Huntfeed\Feed\Feed;
use Hosseinhunta\Huntfeed\Feed\FeedItem;

class UpdateSimulationExample
{
    public static function demonstrateUpdatesWithEvents(): void
    {
        echo "🎯 Feed Update Simulation with Events\n";
        echo str_repeat("=", 70) . "\n\n";

        $manager = new FeedManager();
        
        // Fix SSL for development
        $manager->getFetcher()->setVerifySSL(false);
        
        // Setup event handlers FIRST
        echo "1️⃣ Setting up event handlers...\n\n";

        $manager->on('feed:registered', function($data) {
            echo "   ✓ Feed registered: {$data['feedId']}\n";
        });

        $manager->on('feed:updated', function($data) {
            echo "\n   🔄 Feed updated: {$data['feedId']}\n";
            echo "   📊 New items: {$data['new_items_count']}\n";
        });

        $manager->on('item:new', function($data) {
            $item = $data['item'];
            echo "\n   📰 NEW ITEM: {$item->title}\n";
            echo "      Category: {$item->category}\n";
        });

        // Step 1: Register first feed
        echo "\n2️⃣ Registering first feed (News)...\n";
        $manager->registerFeeds([
            'news' => [
                'url' => 'https://www.khabaronline.ir/rss',
                'category' => 'News',
            ],
        ]);

        // Step 2: Show initial state
        echo "\n\n3️⃣ Initial State:\n";
        echo "─" . str_repeat("─", 69) . "\n";
        $stats = $manager->getStats();
        echo "Total items: {$stats['total_items']}\n";
        echo "Categories: " . implode(', ', $stats['categories_list']) . "\n\n";

        echo "Latest 3 items:\n";
        foreach (array_slice($manager->getLatestItems(3), 0, 3) as $i => $item) {
            echo "  " . ($i+1) . ". " . substr($item->title, 0, 60) . "...\n";
        }

        // Step 3: Simulate searching for specific content
        echo "\n\n4️⃣ Search Examples:\n";
        echo "─" . str_repeat("─", 69) . "\n";

        $searches = ['استان', 'خبر', 'اقتصادی'];
        foreach ($searches as $keyword) {
            $results = $manager->searchItems($keyword);
            echo "\n🔍 Search: '$keyword'\n";
            echo "   Found: " . count($results) . " items\n";
            if (count($results) > 0) {
                foreach (array_slice($results, 0, 2) as $item) {
                    echo "   • " . substr($item->title, 0, 55) . "...\n";
                }
            }
        }

        // Step 4: Filter by category
        echo "\n\n5️⃣ Filter by Category:\n";
        echo "─" . str_repeat("─", 69) . "\n";

        $newsItems = $manager->getItemsByCategory('News');
        echo "\n📂 Category: News\n";
        echo "   Total items: " . count($newsItems) . "\n";
        echo "   Latest items:\n";
        foreach (array_slice($newsItems, 0, 3) as $i => $item) {
            echo "   " . ($i+1) . ". " . substr($item->title, 0, 50) . "...\n";
        }

        // Step 5: Export options
        echo "\n\n6️⃣ Export Options:\n";
        echo "─" . str_repeat("─", 69) . "\n";

        $formats = ['json' => '.json', 'rss' => '.rss', 'csv' => '.csv'];
        foreach ($formats as $format => $ext) {
            $export = $manager->export($format);
            $filename = "feeds_export" . $ext;
            file_put_contents($filename, $export);
            $size = strlen($export);
            echo "\n✓ $format → $filename\n";
            echo "  Size: " . number_format($size) . " bytes\n";
        }

        // Step 6: Summary
        echo "\n\n7️⃣ System Summary:\n";
        echo str_repeat("=", 70) . "\n";

        $metadata = $manager->getMetadata();
        echo "\n📊 Statistics:\n";
        echo "   • Registered Feeds: " . $metadata['stats']['total_feeds'] . "\n";
        echo "   • Total Items: " . $metadata['stats']['total_items'] . "\n";
        echo "   • Categories: " . $metadata['stats']['total_categories'] . "\n";

        echo "\n📁 Items per Category:\n";
        foreach ($metadata['stats']['categories'] as $cat => $info) {
            echo "   • $cat: " . $info['items_count'] . " items\n";
        }

        echo "\n🎯 Usage:\n";
        echo "   1. Register feeds with categories\n";
        echo "   2. Set up event handlers for new items\n";
        echo "   3. Search and filter items\n";
        echo "   4. Export in multiple formats\n";
        echo "   5. Send to external services (Telegram, Email, etc.)\n";

        echo "\n" . str_repeat("=", 70) . "\n";
        echo "✅ All demonstrations completed!\n";
    }
}

// Run the example
UpdateSimulationExample::demonstrateUpdatesWithEvents();
