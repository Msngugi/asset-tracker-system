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

$asset_id = intval($_GET['asset_id'] ?? 0);

if ($asset_id === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid asset ID']);
    exit();
}

$query = $conn->prepare("SELECT image_path FROM qr_codes WHERE asset_id = ?");
$query->bind_param("i", $asset_id);
$query->execute();
$result = $query->get_result()->fetch_assoc();
$query->close();

if ($result) {
    echo json_encode([
        'success' => true,
        'image_path' => htmlspecialchars($result['image_path'])
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'QR code not found'
    ]);
}
?>