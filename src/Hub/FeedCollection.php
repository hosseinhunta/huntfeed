<?php
namespace Hosseinhunta\Huntfeed\Hub;

use Hosseinhunta\Huntfeed\Feed\Feed;
use Hosseinhunta\Huntfeed\Feed\FeedItem;

final class FeedCollection
{
    /** @var array<string, Feed> */
    private array $feeds = [];

    /** @var array<string, array<string>> Categories: category_name => [feed_ids] */
    private array $categories = [];

    /** @var array<string, string> Feed categories mapping: feed_id => category */
    private array $feedCategories = [];

    /** @var string Default category */
    private string $defaultCategory = 'Uncategorized';

    public function __construct(string $defaultCategory = 'Uncategorized')
    {
        $this->defaultCategory = $defaultCategory;
    }

    /**
     * Add a feed to the collection
     * 
     * @param string $feedId Unique identifier
     * @param Feed $feed Feed object
     * @param string|array $categories Single category or array of categories
     */
    public function addFeed(string $feedId, Feed $feed, $categories = null): self
    {
        $this->feeds[$feedId] = $feed;

        if ($categories === null) {
            $categories = [$this->defaultCategory];
        } elseif (is_string($categories)) {
            $categories = [$categories];
        }

        // Store primary category for this feed (first category)
        $this->feedCategories[$feedId] = $categories[0] ?? $this->defaultCategory;

        foreach ($categories as $category) {
            if (!isset($this->categories[$category])) {
                $this->categories[$category] = [];
            }
            if (!in_array($feedId, $this->categories[$category])) {
                $this->categories[$category][] = $feedId;
            }
        }

        return $this;
    }

    /**
     * Remove a feed from the collection
     */
    public function removeFeed(string $feedId): bool
    {
        if (!isset($this->feeds[$feedId])) {
            return false;
        }

        unset($this->feeds[$feedId]);
        unset($this->feedCategories[$feedId]);

        // Remove from categories
        foreach ($this->categories as &$feedIds) {
            $key = array_search($feedId, $feedIds);
            if ($key !== false) {
                unset($feedIds[$key]);
            }
        }

        return true;
    }

    /**
     * Get a feed by ID
     */
    public function getFeed(string $feedId): ?Feed
    {
        return $this->feeds[$feedId] ?? null;
    }

    /**
     * Get all feeds
     * @return array<string, Feed>
     */
    public function getAllFeeds(): array
    {
        return $this->feeds;
    }

    /**
     * Get feeds by category
     * @return array<string, Feed>
     */
    public function getFeedsByCategory(string $category): array
    {
        if (!isset($this->categories[$category])) {
            return [];
        }

        $feeds = [];
        foreach ($this->categories[$category] as $feedId) {
            if (isset($this->feeds[$feedId])) {
                $feeds[$feedId] = $this->feeds[$feedId];
            }
        }

        return $feeds;
    }

    /**
     * Get all categories
     */
    public function getCategories(): array
    {
        return array_keys($this->categories);
    }

    /**
     * Check if feed exists
     */
    public function hasFeed(string $feedId): bool
    {
        return isset($this->feeds[$feedId]);
    }

    /**
     * Check if category exists
     */
    public function hasCategory(string $category): bool
    {
        return isset($this->categories[$category]);
    }

    /**
     * Get all items from all feeds with categories updated
     * @return FeedItem[]
     */
    public function getAllItems(): array
    {
        $items = [];
        foreach ($this->feeds as $feedId => $feed) {
            $feedCategory = $this->feedCategories[$feedId] ?? $this->defaultCategory;
            foreach ($feed->items() as $item) {
                // Update item with feed's category
                $itemWithCategory = $item->category === $feedCategory ? $item : $item->withCategory($feedCategory);
                $items[] = $itemWithCategory;
            }
        }
        return $items;
    }

    /**
     * Get items from a specific category (partial match)
     * 
     * Searches for items where category contains the search term.
     * Example: searching for 'سمنان' will match 'اخبار استان ها > سمنان'
     * 
     * @return FeedItem[]
     */
    public function getItemsByCategory(string $category): array
    {
        $items = [];
        $searchTerm = strtolower($category);
        
        // First try exact match
        if (isset($this->categories[$category])) {
            foreach ($this->categories[$category] as $feedId) {
                if (isset($this->feeds[$feedId])) {
                    $items = array_merge($items, $this->feeds[$feedId]->items());
                }
            }
        }
        
        // Then do partial matching on categories that contain the search term
        foreach ($this->categories as $catName => $feedIds) {
            if (str_contains(strtolower($catName), $searchTerm)) {
                foreach ($feedIds as $feedId) {
                    if (isset($this->feeds[$feedId])) {
                        // Merge while avoiding duplicates
                        foreach ($this->feeds[$feedId]->items() as $item) {
                            if (!in_array($item, $items, true)) {
                                $items[] = $item;
                            }
                        }
                    }
                }
            }
        }

        return $items;
    }

    /**
     * Get items from specific feeds
     * 
     * @param array<string> $feedIds
     * @return FeedItem[]
     */
    public function getItemsByFeeds(array $feedIds): array
    {
        $items = [];

        foreach ($feedIds as $feedId) {
            if (isset($this->feeds[$feedId])) {
                $items = array_merge($items, $this->feeds[$feedId]->items());
            }
        }

        return $items;
    }

    /**
     * Get total items count
     */
    public function getItemsCount(): int
    {
        return count($this->getAllItems());
    }

    /**
     * Get items count by category
     */
    public function getItemsCountByCategory(string $category): int
    {
        return count($this->getItemsByCategory($category));
    }

    /**
     * Get latest items from all feeds
     * 
     * @param int $limit
     * @return FeedItem[]
     */
    public function getLatestItems(int $limit = 10): array
    {
        $items = $this->getAllItems();
        usort($items, fn(FeedItem $a, FeedItem $b) => 
            $b->publishedAt <=> $a->publishedAt
        );
        return array_slice($items, 0, $limit);
    }

    /**
     * Get latest items from a category
     * 
     * @param string $category
     * @param int $limit
     * @return FeedItem[]
     */
    public function getLatestItemsByCategory(string $category, int $limit = 10): array
    {
        $items = $this->getItemsByCategory($category);
        usort($items, fn(FeedItem $a, FeedItem $b) => 
            $b->publishedAt <=> $a->publishedAt
        );
        return array_slice($items, 0, $limit);
    }

    /**
     * Search items across all feeds
     * 
     * Searches in: title, content, category, link
     * Example: searching for 'آتش' will find items with 'آتش' in any field
     * 
     * @return FeedItem[]
     */
    public function searchItems(string $query): array
    {
        $query = strtolower($query);
        $results = [];
        
        foreach ($this->getAllItems() as $item) {
            // Search in title
            if (str_contains(strtolower($item->title), $query)) {
                $results[] = $item;
                continue;
            }
            
            // Search in content
            if (str_contains(strtolower((string)$item->content), $query)) {
                $results[] = $item;
                continue;
            }
            
            // Search in category
            if (!empty($item->category) && str_contains(strtolower($item->category), $query)) {
                $results[] = $item;
                continue;
            }
            
            // Search in link
            if (str_contains(strtolower($item->link), $query)) {
                $results[] = $item;
                continue;
            }
        }
        
        return $results;
    }

    /**
     * Get collection statistics
     */
    public function getStats(): array
    {
        $allItems = $this->getAllItems();
        $categories = $this->getCategories();

        $categoryStats = [];
        foreach ($categories as $cat) {
            $categoryStats[$cat] = [
                'feeds_count' => count($this->getFeedsByCategory($cat)),
                'items_count' => $this->getItemsCountByCategory($cat),
            ];
        }

        return [
            'total_feeds' => count($this->feeds),
            'total_categories' => count($categories),
            'total_items' => count($allItems),
            'categories' => $categoryStats,
            'categories_list' => $categories,
            'feeds_list' => array_keys($this->feeds),
        ];
    }

    /**
     * Get collection metadata
     */
    public function getMetadata(): array
    {
        $dates = [];
        foreach ($this->getAllItems() as $item) {
            $dates[] = $item->publishedAt;
        }

        return [
            'stats' => $this->getStats(),
            'earliest_item' => !empty($dates) ? min($dates)->format(\DateTime::ATOM) : null,
            'latest_item' => !empty($dates) ? max($dates)->format(\DateTime::ATOM) : null,
            'feeds_with_enclosures' => count(
                array_filter($this->feeds, fn(Feed $feed) =>
                    count(array_filter($feed->items(), fn(FeedItem $item) => !empty($item->enclosure))) > 0
                )
            ),
        ];
    }

    /**
     * Export collection to array
     */
    public function toArray(): array
    {
        return [
            'feeds' => array_map(fn(Feed $feed) => $feed->toArray(), $this->feeds),
            'categories' => $this->categories,
            'stats' => $this->getStats(),
        ];
    }

    /**
     * Export collection to JSON
     */
    public function toJSON(bool $pretty = true): string
    {
        $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        if ($pretty) {
            $options |= JSON_PRETTY_PRINT;
        }
        return json_encode($this->toArray(), $options);
    }

    /**
     * Clear all feeds
     */
    public function clear(): void
    {
        $this->feeds = [];
        $this->categories = [];
    }

    /**
     * Get feeds count
     */
    public function count(): int
    {
        return count($this->feeds);
    }
}
