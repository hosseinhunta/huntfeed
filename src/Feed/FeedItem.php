<?php
namespace Hosseinhunta\Huntfeed\Feed;

use JsonSerializable;

final class FeedItem implements JsonSerializable
{
    /**
     * @param string $id Unique identifier for this item
     * @param string $title Item title
     * @param string $link Item URL
     * @param ?string $content Item content/description
     * @param ?string $enclosure Enclosure/media URL
     * @param \DateTimeImmutable $publishedAt Publication date
     * @param ?string $category Item category/tag
     * @param array $extra Additional metadata from feed sources
     */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $link,
        public readonly ?string $content,
        public readonly ?string $enclosure,
        public readonly \DateTimeImmutable $publishedAt,
        public readonly ?string $category,
        public readonly ?array $extra = []
    ) {
        if (empty($this->id) && empty($this->link)) {
            throw new \InvalidArgumentException('Either id or link must be provided');
        }
    }

    /**
     * Generate a unique fingerprint for this item
     * Used to prevent duplicate items in feeds
     * 
     * Different strategies can be used:
     * - By ID and Link (default)
     * - By content hash (for detecting duplicates across sources)
     * - By title and date (for fuzzy matching)
     * 
     * @param string $strategy Hashing strategy: 'default', 'content', 'fuzzy'
     * @return string SHA256 hash
     */
    public function fingerprint(string $strategy = 'default'): string
    {
        return match ($strategy) {
            'default' => $this->fingerprintDefault(),
            'content' => $this->fingerprintContent(),
            'fuzzy' => $this->fingerprintFuzzy(),
            default => $this->fingerprintDefault(),
        };
    }

    /**
     * Default fingerprint: ID + Link
     */
    private function fingerprintDefault(): string
    {
        $data = trim($this->id . '|' . $this->link);
        return hash('sha256', $data);
    }

    /**
     * Content-based fingerprint: Title + Content + Published Date
     * Useful for detecting duplicates from different sources
     */
    private function fingerprintContent(): string
    {
        $data = trim(
            strtolower($this->title) . '|' .
            strtolower(strip_tags((string)$this->content)) . '|' .
            $this->publishedAt->format('Y-m-d')
        );
        return hash('sha256', $data);
    }

    /**
     * Fuzzy fingerprint: Title + Date (lenient matching)
     * Useful for grouping similar items
     */
    private function fingerprintFuzzy(): string
    {
        $data = trim(
            strtolower($this->title) . '|' .
            $this->publishedAt->format('Y-m-d')
        );
        return hash('sha256', $data);
    }

    /**
     * Get a specific extra field value
     * Supports nested access with dot notation (e.g., 'author.name')
     * 
     * @param string $key Field key (supports dot notation for nested fields)
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public function getExtra(string $key, $default = null)
    {
        if (empty($this->extra)) {
            return $default;
        }

        // Support dot notation for nested array access
        if (str_contains($key, '.')) {
            $keys = explode('.', $key);
            $value = $this->extra;
            
            foreach ($keys as $k) {
                if (is_array($value) && isset($value[$k])) {
                    $value = $value[$k];
                } else {
                    return $default;
                }
            }
            return $value;
        }

        return $this->extra[$key] ?? $default;
    }

    /**
     * Check if a specific extra field exists
     */
    public function hasExtra(string $key): bool
    {
        if (empty($this->extra)) {
            return false;
        }

        if (str_contains($key, '.')) {
            $keys = explode('.', $key);
            $value = $this->extra;
            
            foreach ($keys as $k) {
                if (is_array($value) && isset($value[$k])) {
                    $value = $value[$k];
                } else {
                    return false;
                }
            }
            return true;
        }

        return isset($this->extra[$key]);
    }

    /**
     * Get all extra fields
     */
    public function getExtraFields(): array
    {
        return $this->extra ?? [];
    }

    /**
     * Check if this item is valid
     * (has required fields)
     */
    public function isValid(): bool
    {
        return !empty($this->id) || !empty($this->link);
    }

    /**
     * Convert to array for API responses
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'link' => $this->link,
            'content' => $this->content,
            'enclosure' => $this->enclosure,
            'publishedAt' => $this->publishedAt->format(\DateTime::ATOM),
            'category' => $this->category,
            'extra' => $this->extra ?? [],
            'fingerprint' => $this->fingerprint(),
        ];
    }

    /**
     * Convert to JSON (implements JsonSerializable)
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Create a new instance with updated category
     */
    public function withCategory(string $category): self
    {
        return new self(
            $this->id,
            $this->title,
            $this->link,
            $this->content,
            $this->enclosure,
            $this->publishedAt,
            $category,
            $this->extra
        );
    }

    /**
     * Convert to string representation
     */
    public function __toString(): string
    {
        return sprintf(
            '%s - %s (%s)',
            $this->title,
            $this->publishedAt->format('Y-m-d H:i'),
            $this->category ?? 'uncategorized'
        );
    }

    /**
     * Compare two items for equality
     */
    public function equals(FeedItem $other): bool
    {
        return $this->fingerprint() === $other->fingerprint();
    }

    /**
     * Check if this item is similar to another (content-wise)
     */
    public function isSimilar(FeedItem $other): bool
    {
        return $this->fingerprintContent() === $other->fingerprintContent();
    }

    /**
     * Get basic metadata about this item
     */
    public function getMetadata(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->link,
            'category' => $this->category,
            'published_date' => $this->publishedAt->format(\DateTime::ATOM),
            'has_content' => !empty($this->content),
            'has_enclosure' => !empty($this->enclosure),
            'extra_fields_count' => count($this->extra ?? []),
            'fingerprint_default' => $this->fingerprint('default'),
            'fingerprint_content' => $this->fingerprint('content'),
            'fingerprint_fuzzy' => $this->fingerprint('fuzzy'),
        ];
    }
}
