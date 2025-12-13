<?php
namespace Hosseinhunta\Huntfeed\Engine;

use Hosseinhunta\Huntfeed\Feed\FeedItem;

final class UpdateDetector
{
    /**
     * @param FeedItem[] $current
     * @param string[]   $knownFingerprints
     * @return FeedItem[]
     */
    public function detect(array $current, array $knownFingerprints): array
    {
        return array_filter(
            $current,
            fn (FeedItem $item) =>
                !in_array($item->fingerprint(), $knownFingerprints, true)
        );
    }
}
