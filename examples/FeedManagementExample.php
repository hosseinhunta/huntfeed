<?php
/**
 * Complete Example: Using HuntFeed System
 * 
 * This example demonstrates the complete workflow of the HuntFeed system:
 * 1. Registering multiple feeds with categories
 * 2. Checking for updates periodically
 * 3. Handling new items with event listeners
 * 4. Exporting in multiple formats
 * 5. Integration with external services (Telegram, Email, etc.)
 */

namespace Hosseinhunta\Huntfeed\Examples;

include __DIR__ . '/../vendor/autoload.php';

use Hosseinhunta\Huntfeed\Hub\FeedManager;
use Hosseinhunta\Huntfeed\Hub\FeedExporter;

class FeedManagementExample
{
    /**
     * Example 1: Basic Setup and Feed Registration
     * 
     */
    public static function exampleBasicSetup(): void
    {
        // Initialize the FeedManager
        $manager = new FeedManager();
        
        // Fix SSL certificate error for development
        $manager->getFetcher()->setVerifySSL(false);

        // Configure polling interval (5 minutes)
        $manager->setConfig('poll_interval', 300);
        $manager->setConfig('keep_history', true);

        // Register feeds with categories
        $manager->registerFeeds([
            'tech_news' => [
                'url' => 'https://www.khabaronline.ir/rss',
                'category' => 'Technology',
                'interval' => 600, // 10 minutes
            ],
            'php_news' => [
                'url' => 'https://www.php.net/feed.atom',
                'category' => 'Technology',
                'interval' => 1800, // 30 minutes
            ],
            'news_fa' => [
                'url' => 'https://sahebkhabar.ir/rss',
                'category' => 'News',
                'interval' => 300, // 5 minutes
            ],
        ]);

        // Get basic statistics
        $stats = $manager->getStats();
        echo "Registered Feeds: " . $stats['total_feeds'] . "\n";
        echo "Total Items: " . $stats['total_items'] . "\n";
    }

    /**
     * Example 2: Checking for Updates and Handling New Items
     * 
     * نکته: این مثال feed‌ها را register و سپس برای اپدیت چک می‌کند
     * و برای هر خبر جدید event trigger می‌کند
     */
    public static function exampleCheckUpdates(): void
    {
        $manager = new FeedManager();
        
        // Fix SSL for development
        $manager->getFetcher()->setVerifySSL(false);

        // Event: When a new item is found
        $manager->on('item:new', function($data) {
            $item = $data['item'];
            echo "\n[📰 NEW ITEM] Feed: {$data['feedId']}\n";
            echo "   Title: {$item->title}\n";
            echo "   URL: {$item->link}\n";
            echo "   Date: {$item->publishedAt->format('Y-m-d H:i:s')}\n";
            echo "   Category: " . ($item->category ?? 'N/A') . "\n";
            if (!empty($item->content)) {
                echo "   Content: " . substr($item->content, 0, 100) . "...\n";
            }
        });

        // Register feeds
        echo "📡 Registering feeds...\n";
        $manager->registerFeeds([
            'feed1' => [
                'url' => 'https://www.khabaronline.ir/rss',
                'category' => 'News',
            ],
            'feed2' => [
                'url' => 'https://sahebkhabar.ir/rss',
                'category' => 'Tech',
            ],
        ]);

        echo "✅ Feeds registered!\n\n";

        // Show initial items
        echo "⏳ Getting initial feed items...\n";
        echo str_repeat("=", 60) . "\n";
        
        $items = $manager->getAllItems();
        echo "📊 Initial items loaded: " . count($items) . "\n\n";

        // Show categories
        echo "📂 Categories:\n";
        $metadata = $manager->getMetadata();
        foreach ($metadata['stats']['categories'] as $category => $stats) {
            echo "   ✓ $category: {$stats['items_count']} items\n";
        }

        echo "\n" . str_repeat("=", 60) . "\n";

        // Show latest items
        echo "\n🔝 Latest 10 Items:\n";
        $latest = $manager->getLatestItems(10);
        foreach ($latest as $i => $item) {
            echo "\n   " . ($i+1) . ". {$item->title}\n";
            echo "      Category: {$item->category}\n";
            echo "      URL: {$item->link}\n";
        }

        // Show statistics
        echo "\n\n📈 Statistics:\n";
        $stats = $manager->getStats();
        echo "   Total Feeds: {$stats['total_feeds']}\n";
        echo "   Total Items: {$stats['total_items']}\n";
        echo "   Categories: " . implode(', ', $stats['categories_list']) . "\n";
    }

    /**
     * Example 3: Getting and Searching Items
     * 
     */
    public static function exampleSearchAndFilter(): void
    {
        $manager = new FeedManager();
        
        // Fix SSL for development
        $manager->getFetcher()->setVerifySSL(false);

        // Register multiple feeds with different categories
        $manager->registerFeeds([
            'semnan_news' => [
                'url' => 'https://www.khabaronline.ir/rss/tp/108',
                'category' => 'Semnan Province | Local News',
            ],
            'religion_news' => [
                'url' => 'https://www.khabaronline.ir/rss',
                'category' => 'News',
            ],
            'tech_news' => [
                'url' => 'https://sahebkhabar.ir/rss/tp/6',
                'category' => 'Technology',
            ],
        ]);

        echo "=== Registered Categories ===\n";
        // Show available categories
        $metadata = $manager->getMetadata();
        foreach ($metadata['stats']['categories'] as $category => $stats) {
            echo "- $category ({$stats['items_count']} items)\n";
        }

        // Get latest items from all feeds
        echo "\n=== Latest 10 Items ===\n";
        $latest = $manager->getLatestItems(10);
        echo "Found: " . count($latest) . " items\n";
        foreach ($latest as $item) {
            echo "- {$item->title}\n";
        }

        // Get items from specific category (partial match)
        $cat = "فناوری";
        echo "\n=== Items from category containing '$cat' ===\n";
        $catItems = $manager->getItemsByCategory($cat);
        echo "Found items: " . count($catItems) . "\n";
        foreach (array_slice($catItems, 0, 5) as $item) {
            echo "- {$item->title}\n";
        }

        // Search items (searches in title, content, category, link)
        echo "\n=== Search results for 'free/رایگان' ===\n";
        $results = $manager->searchItems('رایگان');
        echo "Found items: " . count($results) . "\n";
        foreach (array_slice($results, 0, 5) as $item) {
            echo "- {$item->title}\n";
        }
        
        // Search with number
        echo "\n=== Search results for number '۱۷' ===\n";
        $results = $manager->searchItems('۱۷');
        echo "Found items: " . count($results) . "\n";
        foreach (array_slice($results, 0, 5) as $item) {
            echo "- {$item->title}\n";
        }
    }

    /**
     * Example 4: Exporting in Multiple Formats
     */
    public static function exampleExporting(): void
    {
        $manager = new FeedManager();
        
        // Fix SSL for development
        $manager->getFetcher()->setVerifySSL(false);

        // Register a feed
        $manager->registerFeeds([
            'news' => [
                'url' => 'https://www.khabaronline.ir/rss',
                'category' => 'News',
            ],
        ]);

        // Export to different formats
        
        // JSON
        $json = $manager->export('json');
        file_put_contents('feeds.json', $json);
        echo "Exported to JSON: feeds.json\n";

        // RSS
        $rss = $manager->export('rss');
        file_put_contents('feeds.rss', $rss);
        echo "Exported to RSS: feeds.rss\n";

        // HTML
        $html = $manager->export('html');
        file_put_contents('feeds.html', $html);
        echo "Exported to HTML: feeds.html\n";

        // CSV
        $csv = $manager->export('csv');
        file_put_contents('feeds.csv', $csv);
        echo "Exported to CSV: feeds.csv\n";

        // Metadata
        $metadata = $manager->exportMetadata('json');
        file_put_contents('metadata.json', $metadata);
        echo "Exported metadata: metadata.json\n";
    }

    /**
     * Example 5: Managing Multiple Categories and Statistics
     */
    public static function exampleStatistics(): void
    {
        $manager = new FeedManager();
        
        // Fix SSL for development
        $manager->getFetcher()->setVerifySSL(false);

        // Register feeds in different categories
        $manager->registerFeeds([
            'hn' => ['url' => 'https://news.ycombinator.com/rss', 'category' => 'Tech'],
            'wired' => ['url' => 'https://www.wired.com/feed/rss', 'category' => 'News'],
        ]);

        // Get complete metadata
        $metadata = $manager->getMetadata();

        echo "=== Feed Manager Statistics ===\n";
        echo "Total Feeds: " . $metadata['stats']['total_feeds'] . "\n";
        echo "Total Categories: " . $metadata['stats']['total_categories'] . "\n";
        echo "Total Items: " . $metadata['stats']['total_items'] . "\n";

        echo "\nBy Category:\n";
        foreach ($metadata['stats']['categories'] as $category => $stats) {
            echo "  $category: {$stats['feeds_count']} feeds, {$stats['items_count']} items\n";
        }

        echo "\nFeed Status:\n";
        foreach ($metadata['feeds_status'] as $feedId => $status) {
            echo "  {$feedId}: Last updated {$status['seconds_since_update']}s ago\n";
        }
    }
}

// Run examples (uncomment to test)
// FeedManagementExample::exampleBasicSetup();
FeedManagementExample::exampleCheckUpdates();
// FeedManagementExample::exampleSearchAndFilter();
// FeedManagementExample::exampleExporting();
// FeedManagementExample::exampleStatistics();
