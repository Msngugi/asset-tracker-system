<?php
/**
 * Cron job to send return reminders and overdue notifications
 * Run manually: php /path/to/cron/send_notifications.php
 * Add to crontab: 0 9 * * * php /home/user/public_html/asset_tracker/cron/send_notifications.php
 * 
 * This script:
 * 1. Sends reminders for assets due tomorrow
 * 2. Sends overdue notifications for assets past return date
 * 3. Logs all actions
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set timezone
date_default_timezone_set('Africa/Nairobi'); // Change to your timezone

require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../config/mail_config.php';
require_once __DIR__ . '/../config/EmailNotification.php';

// Initialize
$emailNotifier = new EmailNotification($conn);
$reminders_sent = 0;
$overdue_sent = 0;
$start_time = date('Y-m-d H:i:s');

echo "\n========================================\n";
echo "📧 Email Notification Cron Job Started\n";
echo "Time: $start_time\n";
echo "========================================\n\n";

// ============ 1️⃣ SEND DUE REMINDERS (Assets due tomorrow) ============

echo "📍 Processing DUE REMINDERS...\n";

$query = $conn->prepare("
    SELECT 
        b.borrow_id,
        b.user_id,
        b.expected_return_date,
        a.asset_id,
        a.asset_name,
        u.email,
        u.user_name
        
    FROM borrow_asset b
    JOIN assets a ON b.asset_id = a.asset_id
    JOIN users u ON b.user_id = u.user_id
    WHERE b.status = 'in-use' 
    AND DATE(b.expected_return_date) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
    AND b.reminder_sent = 0
    ORDER BY b.expected_return_date ASC
");

if (!$query) {
    error_log("[❌ CRON ERROR] Query prepare failed: " . $conn->error);
    echo "❌ Database query failed: " . $conn->error . "\n";
    exit(1);
}

$query->execute();
$result = $query->get_result();

if (!$result) {
    error_log("[❌ CRON ERROR] Query execute failed: " . $conn->error);
    echo "❌ Query execution failed: " . $conn->error . "\n";
    exit(1);
}

while ($row = $result->fetch_assoc()) {
    $days_remaining = 1;
    $user_name = $row['user_name'];
    
    echo "  → Sending reminder to {$user_name} ({$row['email']})...";
    
    if ($emailNotifier->sendReturnDueReminder(
        $row['email'],
        $user_name,
        $row['user_id'],
        $row['asset_name'],
        $row['expected_return_date'],
        $days_remaining
    )) {
        // Mark reminder as sent
        $update = $conn->prepare("UPDATE borrow_asset SET reminder_sent = 1, reminder_sent_date = NOW() WHERE borrow_id = ?");
        if ($update) {
            $update->bind_param("i", $row['borrow_id']);
            $update->execute();
            $update->close();
            
            echo " ✓\n";
            $reminders_sent++;
        } else {
            echo " ✗ (Failed to update)\n";
            error_log("[⚠️ CRON] Failed to update reminder_sent for borrow_id: {$row['borrow_id']}");
        }
    } else {
        echo " ✗\n";
        error_log("[⚠️ CRON] Failed to send reminder for user {$row['user_id']}, asset {$row['asset_id']}");
    }
}

$result->close();
$query->close();

echo "\n✓ Due reminders sent: $reminders_sent\n\n";

// ============ 2️⃣ SEND OVERDUE NOTIFICATIONS ============

echo "📍 Processing OVERDUE NOTIFICATIONS...\n";

$overdue_query = $conn->prepare("
    SELECT 
        b.borrow_id,
        b.user_id,
        b.expected_return_date,
        a.asset_id,
        a.asset_name,
        u.email,
        u.user_name,
        
        DATEDIFF(CURDATE(), DATE(b.expected_return_date)) as overdue_days
    FROM borrow_asset b
    JOIN assets a ON b.asset_id = a.asset_id
    JOIN users u ON b.user_id = u.user_id
    WHERE b.status = 'in-use'
    AND DATE(b.expected_return_date) < CURDATE()
    AND b.reminder_sent = 1
    ORDER BY b.expected_return_date ASC
");

if (!$overdue_query) {
    error_log("[❌ CRON ERROR] Overdue query prepare failed: " . $conn->error);
    echo "❌ Overdue query failed: " . $conn->error . "\n";
} else {
    $overdue_query->execute();
    $overdue_result = $overdue_query->get_result();
    
    while ($row = $overdue_result->fetch_assoc()) {
        $penalty_amount = $row['overdue_days'] * PENALTY_PER_DAY;
        $user_name = $row['user_name'];
        
        echo "  → Sending overdue notice to {$user_name} ({$row['email']})...";
        
        if ($emailNotifier->sendOverdueNotification(
            $row['email'],
            $user_name,
            $row['user_id'],
            $row['asset_name'],
            $row['expected_return_date'],
            $row['overdue_days'],
            $penalty_amount
        )) {
            // Mark notification as sent (update overdue_notification_date)
            $update = $conn->prepare("UPDATE borrow_asset SET overdue_notification_date = NOW() WHERE borrow_id = ?");
            if ($update) {
                $update->bind_param("i", $row['borrow_id']);
                $update->execute();
                $update->close();
                
                echo " ✓\n";
                $overdue_sent++;
            } else {
                echo " ✗ (Failed to update)\n";
                error_log("[⚠️ CRON] Failed to update overdue_notification_date for borrow_id: {$row['borrow_id']}");
            }
        } else {
            echo " ✗\n";
            error_log("[⚠️ CRON] Failed to send overdue notification for user {$row['user_id']}, asset {$row['asset_id']}");
        }
    }
    
    $overdue_result->close();
    $overdue_query->close();
}

echo "\n✓ Overdue notifications sent: $overdue_sent\n\n";

// ============ SUMMARY ============

$end_time = date('Y-m-d H:i:s');
echo "========================================\n";
echo "📊 SUMMARY\n";
echo "========================================\n";
echo "Start time: $start_time\n";
echo "End time: $end_time\n";
echo "Due reminders: $reminders_sent\n";
echo "Overdue notifications: $overdue_sent\n";
echo "Total: " . ($reminders_sent + $overdue_sent) . "\n";
echo "========================================\n\n";

error_log("[✓ CRON COMPLETE] Reminders: $reminders_sent | Overdue: $overdue_sent");

$conn->close();

?>
