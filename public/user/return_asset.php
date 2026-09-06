<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

require '../../config/db_connect.php';
require '../../config/mail_config.php';
require '../../config/EmailNotification.php';

$emailNotifier = new EmailNotification($conn);

$user_id = $_SESSION['user_id'];
$message = null;
$message_type = null;

// ============ HANDLE QR CODE SCANNING FOR RETURN ============

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'scan_return') {
    $qr_code = trim($_POST['qr_code'] ?? '');
    $borrow_id = intval($_POST['borrow_id'] ?? 0);
    $asset_condition_on_return = strtolower($_POST['asset_condition_on_return'] ?? 'good');
    $return_notes = trim($_POST['return_notes'] ?? '');

    if (empty($qr_code)) {
        $message = 'Please scan or enter a QR code';
        $message_type = 'error';
    } elseif ($borrow_id === 0) {
        $message = 'Invalid borrow record';
        $message_type = 'error';
    } else {
        // Find the active borrow record with QR verification
        $stmt = $conn->prepare("
            SELECT 
                b.borrow_id,
                b.asset_id,
                b.borrow_date,
                b.expected_return_date,
                b.asset_condition_on_borrow,
                a.asset_name,
                a.asset_model,
                q.qr_code,
                u.email,
                u.user_name
            FROM borrow_asset b
            JOIN assets a ON b.asset_id = a.asset_id
            JOIN qr_codes q ON a.asset_id = q.asset_id
            JOIN users u ON b.user_id = u.user_id
            WHERE q.qr_code = ? 
            AND b.borrow_id = ? 
            AND b.user_id = ? 
            AND b.status = 'in-use'
            LIMIT 1
        ");
        
        if (!$stmt) {
            $message = 'Database error: ' . $conn->error;
            $message_type = 'error';
        } else {
            $stmt->bind_param("sii", $qr_code, $borrow_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $borrow = $result->fetch_assoc();
            $stmt->close();

            if (!$borrow) {
                $message = 'No active borrow found for this QR code or borrow record';
                $message_type = 'error';
            } else {
                // Validate dates before calculation
                if (empty($borrow['borrow_date']) || $borrow['borrow_date'] === '0000-00-00' || 
                    empty($borrow['expected_return_date']) || $borrow['expected_return_date'] === '0000-00-00') {
                    $message = 'Borrow record has invalid dates';
                    $message_type = 'error';
                } else {
                    // Calculate return details
                    $return_date = date('Y-m-d');
                    $return_time = date('H:i:s');
                    
                    //  Proper date calculations
                    $borrow_timestamp = strtotime($borrow['borrow_date']);
                    $return_timestamp = strtotime($return_date);
                    $due_timestamp = strtotime($borrow['expected_return_date']);
                    
                    $days_borrowed = intval(($return_timestamp - $borrow_timestamp) / 86400);
                    $days_overdue = max(0, intval(($return_timestamp - $due_timestamp) / 86400));
                    
                    $penalty_per_day = PENALTY_PER_DAY;
                    $penalty_amount = $days_overdue * $penalty_per_day;
                    $is_overdue = $days_overdue > 0 ? 1 : 0;

                    // Insert into return_asset with correct columns
                    $return_stmt = $conn->prepare("
                        INSERT INTO return_asset 
                        (borrow_id, asset_id, user_id, return_date, return_time, asset_condition_on_return, return_notes, days_borrowed, is_overdue, overdue_days, penalty_amount)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");

                    if (!$return_stmt) {
                        $message = 'Database error: ' . $conn->error;
                        $message_type = 'error';
                    } else {
                        //   Correct bind_param format (11 parameters)
                        $return_stmt->bind_param(
                            "iisssssiiid",
                            $borrow['borrow_id'],
                            $borrow['asset_id'],
                            $user_id,
                            $return_date,
                            $return_time,
                            $asset_condition_on_return,
                            $return_notes,
                            $days_borrowed,
                            $is_overdue,
                            $days_overdue,
                            $penalty_amount    
                        );

                        if ($return_stmt->execute()) {
                            $return_id = $conn->insert_id;

                            //  Update borrow status to 'returned'
                            $update_borrow = $conn->prepare("UPDATE borrow_asset SET status = 'returned' WHERE borrow_id = ? AND user_id = ?");
                            if ($update_borrow) {
                                $update_borrow->bind_param("ii", $borrow['borrow_id'], $user_id);
                                $update_borrow->execute();
                                $update_borrow->close();
                            }

                            //  Update asset status to 'available' (lowercase with hyphen)
                            $update_asset = $conn->prepare("UPDATE assets SET status = 'available', assigned_to = NULL WHERE asset_id = ?");
                            if ($update_asset) {
                                $update_asset->bind_param("i", $borrow['asset_id']);
                                $update_asset->execute();
                                $update_asset->close();
                            }

                            // Create penalty if overdue
                            if ($penalty_amount > 0) {
                                $penalty_stmt = $conn->prepare("
                                    INSERT INTO penalties (user_id, return_id, penalty_type, penalty_amount, reason, status, issued_date, created_at)
                                    VALUES (?, ?, 'overdue', ?, ?, 'pending', DATE_ADD(CURDATE(), INTERVAL 7 DAY), NOW())
                                ");
                                if ($penalty_stmt) {
                                    $reason = $days_overdue . " days overdue";
                                    $penalty_stmt->bind_param("iids", $user_id, $return_id, $penalty_amount, $reason);
                                    $penalty_stmt->execute();
                                    $penalty_stmt->close();
                                }
                            }

                            // Log the action
                            $log_stmt = $conn->prepare("
                                INSERT INTO asset_logs (asset_id, user_id, action, description)
                                VALUES (?, ?, 'Returned', ?)
                            ");
                            if ($log_stmt) {
                                $description = "Asset returned in $asset_condition_on_return condition";
                                $log_stmt->bind_param("iss", $borrow['asset_id'], $user_id, $description);
                                $log_stmt->execute();
                                $log_stmt->close();
                            }

                            // ============ SEND CONFIRMATION EMAIL ============
                            $emailNotifier->sendReturnConfirmation(
                                $borrow['email'],
                                $borrow['user_name'],
                                $user_id,
                                $borrow['asset_name'],
                                $return_date,
                                $days_borrowed,
                                $penalty_amount,
                                $days_overdue
                            );

                            // ============ SEND PENALTY EMAIL IF OVERDUE ============
                            if ($penalty_amount > 0) {
                                $emailNotifier->sendPenaltyNotification(
                                    $borrow['email'],
                                    $borrow['user_name'],
                                    $user_id,
                                    $borrow['asset_name'],
                                    $penalty_amount,
                                    $days_overdue,
                                    $days_overdue . " days overdue"
                                );
                            }

                            $message = '✓ Asset returned successfully!';
                            if ($penalty_amount > 0) {
                                $message .= " A penalty of Ksh " . number_format($penalty_amount) . " has been charged for being " . $days_overdue . " days overdue.";
                            } else {
                                $message .= " Thank you for returning on time!";
                            }
                            $message_type = 'success';
                        } else {
                            $message = 'Failed to process return: ' . $return_stmt->error;
                            $message_type = 'error';
                        }

                        $return_stmt->close();
                    }
                }
            }
        }
    }
}

// ============ GET DATA FOR DISPLAY ============

// Get user's active borrows (status is 'in-use')
$active_borrows_query = $conn->prepare("
    SELECT 
        b.borrow_id, 
        b.expected_return_date, 
        a.asset_name,
        DATEDIFF(b.expected_return_date, CURDATE()) as days_until_due
    FROM borrow_asset b
    JOIN assets a ON b.asset_id = a.asset_id
    WHERE b.user_id = ? AND b.status = 'in-use'
    ORDER BY b.expected_return_date ASC
    LIMIT 5
");

if (!$active_borrows_query) {
    error_log("Database error: " . $conn->error);
    $active_borrows_result = null;
} else {
    $active_borrows_query->bind_param("i", $user_id);
    $active_borrows_query->execute();
    $active_borrows_result = $active_borrows_query->get_result();
    $active_borrows_query->close();
}

// Get active borrowed assets for cards with proper validation
$active_borrowed_assets_query = $conn->prepare("
    SELECT 
        b.borrow_id, 
        b.borrow_date, 
        b.expected_return_date, 
        b.asset_condition_on_borrow,
        a.asset_id, 
        a.asset_name, 
        a.asset_code, 
        c.category_name,
        ai.image_path,
        q.qr_code,
        q.image_path as qr_image_path,
        DATEDIFF(b.expected_return_date, CURDATE()) as days_until_due
    FROM borrow_asset b
    JOIN assets a ON b.asset_id = a.asset_id
    LEFT JOIN asset_categories c ON a.category_id = c.category_id
    LEFT JOIN asset_images ai ON a.asset_id = ai.asset_id
    LEFT JOIN qr_codes q ON a.asset_id = q.asset_id
    WHERE b.user_id = ? AND b.status = 'in-use' AND b.expected_return_date IS NOT NULL AND b.expected_return_date != '0000-00-00'
    ORDER BY b.expected_return_date ASC
");

if (!$active_borrowed_assets_query) {
    error_log("Database error: " . $conn->error);
    $borrowed_assets_result = null;
} else {
    $active_borrowed_assets_query->bind_param("i", $user_id);
    $active_borrowed_assets_query->execute();
    $borrowed_assets_result = $active_borrowed_assets_query->get_result();
    $active_borrowed_assets_query->close();
}

// ============ LOAD FRONTEND ============
require 'return_asset_page.php';
?>
