<?php
/**
 * Cron Handler - Can be triggered by external services like EasyCron
 * This file wraps the send_notifications.php script
 */

// Prevent direct browser access - only allow from cron services
// Remove this check if you want to test manually in browser
$allowed_ips = ['127.0.0.1', '127.0.0.1', '52.89.214.238']; // EasyCron IP
$remote_ip = $_SERVER['REMOTE_ADDR'] ?? '';

// Uncomment to enable IP filtering (optional)
// if (!in_array($remote_ip, $allowed_ips)) {
//     http_response_code(403);
//     exit('Access Denied');
// }

// Prevent timeout
set_time_limit(300);

// Output headers
header('Content-Type: application/json');

// Start output buffering to capture cron output
ob_start();

// Include and run the main cron script
require_once __DIR__ . '/send_notifications.php';

// Get output
$output = ob_get_clean();

// Return JSON response
echo json_encode([
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'output' => $output
]);

?>