<?php
namespace Hosseinhunta\Huntfeed\Parser;

use Hosseinhunta\Huntfeed\Feed\Feed;
use Hosseinhunta\Huntfeed\Feed\FeedItem;

final class GeoRssParser implements ParserInterface
{
    public function supports(string $xml): bool
    {
        return (str_contains($xml, 'xmlns:georss') || str_contains($xml, 'georss:')) 
            && (str_contains($xml, '<rss') || str_contains($xml, '<feed'));
    }

    public function parse(string $xml, string $sourceUrl): Feed
    {
        libxml_use_internal_errors(true);
        $dom = new \SimpleXMLElement($xml, LIBXML_NOCDATA);

        // Register GeoRSS namespace
        $dom->registerXPathNamespace('georss', 'http://www.georss.org/georss');
        $dom->registerXPathNamespace('gml', 'http://www.opengis.net/gml');
        $dom->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');

        $feed = new Feed(
            $sourceUrl,
            (string) ($dom->channel->title ?? $dom->title ?? 'Unknown Feed')
        );

        // Determine if it's RSS or Atom
        $isAtom = str_contains($xml, '<feed');
        $items = $isAtom ? $dom->entry : $dom->channel->item;

        foreach ($items as $item) {
            $geoData = $this->extractGeoData($item);

            $publishedAt = null;
            if ($isAtom) {
                $publishedAt = isset($item->published) ? new \DateTimeImmutable((string) $item->published) 
                    : (isset($item->updated) ? new \DateTimeImmutable((string) $item->updated) : new \DateTimeImmutable());
            } else {
                $publishedAt = isset($item->pubDate) ? new \DateTimeImmutable((string) $item->pubDate) : new \DateTimeImmutable();
            }

            $content = '';
            if ($isAtom) {
                $content = isset($item->summary) ? (string) $item->summary : (isset($item->content) ? (string) $item->content : '');
            } else {
                $content = (string) ($item->description ?? '');
            }

            $link = '';
            if ($isAtom) {
                if (isset($item->link)) {
                    foreach ($item->link as $l) {
                        if ((string) $l['rel'] === 'alternate' || (string) $l['rel'] === '') {
                            $link = (string) $l['href'];
                            break;
                        }
                    }
                }
            } else {
                $link = (string) ($item->link ?? '');
            }

            $feed->addItem(new FeedItem(
                id: (string) ($isAtom ? $item->id : ($item->guid ?? $link)),
                title: (string) ($item->title ?? 'Untitled'),
                link: $link,
                content: $content ?: null,
                enclosure: null,
                publishedAt: $publishedAt,
                category: null,
                extra: array_merge(
                    [
                        'geo' => $geoData,
                    ],
                    $isAtom ? ['author' => isset($item->author->name) ? (string) $item->author->name : null] : []
                )
            ));
        }

        return $feed;
    }

    private function extractGeoData(\SimpleXMLElement $item): ?array
    {
        $geoData = [];

        // GeoRSS Simple format: georss:point
        if (isset($item->children('http://www.georss.org/georss')->point)) {
            $point = (string) $item->children('http://www.georss.org/georss')->point;
            [$lat, $lon] = explode(' ', trim($point));
            $geoData['type'] = 'point';
            $geoData['latitude'] = (float) $lat;
            $geoData['longitude'] = (float) $lon;
        }

        // GeoRSS Simple format: georss:line
        if (isset($item->children('http://www.georss.org/georss')->line)) {
            $line = (string) $item->children('http://www.georss.org/georss')->line;
            $coordinates = [];
            $pairs = explode(' ', trim($line));
            for ($i = 0; $i < count($pairs) - 1; $i += 2) {
                $coordinates[] = [
                    'latitude' => (float) $pairs[$i],
                    'longitude' => (float) $pairs[$i + 1],
                ];
            }
            $geoData['type'] = 'line';
            $geoData['coordinates'] = $coordinates;
        }

        // GeoRSS Simple format: georss:polygon
        if (isset($item->children('http://www.georss.org/georss')->polygon)) {
            $polygon = (string) $item->children('http://www.georss.org/georss')->polygon;
            $coordinates = [];
            $pairs = explode(' ', trim($polygon));
            for ($i = 0; $i < count($pairs) - 1; $i += 2) {
                $coordinates[] = [
                    'latitude' => (float) $pairs[$i],
                    'longitude' => (float) $pairs[$i + 1],
                ];
            }
            $geoData['type'] = 'polygon';
            $geoData['coordinates'] = $coordinates;
        }

        // GeoRSS Simple format: georss:box
        if (isset($item->children('http://www.georss.org/georss')->box)) {
            $box = (string) $item->children('http://www.georss.org/georss')->box;
            [$lat1, $lon1, $lat2, $lon2] = explode(' ', trim($box));
            $geoData['type'] = 'box';
            $geoData['southwest'] = ['latitude' => (float) $lat1, 'longitude' => (float) $lon1];
            $geoData['northeast'] = ['latitude' => (float) $lat2, 'longitude' => (float) $lon2];
        }

        // GeoRSS GML format: georss:where with gml:Point
        $gmlWhere = $item->children('http://www.georss.org/georss')->where;
        if (isset($gmlWhere)) {
            $gmlPoint = $gmlWhere->children('http://www.opengis.net/gml')->Point;
            if (isset($gmlPoint->pos)) {
                $pos = (string) $gmlPoint->pos;
                [$lat, $lon] = explode(' ', trim($pos));
                $geoData['type'] = 'point';
                $geoData['latitude'] = (float) $lat;
                $geoData['longitude'] = (float) $lon;
                $geoData['format'] = 'gml';
            }
        }

        // GeoRSS Simple format: georss:featureName
        if (isset($item->children('http://www.georss.org/georss')->featureName)) {
            $geoData['featureName'] = (string) $item->children('http://www.georss.org/georss')->featureName;
        }

        // GeoRSS Simple format: georss:relationshipTag
        if (isset($item->children('http://www.georss.org/georss')->relationshipTag)) {
            $geoData['relationshipTag'] = (string) $item->children('http://www.georss.org/georss')->relationshipTag;
        }

        return !empty($geoData) ? $geoData : null;
    }
}
