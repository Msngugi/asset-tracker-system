<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard</title>
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
                    <h1>Reports & Analytics</h1>
                    <p>View detailed insights and statistics about your assets and users</p>
                </div>

                <!-- Date Filter -->
                <div class="chart-card" style="margin-bottom: 30px;">
                    <h3>Filter Reports</h3>
                    <form method="GET" class="filter-form">
                        <div class="form-group">
                            <label>From Date:</label>
                            <input type="date" name="date_from" value="<?php echo $date_from; ?>" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>To Date:</label>
                            <input type="date" name="date_to" value="<?php echo $date_to; ?>" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                        <a href="reports.php" class="btn btn-secondary">Reset</a>
                    </form>
                </div>

                <!-- Asset Status Summary -->
                <div class="chart-card">
                    <h2><i class='bx bx-pie-chart'></i> Asset Status Summary</h2>
                    <div class="report-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                    <th>Visual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($asset_summary as $item): ?>
                                <tr>
                                    <td><strong><?php echo ucfirst($item['status']); ?></strong></td>
                                    <td><?php echo $item['count']; ?></td>
                                    <td><?php echo $item['percentage']; ?>%</td>
                                    <td>
                                        <div class="progress-bar" style="width: 200px;">
                                            <div class="progress" style="width: <?php echo $item['percentage']; ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Assets by Category -->
                <div class="chart-card">
                    <h2><i class='bx bx-box'></i> Assets by Category</h2>
                    <div class="report-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Total</th>
                                    <th>Available</th>
                                    <th>In Use</th>
                                    <th>Damaged</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assets_by_category as $item): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($item['category_name']); ?></strong></td>
                                    <td><?php echo $item['total'] ?? 0; ?></td>
                                    <td><span class="badge-success"><?php echo $item['available'] ?? 0; ?></span></td>
                                    <td><span class="badge-info"><?php echo $item['in_use'] ?? 0; ?></span></td>
                                    <td><span class="badge-danger"><?php echo $item['damaged'] ?? 0; ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Asset Condition Report -->
                <div class="chart-card">
                    <h2><i class='bx bx-check-circle'></i> Assets by Condition</h2>
                    <div class="report-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Condition</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assets_by_condition as $item): ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <?php 
                                            $condition = $item['asset_condition'] ?? 'excellent';
                                            $badge_class = $condition === 'excellent' ? 'badge-success' : 
                                                          ($condition === 'good' ? 'badge-info' : 'badge-warning');
                                            ?>
                                            <span class="<?php echo $badge_class; ?>"><?php echo ucfirst($condition); ?></span>
                                        </strong>
                                    </td>
                                    <td><?php echo $item['count']; ?></td>
                                    <td><?php echo $item['percentage']; ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Asset Value Report -->
                <div class="chart-card">
                    <h2><i class='bx bx-money'></i> Asset Value by Category</h2>
                    <div class="report-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Total Assets</th>
                                    <th>Total Value</th>
                                    <th>Average Value</th>
                                    <th>Highest Value</th>
                                    <th>Lowest Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($asset_value as $item): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($item['category_name']); ?></strong></td>
                                    <td><?php echo $item['total_assets'] ?? 0; ?></td>
                                    <td>₱<?php echo number_format($item['total_value'] ?? 0, 2); ?></td>
                                    <td>₱<?php echo number_format($item['average_value'] ?? 0, 2); ?></td>
                                    <td>₱<?php echo number_format($item['highest_value'] ?? 0, 2); ?></td>
                                    <td>₱<?php echo number_format($item['lowest_value'] ?? 0, 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- User Activity Report -->
                <div class="chart-card">
                    <h2><i class='bx bx-user-circle'></i> User Activity Report</h2>
                    <div class="report-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Department</th>
                                    <th>Total Actions</th>
                                    <th>Last Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($user_activity)): ?>
                                    <?php foreach ($user_activity as $item): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($item['user_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($item['department_name']); ?></td>
                                        <td><?php echo $item['total_actions'] ?? 0; ?></td>
                                        <td><?php echo $item['last_action'] ? date('M d, Y H:i', strtotime($item['last_action'])) : 'Never'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="no-data">No user activity found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Asset Logs Report -->
                <div class="chart-card">
                    <h2><i class='bx bx-history'></i> Recent Asset Logs</h2>
                    <div class="report-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Asset</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>User</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($asset_logs)): ?>
                                    <?php foreach ($asset_logs as $log): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($log['asset_name'] ?? 'N/A'); ?></strong></td>
                                        <td>
                                            <?php 
                                            $action_class = $log['action'] === 'Created' ? 'badge-success' : 
                                                          ($log['action'] === 'Updated' ? 'badge-info' : 'badge-warning');
                                            ?>
                                            <span class="<?php echo $action_class; ?>"><?php echo $log['action']; ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['description']); ?></td>
                                        <td><?php echo htmlspecialchars($log['user_name'] ?? 'System'); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($log['log_time'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="no-data">No logs found for selected period</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <?php include 'includes/admin_footer.php'; ?>
    <script src="js/admin_dashboard.js"></script>
</body>
</html>
