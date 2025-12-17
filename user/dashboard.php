<?php
require_once '../includes/auth.php';
$user_id = Auth::requireUser();
$user = Auth::getUser();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get user dashboard stats
// Get user dashboard stats (Direct query to cover all risk levels)
$query = "SELECT 
    COUNT(*) as total_scans,
    COUNT(CASE WHEN risk_level = 'safe' THEN 1 END) as safe_files,
    COUNT(CASE WHEN risk_level = 'blocked' THEN 1 END) as blocked_files,
    COUNT(CASE WHEN risk_level IN ('low', 'medium', 'high', 'critical') THEN 1 END) as risk_alerts
FROM file_scans WHERE user_id = :uid";
$stmt = $db->prepare($query);
$stmt->execute([':uid' => $user_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent scans
$query = "SELECT * FROM file_scans WHERE user_id = :uid ORDER BY created_at DESC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute([':uid' => $user_id]);
$recent_scans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get scan trends (last 7 days)
$query = "SELECT DATE(scanned_at) as date, COUNT(*) as count, 
          SUM(CASE WHEN risk_level = 'safe' THEN 1 ELSE 0 END) as safe_count,
          SUM(CASE WHEN risk_level = 'blocked' THEN 1 ELSE 0 END) as blocked_count,
          SUM(CASE WHEN risk_level IN ('low', 'medium', 'high', 'critical') THEN 1 ELSE 0 END) as warning_count
          FROM file_scans 
          WHERE user_id = :uid AND scanned_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
          GROUP BY DATE(scanned_at)
          ORDER BY date ASC";
$stmt = $db->prepare($query);
$stmt->execute([':uid' => $user_id]);
$trends = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="User Dashboard - DLPS Enterprise">
    <title>Dashboard - DLPS Enterprise</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <!-- Top Navigation -->
    <?php include 'includes/header.php'; ?>
    
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
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="upload.php" class="nav-item">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Upload & Scan</span>
                </a>
                <a href="scan-history.php" class="nav-item">
                    <i class="fas fa-history"></i>
                    <span>Scan History</span>
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Account</div>
                <a href="profile.php" class="nav-item">
                    <i class="fas fa-user-circle"></i>
                    <span>My Profile</span>
                </a>
                <a href="settings.php" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Welcome back, <?= htmlspecialchars($user['full_name']) ?></p>
            </div>
            <div class="page-actions">
                <a href="upload.php" class="btn btn-primary">
                    <i class="fas fa-cloud-upload-alt"></i> Upload File
                </a>
            </div>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i> 12%
                    </div>
                </div>
                <div class="stat-label">Total Scans</div>
                <div class="stat-value"><?= $stats['total_scans'] ?? 0 ?></div>
                <div class="stat-footer">All time scans performed</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i> 8%
                    </div>
                </div>
                <div class="stat-label">Safe Files</div>
                <div class="stat-value"><?= $stats['safe_files'] ?? 0 ?></div>
                <div class="stat-footer">Files passed security scan</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="stat-trend down">
                        <i class="fas fa-arrow-down"></i> 5%
                    </div>
                </div>
                <div class="stat-label">Blocked Files</div>
                <div class="stat-value"><?= $stats['blocked_files'] ?? 0 ?></div>
                <div class="stat-footer">Files blocked by DLP policies</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i> 3%
                    </div>
                </div>
                <div class="stat-label">Risk Alerts</div>
                <div class="stat-value"><?= $stats['risk_alerts'] ?? 0 ?></div>
                <div class="stat-footer">High-risk detections</div>
            </div>
        </div>
        
        <!-- Charts Row -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Scan Trends (7 Days)</h2>
                </div>
                <div class="card-body">
                    <canvas id="trendsChart" height="250"></canvas>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Scan Results Distribution</h2>
                </div>
                <div class="card-body">
                    <canvas id="distributionChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Scans Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Recent Scans</h2>
                <a href="scan-history.php" class="btn btn-secondary">View All</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Risk Level</th>
                                <th>Status</th>
                                <th>Scanned At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_scans)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                                    No scans yet. Upload your first file to get started!
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($recent_scans as $scan): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-file"></i>
                                        <?= htmlspecialchars($scan['file_name']) ?>
                                    </td>
                                    <td><?= strtoupper($scan['file_type']) ?></td>
                                    <td><?= formatBytes($scan['file_size']) ?></td>
                                    <td>
                                        <?php
                                        $badge_class = match($scan['risk_level']) {
                                            'safe' => 'badge-safe',
                                            'low' => 'badge-info',
                                            'medium' => 'badge-warning',
                                            'high', 'critical', 'blocked' => 'badge-danger',
                                            default => 'badge-info'
                                        };
                                        ?>
                                        <span class="badge <?= $badge_class ?>">
                                            <?= strtoupper($scan['risk_level']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $status_badge = match($scan['scan_status']) {
                                            'completed' => 'badge-safe',
                                            'scanning' => 'badge-warning',
                                            'failed' => 'badge-danger',
                                            default => 'badge-info'
                                        };
                                        ?>
                                        <span class="badge <?= $status_badge ?>">
                                            <?= strtoupper($scan['scan_status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y H:i', strtotime($scan['scanned_at'] ?? $scan['created_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-secondary" style="padding: 0.375rem 0.75rem; font-size: 0.8rem;">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        // Trends Chart
        const trendsCtx = document.getElementById('trendsChart').getContext('2d');
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_map(fn($t) => date('M d', strtotime($t['date'])), $trends)) ?>,
                datasets: [{
                    label: 'Safe Files',
                    data: <?= json_encode(array_map(fn($t) => $t['safe_count'], $trends)) ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Blocked',
                    data: <?= json_encode(array_map(fn($t) => $t['blocked_count'], $trends)) ?>,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Risks',
                    data: <?= json_encode(array_map(fn($t) => $t['warning_count'], $trends)) ?>,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#9ca3af' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#6b7280' },
                        grid: { color: '#2d3548' }
                    },
                    x: {
                        ticks: { color: '#6b7280' },
                        grid: { color: '#2d3548' }
                    }
                }
            }
        });
        
        // Distribution Chart
        const distCtx = document.getElementById('distributionChart').getContext('2d');
        new Chart(distCtx, {
            type: 'doughnut',
            data: {
                labels: ['Safe', 'Blocked', 'Warnings'],
                datasets: [{
                    data: [
                        <?= $stats['safe_files'] ?? 0 ?>,
                        <?= $stats['blocked_files'] ?? 0 ?>,
                        <?= $stats['risk_alerts'] ?? 0 ?>
                    ],
                    backgroundColor: ['#10b981', '#ef4444', '#f59e0b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#9ca3af', padding: 20 }
                    }
                }
            }
        });
        
    </script>
    
    <script>
        function refreshDashboard() {
            location.reload();
        }
    </script>
</body>
</html>

<?php
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>
