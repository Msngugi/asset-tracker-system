<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Asset Tracker</title>
    <link href='https://cdn.boxicons.com/3.0.6/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/admin_dashboard.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <div class="admin-main">
            <?php include 'includes/admin_header.php'; ?>
            
            <main class="admin-content">
                <div class="dashboard-header">
                    <h1>Dashboard</h1>
                    <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
                </div>
                
                <!-- Stats Cards -->
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon users">
                        <i class="bx bx-group"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Total Users</h3>
                            <p class="stat-number"><?php echo $total_users; ?></p>
                            <span class="stat-label">Registered users</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon assets">
                            <i class='bx bx-package'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Total Assets</h3>
                            <p class="stat-number"><?php echo $total_assets; ?></p>
                            <span class="stat-label">Assets tracked</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon in-use">
                        <i class="bx bx-user-check"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Currently Borrowed</h3>
                            <p class="stat-number"><?php echo $borrowed_assets; ?></p>
                            <span class="stat-label">Active borrows</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon available">
                        <i class="bx bx-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Available</h3>
                            <p class="stat-number"><?php echo $available_assets; ?></p>
                            <span class="stat-label">Ready to borrow</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <i class='bx bx-alarm-exclamation'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Overdue</h3>
                            <p class="stat-number"><?php echo $overdue_assets; ?></p>
                            <span class="stat-label">Not returned on time</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon danger">
                        <i class="bx bx-hourglass"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Pending Penalties</h3>
                            <p class="stat-number"><?php echo $pending_penalties; ?></p>
                            <span class="stat-label"><?php echo number_format($pending_amount, 0); ?> to collect</span>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-container">
                    <!-- Assets by Category -->
                    <div class="chart-card">
                        <h2>Assets by Category</h2>
                        <div class="category-list">
                            <?php if (!empty($category_data)): ?>
                                <?php foreach ($category_data as $cat): ?>
                                    <div class="category-item">
                                        <div class="category-info">
                                            <span class="category-name"><?php echo htmlspecialchars($cat['category_name']); ?></span>
                                            <span class="category-count"><?php echo $cat['count']; ?> assets</span>
                                        </div>
                                        <div class="progress-bar">
                                            <div class="progress" style="width: <?php echo ($cat['count'] / $total_assets) * 100; ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-data">No assets added yet</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Assets by Condition -->
                    <div class="chart-card">
                        <h2>Assets by Condition</h2>
                        <div class="condition-list">
                            <?php if (!empty($condition_data)): ?>
                                <?php foreach ($condition_data as $cond): ?>
                                    <div class="condition-item">
                                        <div class="condition-badge <?php echo strtolower($cond['asset_condition']); ?>">
                                            <?php echo htmlspecialchars($cond['asset_condition']); ?>
                                        </div>
                                        <span class="condition-count"><?php echo $cond['count']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-data">No condition data available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions-card">
                    <h2>Quick Actions</h2>
                    <div class="quick-actions">
                        <a href="assets.php?action=add" class="quick-action-btn">
                            <i class='bx bx-plus'></i>
                            <span>Add Asset</span>
                        </a>
                        <a href="borrows.php" class="quick-action-btn">
                            <i class='bx bx-user-check'></i>
                            <span>Manage Borrows</span>
                        </a>
                        <a href="users.php" class="quick-action-btn">
                            <i class='bx bx-user-plus'></i>
                            <span>Manage Users</span>
                        </a>
                        <a href="penalties.php" class="quick-action-btn">
                        <i class="bx bx-wallet-alt"></i>
                            <span>Manage Penalties</span>
                        </a>
                    </div>
                </div>

                <!-- Currently Borrowed Assets -->
                <div class="activity-card">
                    <h2>Currently Borrowed Assets</h2>
                    <div class="assets-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Asset Name</th>
                                    <th>Category</th>
                                    <th>Borrowed By</th>
                                    <th>Borrow Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_borrow_result->num_rows > 0): ?>
                                    <?php while ($borrow = $recent_borrow_result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="asset-name">
                                                <i class='bx bx-package'></i>
                                                <?php echo htmlspecialchars($borrow['asset_name']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($borrow['category_name']); ?></td>
                                            <td><?php echo htmlspecialchars($borrow['user_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($borrow['borrow_date'])); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($borrow['expected_return_date'])); ?></td>
                                            <td>
                                                <?php 
                                                    if ($borrow['days_until_due'] < 0) {
                                                        echo '<span class="status-badge danger">OVERDUE</span>';
                                                    } elseif ($borrow['days_until_due'] <= 2) {
                                                        echo '<span class="status-badge warning">DUE SOON</span>';
                                                    } else {
                                                        echo '<span class="status-badge in-use">IN-USE</span>';
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <a href="borrows.php?view=<?php echo $borrow['borrow_id']; ?>" class="action-link">View</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="no-data">No active borrows</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="activity-card">
                    <h2>Recent Activities</h2>
                    <div class="activity-list">
                        <?php if ($activity_result->num_rows > 0): ?>
                            <?php while ($activity = $activity_result->fetch_assoc()): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <?php if ($activity['activity_type'] === 'Borrow'): ?>
                                            <i class='bx bx-down-arrow' style="color: #3b82f6;"></i>
                                        <?php else: ?>
                                            <i class='bx bx-up-arrow' style="color: #10b981;"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-content">
                                        <p class="activity-text">
                                            <strong><?php echo htmlspecialchars($activity['activity_type']); ?></strong>
                                            <strong><?php echo htmlspecialchars($activity['asset_name']); ?></strong> 
                                            by <strong><?php echo htmlspecialchars($activity['user_name']); ?></strong>
                                            <?php if ($activity['activity_type'] === 'Return' && $activity['activity_status'] === 'Overdue'): ?>
                                                <span style="color: #dc2626; font-weight: 600;"> (OVERDUE)</span>
                                            <?php endif; ?>
                                        </p>
                                        <span class="activity-time">
                                            <?php echo date('M d, Y H:i', strtotime($activity['activity_date'] . ' ' . $activity['activity_time'])); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="no-activity">No recent activities</p>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include 'includes/admin_footer.php'; ?>
    <script src="js/admin_dashboard.js"></script>
</body>
</html>