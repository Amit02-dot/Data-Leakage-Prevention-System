<?php
require_once '../includes/auth.php';
$user_id = Auth::requireUser();
$user = Auth::getUser();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Filter
$risk_filter = $_GET['risk'] ?? '';
$where_sql = "WHERE user_id = :uid";
$params = [':uid' => $user_id];

if (!empty($risk_filter)) {
    $where_sql .= " AND risk_level = :risk";
    $params[':risk'] = $risk_filter;
}

// Get total count
$count_query = "SELECT COUNT(*) as total FROM file_scans $where_sql";
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$total_scans = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_scans / $per_page);

// Get scans
$query = "SELECT fs.*, p.policy_name 
          FROM file_scans fs 
          LEFT JOIN dlp_policies p ON fs.policy_triggered = p.id
          $where_sql 
          ORDER BY fs.scanned_at DESC 
          LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$scans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan History - DLPS Enterprise</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Scan History</h1>
                <p class="page-subtitle">View and filter your file security scan results</p>
            </div>
            <div class="page-actions">
                <a href="upload.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Scan
                </a>
            </div>
        </div>
        
        <!-- Filter Bar -->
        <div class="card" style="margin-bottom: 2rem; padding: 1rem;">
            <form method="GET" style="display: flex; gap: 1rem; align-items: center;">
                <span style="color: var(--text-secondary); font-weight: 500;">Filter by Risk:</span>
                
                <a href="scan-history.php" class="badge badge-info" style="text-decoration: none; opacity: <?= empty($risk_filter) ? '1' : '0.5' ?>">
                    ALL
                </a>
                <a href="?risk=safe" class="badge badge-safe" style="text-decoration: none; opacity: <?= $risk_filter === 'safe' ? '1' : '0.5' ?>">
                    SAFE
                </a>
                <a href="?risk=blocked" class="badge badge-danger" style="text-decoration: none; opacity: <?= $risk_filter === 'blocked' ? '1' : '0.5' ?>">
                    BLOCKED
                </a>
                <a href="?risk=high" class="badge badge-warning" style="text-decoration: none; opacity: <?= $risk_filter === 'high' ? '1' : '0.5' ?>">
                    HIGH RISK
                </a>
            </form>
        </div>
        
        <!-- Scans Table -->
        <div class="card">
            <div class="card-body" style="padding: 0;">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Risk Level</th>
                                <th>Policy Triggered</th>
                                <th>Scanned At</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($scans)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                    <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                                    No scan history found. <a href="upload.php" style="color: var(--accent-blue);">Upload a file</a> to get started.
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($scans as $scan): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <i class="fas fa-file-<?php 
                                            echo match($scan['file_type']) {
                                                'pdf' => 'pdf',
                                                'doc', 'docx' => 'word',
                                                'jpg', 'jpeg', 'png' => 'image',
                                                default => 'alt'
                                            };
                                            ?>" style="font-size: 1.25rem; color: var(--text-secondary);"></i>
                                            <?= htmlspecialchars($scan['file_name']) ?>
                                        </div>
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
                                        <?php if ($scan['policy_name']): ?>
                                            <span style="color: var(--status-danger); font-size: 0.85rem; font-weight: 500;">
                                                <i class="fas fa-shield-alt"></i> <?= htmlspecialchars($scan['policy_name']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M d, Y H:i', strtotime($scan['scanned_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-secondary" style="padding: 0.375rem 0.75rem; font-size: 0.8rem;" 
                                                onclick="viewDetails(<?= htmlspecialchars(json_encode($scan)) ?>)">
                                            View
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
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div style="display: flex; justify-content: center; margin-top: 2rem; gap: 0.5rem;">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&risk=<?= $risk_filter ?>" 
                   class="btn <?= $i === $page ? 'btn-primary' : 'btn-secondary' ?>" 
                   style="padding: 0.5rem 1rem;">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </main>
    
    <!-- Details Modal -->
    <div id="detailsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="width: 500px; max-width: 90%;">
            <div class="card-header" style="border-bottom: 1px solid var(--border-color);">
                <h3 class="card-title">Scan Details</h3>
                <button onclick="document.getElementById('detailsModal').style.display='none'" style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem;">&times;</button>
            </div>
            <div class="card-body" id="modalContent">
                <!-- Populated by JS -->
            </div>
        </div>
    </div>
    
    <script>
        function viewDetails(scan) {
            const modal = document.getElementById('detailsModal');
            const content = document.getElementById('modalContent');
            
            let sensitiveData = '';
            try {
                const data = JSON.parse(scan.sensitive_data_found || '[]');
                if (data.length > 0) sensitiveData = data.join(', ');
                else sensitiveData = 'None';
            } catch (e) { sensitiveData = 'None'; }
            
            content.innerHTML = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">File Name</div>
                        <div style="font-weight: 600;">\${scan.file_name}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">File Size</div>
                        <div style="font-weight: 600;">\${formatBytes(scan.file_size)}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">Risk Level</div>
                        <div style="font-weight: 600; color: \${getRiskColor(scan.risk_level)}">\${scan.risk_level.toUpperCase()}</div>
                    </div>
                     <div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">Scan Duration</div>
                        <div style="font-weight: 600;">\${scan.scan_duration_ms} ms</div>
                    </div>
                </div>
                
                <div style="background: var(--tertiary-bg); padding: 1rem; border-radius: 8px;">
                    <div style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 0.5rem;">Detected Sensitive Data</div>
                    <div style="color: var(--status-danger); font-family: monospace;">\${sensitiveData}</div>
                </div>
            `;
            
            modal.style.display = 'flex';
        }
        
        function getRiskColor(level) {
            if (level === 'safe') return 'var(--status-safe)';
            if (level === 'blocked') return 'var(--status-danger)';
            return 'var(--status-warning)';
        }
        
        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
    </script>
</body>
</html>
<?php
function formatBytes($bytes, $precision = 2) {
    if ($bytes <= 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB'];
    $pow = floor(log($bytes) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>
