<aside class="admin-sidebar">
    <div class="sidebar-header">
        <a href="dashboard.php" class="sidebar-logo">
        <i class='bx bx-menu'></i>
            <i class='bx bx-dots-vertical-rounded'></i>
            <span>AssetFlow</span>
        </a>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                <i class='bx bx-home'></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                <i class='bx bx-group'></i>
                <span>Manage Users</span>
            </a>
        </li>
        <li>
            <a href="assets.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'assets.php' ? 'active' : ''; ?>">
                <i class='bx bx-package'></i>
                <span>Assets</span>
            </a>
        </li>
        <li>
            <a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>">
                <i class='bx bx-file'></i>
                <span>Reports</span>
            </a>
        </li>
        <li>
            <a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
                <i class='bx bx-cog'></i>
                <span>Settings</span>
            </a>
        </li>
        <li style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
            <a href="../logout.php">
            <i class="bx bx-arrow-out-left-square-half"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</aside>