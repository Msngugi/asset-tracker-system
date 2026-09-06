<?php
require '../config/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $type = $_POST['type'] ?? '';
    $value = $_POST['value'] ?? '';

    if ($type === 'username') {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_name = ?");
        $stmt->bind_param("s", $value);
    } elseif ($type === 'email') {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $value);
    } else {
        echo json_encode(['available' => false]);
        exit();
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();

    echo json_encode(['available' => !$exists]);
} else {
    echo json_encode(['available' => false]);
}
?> 