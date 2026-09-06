<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penalties - Asset Tracker</title>
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

        .penalty-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #f59e0b;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .penalty-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .penalty-card.paid {
            border-left-color: #10b981;
        }

        .penalty-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .penalty-amount {
            font-size: 24px;
            font-weight: 700;
            color: #dc2626;
        }

        .penalty-card.paid .penalty-amount {
            color: #10b981;
        }

        .penalty-info {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .penalty-reason {
            font-size: 13px;
            color: #374151;
            background: #f3f4f6;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .penalty-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.pending {
            background: rgba(245, 158, 11, 0.2);
            color: #d97706;
        }

        .badge.paid {
            background: rgba(16, 185, 129, 0.2);
            color: #059669;
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
                    <h1>Penalties</h1>
                    <p>View your penalties and payment history</p>
                </div>

                <!-- Stats -->
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon penalty">
                        <i class="bx bx-hourglass" ></i>
                        </div>
                        <div class="stat-content">
                            <h3>Pending Penalties</h3>
                            <p class="stat-number"><?php echo $pending_count; ?></p>
                            <span class="stat-label"><?php echo number_format($pending_total, 2); ?> to pay</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon available">
                            <i class='bx bx-check-circle'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Paid Penalties</h3>
                            <p class="stat-number"><?php echo $paid_count; ?></p>
                            <span class="stat-label"><?php echo number_format($paid_total, 2); ?> paid</span>
                        </div>
                    </div>
                </div>

                <!-- Filter Tabs -->
                <div class="card">
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

                <!-- Penalties List -->
                <div class="card">
                    <h2><i class='bx bx-list-ol'></i>Penalties List</h2>

                    <?php if ($penalties_result->num_rows > 0): ?>
                        <?php while ($penalty = $penalties_result->fetch_assoc()): ?>
                            <div class="penalty-card <?php echo strtolower($penalty['status']); ?>">
                                <div class="penalty-header">
                                    <div>
                                        <h3 style="color: #1f2937; margin-bottom: 5px;">
                                            <?php echo htmlspecialchars($penalty['asset_name'] ?? 'Unknown Asset'); ?>
                                        </h3>
                                        <span class="badge <?php echo strtolower($penalty['penalty_type']); ?>">
                                            <?php echo htmlspecialchars($penalty['penalty_type']); ?>
                                        </span>
                                    </div>
                                    <div class="penalty-amount">
                                        <?php echo number_format($penalty['penalty_amount'], 2); ?>
                                    </div>
                                </div>

                                <div class="penalty-reason">
                                    <i class='bx bx-info-circle'></i> <?php echo htmlspecialchars($penalty['reason']); ?>
                                </div>

                                <div class="penalty-info">
                                    <i class='bx bx-calendar'></i> <strong>Due Date:</strong> <?php echo date('F d, Y', strtotime($penalty['issued_date'])); ?>
                                </div>

                                <?php if ($penalty['status'] === 'Paid'): ?>
                                    <div class="penalty-info">
                                        <i class='bx bx-check'></i> <strong>Paid on:</strong> <?php echo date('F d, Y', strtotime($penalty['paid_date'])); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="penalty-footer">
                                    <span class="badge <?php echo strtolower($penalty['status']); ?>">
                                        <?php echo $penalty['status']; ?>
                                    </span>
                                    <?php if ($penalty['status'] === 'Pending'): ?>
                                        <button style="padding: 8px 16px; background: #6366f1; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 12px;">
                                            Mark as Paid
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class='bx bx-smile'></i>
                            <p>No penalties - Keep up the good work!</p>
                        </div>
                    <?php endif; ?>
                </div>

            </main>
        </div>
    </div>

    <?php include 'includes/user_footer.php'; ?>
    
    <script>
        function filterPenalties(status) {
            window.location.href = `?status=${status}`;
        }
    </script>
    <script src="js/user_dashboard.js"></script>
</body>
</html>