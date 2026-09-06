<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require '../../config/db_connect.php';

header('Content-Type: application/json');

$user_id = intval($_GET['user_id'] ?? 0);

if ($user_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

$stmt = $conn->prepare("
    SELECT user_id, user_name, email, department_name, role, created_at
    FROM users
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    $stmt->close();
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

echo json_encode([
    'success' => true,
    'user' => $user
]);
?>
