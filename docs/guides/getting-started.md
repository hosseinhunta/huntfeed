# Getting Started with HuntFeed

## Introduction

HuntFeed is a PHP library designed for real-time feed management. It supports RSS 2.0, Atom 1.0, JSON Feed, RDF, and GeoRSS formats, with built-in WebSub (PubSubHubbub) support for push notifications.

## Installation

### Via Composer (Recommended)

```bash
composer require hosseinhunta/huntfeed
Manual Installation
bash
git clone https://github.com/hosseinhunta/huntfeed.git
cd huntfeed
composer install
Basic Usage
1. Initialize FeedManager
php
<?php
require 'vendor/autoload.php';

use Hosseinhunta\Huntfeed\Hub\FeedManager;

$manager = new FeedManager();
2. Register Feeds
php
// Register a single feed
$manager->registerFeed('hackernews', 'https://news.ycombinator.com/rss', [
    'category' => 'Technology',
    'interval' => 300 // Check every 5 minutes
]);

// Register multiple feeds
$manager->registerFeeds([
    'tech' => [
        'url' => 'https://techcrunch.com/feed/',
        'category' => 'Technology'
    ],
    'news' => [
        'url' => 'https://bbc.com/news/world/rss.xml',
        'category' => 'News'
    ]
]);
3. Check for Updates
php
// Check all registered feeds for new items
$updates = $manager->checkUpdates();

foreach ($updates as $feedId => $items) {
    echo "Found " . count($items) . " new items in $feedId\n";
}
4. Handle New Items with Events
php
// Attach event handler for new items
$manager->on('item:new', function($data) {
    $item = $data['item'];
    $feedId = $data['feedId'];
    
    echo "New item: {$item->title}\n";
    echo "From feed: $feedId\n";
    echo "Link: {$item->link}\n";
    
    // You can send notifications, save to database, etc.
});
5. Export Feeds
php
// Export as JSON (perfect for APIs)
$json = $manager->export('json');

// Export as RSS
$rss = $manager->export('rss');

// Export as Atom
$atom = $manager->export('atom');

// Export specific feed only
$techFeedJson = $manager->export('json', 'tech');
Configuration Options
Feed Registration Options
php
$manager->registerFeed('example', 'https://example.com/feed.xml', [
    'category' => 'Example',      // Category for organization
    'interval' => 600,            // Polling interval in seconds
    'enabled' => true,            // Enable/disable the feed
    'max_items' => 50,            // Maximum items to keep
    'keep_history' => true,       // Keep old items
    'user_agent' => 'MyApp/1.0',  // Custom User-Agent
    'headers' => [                // Custom HTTP headers
        'Authorization' => 'Bearer token'
    ]
]);
SSL Configuration
php
$fetcher = $manager->getFetcher();

// For development (disable SSL verification)
$fetcher->setVerifySSL(false);

// For production (set custom CA bundle)
$fetcher->setCaBundlePath('/path/to/cacert.pem');
Next Steps
Learn about WebSub Implementation for real-time updates

Explore API Reference for detailed documentation

Check Examples for practical use cases

Read Production Deployment for deployment best practices

Common Tasks
Search Across Feeds
php
$results = $manager->searchItems('PHP');
$techItems = $manager->getItemsByCategory('Technology');
$latestItems = $manager->getLatestItems(10);
Get Statistics
php
$stats = $manager->getStats();
echo "Total feeds: " . $stats['total_feeds'] . "\n";
echo "Total items: " . $stats['total_items'] . "\n";
echo "Categories: " . implode(', ', $stats['categories']) . "\n";
Force Update
php
// Force update a specific feed
$manager->forceUpdateFeed('hackernews');

// Force update all feeds
$manager->forceUpdateAll();
Troubleshooting
If you encounter issues:

Feed not fetching: Check URL accessibility and SSL configuration

Memory issues: Adjust max_items in feed options

Performance problems: Consider enabling WebSub for real-time updates

For more help, visit the Troubleshooting section.

text

## 5. `docs/api/feed-manager.md`

```markdown
# FeedManager API Reference

## Overview

The `FeedManager` class is the central hub for managing feeds in HuntFeed. It provides methods for registering feeds, checking updates, handling events, and exporting data.

## Class Signature

```php
namespace Hosseinhunta\Huntfeed\Hub;

class FeedManager
{
    public function __construct();
    
    // Feed registration
    public function registerFeed(string $feedId, string $url, array $options = []);
    public function registerFeeds(array $feeds);
    public function unregisterFeed(string $feedId);
    
    // Update management
    public function checkUpdates(): array;
    public function forceUpdateFeed(string $feedId): array;
    public function forceUpdateAll(): array;
    
    // Data retrieval
    public function getAllItems(): array;
    public function getLatestItems(int $limit = 10): array;
    public function getItemsByCategory(string $category): array;
    public function searchItems(string $query, array $options = []): array;
    
    // Export
    public function export(string $format, ?string $feedId = null): string;
    public function exportMetadata(): array;
    
    // Events
    public function on(string $event, callable $handler): void;
    public function off(string $event, callable $handler): void;
    
    // Configuration
    public function setConfig(string $key, $value): void;
    public function getConfig(string $key);
    public function getFetcher(): FeedFetcher;
    public function getStats(): array;
    
    // Feed information
    public function getFeed(string $feedId): ?Feed;
    public function getFeeds(): array;
    public function hasFeed(string $feedId): bool;
}
Methods
registerFeed()
Register a single feed.

php
public function registerFeed(string $feedId, string $url, array $options = []): void
Parameters:

$feedId (string): Unique identifier for the feed

$url (string): Feed URL

$options (array): Optional configuration (see below)

Options:

php
$options = [
    'category' => 'Technology',      // Feed category
    'interval' => 300,               // Polling interval in seconds
    'enabled' => true,               // Enable/disable feed
    'max_items' => 100,              // Maximum items to keep
    'keep_history' => true,          // Keep old items
    'user_agent' => 'Custom Agent',  // Custom User-Agent
    'headers' => [],                 // Custom HTTP headers
    'verify_ssl' => true,            // Verify SSL certificates
    'timeout' => 30,                 // Request timeout in seconds
];
Example:

php
$manager->registerFeed('hackernews', 'https://news.ycombinator.com/rss', [
    'category' => 'Technology',
    'interval' => 600,
    'max_items' => 50
]);
registerFeeds()
Register multiple feeds at once.

php
public function registerFeeds(array $feeds): void
Parameters:

$feeds (array): Associative array of feed configurations

Example:

php
$manager->registerFeeds([
    'techcrunch' => [
        'url' => 'https://techcrunch.com/feed/',
        'category' => 'Technology',
        'interval' => 1800
    ],
    'bbc' => [
        'url' => 'https://bbc.com/news/world/rss.xml',
        'category' => 'News',
        'interval' => 900
    ]
]);
checkUpdates()
Check all registered feeds for updates.

php
public function checkUpdates(): array
Returns:

Array of new items grouped by feed ID

Example:

php
$updates = $manager->checkUpdates();

foreach ($updates as $feedId => $items) {
    echo "Found " . count($items) . " new items in $feedId\n";
    foreach ($items as $item) {
        echo "- {$item->title}\n";
    }
}
export()
Export feeds to various formats.

php
public function export(string $format, ?string $feedId = null): string
Parameters:

$format (string): Export format (json, rss, atom, jsonfeed, csv, html, text)

$feedId (string|null): Specific feed ID or null for all feeds

Example:

php
// Export all feeds as JSON
$json = $manager->export('json');

// Export specific feed as RSS
$rss = $manager->export('rss', 'hackernews');

// Export as CSV for Excel
$csv = $manager->export('csv');
on()
Attach an event handler.

php
public function on(string $event, callable $handler): void
Supported Events:

feed:registered - Triggered when a feed is registered

feed:updated - Triggered when a feed is checked for updates

item:new - Triggered when a new item is found

item:duplicate - Triggered when a duplicate item is detected

error - Triggered when an error occurs

Example:

php
$manager->on('item:new', function($data) {
    $item = $data['item'];
    $feedId = $data['feedId'];
    $category = $data['category'] ?? 'Unknown';
    
    // Send notification
    sendNotification("New $category item: {$item->title}");
});
Event Data Structure:

php
$data = [
    'feedId' => 'hackernews',      // Feed identifier
    'feed' => Feed object,         // Feed object
    'item' => FeedItem object,     // Item object (for item events)
    'category' => 'Technology',    // Feed category
    'timestamp' => 1234567890,     // Event timestamp
    'newItemsCount' => 5,          // Number of new items (for feed:updated)
    'error' => Exception object    // Error object (for error event)
];
searchItems()
Search across all feed items.

php
public function searchItems(string $query, array $options = []): array
Parameters:

$query (string): Search query

$options (array): Search options

Options:

php
$options = [
    'fields' => ['title', 'content'], // Fields to search
    'limit' => 20,                    // Maximum results
    'offset' => 0,                    // Pagination offset
    'category' => 'Technology',       // Filter by category
    'date_from' => '2024-01-01',      // Filter by date range
    'date_to' => '2024-12-31',
    'sort' => 'newest',               // Sort order (newest|oldest|relevance)
];
Example:

php
// Search for PHP articles in Technology category
$results = $manager->searchItems('PHP', [
    'category' => 'Technology',
    'limit' => 10,
    'sort' => 'newest'
]);

foreach ($results as $item) {
    echo "{$item->title} ({$item->publishedAt->format('Y-m-d')})\n";
}
getStats()
Get statistics about feeds and items.

php
public function getStats(): array
Returns:

php
[
    'total_feeds' => 10,
    'total_items' => 1500,
    'categories' => ['Technology', 'News', 'Sports'],
    'categories_count' => [
        'Technology' => 5,
        'News' => 3,
        'Sports' => 2
    ],
    'last_update' => '2024-01-15 14:30:00',
    'new_items_today' => 25,
    'most_active_feed' => 'hackernews'
]
Properties
FeedManager::$config
Default configuration values:

php
[
    'poll_interval' => 300,          // Default polling interval
    'max_items_per_feed' => 100,     // Default max items
    'keep_history' => true,          // Keep history by default
    'user_agent' => 'HuntFeed/1.0',  // Default User-Agent
    'timeout' => 30,                 // Request timeout
    'verify_ssl' => true,            // Verify SSL certificates
    'cache_enabled' => true,         // Enable caching
    'cache_ttl' => 300,              // Cache TTL in seconds
]
Examples
Complete Example
php
<?php
require 'vendor/autoload.php';

use Hosseinhunta\Huntfeed\Hub\FeedManager;

// Initialize
$manager = new FeedManager();

// Configure
$manager->setConfig('poll_interval', 600);
$manager->setConfig('max_items_per_feed', 50);

// Register feeds
$manager->registerFeeds([
    'tech' => [
        'url' => 'https://techcrunch.com/feed/',
        'category' => 'Technology',
        'interval' => 1800
    ]
]);

// Handle new items
$manager->on('item:new', function($data) {
    $item = $data['item'];
    logToDatabase($item);
    sendToTelegram($item);
});

// Check for updates
$updates = $manager->checkUpdates();

// Export data
$json = $manager->export('json');
file_put_contents('feeds.json', $json);

// Get statistics
$stats = $manager->getStats();
print_r($stats);
Error Handling
FeedManager throws exceptions for critical errors:

php
try {
    $manager->registerFeed('invalid', 'not-a-url');
} catch (InvalidArgumentException $e) {
    echo "Invalid URL: " . $e->getMessage();
}

try {
    $updates = $manager->checkUpdates();
} catch (RuntimeException $e) {
    echo "Error checking updates: " . $e->getMessage();
}
Best Practices
Use meaningful feed IDs: Use descriptive IDs like hackernews_tech instead of generic names

Set appropriate intervals: Longer intervals for stable feeds, shorter for news feeds

Handle events efficiently: Use event handlers for notifications and logging

Monitor statistics: Regularly check stats to identify issues

Implement caching: Cache exported data for better performance

See Also
WebSubManager API

FeedFetcher API

Troubleshooting Guide
