<?php
require '../config/db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === "" || $password === "") {
        $_SESSION['login_message'] = ['type' => 'error', 'text' => 'Please enter both username and password.'];
        header("Location: index.php");
        exit();
    }

    // Works for both admin and regular users
    $stmt = $conn->prepare("SELECT user_id, user_name, password, role FROM users WHERE user_name = ?");
    if (!$stmt) {
        $_SESSION['login_message'] = ['type' => 'error', 'text' => 'Database error occurred.'];
        header("Location: index.php");
        exit();
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Login successful for both admin and user
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['user_name'];
        $_SESSION['role'] = $user['role'];

        $stmt->close();

        // Redirect by role
        if ($user['role'] === 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: user/dashboard.php");
        }
        exit();
    } else {
        $_SESSION['login_message'] = ['type' => 'error', 'text' => 'Invalid username or password.'];
        $stmt->close();
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>