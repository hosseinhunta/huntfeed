<?php
namespace Hosseinhunta\Huntfeed\Hub;

use Hosseinhunta\Huntfeed\Feed\Feed;
use Hosseinhunta\Huntfeed\Feed\FeedItem;

final class FeedExporter
{
    /**
     * Export feed to JSON
     */
    public static function toJSON(Feed $feed, bool $pretty = true): string
    {
        $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        if ($pretty) {
            $options |= JSON_PRETTY_PRINT;
        }
        return json_encode($feed, $options);
    }

    /**
     * Export feed to XML (RSS 2.0 format)
     */
    public static function toRSS(Feed $feed): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= "  <channel>\n";
        $xml .= "    <title>" . htmlspecialchars($feed->title) . "</title>\n";
        $xml .= "    <link>" . htmlspecialchars($feed->url) . "</link>\n";
        $xml .= "    <description>" . htmlspecialchars($feed->title) . "</description>\n";
        $xml .= "    <language>en-us</language>\n";
        $xml .= "    <lastBuildDate>" . date('r') . "</lastBuildDate>\n";

        foreach ($feed->items() as $item) {
            $xml .= "    <item>\n";
            $xml .= "      <title>" . htmlspecialchars($item->title) . "</title>\n";
            $xml .= "      <link>" . htmlspecialchars($item->link) . "</link>\n";
            $xml .= "      <description>" . htmlspecialchars((string)$item->content) . "</description>\n";
            $xml .= "      <pubDate>" . $item->publishedAt->format('r') . "</pubDate>\n";
            $xml .= "      <guid>" . htmlspecialchars($item->id) . "</guid>\n";

            if ($item->category) {
                $xml .= "      <category>" . htmlspecialchars($item->category) . "</category>\n";
            }

            if ($item->enclosure) {
                $xml .= "      <enclosure url=\"" . htmlspecialchars($item->enclosure) . "\" />\n";
            }

            $xml .= "    </item>\n";
        }

        $xml .= "  </channel>\n";
        $xml .= "</rss>\n";

        return $xml;
    }

    /**
     * Export feed to Atom format
     */
    public static function toAtom(Feed $feed): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<feed xmlns="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= "  <title>" . htmlspecialchars($feed->title) . "</title>\n";
        $xml .= "  <link href=\"" . htmlspecialchars($feed->url) . "\" />\n";
        $xml .= "  <updated>" . date('c') . "</updated>\n";
        $xml .= "  <id>" . htmlspecialchars($feed->url) . "</id>\n";

        foreach ($feed->items() as $item) {
            $xml .= "  <entry>\n";
            $xml .= "    <title>" . htmlspecialchars($item->title) . "</title>\n";
            $xml .= "    <link href=\"" . htmlspecialchars($item->link) . "\" />\n";
            $xml .= "    <id>" . htmlspecialchars($item->id) . "</id>\n";
            $xml .= "    <published>" . $item->publishedAt->format('c') . "</published>\n";
            $xml .= "    <updated>" . $item->publishedAt->format('c') . "</updated>\n";
            $xml .= "    <summary>" . htmlspecialchars((string)$item->content) . "</summary>\n";
            $xml .= "  </entry>\n";
        }

        $xml .= "</feed>\n";

        return $xml;
    }

    /**
     * Export feed to JSON Feed format
     */
    public static function toJSONFeed(Feed $feed): string
    {
        $items = [];
        foreach ($feed->items() as $item) {
            $items[] = [
                'id' => $item->id,
                'title' => $item->title,
                'url' => $item->link,
                'content_html' => $item->content,
                'date_published' => $item->publishedAt->format(\DateTime::ATOM),
                'tags' => $item->category ? [$item->category] : [],
            ];
        }

        $feedData = [
            'version' => 'https://jsonfeed.org/version/1.1',
            'title' => $feed->title,
            'feed_url' => $feed->url,
            'items' => $items,
        ];

        return json_encode($feedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Export feed to CSV format
     */
    public static function toCSV(Feed $feed, bool $withHeader = true): string
    {
        $csv = '';

        if ($withHeader) {
            $csv .= "ID,Title,Link,Category,Published Date,Has Content,Has Enclosure\n";
        }

        foreach ($feed->items() as $item) {
            $csv .= self::escapeCsvField($item->id) . ',';
            $csv .= self::escapeCsvField($item->title) . ',';
            $csv .= self::escapeCsvField($item->link) . ',';
            $csv .= self::escapeCsvField((string)$item->category) . ',';
            $csv .= self::escapeCsvField($item->publishedAt->format('Y-m-d H:i:s')) . ',';
            $csv .= ($item->content ? 'Yes' : 'No') . ',';
            $csv .= ($item->enclosure ? 'Yes' : 'No') . "\n";
        }

        return $csv;
    }

    /**
     * Export feed to HTML
     */
    public static function toHTML(Feed $feed): string
    {
        $html = '<!DOCTYPE html>' . "\n";
        $html .= '<html lang="en">' . "\n";
        $html .= "<head>\n";
        $html .= "  <meta charset=\"UTF-8\" />\n";
        $html .= "  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" />\n";
        $html .= "  <title>" . htmlspecialchars($feed->title) . "</title>\n";
        $html .= "  <style>\n";
        $html .= "    body { font-family: Arial, sans-serif; margin: 20px; }\n";
        $html .= "    .feed-header { border-bottom: 2px solid #ccc; margin-bottom: 20px; }\n";
        $html .= "    .item { margin-bottom: 30px; padding: 15px; border: 1px solid #eee; border-radius: 5px; }\n";
        $html .= "    .item-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }\n";
        $html .= "    .item-meta { font-size: 12px; color: #666; margin-bottom: 10px; }\n";
        $html .= "    .item-content { margin: 10px 0; }\n";
        $html .= "  </style>\n";
        $html .= "</head>\n";
        $html .= "<body>\n";

        $html .= "  <div class=\"feed-header\">\n";
        $html .= "    <h1>" . htmlspecialchars($feed->title) . "</h1>\n";
        $html .= "    <p><a href=\"" . htmlspecialchars($feed->url) . "\">" . htmlspecialchars($feed->url) . "</a></p>\n";
        $html .= "  </div>\n";

        foreach ($feed->items() as $item) {
            $html .= "  <div class=\"item\">\n";
            $html .= "    <div class=\"item-title\">" . htmlspecialchars($item->title) . "</div>\n";
            $html .= "    <div class=\"item-meta\">\n";
            $html .= "      Published: " . $item->publishedAt->format('Y-m-d H:i:s') . "<br />\n";
            if ($item->category) {
                $html .= "      Category: " . htmlspecialchars($item->category) . "<br />\n";
            }
            $html .= "      <a href=\"" . htmlspecialchars($item->link) . "\">Read More</a>\n";
            $html .= "    </div>\n";
            if ($item->content) {
                $html .= "    <div class=\"item-content\">" . nl2br(htmlspecialchars((string)$item->content)) . "</div>\n";
            }
            $html .= "  </div>\n";
        }

        $html .= "</body>\n";
        $html .= "</html>\n";

        return $html;
    }

    /**
     * Export feed to plain text format
     */
    public static function toText(Feed $feed): string
    {
        $text = "Feed: " . $feed->title . "\n";
        $text .= "URL: " . $feed->url . "\n";
        $text .= "Items: " . $feed->itemsCount() . "\n";
        $text .= str_repeat("=", 80) . "\n\n";

        foreach ($feed->items() as $item) {
            $text .= "Title: " . $item->title . "\n";
            $text .= "Link: " . $item->link . "\n";
            $text .= "Published: " . $item->publishedAt->format('Y-m-d H:i:s') . "\n";
            if ($item->category) {
                $text .= "Category: " . $item->category . "\n";
            }
            if ($item->content) {
                $text .= "Content: " . substr($item->content, 0, 200) . (strlen($item->content) > 200 ? "..." : "") . "\n";
            }
            $text .= str_repeat("-", 80) . "\n\n";
        }

        return $text;
    }

    /**
     * Escape CSV field
     */
    private static function escapeCsvField(string $field): string
    {
        if (empty($field)) {
            return '""';
        }

        if (str_contains($field, ',') || str_contains($field, '"') || str_contains($field, "\n")) {
            return '"' . str_replace('"', '""', $field) . '"';
        }

        return $field;
    }
}
