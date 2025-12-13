<?php

namespace Hosseinhunta\Huntfeed\WebSub;

use Hosseinhunta\Huntfeed\Transport\FeedFetcher;
use Hosseinhunta\Huntfeed\Hub\FeedManager;
use Exception;

/**
 * WebSub Manager
 * 
 * Orchestrates WebSub subscriptions across multiple feeds
 * Integrates with FeedManager for automatic hub detection
 * and subscription management
 */
class WebSubManager
{
    private WebSubSubscriber $subscriber;
    private FeedManager $feedManager;
    private array $feedHubs = []; // feed_id => hub_url
    private bool $autoSubscribe = true;
    private bool $fallbackToPolling = true;

    public function __construct(FeedManager $feedManager, string $callbackUrl)
    {
        $this->feedManager = $feedManager;
        $this->subscriber = new WebSubSubscriber(
            $feedManager->getFetcher(),
            $callbackUrl
        );
    }

    /**
     * Register feed with automatic hub detection and subscription
     * 
     * @param string $feedId Unique feed identifier
     * @param string $feedUrl Feed URL
     * @param array $options Configuration options
     * @return array Registration result with hub info
     */
    public function registerFeedWithWebSub(
        string $feedId,
        string $feedUrl,
        array $options = []
    ): array {
        try {
            // Fetch feed to detect hub
            $feed = $this->feedManager->getFetcher()->fetch($feedUrl);

            // Get feed content to detect hub
            $hubUrl = WebSubSubscriber::detectHubFromFeed($feed->getOriginalContent());

            $result = [
                'feed_id' => $feedId,
                'feed_url' => $feedUrl,
                'has_hub' => $hubUrl !== null,
                'hub_url' => $hubUrl,
            ];

            // Store hub mapping
            if ($hubUrl) {
                $this->feedHubs[$feedId] = $hubUrl;

                // Auto-subscribe if enabled
                if ($this->autoSubscribe) {
                    $subResult = $this->subscriber->subscribe(
                        $feedUrl,
                        $hubUrl,
                        function($data) use ($feedId) {
                            // Handle verification
                            $this->onSubscriptionVerified($feedId, $data);
                        }
                    );

                    $result['subscription'] = $subResult;
                    $result['subscription_status'] = 'pending_verification';
                }
            } else {
                $result['subscription_status'] = 'no_hub_found';
                
                if ($this->fallbackToPolling) {
                    $result['message'] = 'No WebSub hub found. Will fallback to polling.';
                }
            }

            // Register feed with FeedManager
            $this->feedManager->registerFeed($feedId, $feedUrl, $options);

            return $result;
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'feed_id' => $feedId,
            ];
        }
    }

    /**
     * Batch register multiple feeds
     * 
     * @param array $feeds Array of [feed_id => [url, options]]
     * @return array Registration results for each feed
     */
    public function registerMultipleFeeds(array $feeds): array
    {
        $results = [];

        foreach ($feeds as $feedId => $feedData) {
            if (is_string($feedData)) {
                // Simple format: feed_id => url
                $results[$feedId] = $this->registerFeedWithWebSub($feedId, $feedData);
            } else {
                // Complex format: feed_id => [url, options]
                $url = $feedData['url'] ?? $feedData[0] ?? null;
                $options = $feedData['options'] ?? $feedData[1] ?? [];

                if ($url) {
                    $results[$feedId] = $this->registerFeedWithWebSub($feedId, $url, $options);
                }
            }
        }

        return $results;
    }

    /**
     * Get subscription status for all feeds
     * 
     * @return array Detailed subscription information
     */
    public function getSubscriptionStatus(): array
    {
        $status = $this->subscriber->getSubscriptionStatus();

        return [
            'total_feeds' => count($this->feedHubs),
            'websub_enabled_feeds' => count($this->feedHubs),
            'verified_subscriptions' => $this->subscriber->getVerifiedCount(),
            'subscriptions' => $status['subscriptions'] ?? [],
            'auto_subscribe' => $this->autoSubscribe,
            'fallback_polling' => $this->fallbackToPolling,
        ];
    }

    /**
     * Check for new items (combines WebSub and polling)
     * 
     * For feeds with WebSub hub: faster updates via push
     * For feeds without hub: fallback to polling
     * 
     * @return array Items found with their source (websub/polling)
     */
    public function checkUpdates(): array
    {
        // Use FeedManager's checkUpdates which will be enhanced
        // to recognize WebSub-enabled feeds
        return $this->feedManager->checkUpdates();
    }

    /**
     * Handle incoming WebSub notification
     * 
     * @param string $body Request body containing feed content
     * @param array $headers Request headers
     * @param callable|null $onNewItems Callback when new items are detected
     * @return array Processing result
     */
    public function handleWebSubNotification(
        string $body,
        array $headers,
        ?callable $onNewItems = null
    ): array {
        $result = $this->subscriber->handleNotification($body, $headers);

        if (!$result['success']) {
            return $result;
        }

        $processedItems = [];

        // Process each item through FeedManager
        foreach ($result['items'] as $item) {
            $processedItems[] = [
                'title' => $item['title'] ?? 'Unknown',
                'link' => $item['link'] ?? '',
                'source' => 'websub',
                'received_at' => new \DateTimeImmutable(),
            ];
        }

        // Call callback if provided
        if ($onNewItems) {
            call_user_func($onNewItems, $processedItems);
        }

        return [
            'success' => true,
            'items_received' => count($processedItems),
            'items' => $processedItems,
        ];
    }

    /**
     * Enable/disable auto-subscription
     */
    public function setAutoSubscribe(bool $enabled): self
    {
        $this->autoSubscribe = $enabled;
        return $this;
    }

    /**
     * Enable/disable fallback to polling
     */
    public function setFallbackToPolling(bool $enabled): self
    {
        $this->fallbackToPolling = $enabled;
        return $this;
    }

    /**
     * Get WebSubSubscriber instance for advanced control
     */
    public function getSubscriber(): WebSubSubscriber
    {
        return $this->subscriber;
    }

    /**
     * Get WebSubHandler for HTTP endpoint
     */
    public function getHandler(): WebSubHandler
    {
        return new WebSubHandler($this->subscriber);
    }

    /**
     * Callback when subscription is verified
     */
    private function onSubscriptionVerified(string $feedId, array $data): void
    {
        // Callback triggered when subscription verification completes
        // Can be extended to trigger events or logging as needed
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        $status = $this->getSubscriptionStatus();
        $feedStats = $this->feedManager->getStats();

        return [
            'total_feeds' => $feedStats['total_feeds'] ?? 0,
            'websub_enabled' => $status['websub_enabled_feeds'],
            'verified_subscriptions' => $status['verified_subscriptions'],
            'total_items' => $feedStats['total_items'] ?? 0,
            'auto_subscribe' => $this->autoSubscribe,
            'fallback_polling' => $this->fallbackToPolling,
            'subscription_status' => $status,
        ];
    }

    /**
     * Get list of feeds with WebSub hub
     */
    public function getWebSubFeeds(): array
    {
        return array_map(function($feedId, $hubUrl) {
            return [
                'feed_id' => $feedId,
                'hub_url' => $hubUrl,
            ];
        }, array_keys($this->feedHubs), array_values($this->feedHubs));
    }

    /**
     * Generate callback URL for registration
     */
    public static function generateCallbackUrl(string $domain, string $path = '/websub'): string
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return "{$protocol}://{$domain}{$path}";
    }
}
