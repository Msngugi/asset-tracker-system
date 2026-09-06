<?php
session_start();
require '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_name  = trim($_POST['user_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm_pw = $_POST['confirm_password'] ?? '';
    $department = $_POST['department'] ?? '';
    $role       = 'user'; // ALWAYS 'user' - admins cannot self-register

    // Validation...
    if (empty($user_name) || empty($email) || empty($password) || empty($confirm_pw) || empty($department)) {
        $_SESSION['register_message'] = ['type' => 'error', 'text' => 'Please fill in all the fields.'];
        header("Location: index.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_message'] = ['type' => 'error', 'text' => 'Valid email is required.'];
        header("Location: index.php");
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['register_message'] = ['type' => 'error', 'text' => 'Password must be at least 8 characters.'];
        header("Location: index.php");
        exit();
    }

    if ($password !== $confirm_pw) {
        $_SESSION['register_message'] = ['type' => 'error', 'text' => 'Passwords must match.'];
        header("Location: index.php");
        exit();
    }

    // Check if user already exists
    $check = $conn->prepare("SELECT user_id, user_name, email FROM users WHERE user_name = ? OR email = ?");
    if (!$check) {
        $_SESSION['register_message'] = ['type' => 'error', 'text' => 'Database error occurred.'];
        header("Location: index.php");
        exit();
    }

    $check->bind_param("ss", $user_name, $email);
    $check->execute();
    $result = $check->get_result();
    $existing_user = $result->fetch_assoc();

    if ($existing_user) {
        if ($existing_user['user_name'] === $user_name && $existing_user['email'] === $email) {
            $error_msg = 'Username and email are already registered.';
        } elseif ($existing_user['user_name'] === $user_name) {
            $error_msg = 'Username is already taken.';
        } else {
            $error_msg = 'Email is already registered.';
        }
        
        $_SESSION['register_message'] = ['type' => 'error', 'text' => $error_msg];
        $check->close();
        header("Location: index.php");
        exit();
    }

    $check->close();

    // Hash password and insert user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO users (user_name, email, password, role, department_name) 
         VALUES (?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        $_SESSION['register_message'] = ['type' => 'error', 'text' => 'Database error occurred.'];
        header("Location: index.php");
        exit();
    }

    $stmt->bind_param("sssss", $user_name, $email, $hashedPassword, $role, $department);

    if ($stmt->execute()) {
        session_unset();
         session_destroy();
        $stmt->close();
        header("Location: index.php?success=registered");
        exit();
    } else {
        $_SESSION['register_message'] = ['type' => 'error', 'text' => 'Registration failed. Please try again.'];
        $stmt->close();
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
    