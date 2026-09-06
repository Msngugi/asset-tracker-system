<?php
session_start();
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../../config/mail_config.php';
require_once __DIR__ . '/../../services/AssetBorrowService.php';
require_once __DIR__ . '/../../services/EmailService.php';

header('Content-Type: application/json');

// ✅ Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    error_log("❌ [ADMIN BORROW] Unauthorized access");
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'digital_scan_borrow') {
    
    $asset_id = intval($_POST['asset_id'] ?? 0);
    $user_id = intval($_POST['user_id'] ?? 0);
    $qr_code = $conn->real_escape_string($_POST['qr_code'] ?? '');
    $borrow_days = intval($_POST['borrow_days'] ?? 7);
    $condition_on_borrow = $conn->real_escape_string($_POST['asset_condition_on_borrow'] ?? 'Good');
    $remarks = $conn->real_escape_string($_POST['remarks'] ?? '');
    
    try {
        // ============ USE BORROW SERVICE ============
        $borrow_service = new AssetBorrowService($conn);
        $borrow_result = $borrow_service->processBorrow(
            $asset_id,
            $user_id,
            $qr_code,
            $borrow_days,
            $condition_on_borrow,
            $remarks
        );
        
        if (!$borrow_result['success']) {
            echo json_encode(['success' => false, 'message' => $borrow_result['message']]);
            $conn->close();
            exit;
        }
        
        $email_sent = false;
        
        // ============ SEND EMAIL VIA EMAIL SERVICE ============
        try {
            $email_service = new EmailService($conn);
            $email_sent = $email_service->sendBorrowConfirmation(
                $borrow_result['user_email'],
                $borrow_result['data']['user_name'],
                $user_id,
                $borrow_result['asset_data'],
                $borrow_result['borrow_date'],
                $borrow_result['expected_return_date'],
                $borrow_days
            );
        } catch (Exception $e) {
            error_log("⚠️ [ADMIN BORROW] Email service failed: " . $e->getMessage());
        }
        
        // ============ RETURN SUCCESS RESPONSE ============
        echo json_encode([
            'success' => true,
            'message' => 'Asset borrowed successfully' . ($email_sent ? ' - Confirmation email sent' : ''),
            'data' => $borrow_result['data'],
            'email_sent' => $email_sent
        ]);
        
    } catch (Exception $e) {
        error_log("❌ [ADMIN BORROW] Exception: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>