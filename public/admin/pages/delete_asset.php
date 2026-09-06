<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require $_SERVER['DOCUMENT_ROOT'] . '/asset_tracker_system/config/db_connect.php';
require $_SERVER['DOCUMENT_ROOT'] . '/asset_tracker_system/config/AssetImageHandler.php';
require $_SERVER['DOCUMENT_ROOT'] . '/asset_tracker_system/config/SimpleQRCode.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$asset_id = intval($_POST['asset_id'] ?? 0);

if ($asset_id === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid asset ID']);
    exit();
}

error_log("Deleting asset ID: $asset_id");

// Start transaction
$conn->begin_transaction();

try {
    $image_handler = new AssetImageHandler();
    $qr_generator = new SimpleQRCode();
    
    // Get asset details
    $asset_query = $conn->prepare("SELECT asset_name FROM assets WHERE asset_id = ?");
    $asset_query->bind_param("i", $asset_id);
    $asset_query->execute();
    $asset_result = $asset_query->get_result()->fetch_assoc();
    $asset_query->close();
    
    if (!$asset_result) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Asset not found']);
        exit();
    }
    
    $asset_name = $asset_result['asset_name'];
    
    // Delete asset image
    $img_query = $conn->prepare("SELECT image_path FROM asset_images WHERE asset_id = ?");
    $img_query->bind_param("i", $asset_id);
    $img_query->execute();
    $img_result = $img_query->get_result()->fetch_assoc();
    $img_query->close();
    
    if ($img_result) {
        $image_handler->deleteImage($img_result['image_path']);
        $img_delete = $conn->prepare("DELETE FROM asset_images WHERE asset_id = ?");
        $img_delete->bind_param("i", $asset_id);
        $img_delete->execute();
        $img_delete->close();
        error_log("Image deleted");
    }
    
    // Delete QR code
    $qr_query = $conn->prepare("SELECT image_path FROM qr_codes WHERE asset_id = ?");
    $qr_query->bind_param("i", $asset_id);
    $qr_query->execute();
    $qr_result = $qr_query->get_result()->fetch_assoc();
    $qr_query->close();
    
    if ($qr_result) {
        $qr_generator->deleteQRCode($qr_result['image_path']);
        $qr_delete = $conn->prepare("DELETE FROM qr_codes WHERE asset_id = ?");
        $qr_delete->bind_param("i", $asset_id);
        $qr_delete->execute();
        $qr_delete->close();
        error_log("QR code deleted");
    }
    
    // Delete asset logs
    $log_delete = $conn->prepare("DELETE FROM asset_logs WHERE asset_id = ?");
    $log_delete->bind_param("i", $asset_id);
    $log_delete->execute();
    $log_delete->close();
    
    // Delete asset
    $asset_delete = $conn->prepare("DELETE FROM assets WHERE asset_id = ?");
    $asset_delete->bind_param("i", $asset_id);
    
    if (!$asset_delete->execute()) {
        throw new Exception("Failed to delete asset: " . $asset_delete->error);
    }
    $asset_delete->close();
    
    // Commit transaction
    $conn->commit();
    
    error_log("Asset $asset_id deleted successfully");
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Asset deleted successfully',
        'asset_id' => $asset_id
    ]);
    
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    error_log("Error deleting asset: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
