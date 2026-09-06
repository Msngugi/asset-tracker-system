<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

require '../../../config/db_connect.php';
require '../../../config/SimpleQRCode.php';
require '../../../config/AssetImageHandler.php';

error_log("=== process_asset.php START ===");
error_log("REQUEST METHOD: " . $_SERVER['REQUEST_METHOD']);

// ============ HANDLE FORM SUBMISSION ============

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_id = !empty($_POST['asset_id']) ? intval($_POST['asset_id']) : 0;
    $asset_name = trim($_POST['asset_name'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $model = trim($_POST['asset_model'] ?? '');
    $serial_number = trim($_POST['serial_number'] ?? '');
    $assigned_to = !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : NULL;
    $status = strtolower($_POST['status'] ?? 'available'); //  Lowercase status
    $condition = strtolower($_POST['asset_condition'] ?? 'excellent'); // Lowercase condition
    $purchase_date = !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : NULL;
    $cost = !empty($_POST['cost']) ? floatval($_POST['cost']) : NULL;
    $notes = trim($_POST['notes'] ?? '');

    error_log("Form data: asset_id=$asset_id, name=$asset_name, status=$status, assigned_to=$assigned_to");

    // ============ VALIDATION ============

    if (empty($asset_name) || $category_id === 0) {
        $_SESSION['error'] = 'Asset name and category are required';
        error_log("Validation failed: empty name or category");
        header("Location: ../assets.php");
        exit();
    }

    // Check for duplicate serial number (if updating, exclude current asset)
    if (!empty($serial_number)) {
        $check = $conn->prepare("SELECT asset_id FROM assets WHERE serial_number = ? AND asset_id != ?");
        $check->bind_param("si", $serial_number, $asset_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $_SESSION['error'] = 'Serial number already exists';
            $check->close();
            error_log("Validation failed: duplicate serial number");
            header("Location: ../assets.php");
            exit();
        }
        $check->close();
    }

    // ============ HANDLE IMAGE UPLOAD ============

    $image_path = null;
    $image_handler = new AssetImageHandler();
    
    if (isset($_FILES['asset_image']) && $_FILES['asset_image']['size'] > 0) {
        $upload_result = $image_handler->uploadImage($_FILES['asset_image'], $asset_id ?: time());
        
        if (!$upload_result['success']) {
            $_SESSION['error'] = $upload_result['message'];
            error_log("Image upload failed: " . $upload_result['message']);
            header("Location: ../assets.php");
            exit();
        }
        
        $image_path = $upload_result['path'];
        error_log("Image uploaded: $image_path");
    }

    // ============ ADD NEW ASSET ============

    if ($asset_id === 0) {
        error_log("Creating new asset");
    
        $stmt = $conn->prepare("
            INSERT INTO assets 
            (asset_name, category_id, asset_model, serial_number, assigned_to, status, asset_condition, purchase_date, cost, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            $_SESSION['error'] = 'Database error: ' . $conn->error;
            header("Location: ../assets.php");
            exit();
        }

       
        $stmt->bind_param(
            "sississsds",
            $asset_name,      // s - string
            $category_id,     // i - integer
            $model,     // s - string
            $serial_number,   // s - string
            $assigned_to,     // i - integer (nullable)
            $status,          // s - string (lowercase)
            $condition,  // s - string (lowercase)
            $purchase_date,   // s - string
            $cost,            // d - double
            $notes            // s - string
        );

        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            $_SESSION['error'] = 'Failed to add asset: ' . $stmt->error;
            $stmt->close();
            header("Location: ../assets.php");
            exit();
        }

        $new_asset_id = $conn->insert_id;
        error_log("New asset created with ID: $new_asset_id");
        
        // Generate asset code
        $generated_code = 'ASSET-' . str_pad($new_asset_id, 6, '0', STR_PAD_LEFT);
        $update = $conn->prepare("UPDATE assets SET asset_code = ? WHERE asset_id = ?");
        $update->bind_param("si", $generated_code, $new_asset_id);
        $update->execute();
        $update->close();
        error_log("Asset code generated: $generated_code");
        
        // ============ SAVE IMAGE IF UPLOADED ============
        if ($image_path) {
            $img_stmt = $conn->prepare("
                INSERT INTO asset_images (asset_id, image_filename, image_path)
                VALUES (?, ?, ?)
            ");
            $img_filename = basename($image_path);
            $img_stmt->bind_param("iss", $new_asset_id, $img_filename, $image_path);
            
            if (!$img_stmt->execute()) {
                error_log("Failed to save image to DB: " . $img_stmt->error);
            } else {
                error_log("Image saved to DB: $image_path");
            }
            $img_stmt->close();
        }

        // ============ GENERATE QR CODE ============
        try {
            $qr_generator = new SimpleQRCode();
            $qr_result = $qr_generator->generateQRCode($new_asset_id, $asset_name);

            if ($qr_result['success']) {
                error_log("QR code generated: " . $qr_result['image_path']);
                
                // Save QR code info to database
                $qr_stmt = $conn->prepare("
                    INSERT INTO qr_codes (asset_id, qr_code, image_path, image_filename)
                    VALUES (?, ?, ?, ?)
                ");
                
                if (!$qr_stmt) {
                    error_log("QR Prepare failed: " . $conn->error);
                } else {
                    $qr_filename = basename($qr_result['image_path']);
                    $qr_stmt->bind_param("isss", $new_asset_id, $qr_result['qr_code'], $qr_result['image_path'], $qr_filename);
                    
                    if (!$qr_stmt->execute()) {
                        error_log("QR Execute failed: " . $qr_stmt->error);
                    } else {
                        error_log("QR code saved to DB successfully");
                    }
                    $qr_stmt->close();
                }
            } else {
                error_log("QR Code generation failed: " . $qr_result['message']);
            }
        } catch (Exception $e) {
            error_log("QR Code exception: " . $e->getMessage());
        }

        // ============ LOG THE ACTION ============
        $log_stmt = $conn->prepare("
            INSERT INTO asset_logs (asset_id, user_id, action, description)
            VALUES (?, ?, 'Created', ?)
        ");
        $description = "Asset created: $asset_name with code: $generated_code";
        $log_stmt->bind_param("iss", $new_asset_id, $_SESSION['user_id'], $description);
        $log_stmt->execute();
        $log_stmt->close();

        $_SESSION['success'] = 'Asset added successfully with code: ' . $generated_code;

    } else {
        // ============ UPDATE EXISTING ASSET ============
        error_log("Updating asset ID: $asset_id");
        
        $stmt = $conn->prepare("
            UPDATE assets 
            SET asset_name = ?, 
                category_id = ?, 
                asset_model = ?, 
                serial_number = ?, 
                assigned_to = ?, 
                status = ?, 
                asset_condition = ?, 
                purchase_date = ?, 
                cost = ?, 
                notes = ?
            WHERE asset_id = ?
        ");

        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            $_SESSION['error'] = 'Database error: ' . $conn->error;
            header("Location: ../assets.php");
            exit();
        }

        // ✅ Corrected bind_param: 11 parameters
        $stmt->bind_param(
            "sississsdsi",
            $asset_name,      // s - string
            $category_id,     // i - integer
            $model,           // s - string
            $serial_number,   // s - string
            $assigned_to,     // i - integer (nullable)
            $status,          // s - string (lowercase)
            $condition,       // s - string (lowercase)
            $purchase_date,   // s - string
            $cost,            // d - double
            $notes,           // s - string
            $asset_id         // i - integer
        );

        if (!$stmt->execute()) {
            error_log("Update execute failed: " . $stmt->error);
            $_SESSION['error'] = 'Failed to update asset: ' . $stmt->error;
            $stmt->close();
            header("Location: ../assets.php");
            exit();
        }

        error_log("Asset updated successfully");
        
        // ============ UPDATE IMAGE IF UPLOADED ============
        if ($image_path) {
            $old_img = $conn->prepare("SELECT image_path FROM asset_images WHERE asset_id = ?");
            $old_img->bind_param("i", $asset_id);
            $old_img->execute();
            $old_img_result = $old_img->get_result()->fetch_assoc();
            $old_img->close();

            if ($old_img_result) {
                $image_handler->deleteImage($old_img_result['image_path']);
                
                $img_update = $conn->prepare("
                    UPDATE asset_images 
                    SET image_filename = ?, image_path = ?
                    WHERE asset_id = ?
                ");
                $img_filename = basename($image_path);
                $img_update->bind_param("ssi", $img_filename, $image_path, $asset_id);
                $img_update->execute();
                $img_update->close();
                error_log("Image updated");
            } else {
                $img_insert = $conn->prepare("
                    INSERT INTO asset_images (asset_id, image_filename, image_path)
                    VALUES (?, ?, ?)
                ");
                $img_filename = basename($image_path);
                $img_insert->bind_param("iss", $asset_id, $img_filename, $image_path);
                $img_insert->execute();
                $img_insert->close();
                error_log("Image inserted");
            }
        }

        // ============ LOG THE ACTION ============
        $log_stmt = $conn->prepare("
            INSERT INTO asset_logs (asset_id, user_id, action, description)
            VALUES (?, ?, 'Updated', ?)
        ");
        $description = "Asset updated: $asset_name (Status: $status)";
        $log_stmt->bind_param("iss", $asset_id, $_SESSION['user_id'], $description);
        $log_stmt->execute();
        $log_stmt->close();

        $_SESSION['success'] = 'Asset updated successfully!';
        $stmt->close();
    }

    error_log("=== process_asset.php END (SUCCESS) ===");
    // ============ REDIRECT ============
    header("Location: ../assets.php");
    exit();
}

error_log("=== process_asset.php END (NO POST) ===");
?>