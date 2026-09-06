<header class="user-header">
    <div class="header-left">
        <div class="search-box">
            <i class='bx bx-search'></i>
            <input type="text" placeholder="Search assets...">
        </div>
    </div>

    <div class="header-right">
        <div class="header-icons">
            <button class="icon-btn" title="Notifications">
                <i class='bx bx-bell'></i>
            </button>
            <button class="icon-btn" title="Messages">
                <i class='bx bx-message'></i>
            </button>
        </div>

        <div class="user-profile">
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
            </div>
            <div class="user-info">
                <p><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></p>
                <p><?php echo htmlspecialchars($_SESSION['department_name'] ?? 'Department'); ?></p>
            </div>
        </div>
    </div>
</header>