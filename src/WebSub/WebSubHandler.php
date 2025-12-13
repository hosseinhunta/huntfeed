<?php

namespace Hosseinhunta\Huntfeed\WebSub;

use Closure;

/**
 * WebSub HTTP Handler
 * 
 * Receives and processes WebSub callbacks:
 * - GET requests for verification challenges
 * - POST requests for push notifications
 */
class WebSubHandler
{
    private WebSubSubscriber $subscriber;
    private ?Closure $onNotification = null;

    public function __construct(WebSubSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * Set callback when notification is received
     */
    public function onNotification(callable $callback): self
    {
        $this->onNotification = $callback;
        return $this;
    }

    /**
     * Process incoming HTTP request
     * 
     * Handles both GET (verification) and POST (notification) requests
     * 
     * @param string $method HTTP method (GET/POST)
     * @param array $query Query parameters (for GET)
     * @param string $body Request body (for POST)
     * @param array $headers Request headers
     * @return array Processing result
     */
    public function processRequest(string $method, array $query, string $body, array $headers): array
    {
        if ($method === 'GET') {
            return $this->handleVerification($query);
        } elseif ($method === 'POST') {
            return $this->handleNotification($body, $headers);
        }

        return [
            'success' => false,
            'error' => 'Invalid HTTP method',
        ];
    }

    /**
     * Handle verification challenge from hub
     * 
     * @param array $params GET parameters
     * @return array Challenge response
     */
    private function handleVerification(array $params): array
    {
        $result = $this->subscriber->verifyChallenge($params);

        if ($result['success']) {
            return [
                'body' => $result['challenge'],
                'status' => 200,
                'message' => $result['message'],
            ];
        }

        return [
            'body' => 'Verification failed',
            'status' => 403,
            'error' => $result['error'],
        ];
    }

    /**
     * Handle push notification from hub
     * 
     * @param string $body Request body
     * @param array $headers Request headers
     * @return array Notification processing result
     */
    private function handleNotification(string $body, array $headers): array
    {
        $result = $this->subscriber->handleNotification($body, $headers);

        if ($result['success']) {
            // Call notification callback if set
            if (isset($this->onNotification)) {
                call_user_func($this->onNotification, $result);
            }

            return [
                'status' => 204, // No Content
                'body' => '',
                'message' => 'Notification accepted',
                'items' => $result['items'],
            ];
        }

        return [
            'status' => 400,
            'body' => 'Invalid notification',
            'error' => $result['error'],
        ];
    }

    /**
     * Generate integration code for your application
     * 
     * @return string PHP code to integrate handler
     */
    public static function generateIntegrationCode(): string
    {
        return <<<'PHP'
<?php
// Add this to your public HTTP endpoint (e.g., /websub-callback.php)

// Get request data
$method = $_SERVER['REQUEST_METHOD'];
$query = $_GET;
$body = file_get_contents('php://input');

// Get headers
$headers = [];
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0) {
        $headerName = str_replace('HTTP_', '', $key);
        $headerName = str_replace('_', '-', $headerName);
        $headers[$headerName] = $value;
    }
}

// Create handler and process
$subscriber = new WebSubSubscriber($fetcher, 'http://your-domain.com/websub-callback.php');

$handler = new WebSubHandler($subscriber);
$handler->onNotification(function($notification) {
    // Process incoming notification
    // $notification contains 'items_count' and 'items'
    
    // Add items to your database or feed manager
    foreach ($notification['items'] as $item) {
        // Save to database or process
    }
});

$result = $handler->processRequest($method, $query, $body, $headers);

// Send response
http_response_code($result['status'] ?? 200);
echo $result['body'] ?? '';
PHP;
    }
}
