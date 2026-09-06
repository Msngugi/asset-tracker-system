<?php
session_start();
require '../config/db_connect.php';

// Get the QR code value from the scanned code
$qr_code = $_GET['qr'] ?? '';

if (empty($qr_code)) {
    die('Invalid QR code');
}

// Find the asset by QR code
$stmt = $conn->prepare("
    SELECT a.*, ac.category_name, ai.image_path
    FROM assets a
    JOIN asset_categories ac ON a.category_id = ac.category_id
    LEFT JOIN asset_images ai ON a.asset_id = ai.asset_id
    WHERE a.asset_id = (
        SELECT asset_id FROM qr_codes WHERE qr_code = ?
    )
");
$stmt->bind_param("s", $qr_code);
$stmt->execute();
$result = $stmt->get_result();
$asset = $result->fetch_assoc();
$stmt->close();

if (!$asset) {
    die('Asset not found');
}

// Update scan count
$update = $conn->prepare("
    UPDATE qr_codes SET last_scanned = NOW(), scan_count = scan_count + 1 WHERE qr_code = ?
");
$update->bind_param("s", $qr_code);
$update->execute();
$update->close();

// Redirect to appropriate page based on user role
if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
    header("Location: user/borrow_asset.php?qr=" . urlencode($qr_code));
} else {
    header("Location: admin/pages/asset_details.php?id=" . $asset['asset_id']);
}
exit();
?>