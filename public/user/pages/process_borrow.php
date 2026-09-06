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

if ($action === 'user_borrow') {
    
    $qr_code = $conn->real_escape_string($_POST['qr_code'] ?? '');
    $borrow_days = intval($_POST['borrow_days'] ?? 7);
    $condition = $conn->real_escape_string($_POST['asset_condition_on_borrow'] ?? 'good');
    $remarks = $conn->real_escape_string($_POST['remarks'] ?? '');
    
    try {
        // ============ GET ASSET FROM QR CODE ============
        $asset_query = $conn->prepare("
            SELECT a.asset_id, a.asset_name, a.asset_model, a.serial_number 
            FROM assets a
            JOIN qr_codes q ON a.asset_id = q.asset_id
            WHERE q.qr_code = ? AND a.status = 'available'
        ");
        
        if (!$asset_query) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit;
        }
        
        $asset_query->bind_param("s", $qr_code);
        $asset_query->execute();
        $asset_result = $asset_query->get_result();
        
        if ($asset_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Asset not found or not available']);
            $asset_query->close();
            exit;
        }
        
        $asset = $asset_result->fetch_assoc();
        $asset_id = $asset['asset_id'];
        $asset_query->close();
        
        // ============ GET USER DATA ============
        $user_query = $conn->prepare("
            SELECT user_id, user_name, email
            FROM users
            WHERE user_id = ?
        ");
        
        if (!$user_query) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit;
        }
        
        $user_query->bind_param("i", $user_id);
        $user_query->execute();
        $user_result = $user_query->get_result();
        
        if ($user_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            $user_query->close();
            exit;
        }
        
        $user_data = $user_result->fetch_assoc();
        $user_query->close();
        
        // ============ CALCULATE DATES ============
        $borrow_date = date('Y-m-d');
        $borrow_time = date('H:i:s');
        $expected_return_date = date('Y-m-d', strtotime("+{$borrow_days} days"));
        
        // ============ INSERT BORROW RECORD ============
        $borrow_stmt = $conn->prepare("
            INSERT INTO borrow_asset 
            (asset_id, user_id, borrow_date, borrow_time, expected_return_date, asset_condition_on_borrow, qr_code_scanned, status, reminder_sent)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'in-use', 0)
        ");
        
        if (!$borrow_stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit;
        }
        
        $borrow_stmt->bind_param(
            "iisssss",
            $asset_id,
            $user_id,
            $borrow_date,
            $borrow_time,
            $expected_return_date,
            $condition,
            $qr_code
        );
        
        if (!$borrow_stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Failed to create borrow record']);
            $borrow_stmt->close();
            exit;
        }
        
        $borrow_id = $conn->insert_id;
        $borrow_stmt->close();
        
        // ============ UPDATE ASSET STATUS ============
        $update_asset = $conn->prepare("
            UPDATE assets 
            SET status = 'in-use', assigned_to = ?
            WHERE asset_id = ?
        ");
        
        if ($update_asset) {
            $update_asset->bind_param("ii", $user_id, $asset_id);
            $update_asset->execute();
            $update_asset->close();
        }
        
        $email_sent = false;
        
        // ============ SEND EMAIL VIA EMAIL SERVICE ============
        try {
            $email_service = new EmailService($conn);
            $email_sent = $email_service->sendBorrowConfirmation(
                $user_data['email'],
                $user_data['user_name'],
                $user_id,
                $asset,
                $borrow_date,
                $expected_return_date,
                $borrow_days
            );
        } catch (Exception $e) {
            error_log("⚠️ [PROCESS BORROW] Email service failed: " . $e->getMessage());
        }
        
        // ============ RETURN SUCCESS RESPONSE ============
        echo json_encode([
            'success' => true,
            'message' => 'Asset borrowed successfully! ' . ($email_sent ? 'Confirmation email sent.' : 'Email notification pending.'),
            'data' => [
                'borrow_id' => $borrow_id,
                'asset_name' => $asset['asset_name'],
                'asset_code' => $asset['asset_code'],
                'user_name' => $user_data['user_name']
            ],
            'email_sent' => $email_sent
        ]);
        
    } catch (Exception $e) {
        error_log("❌ [PROCESS BORROW] Exception: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>
