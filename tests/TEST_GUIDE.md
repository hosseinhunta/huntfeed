# HuntFeed Test Guide

## 📋 Overview

HuntFeed includes a comprehensive test suite that validates all major components of the feed management system. The test suite includes **10 major test functions** covering functionality, performance, and edge cases.

---

## 🚀 Quick Start

### Run All Tests
```bash
php tests/QuickStartTest.php
```

### Expected Output
You should see a colorful, tree-structured output with:
- ✅ Green checkmarks for passed tests
- ❌ Red X marks for failed tests
- 📊 Summary report with statistics

---

## 📋 Test Suite Details

### Test 1: Auto Detect Parser
**What it tests:** Feed format auto-detection
- ✅ Detects RSS 2.0 feeds
- ✅ Detects Atom feeds
- ✅ Parses item counts correctly
- ✅ Extracts feed titles

**Related Code:**
```php
$fetcher = new FeedFetcher();
$fetcher->setVerifySSL(false);
$feed = $fetcher->fetch('https://example.com/feed');
```

---

### Test 2: SSL Certificate Handling
**What it tests:** SSL verification flexibility
- ✅ Creates FeedFetcher instances
- ✅ Disables SSL for development
- ✅ Supports production CA bundles

**Development Mode:**
```php
$fetcher->setVerifySSL(false);
```

**Production Mode:**
```php
$fetcher->setCaBundlePath('/path/to/cacert.pem');
```

---

### Test 3: Feed Management & Registration
**What it tests:** Feed registration and management
- ✅ Registers feeds with categories
- ✅ Tracks feed statistics
- ✅ Maintains category relationships
- ✅ Manages multiple feeds

**Code Example:**
```php
$manager = new FeedManager();
$manager->registerFeed('tech_news', 'https://example.com/feed', [
    'category' => 'Technology',
    'interval' => 600,
]);
```

---

### Test 4: Category Filtering with Partial Matching
**What it tests:** Smart category filtering
- ✅ Exact category matching
- ✅ Partial/fuzzy matching
- ✅ Multiple categories per collection
- ✅ Collection statistics

**Code Example:**
```php
$collection = new FeedCollection('Main');
$collection->addFeed('tech_feed', $feed, 'Technology');

// Get items by exact category
$items = $collection->getItemsByCategory('Technology');

// Get items by partial match
$items = $collection->getItemsByCategory('Tech');
```

---

### Test 5: Advanced Search (Multiple Fields)
**What it tests:** Full-text search across item fields
- ✅ Searches in titles
- ✅ Searches in content
- ✅ Searches in categories
- ✅ Searches in links
- ✅ Case-insensitive matching

**Code Example:**
```php
// Search across all fields
$results = $collection->searchItems('PHP');

// Returns items containing 'PHP' in:
// - title, content, category, or link
```

---

### Test 6: Item Fingerprinting & Duplicate Detection
**What it tests:** Duplicate detection mechanisms
- ✅ Default fingerprinting (ID + Link)
- ✅ Content-based fingerprinting
- ✅ Item equality checking
- ✅ Similarity detection
- ✅ Extra fields support

**Code Example:**
```php
$item1 = new FeedItem(
    id: '1',
    title: 'Article',
    link: 'https://example.com/1',
    // ... other fields
);

// Check if items are identical
if ($item1->equals($item2)) {
    // Handle duplicate
}

// Check if items are similar
if ($item1->isSimilar($item2)) {
    // Handle similar content
}
```

---

### Test 7: Multi-Format Export System
**What it tests:** Exporting to multiple formats
- ✅ JSON export
- ✅ RSS 2.0 export
- ✅ Atom 1.0 export
- ✅ CSV export
- ✅ HTML export
- ✅ Plain text export

**Code Example:**
```php
$feed = new Feed('https://example.com/feed', 'My Feed');
$feed->addItem($item);

// Export to different formats
$json = FeedExporter::toJson($feed);
$rss = FeedExporter::toRss($feed);
$atom = FeedExporter::toAtom($feed);
$csv = FeedExporter::toCsv($feed);
$html = FeedExporter::toHtml($feed);
$text = FeedExporter::toText($feed);
```

---

### Test 8: Event Handling & Subscription
**What it tests:** Observer pattern implementation
- ✅ Event subscription
- ✅ Feed registration events
- ✅ Feed removal events
- ✅ New item detection events

**Code Example:**
```php
$manager = new FeedManager();

// Subscribe to events
$manager->on('feed:registered', function($data) {
    echo "Feed registered: " . $data['feedId'];
});

$manager->on('item:new', function($data) {
    echo "New item detected: " . $data['itemId'];
});

$manager->on('feed:removed', function($data) {
    echo "Feed removed: " . $data['feedId'];
});
```

---

### Test 9: FeedCollection Management
**What it tests:** Collection-level operations
- ✅ Adding feeds to collections
- ✅ Retrieving all items
- ✅ Category filtering
- ✅ Collection statistics

**Code Example:**
```php
$collection = new FeedCollection('Main');

// Add multiple feeds
$collection->addFeed('tech', $techFeed, 'Technology');
$collection->addFeed('news', $newsFeed, 'News');

// Get all items
$allItems = $collection->getAllItems();

// Get statistics
$stats = $collection->getStats();
echo "Total feeds: " . $stats['total_feeds'];
echo "Total items: " . $stats['total_items'];
```

---

### Test 10: Error Handling & Edge Cases
**What it tests:** Robustness and error handling
- ✅ Invalid URL handling
- ✅ Empty collection searches
- ✅ Null/empty field handling
- ✅ Exception catching
- ✅ Graceful degradation

**Code Example:**
```php
try {
    $feed = $fetcher->fetch('https://invalid.url');
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

---

## 📊 Understanding the Output

### Test Header
```
█ Test 1: Auto Detect Parser
```
Blue section header indicating test starting

### Successful Operations
```
  ├─ Fetching: https://sahebkhabar.ir/rss
  │  ✓ Feed loaded: Feed Title
  │  ├─ Items: 30
```
Tree structure showing:
- `├─` Branch point
- `│` Continuation line
- `✓` Success indicator
- Information with proper indentation

### Failed Operations
```
  │  ✗ Error: Connection refused
```
Red X for failures with error message

### Summary Report
```
OVERALL RESULTS:
  Total Tests: 10
  Passed: 10
  Failed: 0
  Success Rate: 100%
  Total Time: 2.300 seconds

DETAILED RESULTS:
  ├─ Auto Detect Parser
  │  ├─ Status: ✓ PASSED
  │  └─ Time: 1.755s
```

---

## ⚙️ Configuration & Setup

### Development Mode (Default)
```php
$fetcher = new FeedFetcher();
$fetcher->setVerifySSL(false);  // Disable SSL verification
```

### Production Mode
```php
$fetcher = new FeedFetcher();
$fetcher->setCaBundlePath('/etc/ssl/certs/ca-bundle.crt');  // Use CA bundle
```

### Customizing Tests

You can modify `tests/QuickStartTest.php` to:
- Use different feed URLs
- Test with different categories
- Add custom assertions
- Modify test data

---

## 🐛 Troubleshooting

### SSL Certificate Errors
**Problem:** "SSL certificate problem: unable to get local issuer certificate"

**Solution:**
```php
$fetcher->setVerifySSL(false);  // For development
```

### Connection Timeout
**Problem:** Feed fetch takes too long or times out

**Solution:**
- Check internet connection
- Verify feed URL is accessible
- Try different feeds
- Check firewall settings

### Empty Results
**Problem:** Search or filter returns no results

**Solution:**
```php
// Verify feed has items
echo $feed->itemsCount();

// Verify search terms match
$items = $collection->searchItems('your-search-term');
echo count($items);
```

### Extra Fields Not Found
**Problem:** `getExtra()` returns null

**Solution:**
```php
// Always use default value
$author = $item->getExtra('author', 'Unknown');

// Or check if it exists
if ($item->getExtra('author')) {
    // Use author
}
```

---

## 📈 Performance Notes

### Typical Execution Times
- Network operations (fetch): 1.5-2 seconds
- Parser/logic operations: <1 millisecond
- Export operations: <5 milliseconds

### Optimization Tips
1. **Batch Operations:** Process multiple feeds simultaneously
2. **Caching:** Cache parsed feeds for repeated access
3. **Async Fetching:** Use background workers for feed updates
4. **Database:** Store items in database instead of memory

---

## ✅ Success Indicators

A successful test run shows:
- ✅ All 10 tests pass
- ✅ No PHP warnings or errors
- ✅ Colored output with proper formatting
- ✅ Summary shows 100% success rate
- ✅ Execution time < 5 seconds

---

## 📚 Additional Resources

### Example Files
- [RealWorldExample.php](../examples/RealWorldExample.php) - Production-like usage
- [UpdateSimulationExample.php](../examples/UpdateSimulationExample.php) - Update detection demo
- [FeedManagementExample.php](../examples/FeedManagementExample.php) - Feature showcase

### Documentation
- [README.md](../README.md) - Full documentation
- [README_FA.md](../README_FA.md) - Persian documentation
- [ARCHITECTURE.md](../ARCHITECTURE.md) - System architecture

### Test Results
- [TEST_REPORT.md](./TEST_REPORT.md) - Detailed test results

---

## 💡 Best Practices

1. **Always disable SSL in development:**
   ```php
   $fetcher->setVerifySSL(false);
   ```

2. **Handle exceptions gracefully:**
   ```php
   try {
       $feed = $fetcher->fetch($url);
   } catch (\Exception $e) {
       log_error($e->getMessage());
   }
   ```

3. **Use categories for organization:**
   ```php
   $manager->registerFeed('tech1', $url, ['category' => 'Technology']);
   ```

4. **Subscribe to events for notifications:**
   ```php
   $manager->on('item:new', function($data) {
       notify_user('New item: ' . $data['title']);
   });
   ```

5. **Search across multiple collections:**
   ```php
   $results = $collection->searchItems('your-query');
   foreach ($results as $item) {
       // Process each result
   }
   ```

---

*Last Updated: 2025-12-13*
*Test Suite Version: 2.0 (Comprehensive)*
*PHP Requirement: 8.0+*
