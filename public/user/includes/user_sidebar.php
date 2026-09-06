<aside class="user-sidebar">
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
            <a href="my_assets.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'my_assets.php' ? 'active' : ''; ?>">
                <i class='bx bx-package'></i>
                <span>My Assets</span>
            </a>
        </li>
        <li>
            <a href="borrow_asset.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'borrow_asset.php' ? 'active' : ''; ?>">
                <i class='bx bx-plus-circle'></i>
                <span>Borrow Asset</span>
            </a>
        </li>
        <li>
            <a href="return_asset.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'return_asset.php' ? 'active' : ''; ?>">
                <i class='bx bx-check-circle'></i>
                <span>Return Asset</span>
            </a>
        </li>
        <li>
            <a href="penalties.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'penalties.php' ? 'active' : ''; ?>">
            <i class="bx bx-wallet-alt" ></i>
                <span>Penalties</span>
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