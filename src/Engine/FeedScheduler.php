<?php
namespace Hosseinhunta\Huntfeed\Engine;

use Hosseinhunta\Huntfeed\Feed\Feed;
use Hosseinhunta\Huntfeed\Transport\FeedFetcher;

final class FeedScheduler
{
    private FeedFetcher $fetcher;

    /** @var array<string, array{'feed' => Feed, 'last_update' => \DateTimeImmutable, 'interval' => int}> */
    private array $scheduledFeeds = [];

    /** @var array<string, Feed[]> */
    private array $feedHistory = [];

    public function __construct(FeedFetcher $fetcher = null)
    {
        $this->fetcher = $fetcher ?? new FeedFetcher();
    }

    /**
     * Register a feed for periodic polling
     * 
     * @param string $feedId Unique identifier for the feed
     * @param string $url Feed URL
     * @param int $intervalSeconds Update interval in seconds (default: 1800 = 30 minutes)
     * @param bool $keepHistory Keep history of feed states for change detection
     */
    public function register(
        string $feedId,
        string $url,
        int $intervalSeconds = 1800,
        bool $keepHistory = true
    ): self {
        $feed = $this->fetcher->fetch($url);

        $this->scheduledFeeds[$feedId] = [
            'url' => $url,
            'feed' => $feed,
            'last_update' => new \DateTimeImmutable('now'),
            'interval' => $intervalSeconds,
            'keep_history' => $keepHistory,
        ];

        if ($keepHistory) {
            $this->feedHistory[$feedId] = [$feed];
        }

        return $this;
    }

    /**
     * Register multiple feeds at once
     * 
     * @param array<string, array{'url' => string, 'interval' => int}> $feeds
     */
    public function registerMultiple(array $feeds): self
    {
        foreach ($feeds as $feedId => $config) {
            $url = $config['url'] ?? null;
            $interval = $config['interval'] ?? 1800;

            if ($url) {
                $this->register($feedId, $url, $interval);
            }
        }

        return $this;
    }

    /**
     * Check all registered feeds for updates
     * Returns only feeds that have new content
     * 
     * @return array<string, array{'feed' => Feed, 'new_items' => Feed, 'is_updated' => bool}>
     */
    public function checkUpdates(): array
    {
        $updatedFeeds = [];
        $now = new \DateTimeImmutable('now');

        foreach ($this->scheduledFeeds as $feedId => $config) {
            $lastUpdate = $config['last_update'];
            $interval = $config['interval'];

            // Check if it's time to update
            $secondsElapsed = $now->getTimestamp() - $lastUpdate->getTimestamp();
            if ($secondsElapsed < $interval) {
                continue;
            }

            try {
                $oldFeed = $config['feed'];
                $newFeed = $this->fetcher->fetch($config['url']);

                // Check for new items
                $hasNewItems = $this->fetcher->hasNewItems($oldFeed, $newFeed);

                if ($hasNewItems) {
                    $newItems = $this->fetcher->getNewItems($oldFeed, $newFeed);

                    $updatedFeeds[$feedId] = [
                        'feed' => $newFeed,
                        'new_items' => $newItems,
                        'is_updated' => true,
                    ];

                    // Update the feed
                    $this->scheduledFeeds[$feedId]['feed'] = $newFeed;
                    $this->scheduledFeeds[$feedId]['last_update'] = $now;

                    // Keep history
                    if ($config['keep_history'] ?? false) {
                        $this->feedHistory[$feedId][] = $newFeed;
                        // Keep only last 10 versions
                        if (count($this->feedHistory[$feedId]) > 10) {
                            array_shift($this->feedHistory[$feedId]);
                        }
                    }
                }
            } catch (\Exception $e) {
                error_log("Error updating feed {$feedId}: " . $e->getMessage());
            }
        }

        return $updatedFeeds;
    }

    /**
     * Force update a specific feed
     */
    public function forceUpdate(string $feedId): bool
    {
        if (!isset($this->scheduledFeeds[$feedId])) {
            return false;
        }

        try {
            $config = $this->scheduledFeeds[$feedId];
            $oldFeed = $config['feed'];
            $newFeed = $this->fetcher->fetch($config['url']);

            $this->scheduledFeeds[$feedId]['feed'] = $newFeed;
            $this->scheduledFeeds[$feedId]['last_update'] = new \DateTimeImmutable('now');

            if ($config['keep_history'] ?? false) {
                $this->feedHistory[$feedId][] = $newFeed;
            }

            return true;
        } catch (\Exception $e) {
            error_log("Error force-updating feed {$feedId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get a feed by ID
     */
    public function getFeed(string $feedId): ?Feed
    {
        return $this->scheduledFeeds[$feedId]['feed'] ?? null;
    }

    /**
     * Get all registered feeds
     */
    public function getAllFeeds(): array
    {
        $feeds = [];
        foreach ($this->scheduledFeeds as $feedId => $config) {
            $feeds[$feedId] = $config['feed'];
        }
        return $feeds;
    }

    /**
     * Get feed update status
     */
    public function getStatus(string $feedId): ?array
    {
        if (!isset($this->scheduledFeeds[$feedId])) {
            return null;
        }

        $config = $this->scheduledFeeds[$feedId];
        $now = new \DateTimeImmutable('now');
        $lastUpdate = $config['last_update'];
        $secondsElapsed = $now->getTimestamp() - $lastUpdate->getTimestamp();
        $nextUpdate = $lastUpdate->modify("+{$config['interval']} seconds");

        return [
            'feed_id' => $feedId,
            'url' => $config['url'],
            'last_update' => $lastUpdate->format(\DateTime::ATOM),
            'next_update' => $nextUpdate->format(\DateTime::ATOM),
            'interval' => $config['interval'],
            'seconds_since_update' => $secondsElapsed,
            'items_count' => $config['feed']->itemsCount(),
        ];
    }

    /**
     * Get status of all feeds
     */
    public function getAllStatus(): array
    {
        $statuses = [];
        foreach (array_keys($this->scheduledFeeds) as $feedId) {
            $statuses[$feedId] = $this->getStatus($feedId);
        }
        return $statuses;
    }

    /**
     * Get feed history
     */
    public function getHistory(string $feedId): ?array
    {
        return $this->feedHistory[$feedId] ?? null;
    }

    /**
     * Unregister a feed
     */
    public function unregister(string $feedId): bool
    {
        if (isset($this->scheduledFeeds[$feedId])) {
            unset($this->scheduledFeeds[$feedId]);
            unset($this->feedHistory[$feedId]);
            return true;
        }
        return false;
    }

    /**
     * Clear all feeds
     */
    public function clear(): void
    {
        $this->scheduledFeeds = [];
        $this->feedHistory = [];
    }

    /**
     * Get registered feeds count
     */
    public function count(): int
    {
        return count($this->scheduledFeeds);
    }
}
