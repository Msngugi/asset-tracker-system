<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Borrows - Admin Dashboard</title>
    <link href='https://cdn.boxicons.com/3.0.6/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/admin_dashboard.css">
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

        .borrow-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .borrow-badge.active {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }

        .borrow-badge.overdue {
            background: rgba(239, 68, 68, 0.2);
            color: #dc2626;
        }

        .borrow-badge.returned {
            background: rgba(16, 185, 129, 0.2);
            color: #059669;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <div class="admin-main">
            <?php include 'includes/admin_header.php'; ?>
            
            <main class="admin-content">
                <div class="dashboard-header">
                    <h1>Manage Borrows</h1>
                    <p>Track all asset borrowing and returns</p>
                </div>

                <!-- Stats -->
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <i class='bx bx-user-check'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Active Borrows</h3>
                            <p class="stat-number"><?php echo $active_count; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon in-use">
                            <i class='bx bx-check-circle'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Returned</h3>
                            <p class="stat-number"><?php echo $returned_count; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <i class='bx bx-alarm-exclamation'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Overdue</h3>
                            <p class="stat-number"><?php echo $overdue_count; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Filter Tabs -->
                <div style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                    <div class="filter-tabs">
                        <button class="filter-tab <?php echo $status_filter === 'all' ? 'active' : ''; ?>" onclick="filterBorrows('all')">
                            All Borrows
                        </button>
                        <button class="filter-tab <?php echo $status_filter === 'active' ? 'active' : ''; ?>" onclick="filterBorrows('active')">
                            Active
                        </button>
                        <button class="filter-tab <?php echo $status_filter === 'overdue' ? 'active' : ''; ?>" onclick="filterBorrows('overdue')">
                            Overdue
                        </button>
                        <button class="filter-tab <?php echo $status_filter === 'returned' ? 'active' : ''; ?>" onclick="filterBorrows('returned')">
                            Returned
                        </button>
                    </div>
                </div>

                <!-- Borrows Table -->
                <div class="chart-card">
                    <div class="assets-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Asset</th>
                                    <th>User</th>
                                    <th>Borrow Date</th>
                                    <th>Due Date</th>
                                    <th>Days Left</th>
                                    <th>Status</th>
                                    <th>Condition</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($borrows_result->num_rows > 0): ?>
                                    <?php while ($borrow = $borrows_result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="asset-name">
                                                <i class='bx bx-package'></i>
                                                <?php echo htmlspecialchars($borrow['asset_name']); ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($borrow['user_name']); ?></strong><br>
                                                <small><?php echo htmlspecialchars($borrow['department_name']); ?></small>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($borrow['borrow_date'])); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($borrow['expected_return_date'])); ?></td>
                                            <td>
                                                <?php 
                                                    if ($borrow['days_until_due'] < 0) {
                                                        echo '<span style="color: #dc2626; font-weight: 600;">' . abs($borrow['days_until_due']) . ' days overdue</span>';
                                                    } else {
                                                        echo $borrow['days_until_due'] . ' days';
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    if ($borrow['status'] === 'in-use' && $borrow['days_until_due'] < 0) {
                                                        echo '<span class="borrow-badge overdue">OVERDUE</span>';
                                                    } else {
                                                        echo '<span class="borrow-badge ' . strtolower($borrow['status']) . '">' . $borrow['status'] . '</span>';
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <span class="condition-badge <?php echo strtolower($borrow['asset_condition_on_borrow']); ?>">
                                                    <?php echo $borrow['asset_condition_on_borrow']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="#" class="action-link">View Details</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="no-data">No borrows found</td>
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
    
    <script>
        function filterBorrows(status) {
            window.location.href = `?status=${status}`;
        }
    </script>
    <script src="js/admin_dashboard.js"></script>
</body>
</html>