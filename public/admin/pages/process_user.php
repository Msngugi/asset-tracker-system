<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

require __DIR__ . '/../../../config/db_connect.php';
require __DIR__ . '/../../../config/mail_config.php';
require __DIR__ . '/../../../config/EmailNotification.php';

error_log("=== process_user.php START ===");
error_log("REQUEST METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("ACTION: " . ($_POST['action'] ?? 'none'));

// ============ HANDLE FORM SUBMISSION ============

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // ============ ADD NEW USER ============
    if ($action === 'add') {
        error_log("Processing: ADD USER");
        
        $user_name = trim($_POST['user_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $department_name = trim($_POST['department_name'] ?? '');

        // ============ VALIDATION ============
        if (empty($user_name) || empty($email) || empty($password) || empty($department_name)) {
            $_SESSION['error'] = 'All fields are required';
            error_log("Validation failed: empty fields");
            header("Location: ../users.php");
            exit();
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Invalid email format';
            error_log("Validation failed: invalid email");
            header("Location: ../users.php");
            exit();
        }

        // Validate password length
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password must be at least 6 characters long';
            error_log("Validation failed: weak password");
            header("Location: ../users.php");
            exit();
        }

        // Check for duplicate email
        $check_email = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        if (!$check_email) {
            error_log("❌ [USER ADD] Email check prepare failed: " . $conn->error);
            $_SESSION['error'] = 'Database error';
            header("Location: ../users.php");
            exit();
        }
        
        $check_email->bind_param("s", $email);
        $check_email->execute();
        if ($check_email->get_result()->num_rows > 0) {
            $_SESSION['error'] = 'Email already exists';
            $check_email->close();
            error_log("Validation failed: duplicate email");
            header("Location: ../users.php");
            exit();
        }
        $check_email->close();

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        $stmt = $conn->prepare("
            INSERT INTO users (user_name, email, password, department_name, role, created_at)
            VALUES (?, ?, ?, ?, 'user', NOW())
        ");

        if (!$stmt) {
            error_log("❌ [USER ADD] Prepare failed: " . $conn->error);
            $_SESSION['error'] = 'Database error: ' . $conn->error;
            header("Location: ../users.php");
            exit();
        }

        $stmt->bind_param("ssss", $user_name, $email, $hashed_password, $department_name);

        if (!$stmt->execute()) {
            error_log("❌ [USER ADD] Execute failed: " . $stmt->error);
            $_SESSION['error'] = 'Failed to add user: ' . $stmt->error;
            $stmt->close();
            header("Location: ../users.php");
            exit();
        }

        $new_user_id = $conn->insert_id;
        $stmt->close();
        error_log("✅ [USER ADD] New user created with ID: $new_user_id");

        // ✅ SEND WELCOME EMAIL TO NEW USER
        $email_sent = false;
        
        if (class_exists('EmailNotification')) {
            try {
                $email_notif = new EmailNotification($conn);
                
                if (method_exists($email_notif, 'sendWelcomeEmail')) {
                    error_log("📧 [USER ADD] Attempting welcome email to: " . $email);
                    
                    $email_sent = $email_notif->sendWelcomeEmail(
                        $email,
                        $user_name,
                        $new_user_id,
                        $password  // Consider sending a temp password or reset link instead
                    );
                    
                    if ($email_sent) {
                        error_log("✅ [USER ADD] Welcome email sent to: " . $email);
                    } else {
                        error_log("⚠️ [USER ADD] Welcome email method returned false");
                    }
                } else {
                    error_log("⚠️ [USER ADD] Method 'sendWelcomeEmail' not found - will attempt alternative");
                    
                    // Try sending a generic welcome if specific method doesn't exist
                    if (method_exists($email_notif, 'sendGenericEmail')) {
                        $subject = "Welcome to Asset Management System";
                        $body = "Hello $user_name,\n\nYour account has been created.\n\nUsername: $email\n\nPlease change your password on first login.";
                        
                        $email_sent = $email_notif->sendGenericEmail($email, $user_name, $subject, $body);
                        
                        if ($email_sent) {
                            error_log("✅ [USER ADD] Generic welcome email sent");
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("❌ [USER ADD] Email exception: " . $e->getMessage());
            }
        } else {
            error_log("❌ [USER ADD] EmailNotification class not found");
        }

        // Log the action
        $log_stmt = $conn->prepare("
            INSERT INTO user_logs (id, user_id, action, description, created_at)
            VALUES (?, ?, 'Created', ?, NOW())
        ");
        if ($log_stmt) {
            $description = "User created: $user_name ($email)";
            $log_stmt->bind_param("iss", $new_user_id, $_SESSION['user_id'], $description);
            $log_stmt->execute();
            $log_stmt->close();
        }

        $_SESSION['success'] = 'User added successfully!' . ($email_sent ? ' Welcome email sent.' : '');
        error_log("=== process_user.php END (ADD SUCCESS) ===");

    // ============ UPDATE USER ============
    } elseif ($action === 'edit') {
        error_log("Processing: EDIT USER");
        
        $user_id = intval($_POST['user_id'] ?? 0);
        $user_name = trim($_POST['user_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $department_name = trim($_POST['department_name'] ?? '');

        if ($user_id === 0 || empty($user_name) || empty($email) || empty($department_name)) {
            $_SESSION['error'] = 'All fields are required';
            error_log("Validation failed: empty fields");
            header("Location: ../users.php");
            exit();
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Invalid email format';
            error_log("Validation failed: invalid email");
            header("Location: ../users.php");
            exit();
        }

        // Check for duplicate email (exclude current user)
        $check_email = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        if (!$check_email) {
            error_log("❌ [USER EDIT] Email check prepare failed: " . $conn->error);
            $_SESSION['error'] = 'Database error';
            header("Location: ../users.php");
            exit();
        }
        
        $check_email->bind_param("si", $email, $user_id);
        $check_email->execute();
        if ($check_email->get_result()->num_rows > 0) {
            $_SESSION['error'] = 'Email already exists';
            $check_email->close();
            error_log("Validation failed: duplicate email");
            header("Location: ../users.php");
            exit();
        }
        $check_email->close();

        // Update user - with or without password
        if (!empty($password)) {
            // Validate password length
            if (strlen($password) < 6) {
                $_SESSION['error'] = 'Password must be at least 6 characters long';
                error_log("Validation failed: weak password");
                header("Location: ../users.php");
                exit();
            }

            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("
                UPDATE users 
                SET user_name = ?, email = ?, password = ?, department_name = ?
                WHERE user_id = ?
            ");
            if (!$stmt) {
                error_log("❌ [USER EDIT] Prepare failed: " . $conn->error);
                $_SESSION['error'] = 'Database error';
                header("Location: ../users.php");
                exit();
            }
            $stmt->bind_param("ssssi", $user_name, $email, $hashed_password, $department_name, $user_id);
        } else {
            $stmt = $conn->prepare("
                UPDATE users 
                SET user_name = ?, email = ?, department_name = ?
                WHERE user_id = ?
            ");
            if (!$stmt) {
                error_log("❌ [USER EDIT] Prepare failed: " . $conn->error);
                $_SESSION['error'] = 'Database error';
                header("Location: ../users.php");
                exit();
            }
            $stmt->bind_param("sssi", $user_name, $email, $department_name, $user_id);
        }

        if (!$stmt->execute()) {
            error_log("❌ [USER EDIT] Execute failed: " . $stmt->error);
            $_SESSION['error'] = 'Failed to update user: ' . $stmt->error;
            $stmt->close();
            header("Location: ../users.php");
            exit();
        }

        $stmt->close();
        error_log("✅ [USER EDIT] User updated: ID $user_id");

        // ✅ SEND UPDATE NOTIFICATION EMAIL (OPTIONAL)
        $email_sent = false;
        
        if (class_exists('EmailNotification')) {
            try {
                $email_notif = new EmailNotification($conn);
                
                if (method_exists($email_notif, 'sendUpdateEmail')) {
                    error_log("📧 [USER EDIT] Attempting update notification to: " . $email);
                    
                    $email_sent = $email_notif->sendUpdateEmail(
                        $email,
                        $user_name,
                        $user_id
                    );
                    
                    if ($email_sent) {
                        error_log("✅ [USER EDIT] Update notification sent");
                    }
                } else {
                    error_log("⚠️ [USER EDIT] Method 'sendUpdateEmail' not found");
                }
            } catch (Exception $e) {
                error_log("❌ [USER EDIT] Email exception: " . $e->getMessage());
            }
        }

        // Log the action
        $log_stmt = $conn->prepare("
            INSERT INTO user_logs (id, user_id, action, description, created_at)
            VALUES (?, ?, 'Updated', ?, NOW())
        ");
        if ($log_stmt) {
            $description = "User updated: $user_name ($email)";
            $log_stmt->bind_param("iss", $user_id, $_SESSION['user_id'], $description);
            $log_stmt->execute();
            $log_stmt->close();
        }

        $_SESSION['success'] = 'User updated successfully!';
        error_log("=== process_user.php END (EDIT SUCCESS) ===");

    // ============ DELETE USER ============
    } elseif ($action === 'delete') {
        error_log("Processing: DELETE USER");
        
        $user_id = intval($_POST['user_id'] ?? 0);

        if ($user_id === 0) {
            $_SESSION['error'] = 'Invalid user ID';
            error_log("Validation failed: invalid user_id");
            header("Location: ../users.php");
            exit();
        }

        // Prevent deleting own account
        if ($user_id === $_SESSION['user_id']) {
            $_SESSION['error'] = 'Cannot delete your own account';
            error_log("Validation failed: attempting to delete own account");
            header("Location: ../users.php");
            exit();
        }

        // Get user info before deletion for logging
        $get_user = $conn->prepare("SELECT user_name, email FROM users WHERE user_id = ?");
        if (!$get_user) {
            error_log("❌ [USER DELETE] Get user prepare failed: " . $conn->error);
            $_SESSION['error'] = 'Database error';
            header("Location: ../users.php");
            exit();
        }
        
        $get_user->bind_param("i", $user_id);
        $get_user->execute();
        $user_info = $get_user->get_result()->fetch_assoc();
        $get_user->close();

        if (!$user_info) {
            $_SESSION['error'] = 'User not found';
            error_log("Validation failed: user not found");
            header("Location: ../users.php");
            exit();
        }

        // Delete user
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        if (!$stmt) {
            error_log("❌ [USER DELETE] Delete prepare failed: " . $conn->error);
            $_SESSION['error'] = 'Database error';
            header("Location: ../users.php");
            exit();
        }
        
        $stmt->bind_param("i", $user_id);

        if (!$stmt->execute()) {
            error_log("❌ [USER DELETE] Delete execute failed: " . $stmt->error);
            $_SESSION['error'] = 'Failed to delete user: ' . $stmt->error;
            $stmt->close();
            header("Location: ../users.php");
            exit();
        }

        $stmt->close();
        error_log("✅ [USER DELETE] User deleted: ID $user_id");

        // ✅ SEND DELETION NOTIFICATION EMAIL (OPTIONAL)
        $email_sent = false;
        
        if (class_exists('EmailNotification') && !empty($user_info['email'])) {
            try {
                $email_notif = new EmailNotification($conn);
                
                if (method_exists($email_notif, 'sendDeletionEmail')) {
                    error_log("📧 [USER DELETE] Attempting deletion notice to: " . $user_info['email']);
                    
                    $email_sent = $email_notif->sendDeletionEmail(
                        $user_info['email'],
                        $user_info['user_name'],
                        $user_id
                    );
                    
                    if ($email_sent) {
                        error_log("✅ [USER DELETE] Deletion notice sent");
                    }
                }
            } catch (Exception $e) {
                error_log("❌ [USER DELETE] Email exception: " . $e->getMessage());
            }
        }

        // Log the action
        $log_stmt = $conn->prepare("
            INSERT INTO user_logs (id, user_id, action, description, created_at)
            VALUES (?, ?, 'Deleted', ?, NOW())
        ");
        if ($log_stmt) {
            $description = "User deleted: {$user_info['user_name']} ({$user_info['email']})";
            $log_stmt->bind_param("iss", $user_id, $_SESSION['user_id'], $description);
            $log_stmt->execute();
            $log_stmt->close();
        }

        $_SESSION['success'] = 'User deleted successfully!';
        error_log("=== process_user.php END (DELETE SUCCESS) ===");

    } else {
        $_SESSION['error'] = 'Invalid action';
        error_log("Invalid action: $action");
    }

    header("Location: ../users.php");
    exit();
}

error_log("=== process_user.php END (NO POST) ===");
?>