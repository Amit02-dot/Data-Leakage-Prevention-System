<?php
require_once '../includes/auth.php';
$admin_id = Auth::requireAdmin();
$admin = Auth::getAdmin();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get admin dashboard stats
$query = "SELECT * FROM admin_dashboard_stats";
$stmt = $db->query($query);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent violations
$query = "SELECT fs.*, u.username, u.full_name, p.policy_name 
          FROM file_scans fs
          JOIN users u ON fs.user_id = u.id
          LEFT JOIN dlp_policies p ON fs.policy_triggered = p.id
          WHERE fs.risk_level IN ('high', 'critical', 'blocked')
          ORDER BY fs.scanned_at DESC
          LIMIT 10";
$stmt = $db->query($query);
$violations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get policy violations by type
$query = "SELECT p.policy_name, p.data_type, COUNT(*) as violation_count
          FROM file_scans fs
          JOIN dlp_policies p ON fs.policy_triggered = p.id
          WHERE fs.risk_level IN ('high', 'critical', 'blocked')
          GROUP BY p.id
          ORDER BY violation_count DESC
          LIMIT 5";
$stmt = $db->query($query);
$policy_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get most active users
$query = "SELECT u.username, u.full_name, COUNT(*) as scan_count,
          SUM(CASE WHEN fs.risk_level IN ('high', 'critical', 'blocked') THEN 1 ELSE 0 END) as violations
          FROM users u
          JOIN file_scans fs ON u.id = fs.user_id
          GROUP BY u.id
          ORDER BY scan_count DESC
          LIMIT 5";
$stmt = $db->query($query);
$active_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Get data classification stats
$query = "SELECT p.data_type, COUNT(*) as violation_count 
          FROM file_scans fs 
          JOIN dlp_policies p ON fs.policy_triggered = p.id 
          WHERE fs.risk_level IN ('high', 'critical', 'blocked') 
          GROUP BY p.data_type";
$stmt = $db->query($query);
$classification_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin Dashboard - DLPS Enterprise">
    <title>Admin Dashboard - DLPS Enterprise</title>
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
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
                <a href="scanning-engine.php" class="nav-item">
                    <i class="fas fa-cogs"></i>
                    <span>Scanning Engine</span>
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Security</div>
                <a href="policies.php" class="nav-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Policy Management</span>
                    <span class="nav-badge"><?= $stats['active_policies'] ?? 0 ?></span>
                </a>
                <a href="audit-trail.php" class="nav-item">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Audit Trail</span>
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Management</div>
                <a href="users.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
                <a href="reports.php" class="nav-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Reports</span>
                </a>
            </div>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Security Dashboard</h1>
                <p class="page-subtitle">Real-time DLP monitoring and analytics</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-secondary" onclick="refreshDashboard()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i> 5%
                    </div>
                </div>
                <div class="stat-label">Total Users</div>
                <div class="stat-value"><?= $stats['total_users'] ?? 0 ?></div>
                <div class="stat-footer">Active user accounts</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i> 18%
                    </div>
                </div>
                <div class="stat-label">Files Scanned</div>
                <div class="stat-value"><?= $stats['total_scans'] ?? 0 ?></div>
                <div class="stat-footer">Total security scans</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-trend down">
                        <i class="fas fa-arrow-down"></i> 12%
                    </div>
                </div>
                <div class="stat-label">Policy Violations</div>
                <div class="stat-value"><?= $stats['policy_violations'] ?? 0 ?></div>
                <div class="stat-footer">DLP policy breaches</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i> 7%
                    </div>
                </div>
                <div class="stat-label">High-Risk Alerts</div>
                <div class="stat-value"><?= $stats['high_risk_alerts'] ?? 0 ?></div>
                <div class="stat-footer">Last 24 hours</div>
            </div>
        </div>
        
        <!-- Charts Row -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Policy Violations (Top 5)</h2>
                </div>
                <div class="card-body">
                    <canvas id="violationsChart" height="200"></canvas>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Data Classification</h2>
                </div>
                <div class="card-body">
                    <div style="height: 200px; display: flex; justify-content: center;">
                        <canvas id="classificationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Violations Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Recent Policy Violations</h2>
                <a href="audit-trail.php?status=blocked" class="btn btn-secondary">View All</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>File Name</th>
                                <th>Policy Triggered</th>
                                <th>Risk Level</th>
                                <th>Timestamp</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($violations)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                    <i class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: var(--status-safe);"></i>
                                    No policy violations detected
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($violations as $violation): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-user-circle"></i>
                                        <?= htmlspecialchars($violation['full_name']) ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-file"></i>
                                        <?= htmlspecialchars($violation['file_name']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($violation['policy_name'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge badge-danger">
                                            <?= strtoupper($violation['risk_level']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y H:i', strtotime($violation['scanned_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-secondary" style="padding: 0.375rem 0.75rem; font-size: 0.8rem;" onclick='alert("File: " + <?= json_encode($violation["file_name"]) ?> + "\nRisk: " + <?= json_encode($violation["risk_level"]) ?> + "\nPolicy: " + <?= json_encode($violation["policy_name"] ?? "N/A") ?>)'>
                                            <i class="fas fa-eye"></i> Details
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
        
        <!-- Most Active Users -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h2 class="card-title">Most Active Users</h2>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Total Scans</th>
                                <th>Violations</th>
                                <th>Compliance Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($active_users as $user): ?>
                            <tr>
                                <td>
                                    <i class="fas fa-user-circle"></i>
                                    <?= htmlspecialchars($user['full_name']) ?>
                                </td>
                                <td><?= $user['scan_count'] ?></td>
                                <td>
                                    <span class="badge <?= $user['violations'] > 0 ? 'badge-warning' : 'badge-safe' ?>">
                                        <?= $user['violations'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $compliance = $user['scan_count'] > 0 
                                        ? round((($user['scan_count'] - $user['violations']) / $user['scan_count']) * 100) 
                                        : 100;
                                    ?>
                                    <span style="font-weight: 600; color: <?= $compliance >= 90 ? 'var(--status-safe)' : 'var(--status-warning)' ?>">
                                        <?= $compliance ?>%
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        function refreshDashboard() {
            location.reload();
        }

        // Initialize Charts when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Chart Defaults
            Chart.defaults.color = '#8899ac';
            Chart.defaults.borderColor = '#2d3748';
            Chart.defaults.font.family = "'Inter', sans-serif";

            // Policy Violations Chart Data
            const policyLabels = <?= json_encode(array_column($policy_stats, 'policy_name')) ?>;
            const policyData = <?= json_encode(array_column($policy_stats, 'violation_count')) ?>;

            if (policyLabels.length > 0) {
                new Chart(document.getElementById('violationsChart'), {
                    type: 'bar',
                    data: {
                        labels: policyLabels,
                        datasets: [{
                            label: 'Violations',
                            data: policyData,
                            backgroundColor: '#ef4444',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(255, 255, 255, 0.05)' }
                            },
                            x: {
                                grid: { display: false }
                            }
                        }
                    }
                });
            } else {
                // Show empty state if no data
                document.getElementById('violationsChart').parentNode.innerHTML = 
                    '<div style="text-align: center; padding: 2rem; color: var(--text-secondary);">' +
                    '<i class="fas fa-chart-bar" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>' +
                    '<p>No violations recorded yet</p></div>';
            }

            // Data Classification Chart Data
            const classStats = <?= json_encode($classification_stats) ?>;
            const classLabels = classStats.map(item => item.data_type.toUpperCase());
            const classData = classStats.map(item => item.violation_count);

            if (classLabels.length > 0) {
                new Chart(document.getElementById('classificationChart'), {
                    type: 'doughnut',
                    data: {
                        labels: classLabels,
                        datasets: [{
                            data: classData,
                            backgroundColor: [
                                '#3b82f6', // blue
                                '#8b5cf6', // purple
                                '#ef4444', // red
                                '#f59e0b', // orange
                                '#10b981'  // green
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: { boxWidth: 12 }
                            }
                        },
                        cutout: '70%'
                    }
                });
            } else {
                 // Show empty state if no data
                 document.getElementById('classificationChart').parentNode.innerHTML = 
                    '<div style="text-align: center; padding: 2rem; color: var(--text-secondary); height: 100%; display: flex; flex-direction: column; justify-content: center;">' +
                    '<i class="fas fa-chart-pie" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>' +
                    '<p>No data available</p></div>';
            }
        });
    </script>
</body>
</html>
