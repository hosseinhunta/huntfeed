<?php
namespace Hosseinhunta\Huntfeed\Parser;

use RuntimeException;

final class AutoDetectParser
{
    /** @var ParserInterface[] */
    private array $parsers;

    public function __construct(ParserInterface ...$parsers)
    {
        // If no parsers provided, register all available parsers automatically
        if (empty($parsers)) {
            $this->parsers = [
                new GeoRssParser(),
                new Rss20Parser(),
                new AtomParser(),
                new JsonFeedParser(),
                new RdfParser(),
            ];
        } else {
            $this->parsers = $parsers;
        }
    }

    /**
     * Create an instance with all available parsers pre-registered
     */
    public static function createDefault(): self
    {
        return new self();
    }

    /**
     * Add a custom parser to the list
     */
    public function addParser(ParserInterface $parser): self
    {
        $this->parsers[] = $parser;
        return $this;
    }

    /**
     * Parse feed content and automatically detect its format
     */
    public function parse(string $content, string $url)
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports($content)) {
                return $parser->parse($content, $url);
            }
        }

        throw new RuntimeException('Unsupported feed format. Supported formats: GeoRSS, RSS 2.0, Atom, JSON Feed, RDF/RSS 1.0');
    }
}
