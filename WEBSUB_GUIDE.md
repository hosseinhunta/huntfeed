# 🚀 WebSub Integration Guide

## WebSub (PubSubHubbub) - Push-Based Feed Updates

WebSub is a modern protocol for real-time push notifications of feed updates. Instead of continuously polling feeds for updates, feeds can push notifications to your application when new content is available.

---

## 📋 Quick Overview

### What is WebSub?
WebSub (previously known as PubSubHubbub) is an open standard for:
- **Push-based distribution** of content changes
- **Hub-mediated** publication workflows
- **Real-time** notifications instead of polling

### Benefits for HuntFeed
✅ **Real-time updates** - Get news immediately when published  
✅ **Reduced bandwidth** - No empty polling requests  
✅ **Lower latency** - Instant notification delivery  
✅ **Better UX** - Users see content faster  
✅ **Scalable** - Hub handles distribution to many subscribers  

---

## 🏗️ Architecture

### Components

```
┌─────────────────────────────────────────────────────┐
│                  WebSub System                       │
├─────────────────────────────────────────────────────┤
│                                                      │
│  WebSubSubscriber                                   │
│  ├─ subscribe()                                     │
│  ├─ unsubscribe()                                   │
│  ├─ verifyChallenge()                              │
│  ├─ handleNotification()                           │
│  └─ detectHubFromFeed()                            │
│                                                      │
│  WebSubManager (Orchestration)                      │
│  ├─ registerFeedWithWebSub()                       │
│  ├─ registerMultipleFeeds()                        │
│  ├─ getSubscriptionStatus()                        │
│  └─ handleWebSubNotification()                     │
│                                                      │
│  WebSubHandler (HTTP Endpoint)                      │
│  ├─ GET (verification challenge)                    │
│  └─ POST (push notification)                        │
│                                                      │
└─────────────────────────────────────────────────────┘
```

### Workflow

```
1. DISCOVERY
   Your App → FeedFetcher.fetch(url)
   Feed contains: <link rel="hub" href="..." />
   
2. SUBSCRIPTION
   Your App → WebSubManager.registerFeedWithWebSub()
   → WebSubSubscriber.subscribe()
   → HTTP POST to Hub
   
3. VERIFICATION
   Hub → GET /your-callback?hub.challenge=XXX
   Your App → HTTP 200 with challenge
   Hub marks subscription as verified
   
4. NOTIFICATION
   [Feed is updated]
   Hub → POST /your-callback (with feed content)
   Your App → Process notification
   → Add items to database
   → Update search index
   → Notify users
```

---

## 🔧 Implementation Guide

### 1. Basic Setup

```php
use Hosseinhunta\Huntfeed\Hub\FeedManager;
use Hosseinhunta\Huntfeed\WebSub\WebSubManager;

// Initialize
$feedManager = new FeedManager();
$callbackUrl = 'https://your-domain.com/websub-callback.php';

$webSubManager = new WebSubManager($feedManager, $callbackUrl);

// Configure
$webSubManager
    ->setAutoSubscribe(true)           // Auto-subscribe to hubs
    ->setFallbackToPolling(true);      // Fallback if no hub
```

### 2. Register Feeds

```php
// Single feed
$webSubManager->registerFeedWithWebSub('tech_news', 'https://example.com/feed.xml');

// Multiple feeds
$feeds = [
    'tech' => 'https://example.com/tech-feed.xml',
    'news' => 'https://example.com/news-feed.xml',
    'sports' => 'https://example.com/sports-feed.xml',
];

$results = $webSubManager->registerMultipleFeeds($feeds);

foreach ($results as $feedId => $result) {
    if ($result['has_hub']) {
        echo "✓ WebSub enabled for $feedId\n";
    } else {
        echo "✓ Polling enabled for $feedId\n";
    }
}
```

### 3. HTTP Callback Endpoint

Create `/public/websub-callback.php`:

```php
<?php
// Import classes
use Hosseinhunta\Huntfeed\Hub\FeedManager;
use Hosseinhunta\Huntfeed\WebSub\WebSubManager;
use Hosseinhunta\Huntfeed\WebSub\WebSubHandler;

// Setup
$feedManager = new FeedManager();
$callbackUrl = 'https://your-domain.com/websub-callback.php';
$webSubManager = new WebSubManager($feedManager, $callbackUrl);
$handler = $webSubManager->getHandler();

// Get request data
$method = $_SERVER['REQUEST_METHOD'];
$body = file_get_contents('php://input');
$headers = getallheaders();

// Process request
$result = $handler->processRequest($method, $_GET, $body, $headers);

// Handle verification
if ($method === 'GET') {
    // Hub verification challenge
    http_response_code($result['status'] ?? 200);
    echo $result['challenge'] ?? '';
    exit;
}

// Handle notification
if ($method === 'POST') {
    // Process incoming push notification
    $notification = $webSubManager->handleWebSubNotification(
        $body,
        $headers,
        function($items) {
            // Save items to database
            foreach ($items as $item) {
                // Your database insertion logic
                echo "Processing: " . $item['title'] . "\n";
            }
        }
    );
    
    http_response_code($notification['success'] ? 204 : 400);
    exit;
}
```

### 4. Check Subscription Status

```php
$status = $webSubManager->getSubscriptionStatus();

echo "Total Feeds: " . $status['total_feeds'] . "\n";
echo "WebSub Enabled: " . $status['websub_enabled_feeds'] . "\n";
echo "Verified: " . $status['verified_subscriptions'] . "\n";

// Get WebSub feeds list
$websubFeeds = $webSubManager->getWebSubFeeds();
foreach ($websubFeeds as $feed) {
    echo "Feed: {$feed['feed_id']} → {$feed['hub_url']}\n";
}
```

---

## 🔍 Hub Detection

HuntFeed automatically detects WebSub hubs in feeds by looking for:

### Atom Format
```xml
<?xml version="1.0"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>Example Feed</title>
    <link rel="hub" href="https://hub.example.com/" />
    <link rel="self" href="https://example.com/feed.xml" />
    ...
</feed>
```

### RSS Format
```xml
<?xml version="1.0"?>
<rss version="2.0">
    <channel>
        <title>Example Feed</title>
        <link rel="hub" href="https://hub.example.com/" />
        <link>https://example.com</link>
        ...
    </channel>
</rss>
```

### Detection Code
```php
$hubUrl = WebSubSubscriber::detectHubFromFeed($feedContent);

if ($hubUrl) {
    // Subscribe to this hub
} else {
    // Fallback to polling
}
```

---

## 📊 Subscription Workflow

### 1. Subscribe
```
Your App sends:
POST /hub HTTP/1.1
Host: hub.example.com

hub.callback=https://your-domain.com/websub-callback.php
hub.mode=subscribe
hub.topic=https://example.com/feed.xml
hub.lease_seconds=86400
hub.secret=<secret_key>
```

### 2. Verify Challenge
```
Hub sends (GET request):
https://your-domain.com/websub-callback.php
  ?hub.mode=subscribe
  &hub.topic=https://example.com/feed.xml
  &hub.challenge=<challenge_token>
  &hub.lease_seconds=86400

Your App responds:
HTTP/1.1 200 OK

<challenge_token>
```

### 3. Send Notifications
```
Hub sends (POST request):
POST /websub-callback.php HTTP/1.1
Host: your-domain.com

<?xml version="1.0"?>
<rss version="2.0">
    <channel>
        <item>
            <title>New Article</title>
            <link>...</link>
            ...
        </item>
    </channel>
</rss>

X-Hub-Signature: sha1=<hmac-sha1-signature>
```

---

## 🔐 Security Considerations

### Signature Verification
```php
// HuntFeed automatically verifies signatures
// Calculate HMAC-SHA1 of body with secret:
$expectedSignature = hash_hmac('sha1', $body, $secret);
$requestSignature = $headers['X-Hub-Signature'];

if (!hash_equals($expectedSignature, $requestSignature)) {
    // Reject notification
}
```

### Best Practices
- ✅ Always use HTTPS in production
- ✅ Validate signature for each notification
- ✅ Use strong, randomly generated secrets
- ✅ Store secrets securely (environment variables, not code)
- ✅ Implement rate limiting
- ✅ Log all subscription activities
- ✅ Monitor hub availability

---

## 🎯 Integration Examples

### Example 1: Simple Blog Feed

```php
$webSubManager->registerFeedWithWebSub('blog', 'https://myblog.com/feed.xml', [
    'category' => 'Blog',
    'interval' => 3600, // Polling fallback: 1 hour
]);

// If hub is available: instant notifications
// If no hub: polling every hour
```

### Example 2: News Aggregation

```php
$newsFeeds = [
    'tech' => 'https://techcrunch.com/feed/',
    'bbc' => 'https://bbc.com/news/world/rss.xml',
    'nyt' => 'https://nytimes.com/services/xml/rss/',
];

$results = $webSubManager->registerMultipleFeeds($newsFeeds);

// Automatically discover hubs in each feed
// Subscribe to hubs, fallback to polling
// Receive real-time updates via push
```

### Example 3: Event-Driven Processing

```php
$webSubManager->handleWebSubNotification(
    $body,
    $headers,
    function($items) {
        foreach ($items as $item) {
            // Trigger events
            Event::dispatch('feed:updated', $item);
            
            // Send notifications
            if (shouldNotifyUsers($item)) {
                sendPushNotification($item);
                sendEmailAlert($item);
            }
            
            // Update search index
            updateSearchIndex($item);
        }
    }
);
```

---

## 📈 Performance Considerations

### Polling vs WebSub

| Metric | Polling | WebSub |
|--------|---------|--------|
| Requests (no updates) | ∞ (every poll) | 0 |
| Latency | Minutes | Seconds |
| Bandwidth | High | Very Low |
| Server Load | High | Low |
| Real-time | No | Yes |
| Scaling | Difficult | Easy |

### Optimization Tips
1. **Cache subscriptions** - Store in database to survive restarts
2. **Queue processing** - Use background jobs for notifications
3. **Batch inserts** - Insert items in batches, not individually
4. **Index smartly** - Pre-index common search fields
5. **Monitor health** - Track subscription renewals

---

## 🚨 Troubleshooting

### "No WebSub Hub Found"
- Check feed contains `<link rel="hub" />`
- Verify hub URL is valid and accessible
- Some feeds don't have hubs - fallback to polling works fine

### "Subscription Verification Failed"
- Ensure callback URL is publicly accessible
- Check firewall allows incoming requests
- Verify HTTP method (should be GET for verification)
- Return HTTP 200 with challenge in body

### "Notification Not Received"
- Verify subscription was verified
- Check callback URL in subscription request
- Ensure endpoint returns HTTP 204
- Monitor server logs for incoming requests
- Test with manual POST to callback

### "Signature Verification Failed"
- Confirm secret matches subscription request
- Check HMAC algorithm (should be sha1)
- Verify header format: `sha1=<signature>`
- Ensure hub sends X-Hub-Signature header

---

## 📚 Resources

### Specifications
- [WebSub Specification](https://www.w3.org/TR/websub/)
- [WebSub Hub Implementations](https://pubsubhubbub.github.io/)

### HuntFeed Classes
- `WebSubSubscriber` - Core subscription management
- `WebSubManager` - Orchestration layer
- `WebSubHandler` - HTTP endpoint handling

### Example Files
- `examples/WebSubExample.php` - Complete usage examples
- `tests/QuickStartTest.php` - WebSub tests

---

## ✅ Checklist for Production

- [ ] Endpoint publicly accessible on HTTPS
- [ ] Signature verification enabled
- [ ] Secret stored securely
- [ ] Fallback polling configured
- [ ] Rate limiting implemented
- [ ] Logging configured
- [ ] Monitoring/alerting set up
- [ ] Subscription renewal automated
- [ ] Error handling for hub failures
- [ ] Testing with real hubs completed

---

**Status:** ✅ Ready for Production

HuntFeed's WebSub implementation is production-ready, with automatic hub detection, signature verification, and elegant fallback to polling.
