<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Asset Tracker</title>
    <link href='https://cdn.boxicons.com/3.0.6/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/user_dashboard.css">
</head>
<body>
    <div class="user-container">
        <?php include 'includes/user_sidebar.php'; ?>
        
        <div class="user-main">
            <?php include 'includes/user_header.php'; ?>
            
            <main class="user-content">
                <div class="page-header">
                    <h1>Dashboard</h1>
                    <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>! Here's your asset summary.</p>
                </div>

                <!-- Stats Cards -->
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon borrowed">
                            <i class='bx bx-package'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Currently Borrowed</h3>
                            <p class="stat-number"><?php echo $total_borrowed; ?></p>
                            <span class="stat-label">Active borrows</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon available">
                            <i class='bx bx-check-circle'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Available Assets</h3>
                            <p class="stat-number"><?php echo $total_available; ?></p>
                            <span class="stat-label">Ready to borrow</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon overdue">
                            <i class='bx bx-alarm-exclamation'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Overdue</h3>
                            <p class="stat-number"><?php echo $total_overdue; ?></p>
                            <span class="stat-label">Not returned on time</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon penalty">
                        <i class="bx bx-hourglass"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Pending Penalties</h3>
                            <p class="stat-number"><?php echo $total_penalties; ?></p>
                            <span class="stat-label"><?php echo number_format($pending_amount, 0); ?> to pay</span>
                        </div>
                    </div>
                </div>

                <!-- Currently Borrowed Assets -->
                <div class="card">
                    <h2><i class='bx bx-package'></i>Currently Borrowed Assets</h2>
                    <?php if ($current_borrows_result && $current_borrows_result->num_rows > 0): ?>
                        <div class="assets-grid">
                            <?php while ($borrow = $current_borrows_result->fetch_assoc()): ?>
                                <div class="asset-card">
                                    <!-- ✅ FIXED: Display asset image or placeholder -->
                                    <?php if (!empty($borrow['image_path']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $borrow['image_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($borrow['image_path']); ?>" alt="Asset" class="asset-image">
                                    <?php else: ?>
                                        <div class="asset-image-placeholder">
                                            <i class='bx bx-package'></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <h3><?php echo htmlspecialchars($borrow['asset_name']); ?></h3>
                                    <div class="asset-info">
                                        <i class='bx bx-folder'></i>
                                        <strong><?php echo htmlspecialchars($borrow['category_name'] ?? 'General'); ?></strong>
                                    </div>
                                    <div class="asset-info">
                                        <i class='bx bx-info-circle'></i>
                                        <small><?php echo htmlspecialchars($borrow['asset_model'] ?? 'N/A'); ?></small>
                                    </div>

                                    <?php 
                                        // ✅ FIXED: Safe date handling
                                        $days = intval($borrow['days_until_due']);
                                        if ($days < 0) {
                                            echo '<span class="asset-badge overdue">⚠️ OVERDUE (' . abs($days) . ' days)</span>';
                                        } elseif ($days <= 2) {
                                            echo '<span class="asset-badge active">📅 DUE SOON (' . $days . ' days)</span>';
                                        } else {
                                            echo '<span class="asset-badge active">✓ DUE IN ' . $days . ' days</span>';
                                        }
                                    ?>

                                    <div class="asset-info" style="margin-top: 12px;">
                                        <i class='bx bx-calendar'></i>
                                        <small>Return by: <?php echo date('M d, Y', strtotime($borrow['expected_return_date'])); ?></small>
                                    </div>

                                    <div class="asset-actions">
                                        <!-- ✅ FIXED: Added borrow_id and qr_code parameters -->
                                        <a href="return_asset.php?borrow_id=<?php echo urlencode($borrow['borrow_id']); ?>" class="btn-small btn-primary-small">Return Now</a>
                                        <button class="btn-small btn-secondary-small" onclick="viewAssetDetails(<?php echo $borrow['asset_id']; ?>)">Details</button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class='bx bx-inbox'></i>
                            <p>No borrowed assets. Start by borrowing one!</p>
                            <a href="borrow_asset.php" style="color: var(--primary-blue); text-decoration: none; font-weight: 600;">Borrow Asset →</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Returns -->
                <div class="card">
                    <h2><i class='bx bx-history'></i>Recent Returns</h2>
                    <?php if ($recent_returns_result && $recent_returns_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Asset Name</th>
                                        <th>Category</th>
                                        <th>Return Date</th>
                                        <th>Days Borrowed</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($return = $recent_returns_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($return['asset_name']); ?></td>
                                            <td><?php echo htmlspecialchars($return['category_name'] ?? 'General'); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($return['return_date'])); ?></td>
                                            <td><?php echo intval($return['days_borrowed']); ?> days</td>
                                            <td>
                                                <?php if ($return['is_overdue']): ?>
                                                    <span class="badge overdue">⚠️ OVERDUE</span>
                                                <?php else: ?>
                                                    <span class="badge active">✓ ON TIME</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class='bx bx-time'></i>
                            <p>No return history yet</p>
                        </div>
                    <?php endif; ?>
                </div>

            </main>
        </div>
    </div>

    <?php include 'includes/user_footer.php'; ?>
    <script src="js/user_dashboard.js"></script>
</body>
</html>