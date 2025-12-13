<?php
namespace Hosseinhunta\Huntfeed\Parser;

use Hosseinhunta\Huntfeed\Feed\Feed;
use Hosseinhunta\Huntfeed\Feed\FeedItem;

final class Rss20Parser implements ParserInterface
{
    public function supports(string $xml): bool
    {
        return str_contains($xml, '<rss');
    }

    public function parse(string $xml, string $sourceUrl): Feed
    {
        libxml_use_internal_errors(true);
        $dom = new \SimpleXMLElement($xml, LIBXML_NOCDATA);

        // Register common namespaces
        $dom->registerXPathNamespace('content', 'http://purl.org/rss/1.0/modules/content/');
        $dom->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $dom->registerXPathNamespace('media', 'http://search.yahoo.com/mrss/');

        $feed = new Feed(
            $sourceUrl,
            (string) $dom->channel->title
        );

        foreach ($dom->channel->item as $item) {
            $extra = $this->extractExtra($item);
            
            $feed->addItem(new FeedItem(
                id: (string) ($item->guid ?? $item->link),
                title: (string) $item->title,
                link: (string) $item->link,
                content: (string) ($item->description ?? 
                    ($item->children('http://purl.org/rss/1.0/modules/content/')->encoded ?? null)),
                enclosure: isset($item->enclosure) ? (string) $item->enclosure['url'] : null,
                publishedAt: new \DateTimeImmutable((string) $item->pubDate),
                category: (string) ($item->category ?? null),
                extra: $extra
            ));
        }

        return $feed;
    }

    private function extractExtra(\SimpleXMLElement $item): array
    {
        $extra = [];

        // Standard RSS fields
        if (isset($item->author)) {
            $extra['author'] = (string) $item->author;
        }
        if (isset($item->comments)) {
            $extra['comments'] = (string) $item->comments;
        }
        if (isset($item->source)) {
            $extra['source'] = (string) $item->source;
        }

        // Dublin Core namespace
        $dc = $item->children('http://purl.org/dc/elements/1.1/');
        if (isset($dc->creator)) {
            $extra['creator'] = (string) $dc->creator;
        }
        if (isset($dc->date)) {
            $extra['dc_date'] = (string) $dc->date;
        }
        if (isset($dc->subject)) {
            $extra['subject'] = (string) $dc->subject;
        }

        // Content namespace
        $content = $item->children('http://purl.org/rss/1.0/modules/content/');
        if (isset($content->encoded)) {
            $extra['content_encoded'] = (string) $content->encoded;
        }

        // Media RSS namespace
        $media = $item->children('http://search.yahoo.com/mrss/');
        if (isset($media->content)) {
            $mediaContent = [];
            foreach ($media->content as $mc) {
                $mediaContent[] = [
                    'url' => (string) ($mc['url'] ?? ''),
                    'type' => (string) ($mc['type'] ?? ''),
                    'medium' => (string) ($mc['medium'] ?? ''),
                ];
            }
            $extra['media_content'] = $mediaContent;
        }
        if (isset($media->thumbnail)) {
            $extra['media_thumbnail'] = (string) $media->thumbnail['url'];
        }
        if (isset($media->title)) {
            $extra['media_title'] = (string) $media->title;
        }
        if (isset($media->description)) {
            $extra['media_description'] = (string) $media->description;
        }

        // Atom namespace (sometimes used in RSS)
        $atom = $item->children('http://www.w3.org/2005/Atom');
        if (isset($atom->link)) {
            foreach ($atom->link as $link) {
                $rel = (string) ($link['rel'] ?? 'related');
                if (!isset($extra['atom_links'])) {
                    $extra['atom_links'] = [];
                }
                $extra['atom_links'][$rel] = (string) $link['href'];
            }
        }

        // Custom fields (all other elements as-is)
        $customFields = [];
        foreach ($item->children() as $child) {
            $name = $child->getName();
            // Skip standard RSS fields
            if (!in_array($name, ['title', 'link', 'description', 'author', 'category', 'comments', 'enclosure', 'guid', 'pubDate', 'source'])) {
                if (!isset($customFields[$name])) {
                    $customFields[$name] = [];
                }
                $customFields[$name][] = (string) $child;
            }
        }
        if (!empty($customFields)) {
            $extra['custom_fields'] = $customFields;
        }

        return $extra;
    }
}
