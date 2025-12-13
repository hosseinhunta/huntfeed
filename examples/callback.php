<?php
/**
 * WebSub Callback Endpoint Template
 * 
 * This file handles incoming WebSub notifications from hubs.
 * 
 * Setup Instructions:
 * 1. Save this as callback.php in your web root
 * 2. Configure your WebSubManager with the URL: https://your-domain.com/callback.php
 * 3. Ensure HTTPS is enabled (WebSub hubs require HTTPS)
 * 4. Make sure PHP can write to a temp directory for logging (optional)
 * 
 * How It Works:
 * - Hub sends GET request to verify subscription (challenge)
 * - Hub sends POST request with feed updates
 * - This endpoint verifies signatures and processes updates
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Hosseinhunta\Huntfeed\Hub\FeedManager;
use Hosseinhunta\Huntfeed\WebSub\WebSubManager;

// ============================================================================
// CONFIGURATION
// ============================================================================

// Your domain where this callback is hosted (must be HTTPS in production)
define('CALLBACK_URL', 'https://your-domain.com/callback.php');

// Log file path (optional - for debugging)
define('LOG_FILE', __DIR__ . '/../logs/websub.log');

// Enable logging (set to false in production for performance)
define('ENABLE_LOGGING', true);

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Log a message to file
 */
function log_message(string $level, string $message, array $context = []): void
{
    if (!ENABLE_LOGGING) {
        return;
    }
    
    if (!is_dir(dirname(LOG_FILE))) {
        mkdir(dirname(LOG_FILE), 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
    $logLine = "[{$timestamp}] {$level}: {$message}{$contextStr}\n";
    
    file_put_contents(LOG_FILE, $logLine, FILE_APPEND);
}

/**
 * Send response with appropriate headers
 */
function send_response(int $statusCode, string $body = '', string $contentType = 'text/plain'): void
{
    http_response_code($statusCode);
    header("Content-Type: {$contentType}");
    echo $body;
    exit;
}

/**
 * Handle errors gracefully
 */
function handle_error(\Throwable $exception): void
{
    log_message('ERROR', $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString(),
    ]);
    
    // Return 200 to hub (don't want it to retry)
    // But log the error for debugging
    send_response(200, '');
}

// ============================================================================
// MAIN HANDLER
// ============================================================================

try {
    // Get request method and data
    $method = $_SERVER['REQUEST_METHOD'];
    $query = $_GET;
    $body = file_get_contents('php://input');
    $headers = getallheaders();
    
    log_message('INFO', "Received {$method} request", [
        'path' => $_SERVER['REQUEST_URI'],
        'query' => $query,
        'content_type' => $headers['Content-Type'] ?? 'unknown',
        'content_length' => strlen($body),
    ]);
    
    // ========================================================================
    // Option 1: Using WebSubManager (Recommended)
    // ========================================================================
    
    // Initialize FeedManager and WebSubManager
    $feedManager = new FeedManager();
    $webSubManager = new WebSubManager($feedManager, CALLBACK_URL);
    
    // Get the handler
    $handler = $webSubManager->getHandler();
    
    // Process the request
    $result = $handler->processRequest($method, $query, $body, $headers);
    
    // Send response
    $statusCode = $result['status'] ?? 200;
    $responseBody = $result['body'] ?? '';
    
    log_message('INFO', "Sending response", [
        'status' => $statusCode,
        'body_length' => strlen($responseBody),
    ]);
    
    send_response($statusCode, $responseBody);
    
} catch (\Throwable $e) {
    handle_error($e);
}

?>
