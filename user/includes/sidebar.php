<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-user">
            <div class="sidebar-avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
            <div class="sidebar-user-info">
                <h3><?= htmlspecialchars($user['full_name']) ?></h3>
                <div class="sidebar-user-role">User Account</div>
            </div>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <a href="dashboard.php" class="nav-item <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="upload.php" class="nav-item <?= $current_page === 'upload.php' ? 'active' : '' ?>">
                <i class="fas fa-cloud-upload-alt"></i>
                <span>Upload & Scan</span>
            </a>
            <a href="scan-history.php" class="nav-item <?= $current_page === 'scan-history.php' ? 'active' : '' ?>">
                <i class="fas fa-history"></i>
                <span>Scan History</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Account</div>
            <a href="profile.php" class="nav-item <?= $current_page === 'profile.php' ? 'active' : '' ?>">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </a>
            <a href="settings.php" class="nav-item <?= $current_page === 'settings.php' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>
    </nav>
</aside>
