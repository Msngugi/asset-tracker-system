<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <link href='https://cdn.boxicons.com/3.0.6/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <div class="admin-main">
            <?php include 'includes/admin_header.php'; ?>
            
            <main class="admin-content">
                <div class="dashboard-header">
                    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                        <div>
                            <h1>Manage Users</h1>
                            <p>View and manage all registered users</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class='bx bx-plus'></i> Add New User
                        </button>
                    </div>
                </div>

                <!-- Alert Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Stats -->
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <i class='bx bx-group'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Total Users</h3>
                            <p class="stat-number"><?php echo $total_users; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon assets">
                            <i class='bx bx-building'></i>
                        </div>
                        <div class="stat-content">
                            <h3>Departments</h3>
                            <p class="stat-number"><?php echo count($dept_data); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="chart-card">
                    <h2>Users List</h2>
                    <div class="assets-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Username</th> 
                                    <th>Email</th> 
                                    <th>Department</th> 
                                    <th>Joined Date</th> 
                                    <th>Action</th> 
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($users_result->num_rows > 0): ?>
                                    <?php while ($user = $users_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($user['user_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['department_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning edit-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editUserModal"
                                                        data-user-id="<?php echo $user['user_id']; ?>">
                                                    <i class='bx bx-edit'></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-danger delete-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteUserModal"
                                                        data-user-id="<?php echo $user['user_id']; ?>"
                                                        data-user-name="<?php echo htmlspecialchars($user['user_name']); ?>">
                                                    <i class='bx bx-trash'></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="no-data">No users found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Users by Department -->
                <div class="chart-card" style="margin-top: 30px;">
                    <h2>Users by Department</h2>
                    <div class="category-list">
                        <?php if (!empty($dept_data)): ?>
                            <?php foreach ($dept_data as $dept): ?>
                                <div class="category-item">
                                    <div class="category-info">
                                        <span class="category-name"><?php echo htmlspecialchars($dept['department_name']); ?></span>
                                        <span class="category-count"><?php echo $dept['count']; ?> users</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress" style="width: <?php echo ($dept['count'] / $total_users) * 100; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-data">No users found</p>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- ============ ADD USER MODAL ============ -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="pages/process_user.php">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="add_username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_username" name="user_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="add_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="add_email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="add_password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="add_password" name="password" required minlength="6">
                            <small class="form-text text-muted">Minimum 6 characters</small>
                        </div>

                        <div class="mb-3">
                            <label for="add_department" class="form-label">Department <span class="text-danger">*</span></label>
                            <select class="form-select" id="add_department" name="department_name" required>
                                <option value="">Select Department</option>
                                <?php foreach ($all_depts as $dept): ?>
                                    <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============ EDIT USER MODAL ============ -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="pages/process_user.php">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_username" name="user_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_password" class="form-label">Password (Leave blank to keep current)</label>
                            <input type="password" class="form-control" id="edit_password" name="password" minlength="6">
                            <small class="form-text text-muted">Minimum 6 characters (optional)</small>
                        </div>

                        <div class="mb-3">
                            <label for="edit_department" class="form-label">Department <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_department" name="department_name" required>
                                <option value="">Select Department</option>
                                <?php foreach ($all_depts as $dept): ?>
                                    <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============ DELETE USER MODAL ============ -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Delete User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the user <strong id="delete_user_name"></strong>?</p>
                    <p class="text-danger"><small><i class='bx bx-exclamation-circle'></i> This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="pages/process_user.php" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="user_id" id="delete_user_id">
                        <button type="submit" class="btn btn-danger">Delete User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/admin_footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin_dashboard.js"></script>
    
    <script>
        // Edit User Modal - Load user data via AJAX
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.dataset.userId;
                
                fetch('get_user.php?user_id=' + userId)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('edit_user_id').value = data.user.user_id;
                            document.getElementById('edit_username').value = data.user.user_name;
                            document.getElementById('edit_email').value = data.user.email;
                            document.getElementById('edit_department').value = data.user.department_name;
                        } else {
                            alert('Error loading user data');
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        alert('Error loading user data');
                    });
            });
        });

        // Delete User Modal - Set user info
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.dataset.userId;
                const userName = this.dataset.userName;
                
                document.getElementById('delete_user_id').value = userId;
                document.getElementById('delete_user_name').textContent = userName;
            });
        });
    </script>
</body>
</html>
