<?php
/**
 * Real World Example: Continuous Feed Updates
 * 
 */

namespace Hosseinhunta\Huntfeed\Examples;

include __DIR__ . '/../vendor/autoload.php';

use Hosseinhunta\Huntfeed\Hub\FeedManager;

class RealWorldExample
{

    public static function continuousFeedMonitoring(): void
    {
        $manager = new FeedManager();
        $manager->getFetcher()->setVerifySSL(false);

        echo "🔔 Setting up event handlers...\n\n";

        // Event: When a new item is found
        $manager->on('item:new', function($data) {
            $item = $data['item'];
            echo "\n" . str_repeat("─", 70) . "\n";
            echo "📰 NEW ITEM RECEIVED!\n";
            echo "─" . str_repeat("─", 69) . "\n";
            echo "Feed: {$data['feedId']}\n";
            echo "Title: {$item->title}\n";
            echo "Category: " . ($item->category ?? 'Uncategorized') . "\n";
            echo "Published: {$item->publishedAt->format('Y-m-d H:i:s')}\n";
            echo "URL: {$item->link}\n";
            
            if (!empty($item->content)) {
                $preview = strlen($item->content) > 150 
                    ? substr($item->content, 0, 150) . '...' 
                    : $item->content;
                echo "Preview: $preview\n";
            }
            
            // Here you can send to Telegram, Email, Slack, etc.
            // self::sendToTelegram($item);
            // self::sendEmail($item);
            // self::sendToSlack($item);
            
            echo "─" . str_repeat("─", 69) . "\n";
        });

        // Register feeds
        echo "📡 Registering feeds...\n";
        $manager->registerFeeds([
            'news_semnan' => [
                'url' => 'https://www.khabaronline.ir/rss/tp/108',
                'category' => 'Semnan | Local News',
                'interval' => 600, // Check every 10 minutes
            ],
            'news_general' => [
                'url' => 'https://www.khabaronline.ir/rss',
                'category' => 'General | All News',
                'interval' => 1800, // Check every 30 minutes
            ],
        ]);

        echo "✅ Feeds registered successfully!\n\n";

        // Show statistics
        echo "📊 Initial Statistics:\n";
        $stats = $manager->getStats();
        echo "   • Total Feeds: {$stats['total_feeds']}\n";
        echo "   • Total Items: {$stats['total_items']}\n";
        echo "   • Categories: " . implode(', ', $stats['categories_list']) . "\n\n";

        // Show all items by category
        echo str_repeat("=", 70) . "\n";
        echo "📂 Items by Category:\n";
        echo str_repeat("=", 70) . "\n\n";

        foreach ($stats['categories_list'] as $category) {
            $items = $manager->getItemsByCategory($category);
            echo "📁 $category (" . count($items) . " items)\n";
            
            foreach (array_slice($items, 0, 3) as $i => $item) {
                echo "   " . ($i + 1) . ". {$item->title}\n";
            }
            
            if (count($items) > 3) {
                echo "   ... and " . (count($items) - 3) . " more\n";
            }
            echo "\n";
        }

        // Demonstrate search
        echo str_repeat("=", 70) . "\n";
        echo "🔍 Search Examples:\n";
        echo str_repeat("=", 70) . "\n\n";

        // Search by keyword
        $keywords = ['رایگان', 'آتش', 'استان'];
        foreach ($keywords as $keyword) {
            $results = $manager->searchItems($keyword);
            if (count($results) > 0) {
                echo "🔎 Search: '$keyword' → Found " . count($results) . " items\n";
                foreach (array_slice($results, 0, 2) as $item) {
                    echo "   • {$item->title}\n";
                }
                echo "\n";
            }
        }

        // Show latest items
        echo str_repeat("=", 70) . "\n";
        echo "🔝 Latest 5 Items Across All Feeds:\n";
        echo str_repeat("=", 70) . "\n\n";

        $latest = $manager->getLatestItems(5);
        foreach ($latest as $i => $item) {
            echo ($i + 1) . ". {$item->title}\n";
            echo "   Category: {$item->category}\n";
            echo "   Date: {$item->publishedAt->format('Y-m-d H:i')}\n\n";
        }

        // Export example
        echo str_repeat("=", 70) . "\n";
        echo "💾 Export Options:\n";
        echo str_repeat("=", 70) . "\n\n";

        echo "Formats available:\n";
        echo "   • JSON     → \$manager->export('json')\n";
        echo "   • RSS      → \$manager->export('rss')\n";
        echo "   • Atom     → \$manager->export('atom')\n";
        echo "   • CSV      → \$manager->export('csv')\n";
        echo "   • HTML     → \$manager->export('html')\n";
        echo "   • Text     → \$manager->export('text')\n\n";

        // Export to JSON
        $json = $manager->export('json');
        file_put_contents('feeds_export.json', $json);
        echo "✅ Exported to: feeds_export.json\n\n";

        echo str_repeat("=", 70) . "\n";
        echo "✨ Setup complete! The system is ready to:\n";
        echo "   • Monitor feeds for new items\n";
        echo "   • Search and filter content\n";
        echo "   • Export in multiple formats\n";
        echo "   • Trigger events for integrations\n";
        echo str_repeat("=", 70) . "\n";
    }

    /**
     * Example integration: Send to Telegram (commented)
     */
    /*
    private static function sendToTelegram($item): void
    {
        $token = 'YOUR_BOT_TOKEN';
        $chatId = 'YOUR_CHAT_ID';
        
        $message = "*{$item->title}*\n\n";
        $message .= $item->content ?? '';
        $message .= "\n\n[Read Full Article]({$item->link})";
        
        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ];
        
        // Send request...
    }

    private static function sendEmail($item): void
    {
        mail(
            'user@example.com',
            $item->title,
            $item->content,
            "From: noreply@example.com\r\nContent-Type: text/html; charset=UTF-8"
        );
    }
    */
}

// Run the example
RealWorldExample::continuousFeedMonitoring();
