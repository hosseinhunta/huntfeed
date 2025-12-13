<?php
namespace Hosseinhunta\Huntfeed\Hub;

use Hosseinhunta\Huntfeed\Engine\FeedScheduler;
use Hosseinhunta\Huntfeed\Feed\Feed;
use Hosseinhunta\Huntfeed\Feed\FeedItem;
use Hosseinhunta\Huntfeed\Transport\FeedFetcher;
use Closure;

final class FeedManager
{
    private FeedFetcher $fetcher;
    private FeedScheduler $scheduler;
    private FeedCollection $collection;

    /** @var array<string, Closure> Event handlers */
    private array $eventHandlers = [];

    /** @var array<string, mixed> Configuration */
    private array $config = [
        'poll_interval' => 1800, // 30 minutes
        'keep_history' => true,
        'max_items' => 0, // 0 = unlimited
    ];

    public function __construct(
        FeedFetcher $fetcher = null,
        FeedScheduler $scheduler = null,
        FeedCollection $collection = null
    ) {
        $this->fetcher = $fetcher ?? new FeedFetcher();
        $this->scheduler = $scheduler ?? new FeedScheduler($this->fetcher);
        $this->collection = $collection ?? new FeedCollection();
    }

    /**
     * Register a feed for tracking
     * 
     * @param string $feedId Unique identifier
     * @param string $url Feed URL
     * @param array $options Configuration options: ['category', 'interval', 'keep_history']
     */
    public function registerFeed(string $feedId, string $url, array $options = []): self
    {
        $category = $options['category'] ?? 'Uncategorized';
        $interval = $options['interval'] ?? $this->config['poll_interval'];
        $keepHistory = $options['keep_history'] ?? $this->config['keep_history'];

        // Fetch initial feed
        $feed = $this->fetcher->fetch($url);

        // Add to collection
        $this->collection->addFeed($feedId, $feed, $category);

        // Register in scheduler
        $this->scheduler->register($feedId, $url, $interval, $keepHistory);

        // Dispatch event
        $this->dispatchEvent('feed:registered', ['feedId' => $feedId, 'url' => $url]);

        return $this;
    }

    /**
     * Register multiple feeds at once
     * 
     * @param array<string, array{'url' => string, 'category'?: string, 'interval'?: int}> $feeds
     */
    public function registerFeeds(array $feeds): self
    {
        foreach ($feeds as $feedId => $config) {
            if (!isset($config['url'])) {
                continue;
            }

            $this->registerFeed($feedId, $config['url'], $config);
        }

        return $this;
    }

    /**
     * Check all feeds for updates
     * Returns new items grouped by feed
     */
    public function checkUpdates(): array
    {
        $updates = $this->scheduler->checkUpdates();
        $results = [];

        foreach ($updates as $feedId => $data) {
            if ($data['is_updated'] ?? false) {
                $newItems = $data['new_items'];
                $results[$feedId] = [
                    'feed' => $data['feed'],
                    'new_items' => $newItems->items(),
                    'new_items_count' => $newItems->itemsCount(),
                ];

                // Update collection
                if ($feed = $this->collection->getFeed($feedId)) {
                    foreach ($newItems->items() as $item) {
                        $feed->addItem($item);
                    }
                }

                // Dispatch event for each new item
                foreach ($newItems->items() as $item) {
                    $this->dispatchEvent('item:new', [
                        'feedId' => $feedId,
                        'item' => $item,
                    ]);
                }

                // Dispatch feed updated event
                $this->dispatchEvent('feed:updated', [
                    'feedId' => $feedId,
                    'new_items_count' => $newItems->itemsCount(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Force update a specific feed
     */
    public function forceUpdateFeed(string $feedId): bool
    {
        return $this->scheduler->forceUpdate($feedId);
    }

    /**
     * Force update all feeds
     */
    public function forceUpdateAll(): array
    {
        $updated = [];
        $statuses = $this->scheduler->getAllStatus();

        foreach (array_keys($statuses) as $feedId) {
            if ($this->scheduler->forceUpdate($feedId)) {
                $updated[] = $feedId;
                $this->dispatchEvent('feed:force-updated', ['feedId' => $feedId]);
            }
        }

        return $updated;
    }

    /**
     * Get all items across all feeds
     * @return FeedItem[]
     */
    public function getAllItems(): array
    {
        return $this->collection->getAllItems();
    }

    /**
     * Get latest items from all feeds
     * @return FeedItem[]
     */
    public function getLatestItems(int $limit = 10): array
    {
        return $this->collection->getLatestItems($limit);
    }

    /**
     * Get items by category
     * @return FeedItem[]
     */
    public function getItemsByCategory(string $category): array
    {
        return $this->collection->getItemsByCategory($category);
    }

    /**
     * Get latest items from a category
     * @return FeedItem[]
     */
    public function getLatestItemsByCategory(string $category, int $limit = 10): array
    {
        return $this->collection->getLatestItemsByCategory($category, $limit);
    }

    /**
     * Search items across all feeds
     * @return FeedItem[]
     */
    public function searchItems(string $query): array
    {
        return $this->collection->searchItems($query);
    }

    /**
     * Remove a feed from tracking
     */
    public function removeFeed(string $feedId): bool
    {
        $this->collection->removeFeed($feedId);
        $this->scheduler->unregister($feedId);
        $this->dispatchEvent('feed:removed', ['feedId' => $feedId]);

        return true;
    }

    /**
     * Get feed status
     */
    public function getFeedStatus(string $feedId): ?array
    {
        return $this->scheduler->getStatus($feedId);
    }

    /**
     * Get all feeds status
     */
    public function getAllFeedsStatus(): array
    {
        return $this->scheduler->getAllStatus();
    }

    /**
     * Get collection statistics
     */
    public function getStats(): array
    {
        return $this->collection->getStats();
    }

    /**
     * Get complete collection metadata
     */
    public function getMetadata(): array
    {
        return [
            'collection' => $this->collection->getMetadata(),
            'feeds_status' => $this->getAllFeedsStatus(),
            'stats' => $this->getStats(),
        ];
    }

    /**
     * Register an event handler
     * Events: 'feed:registered', 'feed:updated', 'feed:removed', 'item:new'
     */
    public function on(string $event, Closure $handler): self
    {
        if (!isset($this->eventHandlers[$event])) {
            $this->eventHandlers[$event] = [];
        }
        $this->eventHandlers[$event][] = $handler;

        return $this;
    }

    /**
     * Dispatch an event
     */
    private function dispatchEvent(string $event, array $data): void
    {
        if (isset($this->eventHandlers[$event])) {
            foreach ($this->eventHandlers[$event] as $handler) {
                $handler($data);
            }
        }
    }

    /**
     * Export collection in a specific format
     */
    public function export(string $format = 'json', string $feedId = null): string
    {
        if ($feedId) {
            $feed = $this->collection->getFeed($feedId);
            if (!$feed) {
                throw new \RuntimeException("Feed '{$feedId}' not found");
            }
        } else {
            // Create a merged feed from all items
            $feed = new Feed('export', 'All Feeds Export');
            foreach ($this->collection->getAllItems() as $item) {
                $feed->addItem($item);
            }
        }

        return match ($format) {
            'json' => FeedExporter::toJSON($feed),
            'rss' => FeedExporter::toRSS($feed),
            'atom' => FeedExporter::toAtom($feed),
            'jsonfeed' => FeedExporter::toJSONFeed($feed),
            'csv' => FeedExporter::toCSV($feed),
            'html' => FeedExporter::toHTML($feed),
            'text' => FeedExporter::toText($feed),
            default => throw new \RuntimeException("Unsupported export format: {$format}"),
        };
    }

    /**
     * Export collection metadata
     */
    public function exportMetadata(string $format = 'json'): string
    {
        $metadata = $this->getMetadata();

        return match ($format) {
            'json' => json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'csv' => $this->metadataToCSV($metadata),
            'text' => $this->metadataToText($metadata),
            default => throw new \RuntimeException("Unsupported format: {$format}"),
        };
    }

    /**
     * Set configuration
     */
    public function setConfig(string $key, mixed $value): self
    {
        $this->config[$key] = $value;
        return $this;
    }

    /**
     * Get configuration
     */
    public function getConfig(string $key = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }
        return $this->config[$key] ?? null;
    }

    /**
     * Get collection
     */
    public function getCollection(): FeedCollection
    {
        return $this->collection;
    }

    /**
     * Get scheduler
     */
    public function getScheduler(): FeedScheduler
    {
        return $this->scheduler;
    }

    /**
     * Get fetcher
     */
    public function getFetcher(): FeedFetcher
    {
        return $this->fetcher;
    }

    /**
     * Convert metadata to CSV
     */
    private function metadataToCSV(array $metadata): string
    {
        $csv = "Feed,Total Items,Feeds Count,Categories Count\n";
        
        $stats = $metadata['stats'] ?? [];
        $csv .= "All Feeds," . ($stats['total_items'] ?? 0) . ",";
        $csv .= ($stats['total_feeds'] ?? 0) . ",";
        $csv .= ($stats['total_categories'] ?? 0) . "\n";

        return $csv;
    }

    /**
     * Convert metadata to text
     */
    private function metadataToText(array $metadata): string
    {
        $text = "=== Feed Manager Metadata ===\n\n";

        $stats = $metadata['stats'] ?? [];
        $text .= "Total Feeds: " . ($stats['total_feeds'] ?? 0) . "\n";
        $text .= "Total Categories: " . ($stats['total_categories'] ?? 0) . "\n";
        $text .= "Total Items: " . ($stats['total_items'] ?? 0) . "\n\n";

        $text .= "Categories:\n";
        foreach ($stats['categories'] ?? [] as $category => $catStats) {
            $text .= "  - {$category}: {$catStats['feeds_count']} feeds, {$catStats['items_count']} items\n";
        }

        return $text;
    }
}
