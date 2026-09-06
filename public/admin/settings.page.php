<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Dashboard</title>
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
                    <h1>Settings & Configuration</h1>
                    <p>Manage system settings, categories, and application preferences</p>
                </div>

                <!-- Settings Tabs -->
                <div class="settings-tabs">
                    <button class="tab-btn active" onclick="showTab('general')">
                        <i class='bx bx-cog'></i> General Settings
                    </button>
                    <button class="tab-btn" onclick="showTab('categories')">
                        <i class='bx bx-box'></i> Asset Categories
                    </button>
                    <button class="tab-btn" onclick="showTab('departments')">
                        <i class='bx bx-building'></i> Departments
                    </button>
                    <button class="tab-btn" onclick="showTab('security')">
                        <i class='bx bx-shield'></i> Security
                    </button>
                </div>

                <!-- TAB 1: General Settings -->
                <div id="general" class="tab-content active">
                    <div class="chart-card">
                        <h2>General Settings</h2>
                        <form method="POST" action="process_settings.php">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Application Name</label>
                                    <input type="text" name="app_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['app_name'] ?? 'AssetFlow'); ?>"
                                           placeholder="Application name">
                                </div>
                                <div class="form-group">
                                    <label>Application Email</label>
                                    <input type="email" name="app_email" class="form-control"
                                           value="<?php echo htmlspecialchars($settings['app_email'] ?? ''); ?>"
                                           placeholder="support@example.com">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Organization Name</label>
                                    <input type="text" name="org_name" class="form-control"
                                           value="<?php echo htmlspecialchars($settings['org_name'] ?? ''); ?>"
                                           placeholder="Your organization">
                                </div>
                                <div class="form-group">
                                    <label>Organization Address</label>
                                    <input type="text" name="org_address" class="form-control"
                                           value="<?php echo htmlspecialchars($settings['org_address'] ?? ''); ?>"
                                           placeholder="Physical address">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Organization Phone</label>
                                    <input type="tel" name="org_phone" class="form-control"
                                           value="<?php echo htmlspecialchars($settings['org_phone'] ?? ''); ?>"
                                           placeholder="+1 (555) 000-0000">
                                </div>
                                <div class="form-group">
                                    <label>Default Currency</label>
                                    <select name="currency" class="form-control">
                                        <option value="USD" <?php echo ($settings['currency'] ?? '') === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                        <option value="PHP" <?php echo ($settings['currency'] ?? '') === 'PHP' ? 'selected' : ''; ?>>PHP (₱)</option>
                                        <option value="EUR" <?php echo ($settings['currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                                        <option value="GBP" <?php echo ($settings['currency'] ?? '') === 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Items Per Page</label>
                                    <input type="number" name="items_per_page" class="form-control" 
                                           value="<?php echo $settings['items_per_page'] ?? 20; ?>"
                                           min="5" max="100">
                                </div>
                                <div class="form-group">
                                    <label>Session Timeout (minutes)</label>
                                    <input type="number" name="session_timeout" class="form-control"
                                           value="<?php echo $settings['session_timeout'] ?? 30; ?>"
                                           min="5" max="480">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="maintenance_mode" value="1"
                                           <?php echo ($settings['maintenance_mode'] ?? 0) ? 'checked' : ''; ?>>
                                    Enable Maintenance Mode
                                </label>
                                <small>When enabled, only admins can access the system</small>
                            </div>

                            <button type="submit" name="action" value="general" class="btn btn-primary">
                                <i class='bx bx-save'></i> Save General Settings
                            </button>
                        </form>
                    </div>
                </div>

                <!-- TAB 2: Asset Categories -->
                <div id="categories" class="tab-content">
                    <div class="chart-card">
                        <h2>Asset Categories Management</h2>
                        
                        <!-- Add New Category -->
                        <div style="margin-bottom: 30px;">
                            <h3>Add New Category</h3>
                            <form method="POST" action="process_settings.php" class="category-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <input type="text" name="category_name" class="form-control" 
                                               placeholder="Category name" required>
                                    </div>
                                    <div class="form-group">
                                        <textarea name="category_description" class="form-control" 
                                                  placeholder="Description (optional)"></textarea>
                                    </div>
                                    <button type="submit" name="action" value="add_category" class="btn btn-success">
                                        <i class='bx bx-plus'></i> Add Category
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Categories List -->
                        <div>
                            <h3>Existing Categories</h3>
                            <table class="report-table" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Category Name</th>
                                        <th>Description</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($categories)): ?>
                                        <?php foreach ($categories as $cat): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($cat['category_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($cat['description'] ?? 'N/A'); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($cat['created_at'])); ?></td>
                                            <td>
                                                <button class="btn-icon edit-cat" data-id="<?php echo $cat['category_id']; ?>"
                                                        onclick="editCategory(<?php echo $cat['category_id']; ?>)">
                                                    <i class='bx bx-edit'></i>
                                                </button>
                                                <form method="POST" action="process_settings.php" style="display:inline;">
                                                    <input type="hidden" name="category_id" value="<?php echo $cat['category_id']; ?>">
                                                    <button type="submit" name="action" value="delete_category" class="btn-icon" 
                                                            onclick="return confirm('Delete this category?')">
                                                        <i class='bx bx-trash' style="color: #dc3545;"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="no-data">No categories found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: Departments -->
                <div id="departments" class="tab-content">
                    <div class="chart-card">
                        <h2>Departments</h2>
                        <p style="margin-bottom: 20px; color: #666;">
                            Departments are managed through the user management system. 
                            Here's a list of active departments:
                        </p>
                        
                        <?php if (!empty($departments)): ?>
                            <div class="department-grid">
                                <?php foreach ($departments as $dept): ?>
                                    <div class="dept-card">
                                        <i class='bx bx-building'></i>
                                        <h4><?php echo htmlspecialchars($dept['department_name']); ?></h4>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="no-data">No departments found. Create users to add departments.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TAB 4: Security -->
                <div id="security" class="tab-content">
                    <div class="chart-card">
                        <h2>Security Settings</h2>
                        <form method="POST" action="process_settings.php">
                            <div class="form-group">
                                <label>Require Strong Passwords</label>
                                <input type="checkbox" name="require_strong_password" value="1"
                                       <?php echo ($settings['require_strong_password'] ?? 0) ? 'checked' : ''; ?>>
                                <small>Enforce minimum 8 characters, uppercase, lowercase, numbers, and symbols</small>
                            </div>

                            <div class="form-group">
                                <label>Password Expiry (days)</label>
                                <input type="number" name="password_expiry_days" class="form-control"
                                       value="<?php echo $settings['password_expiry_days'] ?? 90; ?>"
                                       min="0" max="365">
                                <small>Set to 0 to disable password expiry</small>
                            </div>

                            <div class="form-group">
                                <label>Maximum Login Attempts</label>
                                <input type="number" name="max_login_attempts" class="form-control"
                                       value="<?php echo $settings['max_login_attempts'] ?? 5; ?>"
                                       min="3" max="20">
                            </div>

                            <div class="form-group">
                                <label>Lockout Duration (minutes)</label>
                                <input type="number" name="lockout_duration" class="form-control"
                                       value="<?php echo $settings['lockout_duration'] ?? 30; ?>"
                                       min="5" max="1440">
                            </div>

                            <div class="form-group">
                                <label>Enable Two-Factor Authentication</label>
                                <input type="checkbox" name="enable_2fa" value="1"
                                       <?php echo ($settings['enable_2fa'] ?? 0) ? 'checked' : ''; ?>>
                            </div>

                            <div class="form-group">
                                <label>Enable Activity Logging</label>
                                <input type="checkbox" name="enable_logging" value="1"
                                       <?php echo ($settings['enable_logging'] ?? 1) ? 'checked' : ''; ?>>
                                <small>Log all user actions and system events</small>
                            </div>

                            <button type="submit" name="action" value="security" class="btn btn-primary">
                                <i class='bx bx-save'></i> Save Security Settings
                            </button>
                        </form>
                    </div>

                    <!-- Change Admin Password -->
                    <div class="chart-card" style="margin-top: 30px;">
                        <h2><i class='bx bx-lock'></i> Change Admin Password</h2>
                        <form method="POST" action="change_password.php">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" class="form-control" required
                                       placeholder="At least 8 characters">
                                <small>Include uppercase, lowercase, numbers, and symbols</small>
                            </div>

                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-warning">
                                <i class='bx bx-lock-open'></i> Change Password
                            </button>
                        </form>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <?php include 'includes/admin_footer.php'; ?>
    
    <style>
        .settings-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
            flex-wrap: wrap;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 15px 20px;
            cursor: pointer;
            font-size: 15px;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn:hover {
            color: #5e72e4;
        }

        .tab-btn.active {
            color: #5e72e4;
            border-bottom-color: #5e72e4;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-control {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .department-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .dept-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .dept-card i {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .category-form {
            display: grid;
            grid-template-columns: 200px 1fr 150px;
            gap: 10px;
            align-items: flex-end;
        }

        .btn-icon {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            font-size: 18px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .report-table th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }

        .report-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .category-form {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));

            // Remove active class from buttons
            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => btn.classList.remove('active'));

            // Show selected tab
            document.getElementById(tabName).classList.add('active');

            // Add active class to clicked button
            event.target.closest('.tab-btn').classList.add('active');
        }
    </script>
    <script src="js/admin_dashboard.js"></script>
</body>
</html>
