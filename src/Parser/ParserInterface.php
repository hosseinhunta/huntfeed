<?php
namespace Hosseinhunta\Huntfeed\Parser;

use Hosseinhunta\Huntfeed\Feed\Feed;

interface ParserInterface
{
    public function supports(string $xml): bool;

    public function parse(string $xml, string $sourceUrl): Feed;
}
