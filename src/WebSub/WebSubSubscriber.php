<?php

namespace Hosseinhunta\Huntfeed\WebSub;

use Hosseinhunta\Huntfeed\Transport\FeedFetcher;
use Exception;

/**
 * WebSub (PubSubHubbub) Subscriber Implementation
 * 
 * Handles subscription to WebSub hubs for push-based feed updates
 * instead of polling. Automatically subscribes to hubs found in RSS feeds.
 */
class WebSubSubscriber
{
    private FeedFetcher $fetcher;
    private string $callbackUrl;
    private array $subscriptions = []; // feed_url => subscription_data
    private int $leaseSeconds = 86400; // 24 hours default
    private int $verificationTimeout = 5;

    public function __construct(FeedFetcher $fetcher, string $callbackUrl)
    {
        $this->fetcher = $fetcher;
        $this->callbackUrl = $callbackUrl;
    }

    /**
     * Subscribe to a WebSub hub for a given feed
     * 
     * @param string $feedUrl The feed URL to subscribe to
     * @param string $hubUrl The WebSub hub URL
     * @param callable|null $onVerification Callback when hub sends verification
     * @return array Subscription result with status
     */
    public function subscribe(string $feedUrl, string $hubUrl, ?callable $onVerification = null): array
    {
        try {
            // Prepare subscription request
            $params = [
                'hub.callback' => $this->callbackUrl,
                'hub.mode' => 'subscribe',
                'hub.topic' => $feedUrl,
                'hub.lease_seconds' => $this->leaseSeconds,
                'hub.secret' => bin2hex(random_bytes(32)), // For signature verification
            ];

            // Send subscription request to hub
            $response = $this->sendHubRequest($hubUrl, $params);

            // Store subscription
            $this->subscriptions[$feedUrl] = [
                'hub_url' => $hubUrl,
                'feed_url' => $feedUrl,
                'callback_url' => $this->callbackUrl,
                'secret' => $params['hub.secret'],
                'lease_seconds' => $this->leaseSeconds,
                'subscribed_at' => new \DateTimeImmutable(),
                'verified' => false,
                'verification_callback' => $onVerification,
            ];

            return [
                'success' => true,
                'feed_url' => $feedUrl,
                'hub_url' => $hubUrl,
                'message' => 'Subscription request sent to hub. Awaiting verification...',
                'subscription_id' => md5($feedUrl),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'feed_url' => $feedUrl,
            ];
        }
    }

    /**
     * Unsubscribe from a WebSub hub
     * 
     * @param string $feedUrl
     * @return array Unsubscription result
     */
    public function unsubscribe(string $feedUrl): array
    {
        if (!isset($this->subscriptions[$feedUrl])) {
            return [
                'success' => false,
                'error' => 'Feed not subscribed',
            ];
        }

        $subscription = $this->subscriptions[$feedUrl];

        try {
            $params = [
                'hub.callback' => $subscription['callback_url'],
                'hub.mode' => 'unsubscribe',
                'hub.topic' => $feedUrl,
            ];

            $this->sendHubRequest($subscription['hub_url'], $params);
            unset($this->subscriptions[$feedUrl]);

            return [
                'success' => true,
                'feed_url' => $feedUrl,
                'message' => 'Unsubscribed from hub',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify a subscription challenge from hub
     * 
     * This is called by the hub to verify that our callback URL is valid
     * 
     * @param array $params GET parameters from hub verification request
     * @return array Verification result
     */
    public function verifyChallenge(array $params): array
    {
        // Verify required parameters
        if (!isset($params['hub_challenge']) || !isset($params['hub_topic'])) {
            return [
                'success' => false,
                'error' => 'Missing required verification parameters',
            ];
        }

        $feedUrl = $params['hub_topic'];
        $challenge = $params['hub_challenge'];
        $mode = $params['hub_mode'] ?? 'subscribe';
        $leaseSeconds = intval($params['hub_lease_seconds'] ?? 0);

        // Check if we have this subscription pending
        if (!isset($this->subscriptions[$feedUrl])) {
            return [
                'success' => false,
                'error' => 'Subscription not found for feed: ' . $feedUrl,
            ];
        }

        // Mark as verified
        $this->subscriptions[$feedUrl]['verified'] = true;
        $this->subscriptions[$feedUrl]['lease_seconds'] = $leaseSeconds;

        // Call verification callback if provided
        if ($callback = $this->subscriptions[$feedUrl]['verification_callback']) {
            call_user_func($callback, [
                'feed_url' => $feedUrl,
                'mode' => $mode,
                'lease_seconds' => $leaseSeconds,
            ]);
        }

        return [
            'success' => true,
            'challenge' => $challenge,
            'feed_url' => $feedUrl,
            'message' => "Subscription verified for $feedUrl",
        ];
    }

    /**
     * Handle incoming push notification from hub
     * 
     * @param string $body Raw request body containing feed content
     * @param array $headers Request headers for signature verification
     * @return array Processing result with parsed feed items
     */
    public function handleNotification(string $body, array $headers): array
    {
        try {
            // Verify signature if secret is set
            $this->verifySignature($body, $headers);

            // Parse the feed content
            $items = $this->parseNotificationContent($body);

            return [
                'success' => true,
                'items_count' => count($items),
                'items' => $items,
                'message' => 'Push notification processed successfully',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Detect WebSub hub URL in a feed
     * 
     * Parses RSS/Atom feed to find hub link according to spec
     * 
     * @param string $feedContent Feed XML content
     * @return string|null Hub URL or null if not found
     */
    public static function detectHubFromFeed(string $feedContent): ?string
    {
        try {
            $xml = simplexml_load_string($feedContent);
            if (!$xml) {
                return null;
            }

            // Register Atom namespace
            $namespaces = $xml->getDocNamespaces(true);
            
            // Try without namespace first (simpler approach)
            
            // Look for hub link - try common patterns
            foreach ($xml->children() as $child) {
                if ($child->getName() === 'channel' || $child->getName() === 'feed') {
                    foreach ($child->children() as $element) {
                        if ($element->getName() === 'link') {
                            $rel = (string)$element->attributes()->rel;
                            $href = (string)$element->attributes()->href;
                            
                            if ($rel === 'hub' && !empty($href)) {
                                return $href;
                            }
                        }
                    }
                }
            }

            // Try link with rel="hub" attribute (for Atom)
            foreach ($xml->xpath('//link[@rel="hub"]') as $link) {
                if (isset($link['href'])) {
                    return (string)$link['href'];
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get subscription status
     * 
     * @param string|null $feedUrl Return specific feed subscription or all
     * @return array Subscription statuses
     */
    public function getSubscriptionStatus(?string $feedUrl = null): array
    {
        if ($feedUrl) {
            return isset($this->subscriptions[$feedUrl]) ? [
                'feed_url' => $feedUrl,
                'data' => $this->subscriptions[$feedUrl],
            ] : [
                'feed_url' => $feedUrl,
                'data' => null,
            ];
        }

        return [
            'total_subscriptions' => count($this->subscriptions),
            'subscriptions' => array_map(function ($sub) {
                return [
                    'feed_url' => $sub['feed_url'],
                    'hub_url' => $sub['hub_url'],
                    'verified' => $sub['verified'],
                    'subscribed_at' => $sub['subscribed_at']->format('Y-m-d H:i:s'),
                    'lease_seconds' => $sub['lease_seconds'],
                ];
            }, $this->subscriptions),
        ];
    }

    /**
     * Private helper: Send request to hub
     */
    private function sendHubRequest(string $hubUrl, array $params): string
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $hubUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'HuntFeed/1.0 (+http://github.com/hosseinhunta/huntfeed)',
            CURLOPT_SSL_VERIFYPEER => false, // For development
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new Exception("cURL error: $error");
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception("Hub returned HTTP $httpCode");
        }

        return $response ?: '';
    }

    /**
     * Private helper: Verify request signature
     */
    private function verifySignature(string $body, array $headers): void
    {
        $signature = $headers['X-Hub-Signature'] ?? null;

        if (!$signature) {
            // Signature not provided, skip verification
            return;
        }

        // Find the corresponding subscription
        $secret = null;
        foreach ($this->subscriptions as $sub) {
            // In real implementation, would need to match based on request data
            $secret = $sub['secret'];
            break;
        }

        if (!$secret) {
            throw new Exception('No secret found for signature verification');
        }

        // Verify signature format: sha1=<signature>
        if (strpos($signature, '=') === false) {
            throw new Exception('Invalid signature format');
        }

        [$algo, $hash] = explode('=', $signature);

        if ($algo !== 'sha1') {
            throw new Exception("Unsupported signature algorithm: $algo");
        }

        $expectedHash = hash_hmac('sha1', $body, $secret);

        if (!hash_equals($hash, $expectedHash)) {
            throw new Exception('Signature verification failed');
        }
    }

    /**
     * Private helper: Parse notification content
     */
    private function parseNotificationContent(string $body): array
    {
        try {
            // Feed content should be in feed format (RSS/Atom)
            // Pass to parser to extract items
            $items = [];

            $xml = simplexml_load_string($body);
            if (!$xml) {
                return [];
            }

            // Extract items based on feed type
            if (isset($xml->item)) {
                // RSS feed
                foreach ($xml->item as $item) {
                    $items[] = [
                        'title' => (string)($item->title ?? ''),
                        'link' => (string)($item->link ?? ''),
                        'pubDate' => (string)($item->pubDate ?? ''),
                        'description' => (string)($item->description ?? ''),
                    ];
                }
            } elseif (isset($xml->entry)) {
                // Atom feed
                foreach ($xml->entry as $entry) {
                    $items[] = [
                        'title' => (string)($entry->title ?? ''),
                        'link' => (string)($entry->link['href'] ?? ''),
                        'published' => (string)($entry->published ?? ''),
                        'summary' => (string)($entry->summary ?? ''),
                    ];
                }
            }

            return $items;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Set callback URL
     */
    public function setCallbackUrl(string $url): self
    {
        $this->callbackUrl = $url;
        return $this;
    }

    /**
     * Set lease seconds
     */
    public function setLeaseSeconds(int $seconds): self
    {
        $this->leaseSeconds = $seconds;
        return $this;
    }

    /**
     * Get subscription count
     */
    public function getSubscriptionCount(): int
    {
        return count($this->subscriptions);
    }

    /**
     * Get verified subscription count
     */
    public function getVerifiedCount(): int
    {
        return count(array_filter(
            $this->subscriptions,
            fn($sub) => $sub['verified']
        ));
    }
}
