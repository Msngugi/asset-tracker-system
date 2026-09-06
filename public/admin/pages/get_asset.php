<?php
/**
 * AJAX endpoint to fetch asset data for editing
 */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Correct path to config
require dirname(__FILE__) . '/../../../config/db_connect.php';

header('Content-Type: application/json');

$asset_id = intval($_GET['id'] ?? 0);

if ($asset_id === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid asset ID']);
    exit();
}

try {
    $query = $conn->prepare("
        SELECT asset_id, asset_name, category_id, asset_model, serial_number, 
               assigned_to, status, asset_condition, purchase_date, cost, notes
        FROM assets 
        WHERE asset_id = ?
    ");
    
    if (!$query) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $query->bind_param("i", $asset_id);
    
    if (!$query->execute()) {
        throw new Exception("Execute failed: " . $query->error);
    }
    
    $result = $query->get_result()->fetch_assoc();
    $query->close();

    if (!$result) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Asset not found']);
        exit();
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => [
            'asset_id' => (int)$result['asset_id'],
            'asset_name' => $result['asset_name'],
            'category_id' => (int)$result['category_id'],
            'asset_model' => $result['asset_model'],
            'serial_number' => $result['serial_number'],
            'assigned_to' => $result['assigned_to'] ? (int)$result['assigned_to'] : null,
            'status' => $result['status'],
            'asset_condition' => $result['asset_condition'],
            'purchase_date' => $result['purchase_date'],
            'cost' => $result['cost'],
            'notes' => $result['notes']
        ]
    ]);
} catch (Exception $e) {
    error_log("Error in get_asset.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>