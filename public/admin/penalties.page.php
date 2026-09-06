<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Penalties - Admin Dashboard</title>
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

        .penalty-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .penalty-badge.overdue {
            background: rgba(245, 158, 11, 0.2);
            color: #d97706;
        }

        .penalty-badge.damage {
            background: rgba(239, 68, 68, 0.2);
            color: #dc2626;
        }

        .penalty-badge.loss {
            background: rgba(139, 92, 246, 0.2);
            color: #8b5cf6;
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
                    <h1>Manage Penalties</h1>
                    <p>Track and manage user penalties</p>
                </div>

                <!-- Stats -->
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <i class='bx bx-error'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Pending Penalties</h3>
                            <p class="stat-number"><?php echo $pending_count; ?></p>
                            <span class="stat-label"><?php echo number_format($pending_total, 0); ?> to collect</span>
                        </div>
                    </div>
                      
                    
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class='bx bx-check-circle'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Paid Penalties</h3>
                            <p class="stat-number"><?php echo $paid_count; ?></p>
                            <span class="stat-label"><?php echo number_format($paid_total, 0); ?> collected</span>
                        </div>
                    </div>
                </div>
               

                <!-- Filter Tabs -->
                <div style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                    <div class="filter-tabs">
                        <button class="filter-tab <?php echo $status_filter === 'all' ? 'active' : ''; ?>" onclick="filterPenalties('all')">
                            All Penalties
                        </button>
                        <button class="filter-tab <?php echo $status_filter === 'pending' ? 'active' : ''; ?>" onclick="filterPenalties('pending')">
                            Pending
                        </button>
                        <button class="filter-tab <?php echo $status_filter === 'paid' ? 'active' : ''; ?>" onclick="filterPenalties('paid')">
                            Paid
                        </button>
                    </div>
                </div>

                <!-- Penalties Table -->
                <div class="chart-card">
                    <div class="assets-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Penalty Type</th>
                                    <th>Amount</th>
                                    <th>Reason</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($penalties_result->num_rows > 0): ?>
                                    <?php while ($penalty = $penalties_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($penalty['user_name']); ?></strong><br>
                                                <small><?php echo htmlspecialchars($penalty['department_name']); ?></small>
                                            </td>
                                            <td>
                                                <span class="penalty-badge <?php echo strtolower($penalty['penalty_type']); ?>">
                                                    <?php echo htmlspecialchars($penalty['penalty_type']); ?>
                                                </span>
                                            </td>
                                            <td><strong><?php echo number_format($penalty['penalty_amount'], 2); ?></strong></td>
                                            <td><?php echo htmlspecialchars(substr($penalty['reason'], 0, 40) . '...'); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($penalty['issued_date'])); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo strtolower($penalty['status']); ?>">
                                                    <?php echo $penalty['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="#" class="action-link">View Details</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="no-data">No penalties found</td>
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
        function filterPenalties(status) {
            window.location.href = `?status=${status}`;
        }
    </script>
    <script src="js/admin_dashboard.js"></script>
</body>
</html>