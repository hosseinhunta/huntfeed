<?php
require __DIR__ . '/../vendor/autoload.php';

use Hosseinhunta\Huntfeed\Transport\PollingTransport;
use Hosseinhunta\Huntfeed\Parser\AutoDetectParser;
use Hosseinhunta\Huntfeed\Engine\UpdateDetector;

$url = "https://sample-feeds.rowanmanning.com/real-world/6c49067b57d4b1c2a25cd455d49e130a/feed.xml";

$transport = new PollingTransport();
$xml = $transport->fetch($url);

$parser = new AutoDetectParser();
$feed = $parser->parse($xml, $url);

$detector = new UpdateDetector();
$newItems = $detector->detect($feed->items(), []);

$timestamp = date('Y-m-d_H-i-s');
$outputFile = __DIR__ . '/json_test/' . "atom_feed_{$timestamp}.json";
$jsonData = json_encode($newItems, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if (file_put_contents($outputFile, $jsonData)) {
    echo "Success: Data has been saved to file '$outputFile'.\n";
    echo "Total items saved: " . count($newItems) . "\n";
    echo "File size: " . filesize($outputFile) . " bytes\n";
} else {
    echo "Error: Failed to write data to file '$outputFile'.\n";
    echo "Check directory permissions.\n";
}