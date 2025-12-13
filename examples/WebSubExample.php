<?php

namespace Hosseinhunta\Huntfeed\Examples;

require __DIR__ . '/../vendor/autoload.php';

use Hosseinhunta\Huntfeed\Hub\FeedManager;
use Hosseinhunta\Huntfeed\WebSub\WebSubManager;
use Hosseinhunta\Huntfeed\WebSub\WebSubHandler;

/**
 * WebSub Integration Example
 * 
 * Demonstrates how to use WebSub (PubSubHubbub) for push-based feed updates
 * 
 * WebSub benefits:
 * - Real-time updates instead of polling
 * - Reduced bandwidth consumption
 * - Lower latency in receiving new items
 * - Automatic subscription to hubs found in feeds
 */

class WebSubExample
{
    public static function basicWebSubUsage(): void
    {
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Basic WebSub Usage\n";
        echo "═══════════════════════════════════════════════════════════\n\n";

        // Create WebSubManager
        // Replace with your actual domain in production
        $callbackUrl = 'http://localhost:8080/websub-callback.php';
        
        $feedManager = new FeedManager();
        $feedManager->getFetcher()->setVerifySSL(false); // Dev mode

        $webSubManager = new WebSubManager($feedManager, $callbackUrl);

        // Register feeds with automatic WebSub detection
        echo "Registering feeds with WebSub hub detection...\n\n";

        $feeds = [
            'tech_feed' => 'https://sahebkhabar.ir/rss',
            'atom_feed' => 'http://dbstheme.com/feed/atom/',
        ];

        $results = $webSubManager->registerMultipleFeeds($feeds);

        foreach ($results as $feedId => $result) {
            echo "📰 Feed: $feedId\n";
            echo "   URL: " . $result['feed_url'] . "\n";
            
            if ($result['has_hub']) {
                echo "   ✅ WebSub Hub Found: " . $result['hub_url'] . "\n";
                echo "   Status: " . ($result['subscription_status'] ?? 'unknown') . "\n";
            } else {
                echo "   ⚠️  No WebSub Hub Found\n";
                if (isset($result['message'])) {
                    echo "   Message: " . $result['message'] . "\n";
                }
            }
            echo "\n";
        }

        // Get subscription status
        $status = $webSubManager->getSubscriptionStatus();
        echo "Subscription Summary:\n";
        echo "  Total Feeds: " . $status['total_feeds'] . "\n";
        echo "  WebSub Enabled: " . $status['websub_enabled_feeds'] . "\n";
        echo "  Verified: " . $status['verified_subscriptions'] . "\n";
        echo "  Auto-Subscribe: " . ($status['auto_subscribe'] ? 'Yes' : 'No') . "\n";
        echo "  Fallback Polling: " . ($status['fallback_polling'] ? 'Yes' : 'No') . "\n\n";
    }

    public static function webSubCallbackEndpoint(): void
    {
        echo "═══════════════════════════════════════════════════════════\n";
        echo "WebSub Callback Endpoint Setup\n";
        echo "═══════════════════════════════════════════════════════════\n\n";

        echo "Your application needs a public HTTP endpoint to receive notifications.\n";
        echo "Here's how to set it up:\n\n";

        echo "1. Create a file: /public/websub-callback.php\n";
        echo "2. Add this code:\n\n";
        echo "```php\n";
        echo "<?php\n";
        echo "// websub-callback.php - Public endpoint for WebSub notifications\n\n";
        echo "\$feedManager = new FeedManager();\n";
        echo "\$webSubManager = new WebSubManager(\$feedManager, 'http://your-domain.com/websub-callback.php');\n";
        echo "\$handler = \$webSubManager->getHandler();\n\n";
        echo "// Handle the request\n";
        echo "\$method = \$_SERVER['REQUEST_METHOD'];\n";
        echo "\$body = file_get_contents('php://input');\n";
        echo "\$headers = getallheaders();\n\n";
        echo "\$result = \$handler->processRequest(\$method, \$_GET, \$body, \$headers);\n\n";
        echo "// Send appropriate response\n";
        echo "http_response_code(\$result['status'] ?? 200);\n";
        echo "echo \$result['body'] ?? '';\n";
        echo "```\n\n";

        echo "3. Configure your web server:\n";
        echo "   - Make the endpoint publicly accessible\n";
        echo "   - Use HTTPS in production\n";
        echo "   - Ensure the endpoint can handle POST requests\n\n";

        echo "4. In your FeedManager initialization:\n";
        echo "   \$callbackUrl = 'https://your-domain.com/websub-callback.php';\n";
        echo "   \$webSubManager = new WebSubManager(\$feedManager, \$callbackUrl);\n\n";
    }

    public static function handlingWebSubNotifications(): void
    {
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Handling WebSub Notifications\n";
        echo "═══════════════════════════════════════════════════════════\n\n";

        $feedManager = new FeedManager();
        $feedManager->getFetcher()->setVerifySSL(false);

        $webSubManager = new WebSubManager($feedManager, 'http://localhost:8080/callback');

        // Set up notification handler
        echo "Setting up notification handler...\n\n";

        // Simulated WebSub notification (in reality, hub sends this)
        $sampleFeedContent = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>Sample Feed</title>
        <link>http://example.com</link>
        <item>
            <title>Breaking News: WebSub Implementation Complete</title>
            <link>http://example.com/article/1</link>
            <pubDate>Mon, 13 Dec 2025 12:00:00 GMT</pubDate>
            <description>New articles are now delivered via WebSub push notifications!</description>
        </item>
        <item>
            <title>Another News Item</title>
            <link>http://example.com/article/2</link>
            <pubDate>Sun, 12 Dec 2025 10:00:00 GMT</pubDate>
            <description>This item was pushed by the WebSub hub.</description>
        </item>
    </channel>
</rss>
XML;

        $result = $webSubManager->handleWebSubNotification(
            $sampleFeedContent,
            ['X-Hub-Signature' => 'sha1=mock-signature'],
            function($items) {
                echo "Processing " . count($items) . " items from notification...\n";
                foreach ($items as $item) {
                    echo "  ✓ " . $item['title'] . "\n";
                }
            }
        );

        echo "\nNotification Processing Result:\n";
        echo "  Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
        echo "  Items Received: " . ($result['items_received'] ?? 0) . "\n\n";
    }

    public static function comparisonPollingVsWebSub(): void
    {
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Polling vs WebSub Comparison\n";
        echo "═══════════════════════════════════════════════════════════\n\n";

        echo "Traditional Polling (Pull):\n";
        echo "  Your app → Request 1 → Server\n";
        echo "  Your app → Request 2 → Server\n";
        echo "  Your app → Request 3 → Server\n";
        echo "  Your app → Request 4 → Server\n";
        echo "  Your app → Request 5 → Server\n";
        echo "  ... repeats every N minutes even if no updates\n\n";

        echo "WebSub (Push):\n";
        echo "  Subscribe → Hub (once)\n";
        echo "  [Waiting for updates...]\n";
        echo "  Hub → New content available → Your app\n";
        echo "  Hub → Another update → Your app\n";
        echo "  ... only receive when there are real updates\n\n";

        echo "Benefits of WebSub:\n";
        echo "  ✅ Real-time updates (low latency)\n";
        echo "  ✅ Reduced bandwidth (no empty polls)\n";
        echo "  ✅ Scalable (hub handles distribution)\n";
        echo "  ✅ Server-friendly (less CPU/memory)\n";
        echo "  ✅ Better for users (get updates immediately)\n\n";

        echo "Hybrid Approach (Recommended):\n";
        echo "  1. Use WebSub when hub is available\n";
        echo "  2. Fallback to polling for feeds without hub\n";
        echo "  3. Combine both for reliability\n";
        echo "  4. No single point of failure\n\n";
    }

    public static function autoDetectionExample(): void
    {
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Automatic WebSub Hub Detection\n";
        echo "═══════════════════════════════════════════════════════════\n\n";

        echo "HuntFeed automatically detects WebSub hubs in feeds.\n";
        echo "The parser looks for these indicators:\n\n";

        echo "In Atom feeds:\n";
        echo "  <link rel=\"hub\" href=\"https://hub.example.com/\" />\n\n";

        echo "In RSS feeds:\n";
        echo "  <rss><channel>\n";
        echo "    <link rel=\"hub\" href=\"https://hub.example.com/\" />\n";
        echo "  </channel></rss>\n\n";

        echo "When a feed is registered:\n";
        echo "  1. FeedFetcher downloads the feed\n";
        echo "  2. WebSubSubscriber automatically detects hub\n";
        echo "  3. WebSubManager subscribes to hub (if found)\n";
        echo "  4. Subsequent updates arrive via push\n\n";

        echo "If no hub is found:\n";
        echo "  - Falls back to traditional polling\n";
        echo "  - Transparent to your application\n";
        echo "  - Same API for both methods\n\n";
    }

    public static function productionSetup(): void
    {
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Production Setup Checklist\n";
        echo "═══════════════════════════════════════════════════════════\n\n";

        echo "☐ Security:\n";
        echo "  ✓ Use HTTPS for callback endpoint\n";
        echo "  ✓ Verify WebSub signatures\n";
        echo "  ✓ Store secrets securely\n";
        echo "  ✓ Validate all incoming requests\n\n";

        echo "☐ Reliability:\n";
        echo "  ✓ Enable fallback polling\n";
        echo "  ✓ Handle hub failures gracefully\n";
        echo "  ✓ Log all subscription activities\n";
        echo "  ✓ Monitor subscription status\n\n";

        echo "☐ Performance:\n";
        echo "  ✓ Use database for subscription storage\n";
        echo "  ✓ Queue notification processing\n";
        echo "  ✓ Cache verification challenges\n";
        echo "  ✓ Load balance callback endpoint\n\n";

        echo "☐ Operations:\n";
        echo "  ✓ Monitor subscription renewals (lease expiry)\n";
        echo "  ✓ Auto-renew subscriptions\n";
        echo "  ✓ Track hub availability\n";
        echo "  ✓ Maintain audit logs\n\n";

        echo "Example Production Code:\n";
        echo "```php\n";
        echo "\$webSubManager = new WebSubManager(\$feedManager, \$callbackUrl);\n";
        echo "\$webSubManager\n";
        echo "  ->setAutoSubscribe(true)\n";
        echo "  ->setFallbackToPolling(true);\n";
        echo "\n";
        echo "\$feedManager->getFetcher()->setVerifySSL(true);\n";
        echo "\$feedManager->getFetcher()->setCaBundlePath('/etc/ssl/certs/ca-bundle.crt');\n";
        echo "```\n\n";
    }

    public static function runAll(): void
    {
        echo "\n";
        echo "╔═══════════════════════════════════════════════════════════╗\n";
        echo "║         WebSub (PubSubHubbub) Integration Guide           ║\n";
        echo "║     Real-time Feed Updates with Push Notifications        ║\n";
        echo "╚═══════════════════════════════════════════════════════════╝\n";
        echo "\n";

        self::basicWebSubUsage();
        echo "\n";

        self::autoDetectionExample();
        echo "\n";

        self::webSubCallbackEndpoint();
        echo "\n";

        self::handlingWebSubNotifications();
        echo "\n";

        self::comparisonPollingVsWebSub();
        echo "\n";

        self::productionSetup();
        echo "\n";

        echo "═══════════════════════════════════════════════════════════\n";
        echo "For more information, see:\n";
        echo "  - WebSubExample.php (this file)\n";
        echo "  - WebSubSubscriber.php (core implementation)\n";
        echo "  - WebSubManager.php (orchestration)\n";
        echo "  - WebSubHandler.php (HTTP endpoint)\n";
        echo "═══════════════════════════════════════════════════════════\n\n";
    }
}

// Run examples
WebSubExample::runAll();
