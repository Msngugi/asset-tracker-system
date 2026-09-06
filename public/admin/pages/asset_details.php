<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

require '../../../config/db_connect.php';
require '../../../config/SimpleQRCode.php';
require '../../../config/AssetImageHandler.php';

$asset_id = intval($_GET['id'] ?? 0);

if ($asset_id === 0) {
    $_SESSION['error'] = 'Invalid asset ID';
    header("Location: ../assets.php");
    exit();
}

// ============ GET ASSET DATA ============

$asset_query = $conn->prepare("
    SELECT a.*, ac.category_name, u.user_name, u.department_name
    FROM assets a
    JOIN asset_categories ac ON a.category_id = ac.category_id
    LEFT JOIN users u ON a.assigned_to = u.user_id
    WHERE a.asset_id = ?
");
$asset_query->bind_param("i", $asset_id);
$asset_query->execute();
$asset_result = $asset_query->get_result();
$asset = $asset_result->fetch_assoc();
$asset_query->close();

if (!$asset) {
    $_SESSION['error'] = 'Asset not found';
    header("Location: ../assets.php");
    exit();
}

// Get asset image
$image_query = $conn->prepare("
    SELECT image_path, image_filename FROM asset_images WHERE asset_id = ? LIMIT 1
");
$image_query->bind_param("i", $asset_id);
$image_query->execute();
$image_result = $image_query->get_result()->fetch_assoc();
$image_query->close();

// Get QR code info
$qr_query = $conn->prepare("
    SELECT * FROM qr_codes WHERE asset_id = ?
");
$qr_query->bind_param("i", $asset_id);
$qr_query->execute();
$qr_result = $qr_query->get_result()->fetch_assoc();
$qr_query->close();

// Get asset logs
$logs_query = $conn->prepare("
    SELECT al.*, u.user_name
    FROM asset_logs al
    LEFT JOIN users u ON al.user_id = u.user_id
    WHERE al.asset_id = ?
    ORDER BY al.log_time DESC
    LIMIT 10
");
$logs_query->bind_param("i", $asset_id);
$logs_query->execute();
$logs_result = $logs_query->get_result();
$logs_query->close();

$image_handler = new AssetImageHandler();
$qr_generator = new SimpleQRCode();

// Get image and QR paths from database (these are already web paths)
$asset_image_path = $image_result ? $image_result['image_path'] : null;
$qr_image_path = $qr_result ? $qr_result['image_path'] : null;

error_log("Asset Details - Image Path: " . ($asset_image_path ?? 'null'));
error_log("Asset Details - QR Path: " . ($qr_image_path ?? 'null'));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Details - <?php echo htmlspecialchars($asset['asset_name']); ?></title>
    <link href='https://cdn.boxicons.com/3.0.6/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <style>
        .details-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin: 30px;
        }

        .details-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .details-card h2 {
            font-size: 18px;
            color: #1f2937;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .details-card h2 i {
            color: #6366f1;
            font-size: 24px;
        }

        .detail-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f3f4f6;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 14px;
            color: #1f2937;
            font-weight: 500;
        }

        .image-display {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid #e5e7eb;
        }

        .image-placeholder {
            width: 100%;
            height: 300px;
            background: #f3f4f6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #9ca3af;
            margin-bottom: 10px;
            border: 1px dashed #e5e7eb;
        }

        .image-placeholder i {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .qr-section {
            text-align: center;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .qr-display {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 250px;
        }

        .qr-display img {
            max-width: 250px;
            max-height: 250px;
            border: 2px solid #ddd;
            padding: 10px;
            border-radius: 8px;
            background: white;
        }

        .qr-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #9ca3af;
            min-height: 250px;
        }

        .qr-placeholder i {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.available {
            background: rgba(16, 185, 129, 0.2);
            color: #059669;
        }

        .badge.excellent {
            background: rgba(99, 102, 241, 0.2);
            color: #6366f1;
        }

        .timeline {
            position: relative;
        }

        .timeline-item {
            position: relative;
            padding-left: 30px;
            margin-bottom: 20px;
        }

        .timeline-item:before {
            content: '';
            position: absolute;
            left: 0;
            top: 5px;
            width: 12px;
            height: 12px;
            background: #6366f1;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #6366f1;
        }

        .timeline-item:after {
            content: '';
            position: absolute;
            left: 5px;
            top: 20px;
            width: 2px;
            height: calc(100% + 10px);
            background: #e5e7eb;
        }

        .timeline-item:last-child:after {
            display: none;
        }

        .timeline-content {
            background: #f9fafb;
            padding: 12px;
            border-radius: 6px;
        }

        .timeline-date {
            font-size: 12px;
            color: #6b7280;
            font-weight: 600;
        }

        .timeline-text {
            font-size: 13px;
            color: #1f2937;
            margin-top: 5px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .header h1 {
            font-size: 28px;
            color: #1f2937;
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #6366f1;
            color: white;
        }

        .btn-primary:hover {
            background: #4f46e5;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #1f2937;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 12px;
            border-radius: 4px;
            margin-top: 10px;
            font-size: 12px;
            color: #1e40af;
        }

        .info-box i {
            margin-right: 8px;
        }

        @media (max-width: 1024px) {
            .details-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="admin-main">
            <?php include '../includes/admin_header.php'; ?>
            
            <main class="admin-content">
                <div class="header">
                    <div>
                        <h1><?php echo htmlspecialchars($asset['asset_name']); ?></h1>
                        <p style="color: #6b7280; margin-top: 5px;">Asset ID: #<?php echo $asset_id; ?> | Code: <?php echo htmlspecialchars($asset['asset_code'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="header-actions">
                        <a href="../assets.php" class="btn btn-secondary">← Back to Assets</a>
                        <button class="btn btn-primary" onclick="window.print()"><i class='bx bx-printer'></i> Print</button>
                    </div>
                </div>

                <div class="details-container">
                    <!-- Left Column -->
                    <div>
                        <!-- Image Section -->
                        <div class="details-card">
                            <h2><i class='bx bx-image'></i>Asset Image</h2>
                            <div>
                                <?php if ($asset_image_path): ?>
                                    <img src="<?php echo htmlspecialchars($asset_image_path); ?>" alt="Asset" class="image-display" onerror="this.style.display='none'; document.getElementById('img-placeholder').style.display='flex';">
                                    <div id="img-placeholder" style="display:none;" class="image-placeholder">
                                        <i class='bx bx-image-add'></i>
                                        <p>Image not found</p>
                                    </div>
                                    <div class="info-box">
                                        <i class='bx bx-check-circle'></i> Image successfully uploaded and loaded
                                    </div>
                                <?php else: ?>
                                    <div class="image-placeholder">
                                        <i class='bx bx-image-add'></i>
                                        <p>No image uploaded yet</p>
                                    </div>
                                    <div class="info-box">
                                        <i class='bx bx-info-circle'></i> Upload an image to display it here
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="details-card" style="margin-top: 20px;">
                            <h2><i class='bx bx-info-circle'></i>Asset Information</h2>
                            
                            <div class="detail-item">
                                <div class="detail-label">Category</div>
                                <div class="detail-value"><?php echo htmlspecialchars($asset['category_name']); ?></div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-label">Asset Code</div>
                                <div class="detail-value"><?php echo htmlspecialchars($asset['asset_code'] ?? 'N/A'); ?></div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-label">Model</div>
                                <div class="detail-value"><?php echo htmlspecialchars($asset['asset_model'] ?? 'N/A'); ?></div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-label">Serial Number</div>
                                <div class="detail-value"><?php echo htmlspecialchars($asset['serial_number'] ?? 'N/A'); ?></div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-label">Cost</div>
                                <div class="detail-value">$<?php echo $asset['cost'] ? number_format($asset['cost'], 2) : 'N/A'; ?></div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-label">Purchase Date</div>
                                <div class="detail-value"><?php echo $asset['purchase_date'] ? date('F d, Y', strtotime($asset['purchase_date'])) : 'N/A'; ?></div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-label">Status</div>
                                <div class="detail-value">
                                    <span class="badge available"><?php echo htmlspecialchars($asset['status'] ?? 'N/A'); ?></span>
                                </div>
                            </div>

                            <div class="detail-item" style="border-bottom: none;">
                                <div class="detail-label">Condition</div>
                                <div class="detail-value">
                                    <span class="badge excellent"><?php echo htmlspecialchars($asset['asset_condition'] ?? 'Excellent'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div>
                                                <!-- QR Code Section -->
                                                <div class="details-card">
                            <h2><i class='bx bx-qr'></i>QR Code</h2>
                            <div class="qr-section">
                                <div class="qr-display">
                                    <?php if ($qr_image_path && $qr_result): ?>
                                        <div style="text-align: center;">
                                            <img src="<?php echo htmlspecialchars($qr_image_path); ?>" alt="QR Code" style="max-width: 250px; max-height: 250px; border: 2px solid #ddd; padding: 10px; border-radius: 8px; background: white;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div style="display: none; align-items: center; justify-content: center; flex-direction: column; color: #9ca3af; min-height: 250px;">
                                                <i class='bx bx-qr' style="font-size: 48px; margin-bottom: 10px;"></i>
                                                <p>QR code image not found</p>
                                            </div>
                                            <p style="font-size: 11px; color: #6b7280; margin-top: 15px; word-break: break-all;">
                                                <strong>QR Code:</strong> <?php echo htmlspecialchars($qr_result['qr_code']); ?>
                                            </p>
                                            <p style="font-size: 11px; color: #6b7280; margin-top: 8px;">
                                                <strong>Path:</strong> <?php echo htmlspecialchars($qr_result['image_path']); ?>
                                            </p>
                                            <p style="font-size: 11px; color: #6b7280;">
                                                <strong>Scans:</strong> <?php echo $qr_result['scan_count'] ?? 0; ?>
                                            </p>
                                            <?php if ($qr_result['last_scanned']): ?>
                                                <p style="font-size: 11px; color: #6b7280;">
                                                    <strong>Last Scanned:</strong> <?php echo date('M d, Y H:i', strtotime($qr_result['last_scanned'])); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="qr-placeholder">
                                            <i class='bx bx-qr'></i>
                                            <p style="margin-top: 10px;">QR code not generated yet</p>
                                            <small style="color: #d1d5db; margin-top: 5px;">QR code will be generated automatically when asset is created</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Assignment -->
                        <div class="details-card" style="margin-top: 20px;">
                            <h2><i class='bx bx-user'></i>Assignment</h2>
                            
                            <div class="detail-item">
                                <div class="detail-label">Assigned To</div>
                                <div class="detail-value">
                                    <?php if ($asset['assigned_to']): ?>
                                        <?php echo htmlspecialchars($asset['user_name']); ?><br>
                                        <small style="color: #6b7280;"><?php echo htmlspecialchars($asset['department_name']); ?></small>
                                    <?php else: ?>
                                        <span style="color: #6b7280;">Unassigned</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="detail-item" style="border-bottom: none;">
                                <div class="detail-label">Notes</div>
                                <div class="detail-value" style="color: #6b7280; font-weight: 400;">
                                    <?php echo htmlspecialchars($asset['notes'] ?? 'No notes'); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Activity Log -->
                        <div class="details-card" style="margin-top: 20px;">
                            <h2><i class='bx bx-history'></i>Activity Log</h2>
                            <div class="timeline">
                                <?php if ($logs_result->num_rows > 0): ?>
                                    <?php while ($log = $logs_result->fetch_assoc()): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-content">
                                                <div class="timeline-text">
                                                    <strong><?php echo htmlspecialchars($log['action']); ?></strong>
                                                    <?php if ($log['user_name']): ?>
                                                        by <?php echo htmlspecialchars($log['user_name']); ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="timeline-date"><?php echo date('M d, Y H:i', strtotime($log['log_time'])); ?></div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p style="color: #6b7280; text-align: center;">No activity yet</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <?php include '../includes/admin_footer.php'; ?>
</body>
</html>