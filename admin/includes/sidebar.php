<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-user">
            <div class="sidebar-avatar" style="background: linear-gradient(135deg, #ef4444, #f59e0b);">
                <?= strtoupper(substr($admin['full_name'], 0, 1)) ?>
            </div>
            <div class="sidebar-user-info">
                <h3><?= htmlspecialchars($admin['full_name']) ?></h3>
                <div class="sidebar-user-role">Security Administrator</div>
            </div>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Overview</div>
            <a href="dashboard.php" class="nav-item <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
            <a href="scanning-engine.php" class="nav-item <?= $current_page === 'scanning-engine.php' ? 'active' : '' ?>">
                <i class="fas fa-cogs"></i>
                <span>Scanning Engine</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Security</div>
            <a href="policies.php" class="nav-item <?= $current_page === 'policies.php' ? 'active' : '' ?>">
                <i class="fas fa-shield-alt"></i>
                <span>Policy Management</span>
            </a>
            <a href="audit-trail.php" class="nav-item <?= $current_page === 'audit-trail.php' ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list"></i>
                <span>Audit Trail</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Management</div>
            <a href="users.php" class="nav-item <?= $current_page === 'users.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>User Management</span>
            </a>
            <a href="reports.php" class="nav-item <?= $current_page === 'reports.php' ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i>
                <span>Reports</span>
            </a>
        </div>
    </nav>
</aside>
