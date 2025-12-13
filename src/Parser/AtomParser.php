<?php
namespace Hosseinhunta\Huntfeed\Parser;

use Hosseinhunta\Huntfeed\Feed\Feed;
use Hosseinhunta\Huntfeed\Feed\FeedItem;

final class AtomParser implements ParserInterface
{
    public function supports(string $xml): bool
    {
        return str_contains($xml, '<feed') && str_contains($xml, 'xmlns="http://www.w3.org/2005/Atom"');
    }

    public function parse(string $xml, string $sourceUrl): Feed
    {
        libxml_use_internal_errors(true);
        $dom = new \SimpleXMLElement($xml, LIBXML_NOCDATA);

        // Register namespaces
        $dom->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
        $dom->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $dom->registerXPathNamespace('media', 'http://search.yahoo.com/mrss/');

        $feed = new Feed(
            $sourceUrl,
            (string) ($dom->title ?? 'Unknown Feed')
        );

        foreach ($dom->entry as $entry) {
            // Handle multiple links (Atom allows multiple links)
            $link = '';
            if (isset($entry->link)) {
                foreach ($entry->link as $l) {
                    if ((string) $l['rel'] === 'alternate' || (string) $l['rel'] === '') {
                        $link = (string) $l['href'];
                        break;
                    }
                }
            }

            // Get published date
            $publishedAt = null;
            if (isset($entry->published)) {
                $publishedAt = new \DateTimeImmutable((string) $entry->published);
            } elseif (isset($entry->updated)) {
                $publishedAt = new \DateTimeImmutable((string) $entry->updated);
            } else {
                $publishedAt = new \DateTimeImmutable();
            }

            // Handle content/summary
            $content = '';
            if (isset($entry->summary)) {
                $content = (string) $entry->summary;
            } elseif (isset($entry->content)) {
                $content = (string) $entry->content;
            }

            $extra = $this->extractExtra($entry);

            $feed->addItem(new FeedItem(
                id: (string) ($entry->id ?? $link),
                title: (string) ($entry->title ?? 'Untitled'),
                link: $link,
                content: $content ?: null,
                enclosure: isset($entry->link) ? $this->getEnclosure($entry->link) : null,
                publishedAt: $publishedAt,
                category: isset($entry->category) ? (string) $entry->category['term'] : null,
                extra: $extra
            ));
        }

        return $feed;
    }

    private function extractExtra(\SimpleXMLElement $entry): array
    {
        $extra = [];

        // Author information
        if (isset($entry->author)) {
            $author = [];
            if (isset($entry->author->name)) {
                $author['name'] = (string) $entry->author->name;
            }
            if (isset($entry->author->email)) {
                $author['email'] = (string) $entry->author->email;
            }
            if (isset($entry->author->uri)) {
                $author['uri'] = (string) $entry->author->uri;
            }
            if (!empty($author)) {
                $extra['author'] = $author;
            }
        }

        // Updated date
        if (isset($entry->updated)) {
            $extra['updated'] = (string) $entry->updated;
        }

        // All links with their relationships
        if (isset($entry->link)) {
            $links = [];
            foreach ($entry->link as $link) {
                $rel = (string) ($link['rel'] ?? 'alternate');
                $links[$rel] = [
                    'href' => (string) $link['href'],
                    'type' => (string) ($link['type'] ?? ''),
                    'hreflang' => (string) ($link['hreflang'] ?? ''),
                ];
            }
            $extra['links'] = $links;
        }

        // Categories
        if (isset($entry->category)) {
            $categories = [];
            foreach ($entry->category as $cat) {
                $categories[] = [
                    'term' => (string) ($cat['term'] ?? ''),
                    'scheme' => (string) ($cat['scheme'] ?? ''),
                    'label' => (string) ($cat['label'] ?? ''),
                ];
            }
            $extra['categories'] = $categories;
        }

        // Contributors
        if (isset($entry->contributor)) {
            $contributors = [];
            foreach ($entry->contributor as $contrib) {
                $contributors[] = [
                    'name' => isset($contrib->name) ? (string) $contrib->name : '',
                    'email' => isset($contrib->email) ? (string) $contrib->email : '',
                    'uri' => isset($contrib->uri) ? (string) $contrib->uri : '',
                ];
            }
            $extra['contributors'] = $contributors;
        }

        // Dublin Core metadata
        $dc = $entry->children('http://purl.org/dc/elements/1.1/');
        if (isset($dc->creator)) {
            $extra['dc_creator'] = (string) $dc->creator;
        }
        if (isset($dc->subject)) {
            $extra['dc_subject'] = (string) $dc->subject;
        }
        if (isset($dc->rights)) {
            $extra['dc_rights'] = (string) $dc->rights;
        }

        // Media RSS
        $media = $entry->children('http://search.yahoo.com/mrss/');
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

        // Rights/Copyright
        if (isset($entry->rights)) {
            $extra['rights'] = (string) $entry->rights;
        }

        // Source (if entry is republished)
        if (isset($entry->source)) {
            $extra['source'] = [
                'id' => isset($entry->source->id) ? (string) $entry->source->id : '',
                'title' => isset($entry->source->title) ? (string) $entry->source->title : '',
            ];
        }

        // Published date (if available)
        if (isset($entry->published)) {
            $extra['published'] = (string) $entry->published;
        }

        return $extra;
    }

    private function getEnclosure(\SimpleXMLElement $links): ?string
    {
        foreach ($links as $link) {
            if ((string) $link['rel'] === 'enclosure') {
                return (string) $link['href'];
            }
        }
        return null;
    }
}
