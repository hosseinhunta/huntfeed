<?php
namespace Hosseinhunta\Huntfeed\Parser;

use Hosseinhunta\Huntfeed\Feed\Feed;
use Hosseinhunta\Huntfeed\Feed\FeedItem;

final class RdfParser implements ParserInterface
{
    public function supports(string $xml): bool
    {
        return str_contains($xml, '<rdf:RDF') && str_contains($xml, 'http://purl.org/rss/1.0/');
    }

    public function parse(string $xml, string $sourceUrl): Feed
    {
        libxml_use_internal_errors(true);
        $dom = new \SimpleXMLElement($xml, LIBXML_NOCDATA);

        // Register namespaces
        $dom->registerXPathNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
        $dom->registerXPathNamespace('rss', 'http://purl.org/rss/1.0/');
        $dom->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $dom->registerXPathNamespace('content', 'http://purl.org/rss/1.0/modules/content/');
        $dom->registerXPathNamespace('media', 'http://search.yahoo.com/mrss/');

        // Get channel/feed info
        $channel = $dom->xpath('//rss:channel')[0] ?? null;
        $feedTitle = $channel ? (string) $channel->title : 'Unknown Feed';

        $feed = new Feed($sourceUrl, $feedTitle);

        // Get items
        $items = $dom->xpath('//rss:item');
        foreach ($items as $item) {
            $pubDate = null;
            if (isset($item->children('http://purl.org/dc/elements/1.1/')->date)) {
                $pubDate = new \DateTimeImmutable((string) $item->children('http://purl.org/dc/elements/1.1/')->date);
            } else {
                $pubDate = new \DateTimeImmutable();
            }

            $extra = $this->extractExtra($item);

            $feed->addItem(new FeedItem(
                id: (string) ($item->attributes('http://www.w3.org/1999/02/22-rdf-syntax-ns#')->about ?? $item->link),
                title: (string) ($item->title ?? 'Untitled'),
                link: (string) ($item->link ?? ''),
                content: (string) ($item->description ?? null),
                enclosure: null,
                publishedAt: $pubDate,
                category: (string) ($item->subject ?? null),
                extra: $extra
            ));
        }

        return $feed;
    }

    private function extractExtra(\SimpleXMLElement $item): array
    {
        $extra = [];

        // RDF about attribute
        $about = $item->attributes('http://www.w3.org/1999/02/22-rdf-syntax-ns#')->about;
        if ($about) {
            $extra['about'] = (string) $about;
        }

        // Dublin Core namespace
        $dc = $item->children('http://purl.org/dc/elements/1.1/');
        
        if (isset($dc->creator)) {
            $extra['creator'] = (string) $dc->creator;
        }
        if (isset($dc->subject)) {
            $extra['subject'] = (string) $dc->subject;
        }
        if (isset($dc->date)) {
            $extra['dc_date'] = (string) $dc->date;
        }
        if (isset($dc->language)) {
            $extra['language'] = (string) $dc->language;
        }
        if (isset($dc->rights)) {
            $extra['rights'] = (string) $dc->rights;
        }
        if (isset($dc->contributor)) {
            $extra['contributor'] = (string) $dc->contributor;
        }
        if (isset($dc->publisher)) {
            $extra['publisher'] = (string) $dc->publisher;
        }
        if (isset($dc->relation)) {
            $extra['relation'] = (string) $dc->relation;
        }
        if (isset($dc->coverage)) {
            $extra['coverage'] = (string) $dc->coverage;
        }

        // Content namespace
        $content = $item->children('http://purl.org/rss/1.0/modules/content/');
        if (isset($content->encoded)) {
            $extra['content_encoded'] = (string) $content->encoded;
        }

        // Media RSS
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

        // Standard RSS fields
        if (isset($item->author)) {
            $extra['author'] = (string) $item->author;
        }
        if (isset($item->comments)) {
            $extra['comments'] = (string) $item->comments;
        }
        if (isset($item->category)) {
            $extra['category'] = (string) $item->category;
        }

        return $extra;
    }
}
