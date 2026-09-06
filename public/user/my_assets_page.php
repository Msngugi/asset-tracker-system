<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assets - Asset Tracker</title>
    <link href='https://cdn.boxicons.com/3.0.6/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/user_dashboard.css">
    <style>
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }

        .filter-tab {
            padding: 12px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            color: #6b7280;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .filter-tab:hover {
            color: #6366f1;
        }

        .filter-tab.active {
            color: #6366f1;
            border-bottom-color: #6366f1;
        }

        /* ✅ NEW: Asset Card with QR Code */
        .asset-card-wrapper {
            position: relative;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            border: 1px solid #e5e7eb;
        }

        .asset-card-wrapper:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-4px);
        }

        .asset-card-header {
            position: relative;
            height: 200px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .asset-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .asset-image-placeholder {
            font-size: 48px;
            color: #9ca3af;
        }

        /* ✅ NEW: QR Code Badge on Card */
        .qr-code-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: white;
            border: 2px solid #2563eb;
            border-radius: 8px;
            padding: 6px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .qr-code-badge:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .qr-code-badge img {
            width: 40px;
            height: 40px;
            display: block;
        }

        .qr-code-tooltip {
            position: absolute;
            bottom: 100%;
            right: 0;
            background: #1f2937;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 11px;
            white-space: nowrap;
            margin-bottom: 8px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        .qr-code-badge:hover .qr-code-tooltip {
            opacity: 1;
        }

        .asset-card-body {
            padding: 16px;
        }

        .asset-card-category {
            display: inline-block;
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .asset-card-name {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .asset-info {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .asset-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 8px;
            margin-bottom: 12px;
        }

        .asset-badge.overdue {
            background: rgba(239, 68, 68, 0.2);
            color: #dc2626;
        }

        .asset-badge.active {
            background: rgba(34, 197, 94, 0.2);
            color: #16a34a;
        }

        .asset-badge.returned {
            background: rgba(99, 102, 241, 0.2);
            color: #6366f1;
        }

        .asset-actions {
            margin-top: 12px;
        }

        .btn-small {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 13px;
            text-align: center;
            width: 100%;
        }

        .btn-primary-small {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
        }

        .btn-primary-small:hover {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="user-container">
        <?php include 'includes/user_sidebar.php'; ?>
        
        <div class="user-main">
            <?php include 'includes/user_header.php'; ?>
            
            <main class="user-content">
                <div class="page-header">
                    <h1>My Assets</h1>
                    <p>View all your borrowed and returned assets</p>
                </div>

                <!-- Stats -->
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon borrowed">
                            <i class='bx bx-package'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Active Borrows</h3>
                            <p class="stat-number"><?php echo $active_count ?? 0; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon available">
                            <i class='bx bx-check-circle'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Returned</h3>
                            <p class="stat-number"><?php echo $returned_count ?? 0; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon overdue">
                            <i class='bx bx-alarm-exclamation'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Overdue</h3>
                            <p class="stat-number"><?php echo $overdue_count ?? 0; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Filter Tabs -->
                <div class="card">
                    <div class="filter-tabs">
                        <button class="filter-tab <?php echo ($status_filter ?? 'all') === 'all' ? 'active' : ''; ?>" onclick="filterAssets('all')">
                            All Assets
                        </button>
                        <button class="filter-tab <?php echo ($status_filter ?? '') === 'active' ? 'active' : ''; ?>" onclick="filterAssets('active')">
                            Active
                        </button>
                        <button class="filter-tab <?php echo ($status_filter ?? '') === 'overdue' ? 'active' : ''; ?>" onclick="filterAssets('overdue')">
                            Overdue
                        </button>
                        <button class="filter-tab <?php echo ($status_filter ?? '') === 'returned' ? 'active' : ''; ?>" onclick="filterAssets('returned')">
                            Returned
                        </button>
                    </div>
                </div>

                <!-- Assets Grid -->
                <div class="card">
                    <h2><i class='bx bx-grid'></i> Assets List</h2>
                    <?php if (isset($assets_result) && $assets_result->num_rows > 0): ?>
                        <div class="assets-grid">
                            <?php while ($asset = $assets_result->fetch_assoc()): ?>
                                <div class="asset-card-wrapper">
                                    <!-- Asset Header with Image -->
                                    <div class="asset-card-header">
                                        <?php if ($asset['image_path']): ?>
                                            <img src="<?php echo htmlspecialchars($asset['image_path']); ?>" alt="Asset" class="asset-image">
                                        <?php else: ?>
                                            <div class="asset-image-placeholder">
                                                <i class='bx bx-package'></i>
                                            </div>
                                        <?php endif; ?>

                                        <!-- ✅ NEW: QR Code Badge -->
                                        <?php if (!empty($asset['qr_code'])): ?>
                                            <div class="qr-code-badge" title="QR Code">
                                                <?php if (!empty($asset['qr_image_path'])): ?>
                                                    <img src="<?php echo htmlspecialchars($asset['qr_image_path']); ?>" alt="QR Code">
                                                    <span class="qr-code-tooltip">Scan me</span>
                                                <?php else: ?>
                                                    <i class='bx bx-qr' style="font-size: 40px; color: #2563eb;"></i>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Asset Body -->
                                    <div class="asset-card-body">
                                        <span class="asset-card-category"><?php echo htmlspecialchars($asset['category_name'] ?? 'General'); ?></span>
                                        <h3 class="asset-card-name"><?php echo htmlspecialchars($asset['asset_name']); ?></h3>

                                        <div class="asset-info">
                                            <i class='bx bx-folder'></i>
                                            <strong><?php echo htmlspecialchars($asset['category_name'] ?? 'General'); ?></strong>
                                        </div>

                                        <div class="asset-info">
                                            <i class='bx bx-barcode'></i>
                                            <small><?php echo htmlspecialchars(substr($asset['serial_number'] ?? 'N/A', 0, 20)); ?></small>
                                        </div>

                                        <div class="asset-info">
                                            <i class='bx bx-info-circle'></i>
                                            <small><?php echo htmlspecialchars($asset['asset_model'] ?? 'N/A'); ?></small>
                                        </div>

                                        <!-- Status Badge -->
                                        <?php 
                                            $status_display = $asset['status_display'] ?? 'Active';
                                            if ($status_display === 'Overdue') {
                                                echo '<span class="asset-badge overdue">⚠️ Overdue (' . abs($asset['days_until_due'] ?? 0) . ' days)</span>';
                                            } elseif ($status_display === 'Due Soon') {
                                                echo '<span class="asset-badge active">📅 Due in ' . ($asset['days_until_due'] ?? 0) . ' days</span>';
                                            } elseif ($asset['status'] === 'returned') { // ✅ FIXED: lowercase
                                                echo '<span class="asset-badge returned">✓ Returned</span>';
                                            } else {
                                                echo '<span class="asset-badge active">✓ Active</span>';
                                            }
                                        ?>

                                        <div class="asset-info" style="margin-top: 12px;">
                                            <i class='bx bx-calendar'></i>
                                            <small>Borrowed: <?php echo date('M d, Y', strtotime($asset['borrow_date'])); ?></small>
                                        </div>

                                        <!-- ✅ FIXED: Proper status check with lowercase values -->
                                        <?php if ($asset['status'] === 'in-use' || $asset['status'] === 'in-use'): ?>
                                            <div class="asset-info">
                                                <i class='bx bx-calendar-check'></i>
                                                <?php
                                                    $due_date = $asset['expected_return_date'] ?? null;
                                                    if (!empty($due_date) && $due_date !== '0000-00-00' && $due_date !== null && strtotime($due_date) !== false) {
                                                        echo '<small>Due: ' . htmlspecialchars(date('M d, Y', strtotime($due_date))) . '</small>';
                                                    } else {
                                                        echo '<small style="color: #ef4444;">Due: No due date set</small>';
                                                    }
                                                ?>
                                            </div>

                                            <div class="asset-actions">
                                                <a href="return_asset.php?borrow_id=<?php echo urlencode($asset['borrow_id'] ?? ''); ?>&qr_code=<?php echo urlencode($asset['qr_code'] ?? ''); ?>" class="btn-small btn-primary-small">Return Now</a>
                                            </div>
                                        <?php else: ?>
                                            <div class="asset-info">
                                                <i class='bx bx-calendar-check'></i>
                                                <small>Expected Return: <?php 
                                                    $due_date = $asset['expected_return_date'] ?? null;
                                                    if (!empty($due_date) && $due_date !== '0000-00-00' && $due_date !== null && strtotime($due_date) !== false) {
                                                        echo htmlspecialchars(date('M d, Y', strtotime($due_date)));
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class='bx bx-inbox'></i>
                            <p>No assets found</p>
                            <a href="borrow_asset.php" style="color: var(--primary-blue); text-decoration: none; font-weight: 600;">Borrow Asset →</a>
                        </div>
                    <?php endif; ?>
                </div>

            </main>
        </div>
    </div>

    <?php include 'includes/user_footer.php'; ?>
    
    <script>
        function filterAssets(status) {
            window.location.href = `?status=${status}`;
        }
    </script>
    <script src="js/user_dashboard.js"></script>
</body>
</html>