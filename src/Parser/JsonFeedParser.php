<?php
namespace Hosseinhunta\Huntfeed\Parser;

use Hosseinhunta\Huntfeed\Feed\Feed;
use Hosseinhunta\Huntfeed\Feed\FeedItem;

final class JsonFeedParser implements ParserInterface
{
    public function supports(string $content): bool
    {
        // Try to detect if it's valid JSON
        if (!$this->isJson($content)) {
            return false;
        }

        $data = json_decode($content, true);
        return is_array($data) && isset($data['version']) && str_contains($data['version'], 'https://jsonfeed.org');
    }

    public function parse(string $content, string $sourceUrl): Feed
    {
        $data = json_decode($content, true);

        if (!is_array($data)) {
            throw new \RuntimeException('Invalid JSON Feed format');
        }

        $feed = new Feed(
            $sourceUrl,
            $data['title'] ?? 'Unknown Feed'
        );

        $items = $data['items'] ?? [];
        foreach ($items as $item) {
            $publishedAt = $item['date_published'] ?? $item['date_modified'] ?? null;
            if ($publishedAt) {
                $publishedAt = new \DateTimeImmutable($publishedAt);
            } else {
                $publishedAt = new \DateTimeImmutable();
            }

            $content = $item['content_html'] ?? $item['content_text'] ?? '';

            $extra = $this->extractExtra($item);

            $feed->addItem(new FeedItem(
                id: $item['id'] ?? ($item['url'] ?? ''),
                title: $item['title'] ?? 'Untitled',
                link: $item['url'] ?? '',
                content: $content ?: null,
                enclosure: isset($item['attachments']) && !empty($item['attachments'])
                    ? $item['attachments'][0]['url'] ?? null
                    : null,
                publishedAt: $publishedAt,
                category: $item['tags'][0] ?? null,
                extra: $extra
            ));
        }

        return $feed;
    }

    private function extractExtra(array $item): array
    {
        $extra = [];

        // Author information
        if (isset($item['author'])) {
            $extra['author'] = $item['author'];
        }

        // Tags
        if (isset($item['tags']) && !empty($item['tags'])) {
            $extra['tags'] = $item['tags'];
        }

        // Attachments with all metadata
        if (isset($item['attachments']) && !empty($item['attachments'])) {
            $extra['attachments'] = $item['attachments'];
        }

        // Published and modified dates
        if (isset($item['date_published'])) {
            $extra['date_published'] = $item['date_published'];
        }
        if (isset($item['date_modified'])) {
            $extra['date_modified'] = $item['date_modified'];
        }

        // Both content formats
        if (isset($item['content_html'])) {
            $extra['content_html'] = $item['content_html'];
        }
        if (isset($item['content_text'])) {
            $extra['content_text'] = $item['content_text'];
        }

        // Summary
        if (isset($item['summary'])) {
            $extra['summary'] = $item['summary'];
        }

        // Image and banner_image
        if (isset($item['image'])) {
            $extra['image'] = $item['image'];
        }
        if (isset($item['banner_image'])) {
            $extra['banner_image'] = $item['banner_image'];
        }

        // Language
        if (isset($item['language'])) {
            $extra['language'] = $item['language'];
        }

        // External URL (for entries linking to external content)
        if (isset($item['external_url'])) {
            $extra['external_url'] = $item['external_url'];
        }

        // Collect any custom/unknown fields
        $knownFields = ['id', 'url', 'title', 'content_html', 'content_text', 'summary', 'image', 'banner_image',
                        'date_published', 'date_modified', 'author', 'tags', 'language', 'attachments', 'external_url'];
        $customFields = [];
        foreach ($item as $key => $value) {
            if (!in_array($key, $knownFields)) {
                $customFields[$key] = $value;
            }
        }
        if (!empty($customFields)) {
            $extra['custom_fields'] = $customFields;
        }

        return $extra;
    }

    private function isJson(string $content): bool
    {
        json_decode($content);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
