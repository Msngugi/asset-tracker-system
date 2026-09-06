<?php
session_start();
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../../config/mail_config.php';
require_once __DIR__ . '/../../services/EmailService.php';

header('Content-Type: application/json');

// ✅ Check user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'user_return') {
    
    $borrow_id = intval($_POST['borrow_id'] ?? 0);
    $qr_code = $conn->real_escape_string($_POST['qr_code'] ?? '');
    $condition = $conn->real_escape_string($_POST['asset_condition_on_return'] ?? 'good');
    $notes = $conn->real_escape_string($_POST['return_notes'] ?? '');
    
    try {
        // ============ VERIFY BORROW BELONGS TO USER & GET DETAILS ============
        $borrow_query = $conn->prepare("
            SELECT b.borrow_id, b.asset_id, b.user_id, b.borrow_date, b.expected_return_date,
                   a.asset_name, q.qr_code, u.email, u.user_name
            FROM borrow_asset b
            JOIN assets a ON b.asset_id = a.asset_id
            JOIN qr_codes q ON a.asset_id = q.asset_id
            JOIN users u ON b.user_id = u.user_id
            WHERE b.borrow_id = ? AND b.user_id = ? AND b.status = 'in-use'
        ");
        
        if (!$borrow_query) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit;
        }
        
        $borrow_query->bind_param("ii", $borrow_id, $user_id);
        $borrow_query->execute();
        $borrow_result = $borrow_query->get_result();
        
        if ($borrow_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Borrow record not found']);
            $borrow_query->close();
            exit;
        }
        
        $borrow = $borrow_result->fetch_assoc();
        $borrow_query->close();
        
        // ============ VERIFY QR CODE ============
        if ($qr_code !== $borrow['qr_code']) {
            echo json_encode(['success' => false, 'message' => 'QR code does not match']);
            exit;
        }
        
        // ============ CALCULATE RETURN DETAILS ============
        $return_date = date('Y-m-d');
        $return_time = date('H:i:s');
        
        $borrow_timestamp = strtotime($borrow['borrow_date']);
        $return_timestamp = strtotime($return_date);
        $due_timestamp = strtotime($borrow['expected_return_date']);
        
        $days_borrowed = intval(($return_timestamp - $borrow_timestamp) / 86400);
        $overdue_days = max(0, intval(($return_timestamp - $due_timestamp) / 86400));
        $penalty_amount = $overdue_days * PENALTY_PER_DAY;
        $is_overdue = $overdue_days > 0 ? 1 : 0;
        
        // ============ INSERT INTO RETURN_ASSET TABLE ============
        $return_stmt = $conn->prepare("
            INSERT INTO return_asset 
            (borrow_id, asset_id, user_id, return_date, return_time, asset_condition_on_return, return_notes, days_borrowed, is_overdue, overdue_days, penalty_amount)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$return_stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit;
        }
        
        $return_stmt->bind_param(
            "iisssssiiid",
            $borrow['borrow_id'],
            $borrow['asset_id'],
            $user_id,
            $return_date,
            $return_time,
            $condition,
            $notes,
            $days_borrowed,
            $is_overdue,
            $overdue_days,
            $penalty_amount
        );
        
        if (!$return_stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Failed to process return']);
            $return_stmt->close();
            exit;
        }
        
        $return_id = $conn->insert_id;
        $return_stmt->close();
        
        // ============ UPDATE BORROW STATUS ============
        $update_borrow = $conn->prepare("
            UPDATE borrow_asset 
            SET status = 'returned' 
            WHERE borrow_id = ? AND user_id = ?
        ");
        
        if ($update_borrow) {
            $update_borrow->bind_param("ii", $borrow['borrow_id'], $user_id);
            $update_borrow->execute();
            $update_borrow->close();
        }
        
        // ============ UPDATE ASSET STATUS ============
        $update_asset = $conn->prepare("
            UPDATE assets 
            SET status = 'available', assigned_to = NULL 
            WHERE asset_id = ?
        ");
        
        if ($update_asset) {
            $update_asset->bind_param("i", $borrow['asset_id']);
            $update_asset->execute();
            $update_asset->close();
        }
        
        // ============ CREATE PENALTY IF OVERDUE ============
        if ($penalty_amount > 0) {
            $penalty_stmt = $conn->prepare("
                INSERT INTO penalties (user_id, return_id, penalty_type, penalty_amount, reason, status, issued_date, created_at)
                VALUES (?, ?, 'overdue', ?, ?, 'pending', DATE_ADD(CURDATE(), INTERVAL 7 DAY), NOW())
            ");
            
            if ($penalty_stmt) {
                $reason = $overdue_days . " days overdue";
                $penalty_stmt->bind_param("iids", $user_id, $return_id, $penalty_amount, $reason);
                $penalty_stmt->execute();
                $penalty_stmt->close();
            }
        }
        
        $email_sent = false;
        $penalty_email_sent = false;
        
        // ============ SEND EMAILS VIA EMAIL SERVICE ============
        try {
            $email_service = new EmailService($conn);
            
            // Send return confirmation
            $email_sent = $email_service->sendReturnConfirmation(
                $borrow['email'],
                $borrow['user_name'],
                $user_id,
                $borrow['asset_name'],
                $return_date,
                $days_borrowed,
                $penalty_amount,
                $overdue_days
            );
            
            // Send penalty email if overdue
            if ($penalty_amount > 0) {
                $penalty_email_sent = $email_service->sendPenaltyNotification(
                    $borrow['email'],
                    $borrow['user_name'],
                    $user_id,
                    $borrow['asset_name'],
                    $penalty_amount,
                    $overdue_days,
                    $overdue_days . " day(s) overdue"
                );
            }
            
        } catch (Exception $e) {
            error_log("⚠️ [PROCESS RETURN] Email service failed: " . $e->getMessage());
        }
        
        // ============ RETURN SUCCESS RESPONSE ============
        echo json_encode([
            'success' => true,
            'message' => 'Asset returned successfully! ' . ($email_sent ? 'Confirmation email sent.' : 'Email notification pending.'),
            'data' => [
                'asset_name' => $borrow['asset_name'],
                'return_date' => $return_date,
                'days_borrowed' => $days_borrowed,
                'penalty_amount' => $penalty_amount,
                'overdue_days' => $overdue_days
            ],
            'email_sent' => $email_sent,
            'penalty_email_sent' => $penalty_email_sent
        ]);
        
    } catch (Exception $e) {
        error_log("❌ [PROCESS RETURN] Exception: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>
