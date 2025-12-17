<?php
require_once '../includes/auth.php';
$admin_id = Auth::requireAdmin();
$admin = Auth::getAdmin();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get scanning rules
$query = "SELECT * FROM scanning_rules ORDER BY priority DESC, created_at DESC";
$stmt = $db->query($query);
$rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get engine stats (simulated for now, or derived from file_scans)
$query = "SELECT 
    COUNT(*) as total_scans_today,
    AVG(scan_duration_ms) as avg_duration
    FROM file_scans 
    WHERE created_at >= CURDATE()";
$stmt = $db->query($query);
$engine_stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanning Engine - DLPS Enterprise</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .engine-status-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .status-indicator {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .status-dot {
            width: 16px;
            height: 16px;
            background: var(--status-safe);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--status-safe);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        
        .engine-metrics {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .metric-card {
            background: var(--tertiary-bg);
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .metric-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .metric-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .rules-table-container {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--border-color);
            transition: .4s;
            border-radius: 24px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: var(--accent-blue);
        }
        
        input:checked + .slider:before {
            transform: translateX(20px);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            width: 90%;
            max-width: 500px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Scanning Engine Configuration</h1>
                <p class="page-subtitle">Manage DLP detection rules and engine parameters</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-primary" onclick="openAddRuleModal()">
                    <i class="fas fa-plus"></i> Add Detection Rule
                </button>
            </div>
        </div>
        
        <div class="engine-status-card">
            <div class="status-indicator">
                <div class="status-dot"></div>
                <div>
                    <h3 style="margin-bottom: 0.25rem;">Engine Operational</h3>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">Running v2.4.0 • All systems nominal</p>
                </div>
            </div>
            <div style="text-align: right;">
                <button class="btn btn-secondary" onclick="restartEngine()">
                    <i class="fas fa-sync"></i> Restart Service
                </button>
            </div>
        </div>
        
        <div class="engine-metrics">
            <div class="metric-card">
                <div class="metric-value"><?= number_format($engine_stats['total_scans_today'] ?? 0) ?></div>
                <div class="metric-label">Scans Processed Today</div>
            </div>
            <div class="metric-card">
                <div class="metric-value"><?= round($engine_stats['avg_duration'] ?? 0) ?>ms</div>
                <div class="metric-label">Avg. Scan Latency</div>
            </div>
            <div class="metric-card">
                <div class="metric-value"><?= count($rules) ?></div>
                <div class="metric-label">Active Detection Rules</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Detection Rules</h2>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Priority</th>
                                <th>Rule Name</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Pattern / Value</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rules as $rule): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-info"><?= $rule['priority'] ?></span>
                                </td>
                                <td style="font-weight: 500; white-space: nowrap;"><?= htmlspecialchars($rule['rule_name']) ?></td>
                                <td style="color: var(--text-secondary); font-size: 0.9rem;"><?= htmlspecialchars($rule['description']) ?></td>
                                <td><span class="badge badge-secondary"><?= strtoupper($rule['rule_type']) ?></span></td>
                                <td style="font-family: var(--font-mono); font-size: 0.85rem; width: 35%;">
                                    <div style="background: rgba(0,0,0,0.2); padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border-color); color: var(--accent-cyan); overflow-wrap: break-word; word-wrap: break-word;">
                                        <?= htmlspecialchars($rule['rule_value']) ?>
                                    </div>
                                </td>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox" <?= $rule['enabled'] ? 'checked' : '' ?> onchange="toggleRule(<?= $rule['id'] ?>, this.checked)">
                                        <span class="slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <button class="btn btn-secondary" style="padding: 0.375rem 0.75rem;" onclick="deleteRule(<?= $rule['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Add Rule Modal -->
    <div class="modal" id="addRuleModal">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700;">Add Detection Rule</h2>
                <button style="background: none; border: none; color: white; cursor: pointer;" onclick="closeAddRuleModal()"><i class="fas fa-times"></i></button>
            </div>
            
            <form id="addRuleForm">
                <div class="form-group">
                    <label class="form-label">Rule Name</label>
                    <input type="text" name="rule_name" class="form-input" required placeholder="e.g. Forbidden IP Address">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Rule Type</label>
                    <select name="rule_type" class="form-select" required>
                        <option value="keyword">Keyword</option>
                        <option value="regex">Regex Pattern</option>
                        <option value="file_type">File Type</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Pattern / Value</label>
                    <textarea name="rule_value" class="form-textarea" required placeholder="Enter keyword or regex pattern..."></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Priority (1-100)</label>
                    <input type="number" name="priority" class="form-input" value="10" min="1" max="100">
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Add Rule</button>
            </form>
        </div>
    </div>

    <script>
        function openAddRuleModal() {
            document.getElementById('addRuleModal').classList.add('show');
        }
        
        function closeAddRuleModal() {
            document.getElementById('addRuleModal').classList.remove('show');
        }
        
        function restartEngine() {
            const btn = event.currentTarget;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Restarting...';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert('Scanning engine service restarted successfully.');
            }, 2000);
        }
        
        function toggleRule(id, state) {
            // In a real app, send AJAX request
            console.log(`Toggling rule ${id} to ${state}`);
        }
        
        function deleteRule(id) {
            if(confirm('Are you sure you want to delete this rule?')) {
                // In a real app, send AJAX request
                console.log(`Deleting rule ${id}`);
                location.reload(); // Simulate success
            }
        }
        
        document.getElementById('addRuleForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // In a real app, send AJAX request to add rule
            alert('Rule added successfully! (Simulation)');
            location.reload();
        });
        
        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('addRuleModal');
            if (event.target == modal) {
                closeAddRuleModal();
            }
        }
    </script>
</body>
</html>
