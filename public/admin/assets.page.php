<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assets - Admin Dashboard</title>
    <link href='https://cdn.boxicons.com/3.0.6/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <style>
        .assets-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-light));
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
        }

        .filter-box {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            background: white;
            padding: 20px;
            border-radius: 12px;
        }

        .filter-box select {
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-box select:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--text-dark);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--text-dark);
            font-size: 13px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 13px;
            font-family: inherit;
            transition: all 0.3s;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .btn-close {
            background: var(--light-gray);
            color: var(--text-dark);
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-close:hover {
            background: var(--border-color);
        }

        .btn-submit {
            background: var(--primary-blue);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-submit:hover {
            background: var(--primary-dark);
        }

        /* Table alignment styles */
        .assets-table {
            width: 100%;
            overflow-x: auto;
        }

        .assets-table table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            table-layout: fixed;
        }

        .assets-table thead {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
        }

        .assets-table thead th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            white-space: normal;
            word-wrap: break-word;
        }

        .assets-table tbody tr {
            border-bottom: 1px solid #e5e7eb;
            transition: background-color 0.2s;
        }

        .assets-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .assets-table tbody td {
            padding: 12px 15px;
            font-size: 13px;
            color: #1f2937;
            word-wrap: break-word;
            vertical-align: middle;
        }

        /* Column widths - FIXED */
        .assets-table th:nth-child(1),
        .assets-table td:nth-child(1) {
            width: 16%;
            min-width: 140px;
        }

        .assets-table th:nth-child(2),
        .assets-table td:nth-child(2) {
            width: 10%;
            min-width: 90px;
        }

        .assets-table th:nth-child(3),
        .assets-table td:nth-child(3) {
            width: 11%;
            min-width: 80px;
        }

        .assets-table th:nth-child(4),
        .assets-table td:nth-child(4) {
            width: 9%;
            min-width: 75px;
        }

        .assets-table th:nth-child(5),
        .assets-table td:nth-child(5) {
            width: 12%;
            min-width: 95px;
        }

        .assets-table th:nth-child(6),
        .assets-table td:nth-child(6) {
            width: 11%;
            min-width: 100px;
        }

        .assets-table th:nth-child(7),
        .assets-table td:nth-child(7) {
            width: 14%;
            min-width: 110px;
        }

        .assets-table th:nth-child(8),
        .assets-table td:nth-child(8) {
            width: 11%;
            min-width: 100px;
        }

        .assets-table th:nth-child(9),
        .assets-table td:nth-child(9) {
            width: 12%;
            min-width: 120px;
        }

        .asset-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 10px;
            border: 1px solid #e5e7eb;
        }

        .asset-cell {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .asset-placeholder {
            width: 50px;
            height: 50px;
            background: #e5e7eb;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #9ca3af;
            font-size: 15px;
        }

        /* ✅ UPDATED: Status badges - use lowercase data attributes */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            text-transform: capitalize;
        }

        /* ✅ Match lowercase status values from database */
        .status-badge[data-status="available"] {
            background: rgba(16, 185, 129, 0.2);
            color: #059669;
        }

        .status-badge[data-status="in-use"] {
            background: rgba(59, 130, 246, 0.2);
            color: #2563eb;
        }

        .status-badge[data-status="under-maintenance"] {
            background: rgba(249, 115, 22, 0.2);
            color: #d97706;
        }

        .status-badge[data-status="lost"] {
            background: rgba(139, 92, 246, 0.2);
            color: #6d28d9;
        }

        .status-badge[data-status="damaged"] {
            background: rgba(239, 68, 68, 0.2);
            color: #dc2626;
        }

        .status-badge[data-status="retired"] {
            background: rgba(107, 114, 128, 0.2);
            color: #374151;
        }

        /* Condition badges */
        .condition-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            text-transform: capitalize;
        }

        .condition-badge.excellent {
            background: rgba(99, 102, 241, 0.2);
            color: #6366f1;
        }

        .condition-badge.good {
            background: rgba(34, 197, 94, 0.2);
            color: #16a34a;
        }

        .condition-badge.fair {
            background: rgba(251, 191, 36, 0.2);
            color: #b45309;
        }

        .condition-badge.poor {
            background: rgba(239, 68, 68, 0.2);
            color: #dc2626;
        }

        .condition-badge.damaged {
            background: rgba(239, 68, 68, 0.2);
            color: #dc2626;
        }

        /* Action buttons - FIXED */
        .action-buttons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            align-items: center;
        }

        .edit-btn, .delete-btn, .view-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
            white-space: nowrap;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .edit-btn {
            background: rgba(99, 102, 241, 0.2);
            color: var(--primary-blue);
        }

        .edit-btn:hover:not(:disabled) {
            background: rgba(99, 102, 241, 0.3);
        }

        .delete-btn {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }

        .delete-btn:hover:not(:disabled) {
            background: rgba(239, 68, 68, 0.3);
        }

        .delete-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .view-btn {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
        }

        .view-btn:hover {
            background: rgba(16, 185, 129, 0.3);
        }

        .no-data {
            text-align: center;
            padding: 40px !important;
            color: #6b7280;
        }

        .unassigned {
            color: #6b7280;
            font-style: italic;
        }

        @media (max-width: 1200px) {
            .assets-table {
                font-size: 12px;
            }

            .assets-table th,
            .assets-table td {
                padding: 10px;
            }

            .asset-image {
                width: 40px;
                height: 40px;
            }
        }

        /* Delete Modal */
        .af-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(3px);
        }

        .af-modal-box {
            background: #ffffff;
            width: 420px;
            max-width: 90%;
            margin: 12% auto;
            padding: 25px 30px;
            border-radius: 14px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: fadeSlideIn 0.3s ease;
        }

        .af-modal-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .af-warning-icon {
            font-size: 22px;
        }

        #af-deleteText {
            color: #555;
            font-size: 14px;
        }

        .af-modal-actions {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 12px;
        }

        .af-btn {
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: 0.2s ease;
        }

        .af-btn-light {
            background: #eef1f5;
            color: #333;
        }

        .af-btn-light:hover {
            background: #dce1e7;
        }

        .af-btn-danger {
            background: #e74c3c;
            color: white;
        }

        .af-btn-danger:hover {
            background: #c0392b;
        }

        @keyframes fadeSlideIn {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <div class="admin-main">
            <?php include 'includes/admin_header.php'; ?>
            
            <main class="admin-content">
                <div class="assets-header">
                    <div>
                        <h1>Assets Management</h1>
                        <p style="color: var(--text-light);">Manage all IT assets and track assignments</p>
                    </div>
                    <button class="btn-primary" onclick="openAddModal()">
                        <i class='bx bx-plus'></i> Add New Asset
                    </button>
                </div>

                <!-- Stats -->
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon assets">
                            <i class='bx bx-package'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Total Assets</h3>
                            <p class="stat-number"><?php echo $total_assets; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon available">
                            <i class='bx bx-circle'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Available</h3>
                            <p class="stat-number"><?php echo $available_assets; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon in_use">
                            <i class='bx bx-check-circle'></i>
                        </div>
                        <div class="stat-content">
                            <h3>In-Use</h3>
                            <p class="stat-number"><?php echo $in_use_assets; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon under_maintenance">
                            <i class='bx bx-spanner'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Under Maintenance</h3>
                            <p class="stat-number"><?php echo $under_maintenance_assets; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon danger">
                            <i class='bx bx-cross-circle'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Damaged</h3>
                            <p class="stat-number"><?php echo $damaged_assets; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Filter Box -->
                <div class="filter-box">
                    <select onchange="filterAssets()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>">
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select onchange="filterAssets()">
                        <option value="">All Status</option>
                        <!-- ✅ UPDATED: lowercase status values -->
                        <option value="available">Available</option>
                        <option value="in-use">In-Use</option>
                        <option value="under-maintenance">Under-Maintenance</option>
                        <option value="lost">Lost</option>
                        <option value="damaged">Damaged</option>
                        <option value="retired">Retired</option>
                    </select>
                </div>

                <!-- Assets Table -->
                <div class="chart-card">
                    <h2>Assets List</h2>
                    <div class="assets-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Asset Name</th>
                                    <th>Asset Code</th>
                                    <th>Category</th>
                                    <th>Model</th>
                                    <th>Serial Number</th>
                                    <th>Assigned To</th>
                                    <th>Status</th>
                                    <th>Condition</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($assets_result->num_rows > 0): ?>
                                    <?php while ($asset = $assets_result->fetch_assoc()): ?>
                                        <tr id="row-<?php echo $asset['asset_id']; ?>">
                                            <td>
                                                <div class="asset-cell">
                                                    <?php if ($asset['image_path']): ?>
                                                        <img src="<?php echo htmlspecialchars($asset['image_path']); ?>" alt="Asset" class="asset-image">
                                                    <?php else: ?>
                                                        <div class="asset-placeholder">
                                                            <i class='bx bx-package'></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <strong><?php echo htmlspecialchars($asset['asset_name']); ?></strong>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($asset['asset_code'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($asset['category_name']); ?></td>
                                            <td>
                                                <small><?php echo htmlspecialchars($asset['asset_model'] ?? 'N/A'); ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars(substr($asset['serial_number'] ?? 'N/A', 0, 15)); ?></small>
                                            </td>
                                            <td>
                                                <?php 
                                                    if ($asset['assigned_to']) {
                                                        echo htmlspecialchars($asset['user_name']) . '<br>';
                                                        echo '<small style="color: var(--text-light);">' . htmlspecialchars($asset['department_name']) . '</small>';
                                                    } else {
                                                        echo '<span class="unassigned">Unassigned</span>';
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <!-- ✅ UPDATED: use lowercase data-status -->
                                                <span class="status-badge" data-status="<?php echo strtolower($asset['status']); ?>">
                                                    <?php echo htmlspecialchars($asset['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="condition-badge <?php echo strtolower($asset['asset_condition']); ?>">
                                                    <?php echo htmlspecialchars($asset['asset_condition']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="pages/asset_details.php?id=<?php echo $asset['asset_id']; ?>" class="view-btn" title="View Details">View</a>
                                                    <button type="button" class="edit-btn" onclick="editAsset(<?php echo $asset['asset_id']; ?>)" title="Edit Asset">Edit</button>
                                                    <button type="button" class="delete-btn" onclick="deleteAsset(this)" title="Delete Asset">Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="no-data">No assets found. Add your first asset!</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Asset Modal -->
    <div id="assetModal" class="modal">
        <div class="modal-content">
            <div class="modal-header" id="modalTitle">Add New Asset</div>
            <form id="assetForm" action="pages/process_asset.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="asset_id" name="asset_id" value="">
                
                <div class="form-group">
                    <label>Asset Name *</label>
                    <input type="text" id="asset_name" name="asset_name" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Category *</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>">
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select id="status" name="status">
                            <!-- ✅ UPDATED: lowercase status values -->
                            <option value="available">Available</option>
                            <option value="in-use">In-Use</option>
                            <option value="under-maintenance">Under-Maintenance</option>
                            <option value="lost">Lost</option>
                            <option value="damaged">Damaged</option>
                            <option value="retired">Retired</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Model</label>
                        <input type="text" id="asset_model" name="asset_model">
                    </div>

                    <div class="form-group">
                        <label>Serial Number</label>
                        <input type="text" id="serial_number" name="serial_number">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Condition</label>
                        <select id="condition" name="asset_condition">
                            <!-- ✅ UPDATED: lowercase condition values -->
                            <option value="excellent">Excellent</option>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                            <option value="damaged">Damaged</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Assign To User</label>
                        <select id="assigned_to" name="assigned_to">
                            <option value="">Unassigned</option>
                            <?php foreach ($users_array as $user): ?>
                                <option value="<?php echo $user['user_id']; ?>">
                                    <?php echo htmlspecialchars($user['user_name']) . ' (' . htmlspecialchars($user['department_name']) . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Purchase Date</label>
                        <input type="date" id="purchase_date" name="purchase_date">
                    </div>

                    <div class="form-group">
                        <label>Cost</label>
                        <input type="number" id="cost" name="cost" step="0.01">
                    </div>
                </div>

                <div class="form-group">
                    <label>Asset Image</label>
                    <input type="file" id="asset_image" name="asset_image" accept="image/*">
                    <small style="color: var(--text-light);">Max 5MB. JPG, PNG, GIF only.</small>
                </div>

                <div class="form-group">
                    <label>Notes</label>
                    <textarea id="notes" name="notes" placeholder="Any additional notes..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-close" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Save Asset</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="af-modal">
        <div class="af-modal-box">
            <div class="af-modal-header">
                <span class="af-warning-icon">⚠️</span>
                <h3>Delete Asset</h3>
            </div>

            <p id="af-deleteText">
                Are you sure you want to delete this asset?
                <br><small>This action cannot be undone.</small>
            </p>

            <div class="af-modal-actions">
                <button id="cancelDelete" class="af-btn af-btn-light">Cancel</button>
                <button id="confirmDelete" class="af-btn af-btn-danger">Delete</button>
            </div>
        </div>
    </div>

    <?php include 'includes/admin_footer.php'; ?>
    
    <script>
        console.log('Assets page loaded');

        function openAddModal() {
            console.log('Opening add modal');
            document.getElementById('modalTitle').textContent = 'Add New Asset';
            document.getElementById('assetForm').reset();
            document.getElementById('asset_id').value = '';
            document.getElementById('assetModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('assetModal').style.display = 'none';
        }

        function editAsset(assetId) {
            console.log('Edit asset called with ID:', assetId);
            
            document.getElementById('modalTitle').textContent = 'Edit Asset';
            document.getElementById('asset_id').value = assetId;
            
            const getAssetUrl = '/asset_tracker_system/public/admin/pages/get_asset.php?id=' + assetId;
            console.log('Fetching from:', getAssetUrl);
            
            fetch(getAssetUrl)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('HTTP Error: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Asset data received:', data);
                    
                    if (data.success) {
                        const asset = data.data;
                        
                        // Populate form fields
                        document.getElementById('asset_name').value = asset.asset_name || '';
                        document.getElementById('category_id').value = asset.category_id || '';
                        document.getElementById('asset_model').value = asset.asset_model || '';
                        document.getElementById('serial_number').value = asset.serial_number || '';
                        document.getElementById('status').value = asset.status || 'available';
                        document.getElementById('condition').value = asset.asset_condition || 'excellent';
                        document.getElementById('assigned_to').value = asset.assigned_to || '';
                        document.getElementById('purchase_date').value = asset.purchase_date || '';
                        document.getElementById('cost').value = asset.cost || '';
                        document.getElementById('notes').value = asset.notes || '';
                        
                        // Clear file input
                        document.getElementById('asset_image').value = '';
                        
                        console.log('Form populated successfully');
                        document.getElementById('assetModal').style.display = 'block';
                    } else {
                        alert('Error: ' + (data.message || 'Failed to load asset'));
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Error loading asset: ' + error.message);
                });
        }
        
        let selectedRow = null;
        let selectedButton = null;
        let selectedAssetId = null;

        function deleteAsset(button) {
            const row = button.closest('tr');
            if (!row) return;

            selectedRow = row;
            selectedButton = button;
            selectedAssetId = row.id.replace('row-', '');

            const assetName = row.querySelector('strong')?.textContent || 'this asset';

            document.getElementById("af-deleteText").innerHTML =
                `Are you sure you want to delete <strong>${assetName}</strong>?<br><small>This action cannot be undone.</small>`;

            document.getElementById("deleteModal").style.display = "block";
        }
            
        document.getElementById("cancelDelete").onclick = function () {
            document.getElementById("deleteModal").style.display = "none";
        };

        document.getElementById("confirmDelete").onclick = function () {
            if (!selectedAssetId) return;

            selectedButton.disabled = true;
            const originalText = selectedButton.textContent;
            selectedButton.textContent = 'Deleting...';

            const deleteUrl = '/asset_tracker_system/public/admin/pages/delete_asset.php';

            console.log('Deleting asset ID:', selectedAssetId);

            fetch(deleteUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'asset_id=' + selectedAssetId
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP Error: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Delete response:', data);

                if (data.success) {
                    selectedRow.style.transition = 'opacity 0.3s ease';
                    selectedRow.style.opacity = '0';

                    setTimeout(() => {
                        selectedRow.remove();
                    }, 300);

                    document.getElementById("deleteModal").style.display = "none";
                    showNotification('Asset deleted successfully!', 'success');
                } else {
                    throw new Error(data.message || 'Delete failed');
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                showNotification('Error: ' + error.message, 'error');
                selectedButton.disabled = false;
                selectedButton.textContent = originalText;
            });
        };

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.padding = '15px 20px';
            notification.style.borderRadius = '8px';
            notification.style.zIndex = '9999';
            notification.style.fontSize = '14px';
            notification.style.fontWeight = '600';
            notification.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
            
            if (type === 'success') {
                notification.style.background = '#d4edda';
                notification.style.color = '#155724';
                notification.style.border = '1px solid #c3e6cb';
            } else {
                notification.style.background = '#f8d7da';
                notification.style.color = '#721c24';
                notification.style.border = '1px solid #f5c6cb';
            }
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transition = 'opacity 0.3s ease';
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        function filterAssets() {
            const selects = document.querySelectorAll('.filter-box select');
            const category = selects[0].value;
            const status = selects[1].value;
            
            let url = '?';
            if (category) url += 'category=' + category;
            if (status) url += (category ? '&' : '') + 'status=' + status;
            
            window.location.href = url;
        }

        window.onclick = function(event) {
            const modal = document.getElementById('assetModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
    <script src="js/admin_dashboard.js"></script>
</body>
</html>