<?php
namespace Hosseinhunta\Huntfeed\Feed;

use JsonSerializable;

final class Feed implements JsonSerializable
{
    /** @var FeedItem[] */
    private array $items = [];

    /** @var array<string, FeedItem> Duplicate detection map */
    private array $duplicateMap = [];

    private ?string $originalContent = null;

    public function __construct(
        public readonly string $url,
        public readonly string $title
    ) {}

    /**
     * Add an item to the feed
     * Automatically prevents duplicates using fingerprint
     */
    public function addItem(FeedItem $item): void
    {
        $fingerprint = $item->fingerprint();
        if (!isset($this->items[$fingerprint])) {
            $this->items[$fingerprint] = $item;
            $this->duplicateMap[$fingerprint] = $item;
        }
    }

    /**
     * Add multiple items at once
     * @param FeedItem[] $items
     */
    public function addItems(array $items): void
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    /**
     * Get all items
     * @return FeedItem[]
     */
    public function items(): array
    {
        return array_values($this->items);
    }

    /**
     * Get items count
     */
    public function itemsCount(): int
    {
        return count($this->items);
    }

    /**
     * Get a specific item by fingerprint
     */
    public function getItemByFingerprint(string $fingerprint): ?FeedItem
    {
        return $this->items[$fingerprint] ?? null;
    }

    /**
     * Check if item exists by fingerprint
     */
    public function hasItem(string $fingerprint): bool
    {
        return isset($this->items[$fingerprint]);
    }

    /**
     * Remove an item by fingerprint
     */
    public function removeItem(string $fingerprint): bool
    {
        if (isset($this->items[$fingerprint])) {
            unset($this->items[$fingerprint]);
            unset($this->duplicateMap[$fingerprint]);
            return true;
        }
        return false;
    }

    /**
     * Remove all items
     */
    public function clear(): void
    {
        $this->items = [];
        $this->duplicateMap = [];
    }

    /**
     * Find items by category
     * @return FeedItem[]
     */
    public function findByCategory(string $category): array
    {
        return array_filter($this->items, fn(FeedItem $item) => $item->category === $category);
    }

    /**
     * Find items published after a specific date
     * @return FeedItem[]
     */
    public function findAfterDate(\DateTimeImmutable $date): array
    {
        return array_filter($this->items, fn(FeedItem $item) => $item->publishedAt > $date);
    }

    /**
     * Find items published before a specific date
     * @return FeedItem[]
     */
    public function findBeforeDate(\DateTimeImmutable $date): array
    {
        return array_filter($this->items, fn(FeedItem $item) => $item->publishedAt < $date);
    }

    /**
     * Find items by title search (case-insensitive)
     * @return FeedItem[]
     */
    public function searchByTitle(string $query): array
    {
        $query = strtolower($query);
        return array_filter($this->items, fn(FeedItem $item) => 
            str_contains(strtolower($item->title), $query)
        );
    }

    /**
     * Get items sorted by published date (newest first by default)
     * @param bool $descending Sort descending (newest first) if true
     * @return FeedItem[]
     */
    public function getItemsSorted(bool $descending = true): array
    {
        $items = $this->items();
        usort($items, function(FeedItem $a, FeedItem $b) use ($descending) {
            $comparison = $a->publishedAt <=> $b->publishedAt;
            return $descending ? -$comparison : $comparison;
        });
        return $items;
    }

    /**
     * Get paginated items
     * @return FeedItem[]
     */
    public function paginate(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        return array_slice($this->items(), $offset, $perPage);
    }

    /**
     * Get the latest items
     * @return FeedItem[]
     */
    public function getLatest(int $limit = 10): array
    {
        return array_slice($this->getItemsSorted(), 0, $limit);
    }

    /**
     * Set original feed content (XML/JSON)
     */
    public function setOriginalContent(string $content): self
    {
        $this->originalContent = $content;
        return $this;
    }

    /**
     * Get original feed content
     */
    public function getOriginalContent(): ?string
    {
        return $this->originalContent;
    }

    /**
     * Get duplicate detection statistics
     */
    public function getDuplicateStats(): array
    {
        return [
            'total_fingerprints' => count($this->items),
            'unique_items' => count($this->items),
            'duplicate_map_size' => count($this->duplicateMap),
        ];
    }

    /**
     * Export feed to array
     */
    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'title' => $this->title,
            'items_count' => $this->itemsCount(),
            'items' => array_map(fn(FeedItem $item) => $item->toArray(), $this->items()),
        ];
    }

    /**
     * Export feed to JSON (implements JsonSerializable)
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Get feed metadata
     */
    public function getMetadata(): array
    {
        $allItems = $this->items();
        $dates = array_map(fn(FeedItem $item) => $item->publishedAt, $allItems);

        return [
            'url' => $this->url,
            'title' => $this->title,
            'total_items' => count($allItems),
            'first_item_date' => !empty($dates) ? min($dates)->format(\DateTime::ATOM) : null,
            'latest_item_date' => !empty($dates) ? max($dates)->format(\DateTime::ATOM) : null,
            'categories' => array_unique(
                array_filter(array_map(fn(FeedItem $item) => $item->category, $allItems))
            ),
            'items_with_enclosures' => count(array_filter($allItems, fn(FeedItem $item) => !empty($item->enclosure))),
            'duplicate_stats' => $this->getDuplicateStats(),
        ];
    }
}
