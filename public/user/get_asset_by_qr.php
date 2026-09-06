<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require '../../config/db_connect.php';

header('Content-Type: application/json');

$qr_code = trim($_GET['qr_code'] ?? '');

if (empty($qr_code)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'QR code is required']);
    exit();
}

// Get asset details by QR code
$query = $conn->prepare("
    SELECT a.asset_id, a.asset_name, a.asset_code, a.asset_model, 
           a.serial_number, a.status, a.asset_condition, a.assigned_to,
           ac.category_name, ai.image_path, q.qr_code
    FROM assets a
    LEFT JOIN asset_categories ac ON a.category_id = ac.category_id
    LEFT JOIN asset_images ai ON a.asset_id = ai.asset_id
    LEFT JOIN qr_codes q ON a.asset_id = q.asset_id
    WHERE q.qr_code = ?
");

$query->bind_param("s", $qr_code);
$query->execute();
$result = $query->get_result();
$asset = $result->fetch_assoc();
$query->close();

if ($asset) {
    echo json_encode([
        'success' => true,
        'data' => $asset
    ]);
} else {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Asset not found'
    ]);
}
?>