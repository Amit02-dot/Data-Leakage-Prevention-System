<?php
require_once '../includes/auth.php';
$admin_id = Auth::requireAdmin();
$admin = Auth::getAdmin();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Filters
$filter_type = $_GET['actor_type'] ?? '';
$filter_action = $_GET['action_type'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Build query
$where_clauses = [];
$params = [];

if (!empty($filter_type)) {
    $where_clauses[] = "actor_type = :actor_type";
    $params[':actor_type'] = $filter_type;
}

if (!empty($filter_action)) {
    $where_clauses[] = "action_type = :action_type";
    $params[':action_type'] = $filter_action;
}

if (!empty($filter_status)) {
    $where_clauses[] = "status = :status";
    $params[':status'] = $filter_status;
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Get total count
$count_query = "SELECT COUNT(*) as total FROM audit_logs $where_sql";
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$total_logs = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_logs / $per_page);

// Get logs
$query = "SELECT * FROM audit_logs $where_sql ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Trail - DLPS Enterprise</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .audit-filters {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: flex-end;
        }
        
        .filter-group {
            flex: 1;
        }
        
        .audit-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .audit-stat {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
        }
        
        .audit-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .audit-stat-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .log-row {
            transition: all var(--transition-fast);
        }
        
        .log-row:hover {
            background: var(--hover-bg);
            cursor: pointer;
        }
        
        .log-details {
            font-family: var(--font-mono);
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .page-btn {
            padding: 0.5rem 1rem;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-primary);
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .page-btn:hover {
            background: var(--hover-bg);
            border-color: var(--accent-blue);
        }
        
        .page-btn.active {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }
        
        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Audit Trail</h1>
                <p class="page-subtitle">Immutable security and activity logs</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-secondary" onclick="exportLogs()">
                    <i class="fas fa-download"></i> Export Logs
                </button>
                <button class="btn btn-secondary" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        
        <!-- Audit Stats -->
        <div class="audit-stats">
            <div class="audit-stat">
                <div class="audit-stat-value"><?= number_format($total_logs) ?></div>
                <div class="audit-stat-label">Total Logs</div>
            </div>
            <div class="audit-stat">
                <div class="audit-stat-value" style="color: var(--status-safe);">
                    <?php
                    $stmt = $db->query("SELECT COUNT(*) as count FROM audit_logs WHERE status = 'success'");
                    echo number_format($stmt->fetch()['count']);
                    ?>
                </div>
                <div class="audit-stat-label">Successful</div>
            </div>
            <div class="audit-stat">
                <div class="audit-stat-value" style="color: var(--status-danger);">
                    <?php
                    $stmt = $db->query("SELECT COUNT(*) as count FROM audit_logs WHERE status = 'failure'");
                    echo number_format($stmt->fetch()['count']);
                    ?>
                </div>
                <div class="audit-stat-label">Failed</div>
            </div>
            <div class="audit-stat">
                <div class="audit-stat-value" style="color: var(--status-warning);">
                    <?php
                    $stmt = $db->query("SELECT COUNT(*) as count FROM audit_logs WHERE status = 'blocked'");
                    echo number_format($stmt->fetch()['count']);
                    ?>
                </div>
                <div class="audit-stat-label">Blocked</div>
            </div>
        </div>
        
        <!-- Filters -->
        <form class="audit-filters" method="GET">
            <div class="filter-group">
                <label class="form-label">Actor Type</label>
                <select name="actor_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="user" <?= $filter_type === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= $filter_type === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="system" <?= $filter_type === 'system' ? 'selected' : '' ?>>System</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="form-label">Action Type</label>
                <select name="action_type" class="form-select">
                    <option value="">All Actions</option>
                    <option value="login" <?= $filter_action === 'login' ? 'selected' : '' ?>>Login</option>
                    <option value="logout" <?= $filter_action === 'logout' ? 'selected' : '' ?>>Logout</option>
                    <option value="upload" <?= $filter_action === 'upload' ? 'selected' : '' ?>>Upload</option>
                    <option value="scan" <?= $filter_action === 'scan' ? 'selected' : '' ?>>Scan</option>
                    <option value="policy_create" <?= $filter_action === 'policy_create' ? 'selected' : '' ?>>Policy Create</option>
                    <option value="policy_update" <?= $filter_action === 'policy_update' ? 'selected' : '' ?>>Policy Update</option>
                    <option value="access_denied" <?= $filter_action === 'access_denied' ? 'selected' : '' ?>>Access Denied</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="success" <?= $filter_status === 'success' ? 'selected' : '' ?>>Success</option>
                    <option value="failure" <?= $filter_status === 'failure' ? 'selected' : '' ?>>Failure</option>
                    <option value="blocked" <?= $filter_status === 'blocked' ? 'selected' : '' ?>>Blocked</option>
                    <option value="warning" <?= $filter_status === 'warning' ? 'selected' : '' ?>>Warning</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Apply Filters
            </button>
            
            <a href="audit-trail.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Clear
            </a>
        </form>
        
        <!-- Audit Logs Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Activity Logs</h2>
                <span style="color: var(--text-secondary); font-size: 0.9rem;">
                    Showing <?= count($logs) ?> of <?= number_format($total_logs) ?> logs
                </span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Actor</th>
                                <th>Action</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>IP Address</th>
                                <th>Timestamp</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr class="log-row">
                                <td style="font-family: var(--font-mono); color: var(--text-muted);">
                                    #<?= $log['id'] ?>
                                </td>
                                <td>
                                    <?php
                                    $actor_icon = match($log['actor_type']) {
                                        'admin' => 'fa-user-shield',
                                        'user' => 'fa-user',
                                        'system' => 'fa-server',
                                        default => 'fa-question'
                                    };
                                    ?>
                                    <i class="fas <?= $actor_icon ?>"></i>
                                    <?= htmlspecialchars($log['actor_name']) ?>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                                        <?= ucfirst($log['actor_type']) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($log['action']) ?></td>
                                <td>
                                    <span class="badge badge-info">
                                        <?= strtoupper(str_replace('_', ' ', $log['action_type'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $status_badge = match($log['status']) {
                                        'success' => 'badge-safe',
                                        'failure' => 'badge-danger',
                                        'blocked' => 'badge-critical',
                                        'warning' => 'badge-warning',
                                        default => 'badge-info'
                                    };
                                    ?>
                                    <span class="badge <?= $status_badge ?>">
                                        <?= strtoupper($log['status']) ?>
                                    </span>
                                </td>
                                <td style="font-family: var(--font-mono); font-size: 0.85rem;">
                                    <?= htmlspecialchars($log['ip_address']) ?>
                                </td>
                                <td style="font-size: 0.85rem;">
                                    <?= date('M d, Y', strtotime($log['created_at'])) ?>
                                    <div style="color: var(--text-muted);">
                                        <?= date('H:i:s', strtotime($log['created_at'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <button class="btn btn-secondary" style="padding: 0.375rem 0.75rem; font-size: 0.8rem;" 
                                            onclick="showDetails(<?= htmlspecialchars(json_encode($log)) ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <button class="page-btn" <?= $page <= 1 ? 'disabled' : '' ?> 
                    onclick="location.href='?page=<?= $page - 1 ?>&actor_type=<?= $filter_type ?>&action_type=<?= $filter_action ?>&status=<?= $filter_status ?>'">
                <i class="fas fa-chevron-left"></i> Previous
            </button>
            
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <button class="page-btn <?= $i === $page ? 'active' : '' ?>" 
                    onclick="location.href='?page=<?= $i ?>&actor_type=<?= $filter_type ?>&action_type=<?= $filter_action ?>&status=<?= $filter_status ?>'">
                <?= $i ?>
            </button>
            <?php endfor; ?>
            
            <button class="page-btn" <?= $page >= $total_pages ? 'disabled' : '' ?> 
                    onclick="location.href='?page=<?= $page + 1 ?>&actor_type=<?= $filter_type ?>&action_type=<?= $filter_action ?>&status=<?= $filter_status ?>'">
                Next <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <?php endif; ?>
    </main>
    
    <script>
        function showDetails(log) {
            const details = JSON.parse(log.details || '{}');
            let detailsHtml = '<strong>Log Details:</strong><br><br>';
            detailsHtml += '<strong>User Agent:</strong><br>' + log.user_agent + '<br><br>';
            
            if (Object.keys(details).length > 0) {
                detailsHtml += '<strong>Additional Information:</strong><br>';
                detailsHtml += '<pre style="background: var(--tertiary-bg); padding: 1rem; border-radius: 6px; overflow-x: auto;">';
                detailsHtml += JSON.stringify(details, null, 2);
                detailsHtml += '</pre>';
            }
            
            alert(detailsHtml); // In production, use a modal
        }
        
        function exportLogs() {
            window.location.href = '../api/admin/export-logs.php';
        }
    </script>
</body>
</html>
