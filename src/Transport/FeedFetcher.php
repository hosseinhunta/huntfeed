<?php
namespace Hosseinhunta\Huntfeed\Transport;

use Hosseinhunta\Huntfeed\Feed\Feed;
use Hosseinhunta\Huntfeed\Parser\AutoDetectParser;
use RuntimeException;

final class FeedFetcher
{
    private AutoDetectParser $parser;
    private int $timeout = 30;
    private int $maxRedirects = 5;
    private array $headers = [];
    private bool $verifySsl = true;
    private ?string $caBundlePath = null;

    public function __construct(AutoDetectParser $parser = null)
    {
        $this->parser = $parser ?? AutoDetectParser::createDefault();
        
        // Auto-detect CA bundle path for Windows/Linux/macOS
        $this->detectCaBundlePath();
    }

    /**
     * Auto-detect CA bundle path
     */
    private function detectCaBundlePath(): void
    {
        $commonPaths = [
            // Windows
            'C:\\php\\extras\\ssl\\cacert.pem',
            getenv('COMPOSER_HOME') . '/vendor/cacert.pem',
            // Linux
            '/etc/ssl/certs/ca-certificates.crt',
            '/etc/pki/tls/certs/ca-bundle.crt',
            // macOS
            '/usr/local/etc/openssl/cert.pem',
            '/opt/local/etc/openssl/cert.pem',
        ];

        foreach ($commonPaths as $path) {
            if (!empty($path) && file_exists($path)) {
                $this->caBundlePath = $path;
                return;
            }
        }
    }

    /**
     * Set SSL verification (for development only!)
     */
    public function setVerifySSL(bool $verify): self
    {
        $this->verifySsl = $verify;
        return $this;
    }

    /**
     * Set custom CA bundle path
     */
    public function setCaBundlePath(string $path): self
    {
        if (!file_exists($path)) {
            throw new RuntimeException("CA bundle not found: {$path}");
        }
        $this->caBundlePath = $path;
        return $this;
    }

    /**
     * Set HTTP request timeout in seconds
     */
    public function setTimeout(int $seconds): self
    {
        $this->timeout = $seconds;
        return $this;
    }

    /**
     * Set maximum redirects to follow
     */
    public function setMaxRedirects(int $max): self
    {
        $this->maxRedirects = $max;
        return $this;
    }

    /**
     * Add custom HTTP header
     */
    public function addHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Set User-Agent
     */
    public function setUserAgent(string $userAgent): self
    {
        return $this->addHeader('User-Agent', $userAgent);
    }

    /**
     * Fetch and parse a feed from URL
     */
    public function fetch(string $url): Feed
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException("Invalid URL: {$url}");
        }

        try {
            $content = $this->fetchContent($url);
            $feed = $this->parser->parse($content, $url);
            
            // Store original content for WebSub hub detection
            $feed->setOriginalContent($content);
            
            return $feed;
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to fetch feed from {$url}: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Fetch raw content from URL using cURL
     */
    private function fetchContent(string $url): string
    {
        $ch = curl_init($url);

        // Basic options
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => $this->maxRedirects,
            CURLOPT_ENCODING => '', // Accept all encodings
        ];

        // SSL verification options
        if ($this->verifySsl) {
            $options[CURLOPT_SSL_VERIFYPEER] = true;
            $options[CURLOPT_SSL_VERIFYHOST] = 2;
            
            // Add CA bundle if found
            if (!empty($this->caBundlePath)) {
                $options[CURLOPT_CAINFO] = $this->caBundlePath;
            }
        } else {
            // For development only - disables SSL verification
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            $options[CURLOPT_SSL_VERIFYHOST] = 0;
        }

        curl_setopt_array($ch, $options);

        // Add custom headers
        if (!empty($this->headers)) {
            $headerLines = [];
            foreach ($this->headers as $key => $value) {
                $headerLines[] = "{$key}: {$value}";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerLines);
        }

        // Default User-Agent if not set
        if (!isset($this->headers['User-Agent'])) {
            curl_setopt($ch, CURLOPT_USERAGENT, 'Huntfeed/1.0 (PHP)');
        }

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new RuntimeException("cURL Error: {$curlError}");
        }

        if ($httpCode >= 400) {
            throw new RuntimeException("HTTP Error {$httpCode}");
        }

        if (empty($content)) {
            throw new RuntimeException("Empty response from server");
        }

        return $content;
    }

    /**
     * Fetch multiple feeds and merge them
     * @param array<string, string|array> $urls Associative array: ['feed_id' => 'url' or ['url' => 'url', 'category' => 'cat']]
     * @return array<string, Feed>
     */
    public function fetchMultiple(array $urls): array
    {
        $feeds = [];

        foreach ($urls as $id => $urlInfo) {
            try {
                if (is_string($urlInfo)) {
                    $url = $urlInfo;
                } elseif (is_array($urlInfo) && isset($urlInfo['url'])) {
                    $url = $urlInfo['url'];
                } else {
                    continue;
                }

                $feeds[$id] = $this->fetch($url);
            } catch (RuntimeException $e) {
                // Log error but continue with other feeds
                error_log("Feed fetch error for {$id}: " . $e->getMessage());
                continue;
            }
        }

        return $feeds;
    }

    /**
     * Check if a feed has new items compared to previous fetch
     */
    public function hasNewItems(Feed $oldFeed, Feed $newFeed): bool
    {
        $oldFingerprints = array_map(
            fn($item) => $item->fingerprint(),
            $oldFeed->items()
        );

        foreach ($newFeed->items() as $newItem) {
            if (!in_array($newItem->fingerprint(), $oldFingerprints)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get only new items from a feed compared to previous version
     * @return Feed containing only new items
     */
    public function getNewItems(Feed $oldFeed, Feed $newFeed): Feed
    {
        $oldFingerprints = array_map(
            fn($item) => $item->fingerprint(),
            $oldFeed->items()
        );

        $newItemsFeed = new Feed($newFeed->url, $newFeed->title);

        foreach ($newFeed->items() as $newItem) {
            if (!in_array($newItem->fingerprint(), $oldFingerprints)) {
                $newItemsFeed->addItem($newItem);
            }
        }

        return $newItemsFeed;
    }
}
