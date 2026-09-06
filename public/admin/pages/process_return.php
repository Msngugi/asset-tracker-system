<?php
session_start();
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../../config/mail_config.php';
require_once __DIR__ . '/../../services/AssetReturnService.php';
require_once __DIR__ . '/../../services/EmailService.php';

header('Content-Type: application/json');

// ✅ Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    error_log("❌ [ADMIN RETURN] Unauthorized access");
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'digital_scan_return') {
    
    $borrow_id = intval($_POST['borrow_id'] ?? 0);
    $qr_code = $conn->real_escape_string($_POST['qr_code'] ?? '');
    $asset_condition_on_return = $conn->real_escape_string($_POST['asset_condition_on_return'] ?? 'Good');
    $return_notes = $conn->real_escape_string($_POST['return_notes'] ?? '');
    
    try {
        // ============ USE RETURN SERVICE ============
        $return_service = new AssetReturnService($conn);
        $return_result = $return_service->processReturn(
            $borrow_id,
            $qr_code,
            $asset_condition_on_return,
            $return_notes
        );
        
        if (!$return_result['success']) {
            echo json_encode(['success' => false, 'message' => $return_result['message']]);
            $conn->close();
            exit;
        }
        
        $email_sent = false;
        $penalty_email_sent = false;
        
        // ============ SEND EMAILS VIA EMAIL SERVICE ============
        try {
            $email_service = new EmailService($conn);
            
            // Send return confirmation
            $email_sent = $email_service->sendReturnConfirmation(
                $return_result['user_email'],
                $return_result['user_name'],
                $return_result['user_id'],
                $return_result['data']['asset_name'],
                $return_result['data']['return_date'],
                $return_result['data']['days_borrowed'],
                $return_result['data']['penalty_amount'],
                $return_result['data']['overdue_days']
            );
            
            // Send penalty email if overdue
            if ($return_result['data']['penalty_amount'] > 0) {
                $penalty_email_sent = $email_service->sendPenaltyNotification(
                    $return_result['user_email'],
                    $return_result['user_name'],
                    $return_result['user_id'],
                    $return_result['data']['asset_name'],
                    $return_result['data']['penalty_amount'],
                    $return_result['data']['overdue_days'],
                    "Asset returned " . $return_result['data']['overdue_days'] . " day(s) late"
                );
            }
            
        } catch (Exception $e) {
            error_log("⚠️ [ADMIN RETURN] Email service failed: " . $e->getMessage());
        }
        
        // ============ RETURN SUCCESS RESPONSE ============
        echo json_encode([
            'success' => true,
            'message' => 'Asset returned successfully' . ($email_sent ? ' - Confirmation email sent' : ''),
            'data' => $return_result['data'],
            'email_sent' => $email_sent,
            'penalty_email_sent' => $penalty_email_sent
        ]);
        
    } catch (Exception $e) {
        error_log("❌ [ADMIN RETURN] Exception: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>