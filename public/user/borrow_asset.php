<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

require '../../config/db_connect.php';
require '../../config/mail_config.php';
require '../../services/EmailService.php';

$user_id = $_SESSION['user_id'];
$message = null;
$message_type = null;

// ============ HANDLE QR CODE SCANNING ============

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'scan_borrow') {
    $qr_code = trim($_POST['qr_code'] ?? '');
    $borrow_days = intval($_POST['borrow_days'] ?? 7);
    $asset_condition_on_borrow = strtolower($_POST['asset_condition_on_borrow'] ?? 'good');
    $remarks = trim($_POST['remarks'] ?? '');

    if (empty($qr_code)) {
        $message = 'Please scan or enter a QR code';
        $message_type = 'error';
    } else {
        //  Find asset by QR code
        $stmt = $conn->prepare("
            SELECT a.asset_id, a.asset_name, a.asset_model, a.serial_number, a.status, q.qr_code, ai.image_path
            FROM assets a
            JOIN qr_codes q ON a.asset_id = q.asset_id
            LEFT JOIN asset_images ai ON a.asset_id = ai.asset_id
            WHERE q.qr_code = ?
        ");
        $stmt->bind_param("s", $qr_code);
        $stmt->execute();
        $result = $stmt->get_result();
        $asset = $result->fetch_assoc();
        $stmt->close();

        if (!$asset) {
            $message = 'QR code not found in system';
            $message_type = 'error';
        } elseif ($asset['status'] !== 'available') {
            $message = 'This asset is not available for borrowing. Current status: ' . htmlspecialchars($asset['status']);
            $message_type = 'error';
        } else {
            //  Check if user already has max borrowed items
            $max_check = $conn->prepare("SELECT COUNT(*) as count FROM borrow_asset WHERE user_id = ? AND status = 'in-use'");
            $max_check->bind_param("i", $user_id);
            $max_check->execute();
            $max_data = $max_check->get_result()->fetch_assoc();
            $max_check->close();

            if ($max_data['count'] >= 5) {
                $message = 'You have reached the maximum number of borrowable items (5)';
                $message_type = 'error';
            } else {
                //  Calculate dates correctly
                $borrow_date = date('Y-m-d');
                $borrow_time = date('H:i:s');
                $expected_return_date = date('Y-m-d', strtotime($borrow_date . ' + ' . $borrow_days . ' days'));
                
                error_log("DEBUG: borrow_days=$borrow_days, borrow_date=$borrow_date, expected_return_date=$expected_return_date");

                // Validate dates
                if (empty($expected_return_date) || $expected_return_date === '1970-01-01' || !strtotime($expected_return_date)) {
                    $message = 'Error calculating return date. Please try again.';
                    $message_type = 'error';
                } else {
                    // Insert with correct status 'in-use'
                    $borrow_stmt = $conn->prepare("
                        INSERT INTO borrow_asset 
                        (asset_id, user_id, borrow_date, borrow_time, expected_return_date, asset_condition_on_borrow, status)
                        VALUES (?, ?, ?, ?, ?, ?, 'in-use')
                    ");

                    if (!$borrow_stmt) {
                        $message = 'Database error: ' . $conn->error;
                        $message_type = 'error';
                    } else {
                        $borrow_stmt->bind_param(
                            "iissss", 
                            $asset['asset_id'], 
                            $user_id, 
                            $borrow_date,
                            $borrow_time, 
                            $expected_return_date,
                            $asset_condition_on_borrow
                        );

                        if ($borrow_stmt->execute()) {
                            $borrow_id = $conn->insert_id;

                            // Update asset status to 'in-use'
                            $update_stmt = $conn->prepare("UPDATE assets SET status = 'in-use', assigned_to = ? WHERE asset_id = ?");
                            $update_stmt->bind_param("ii", $user_id, $asset['asset_id']);
                            $update_stmt->execute();
                            $update_stmt->close();

                            // Log the action
                            $log_stmt = $conn->prepare("
                                INSERT INTO asset_logs (asset_id, user_id, action, description)
                                VALUES (?, ?, 'Borrowed', ?)
                            ");
                            $description = "Asset borrowed by " . $_SESSION['username'];
                            $log_stmt->bind_param("iss", $asset['asset_id'], $user_id, $description);
                            $log_stmt->execute();
                            $log_stmt->close();

                            // ============ SEND CONFIRMATION EMAIL ============
                            $user_query = $conn->prepare("SELECT email, user_name FROM users WHERE user_id = ?");
                            $user_query->bind_param("i", $user_id);
                            $user_query->execute();
                            $user_data = $user_query->get_result()->fetch_assoc();
                            $user_query->close();

                            // FIXED: Use EmailService with correct parameters
                            if ($user_data) {
                                try {
                                    $email_service = new EmailService($conn);
                                    $email_service->sendBorrowConfirmation(
                                        $user_data['email'],           // user_email 
                                        $user_data['user_name'],       // user_name 
                                        $user_id,                      // user_id 
                                        $asset,                        // asset_data (array) 
                                        $borrow_date,                  // borrow_date 
                                        $expected_return_date,         // expected_return_date 
                                        $borrow_days                   // borrow_days 
                                    );
                                    error_log("✅ [BORROW] Confirmation email sent to: " . $user_data['email']);
                                } catch (Exception $e) {
                                    error_log("⚠️ [BORROW] Email service error: " . $e->getMessage());
                                }
                            }

                            $message = '✓ Asset borrowed successfully! You have ' . $borrow_days . ' days to return it. A confirmation email has been sent.';
                            $message_type = 'success';
                        } else {
                            $message = 'Failed to borrow asset: ' . $borrow_stmt->error;
                            $message_type = 'error';
                        }

                        $borrow_stmt->close();
                    }
                }
            }
        }
    }
}

// ============ GET DATA FOR DISPLAY ============

// Get user's active borrows
$borrows_query = $conn->prepare("
    SELECT b.borrow_id, b.expected_return_date, a.asset_name, a.asset_model,
    DATEDIFF(b.expected_return_date, CURDATE()) as days_remaining
    FROM borrow_asset b
    JOIN assets a ON b.asset_id = a.asset_id
    WHERE b.user_id = ? AND b.status = 'in-use'
    ORDER BY b.expected_return_date ASC
    LIMIT 5
");
$borrows_query->bind_param("i", $user_id);
$borrows_query->execute();
$borrows_result = $borrows_query->get_result();
$borrows_query->close();

// Get available assets count
$available_query = $conn->prepare("SELECT COUNT(*) as count FROM assets WHERE status = 'available'");
$available_query->execute();
$available_data = $available_query->get_result()->fetch_assoc();
$available_count = $available_data['count'];
$available_query->close();

// Get available assets for display
$available_assets_query = $conn->prepare("
    SELECT a.asset_id, a.asset_name, a.asset_code, a.asset_model, a.asset_condition, 
           a.serial_number, ac.category_name, ai.image_path, q.qr_code, q.image_path as qr_image_path
    FROM assets a
    LEFT JOIN asset_categories ac ON a.category_id = ac.category_id
    LEFT JOIN asset_images ai ON a.asset_id = ai.asset_id
    LEFT JOIN qr_codes q ON a.asset_id = q.asset_id
    WHERE a.status = 'available'
    ORDER BY a.asset_name ASC
    LIMIT 12
");
$available_assets_query->execute();
$available_assets_result = $available_assets_query->get_result();
$available_assets_query->close();

// ============ LOAD FRONTEND ============
require 'borrow_asset_page.php';
?>