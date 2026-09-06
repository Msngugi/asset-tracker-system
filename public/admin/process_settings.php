<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

require '../../config/db_connect.php';

$action = $_POST['action'] ?? '';

// ============ HANDLE GENERAL SETTINGS ============
if ($action === 'general') {
    $app_name = trim($_POST['app_name'] ?? '');
    $app_email = trim($_POST['app_email'] ?? '');
    $org_name = trim($_POST['org_name'] ?? '');
    $org_address = trim($_POST['org_address'] ?? '');
    $org_phone = trim($_POST['org_phone'] ?? '');
    $currency = $_POST['currency'] ?? 'PHP';
    $items_per_page = intval($_POST['items_per_page'] ?? 20);
    $session_timeout = intval($_POST['session_timeout'] ?? 30);
    $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;

    // Check if settings exist
    $check = $conn->prepare("SELECT COUNT(*) as count FROM admin_settings");
    $check->execute();
    $exists = $check->get_result()->fetch_assoc()['count'] > 0;
    $check->close();

    if ($exists) {
        $stmt = $conn->prepare("
            UPDATE admin_settings SET 
            app_name = ?,
            app_email = ?,
            org_name = ?,
            org_address = ?,
            org_phone = ?,
            currency = ?,
            items_per_page = ?,
            session_timeout = ?,
            maintenance_mode = ?
        ");
        $stmt->bind_param(
            "sssssssii",
            $app_name, $app_email, $org_name, $org_address, 
            $org_phone, $currency, $items_per_page, $session_timeout, $maintenance_mode
        );
    } else {
        $stmt = $conn->prepare("
            INSERT INTO admin_settings 
            (app_name, app_email, org_name, org_address, org_phone, currency, items_per_page, session_timeout, maintenance_mode)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "sssssssii",
            $app_name, $app_email, $org_name, $org_address,
            $org_phone, $currency, $items_per_page, $session_timeout, $maintenance_mode
        );
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = 'General settings saved successfully!';
        error_log("General settings updated by admin: " . $_SESSION['user_id']);
    } else {
        $_SESSION['error'] = 'Failed to save settings: ' . $stmt->error;
    }
    $stmt->close();
}

// ============ HANDLE SECURITY SETTINGS ============
elseif ($action === 'security') {
    $require_strong_password = isset($_POST['require_strong_password']) ? 1 : 0;
    $password_expiry_days = intval($_POST['password_expiry_days'] ?? 90);
    $max_login_attempts = intval($_POST['max_login_attempts'] ?? 5);
    $lockout_duration = intval($_POST['lockout_duration'] ?? 30);
    $enable_2fa = isset($_POST['enable_2fa']) ? 1 : 0;
    $enable_logging = isset($_POST['enable_logging']) ? 1 : 0;

    $check = $conn->prepare("SELECT COUNT(*) as count FROM admin_settings");
    $check->execute();
    $exists = $check->get_result()->fetch_assoc()['count'] > 0;
    $check->close();

    if ($exists) {
        $stmt = $conn->prepare("
            UPDATE admin_settings SET 
            require_strong_password = ?,
            password_expiry_days = ?,
            max_login_attempts = ?,
            lockout_duration = ?,
            enable_2fa = ?,
            enable_logging = ?
        ");
        $stmt->bind_param(
            "iiiiii",
            $require_strong_password, $password_expiry_days, $max_login_attempts,
            $lockout_duration, $enable_2fa, $enable_logging
        );
    } else {
        $stmt = $conn->prepare("
            INSERT INTO admin_settings 
            (require_strong_password, password_expiry_days, max_login_attempts, lockout_duration, enable_2fa, enable_logging)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iiiiii",
            $require_strong_password, $password_expiry_days, $max_login_attempts,
            $lockout_duration, $enable_2fa, $enable_logging
        );
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Security settings saved successfully!';
        error_log("Security settings updated by admin: " . $_SESSION['user_id']);
    } else {
        $_SESSION['error'] = 'Failed to save settings: ' . $stmt->error;
    }
    $stmt->close();
}

// ============ ADD CATEGORY ============
elseif ($action === 'add_category') {
    $category_name = trim($_POST['category_name'] ?? '');
    $category_description = trim($_POST['category_description'] ?? '');

    if (empty($category_name)) {
        $_SESSION['error'] = 'Category name is required';
    } else {
        // Check for duplicate
        $check = $conn->prepare("SELECT COUNT(*) as count FROM asset_categories WHERE category_name = ?");
        $check->bind_param("s", $category_name);
        $check->execute();
        $dup = $check->get_result()->fetch_assoc()['count'];
        $check->close();

        if ($dup > 0) {
            $_SESSION['error'] = 'Category already exists';
        } else {
            $stmt = $conn->prepare("
                INSERT INTO asset_categories (category_name, description)
                VALUES (?, ?)
            ");
            $stmt->bind_param("ss", $category_name, $category_description);

            if ($stmt->execute()) {
                $_SESSION['success'] = 'Category added successfully!';
                error_log("Category added: $category_name by admin: " . $_SESSION['user_id']);
            } else {
                $_SESSION['error'] = 'Failed to add category: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// ============ DELETE CATEGORY ============
elseif ($action === 'delete_category') {
    $category_id = intval($_POST['category_id'] ?? 0);

    if ($category_id === 0) {
        $_SESSION['error'] = 'Invalid category ID';
    } else {
        // Check if category has assets
        $check = $conn->prepare("SELECT COUNT(*) as count FROM assets WHERE category_id = ?");
        $check->bind_param("i", $category_id);
        $check->execute();
        $asset_count = $check->get_result()->fetch_assoc()['count'];
        $check->close();

        if ($asset_count > 0) {
            $_SESSION['error'] = "Cannot delete category with $asset_count assets. Remove assets first.";
        } else {
            $stmt = $conn->prepare("DELETE FROM asset_categories WHERE category_id = ?");
            $stmt->bind_param("i", $category_id);

            if ($stmt->execute()) {
                $_SESSION['success'] = 'Category deleted successfully!';
                error_log("Category deleted with ID: $category_id by admin: " . $_SESSION['user_id']);
            } else {
                $_SESSION['error'] = 'Failed to delete category: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// ============ REDIRECT ============
header("Location: settings.php");
exit();
?>
