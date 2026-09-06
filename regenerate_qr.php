<?php
require 'config/db_connect.php';
require 'config/SimpleQRCode.php';

// Get all assets without QR codes
$query = $conn->prepare("
    SELECT a.asset_id, a.asset_name
    FROM assets a
    LEFT JOIN qr_codes q ON a.asset_id = q.asset_id
    WHERE q.qr_id IS NULL
    LIMIT 10
");
$query->execute();
$result = $query->get_result();

echo "<h2>Regenerating QR Codes for Assets Without Them</h2>";

$qr = new SimpleQRCode();
$count = 0;

while ($asset = $result->fetch_assoc()) {
    $qr_result = $qr->generateQRCode($asset['asset_id'], $asset['asset_name']);
    
    if ($qr_result['success']) {
        // Save to database
        $insert = $conn->prepare("
            INSERT INTO qr_codes (asset_id, qr_code, image_path, image_filename)
            VALUES (?, ?, ?, ?)
        ");
        $filename = basename($qr_result['image_path']);
        $insert->bind_param("isss", $asset['asset_id'], $qr_result['qr_code'], $qr_result['image_path'], $filename);
        
        if ($insert->execute()) {
            echo "✓ QR code generated for Asset ID: " . $asset['asset_id'] . " (" . htmlspecialchars($asset['asset_name']) . ")<br>";
            $count++;
        } else {
            echo "✗ Failed to save QR code for Asset ID: " . $asset['asset_id'] . "<br>";
        }
        $insert->close();
    } else {
        echo "✗ Failed to generate QR code: " . $qr_result['message'] . "<br>";
    }
}

$query->close();

echo "<h3>$count QR codes generated!</h3>";
echo '<a href="public/admin/assets.php">Go back to Assets</a>';
?>