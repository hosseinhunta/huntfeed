<?php
namespace Hosseinhunta\Huntfeed\Transport;

final class PollingTransport
{
    public function fetch(string $url): string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Huntfeed/1.0'
            ]
        ]);

        $xml = file_get_contents($url, false, $context);

        if ($xml === false) {
            throw new \RuntimeException('Failed to fetch feed.');
        }

        return $xml;
    }
}
