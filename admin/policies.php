<?php
require_once '../includes/auth.php';
$admin_id = Auth::requireAdmin();
$admin = Auth::getAdmin();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get all policies
$query = "SELECT p.*, a.username as created_by_name 
          FROM dlp_policies p
          LEFT JOIN admins a ON p.created_by = a.id
          ORDER BY p.severity DESC, p.created_at DESC";
$stmt = $db->query($query);
$policies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Management - DLPS Enterprise</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .policy-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 1.5rem;
        }
        
        .policy-list {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            height: fit-content;
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }
        
        .policy-list-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            background: var(--card-bg);
            z-index: 10;
        }
        
        .policy-item {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .policy-item:hover {
            background: var(--hover-bg);
        }
        
        .policy-item.active {
            background: var(--tertiary-bg);
            border-left: 3px solid var(--accent-blue);
        }
        
        .policy-item-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
        
        .policy-item-desc {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }
        
        .policy-item-meta {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .policy-detail {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
        }
        
        .policy-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .policy-title-section h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .policy-actions {
            display: flex;
            gap: 0.75rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .info-item {
            background: var(--tertiary-bg);
            padding: 1.25rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .info-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        
        .info-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .condition-box {
            background: var(--tertiary-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            font-family: var(--font-mono);
            font-size: 0.9rem;
            color: var(--text-accent);
            margin-top: 1rem;
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
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .close-modal {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.5rem;
            cursor: pointer;
            transition: color var(--transition-fast);
        }
        
        .close-modal:hover {
            color: var(--text-primary);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Policy Management</h1>
                <p class="page-subtitle">Create and manage DLP security policies</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-primary" onclick="openCreateModal()">
                    <i class="fas fa-plus"></i> Create New Policy
                </button>
            </div>
        </div>
        
        <div class="policy-grid">
            <!-- Policy List -->
            <div class="policy-list">
                <div class="policy-list-header">
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem;">All Policies</h3>
                    <p style="font-size: 0.85rem; color: var(--text-secondary);"><?= count($policies) ?> total policies</p>
                </div>
                
                <?php foreach ($policies as $index => $policy): ?>
                <div class="policy-item <?= $index === 0 ? 'active' : '' ?>" 
                     onclick="showPolicy(<?= $policy['id'] ?>)" 
                     id="policy-item-<?= $policy['id'] ?>">
                    <div class="policy-item-name"><?= htmlspecialchars($policy['policy_name']) ?></div>
                    <div class="policy-item-desc"><?= htmlspecialchars(substr($policy['description'], 0, 60)) ?>...</div>
                    <div class="policy-item-meta">
                        <span class="badge badge-<?= $policy['severity'] === 'critical' ? 'danger' : ($policy['severity'] === 'high' ? 'warning' : 'info') ?>">
                            <?= strtoupper($policy['severity']) ?>
                        </span>
                        <span class="badge badge-<?= $policy['status'] === 'active' ? 'safe' : 'info' ?>">
                            <?= strtoupper($policy['status']) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Policy Detail -->
            <div class="policy-detail" id="policyDetail">
                <?php if (!empty($policies)): ?>
                <?php $first_policy = $policies[0]; ?>
                <div class="policy-header">
                    <div class="policy-title-section">
                        <h2 id="detail-name"><?= htmlspecialchars($first_policy['policy_name']) ?></h2>
                        <p id="detail-desc" style="color: var(--text-secondary);"><?= htmlspecialchars($first_policy['description']) ?></p>
                    </div>
                    <div class="policy-actions">
                        <button class="btn btn-secondary" id="btn-edit" onclick="editPolicy(<?= $first_policy['id'] ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger" id="btn-delete" onclick="deletePolicy(<?= $first_policy['id'] ?>)">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Data Type</div>
                        <div class="info-value" id="detail-datatype"><?= strtoupper($first_policy['data_type']) ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Condition Type</div>
                        <div class="info-value" id="detail-conditiontype"><?= strtoupper($first_policy['condition_type']) ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Action</div>
                        <div class="info-value" id="detail-action">
                            <span class="badge badge-<?= $first_policy['action'] === 'block' ? 'danger' : 'warning' ?>">
                                <?= strtoupper($first_policy['action']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Severity</div>
                        <div class="info-value" id="detail-severity">
                            <span class="badge badge-<?= $first_policy['severity'] === 'critical' ? 'danger' : 'warning' ?>">
                                <?= strtoupper($first_policy['severity']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value" id="detail-status">
                            <span class="badge badge-<?= $first_policy['status'] === 'active' ? 'safe' : 'info' ?>">
                                <?= strtoupper($first_policy['status']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Created By</div>
                        <div class="info-value" id="detail-createdby"><?= htmlspecialchars($first_policy['created_by_name'] ?? 'System') ?></div>
                    </div>
                </div>
                
                <div style="margin-top: 2rem;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem;">Policy Logic</h3>
                    <div style="background: var(--tertiary-bg); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-color);">
                        <div style="margin-bottom: 1rem;">
                            <strong style="color: var(--accent-blue);">IF</strong>
                            <span style="color: var(--text-secondary);"> file contains </span>
                            <strong id="logic-condition"><?= strtoupper($first_policy['condition_type']) ?></strong>
                        </div>
                        <div class="condition-box" id="detail-conditionvalue">
                            <?= htmlspecialchars($first_policy['condition_value']) ?>
                        </div>
                        <div style="margin-top: 1rem;">
                            <strong style="color: var(--accent-red);">THEN</strong>
                            <span style="color: var(--text-secondary);"> perform action: </span>
                            <strong id="logic-action"><?= strtoupper($first_policy['action']) ?></strong>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; color: var(--text-muted); font-size: 0.85rem;">
                        <span id="detail-createdat">Created: <?= date('M d, Y H:i', strtotime($first_policy['created_at'])) ?></span>
                        <span id="detail-updatedat">Updated: <?= date('M d, Y H:i', strtotime($first_policy['updated_at'])) ?></span>
                    </div>
                </div>
                <?php else: ?>
                <div style="text-align: center; padding: 4rem; color: var(--text-secondary);">
                    <i class="fas fa-shield-alt" style="font-size: 4rem; margin-bottom: 1rem; display: block;"></i>
                    <h3>No Policies Yet</h3>
                    <p>Create your first DLP policy to get started</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Create/Edit Policy Modal -->
    <div class="modal" id="policyModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Create New Policy</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            
            <form id="policyForm">
                <input type="hidden" name="policy_id" id="policyId">
                
                <div class="form-group">
                    <label class="form-label">Policy Name</label>
                    <input type="text" name="policy_name" id="policyName" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="policyDescription" class="form-textarea" required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Data Type</label>
                    <select name="data_type" id="dataType" class="form-select" required>
                        <option value="pii">PII (Personal Identifiable Information)</option>
                        <option value="financial">Financial Data</option>
                        <option value="confidential">Confidential</option>
                        <option value="health">Health Information</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Condition Type</label>
                    <select name="condition_type" id="conditionType" class="form-select" required>
                        <option value="keyword">Keyword Match</option>
                        <option value="regex">Regular Expression</option>
                        <option value="pattern">Pattern Match</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Condition Value</label>
                    <textarea name="condition_value" id="conditionValue" class="form-textarea" 
                              placeholder="e.g., for keywords: password,secret,confidential" required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Action</label>
                    <select name="action" id="policyAction" class="form-select" required>
                        <option value="block">Block</option>
                        <option value="warn">Warn</option>
                        <option value="quarantine">Quarantine</option>
                        <option value="alert">Alert Only</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Severity</label>
                    <select name="severity" id="policySeverity" class="form-select" required>
                        <option value="critical">Critical</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="policyStatus" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-save"></i> Save Policy
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Embed all policies as JS object for instant switching
        const allPolicies = <?= json_encode($policies) ?>;
        
        function showPolicy(policyId) {
            // Remove active class from all items
            document.querySelectorAll('.policy-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to clicked item
            const activeItem = document.getElementById('policy-item-' + policyId);
            if(activeItem) activeItem.classList.add('active');
            
            // Find policy data
            const policy = allPolicies.find(p => p.id == policyId);
            if (!policy) return;
            
            // Update Detail View
            document.getElementById('detail-name').textContent = policy.policy_name;
            document.getElementById('detail-desc').textContent = policy.description;
            
            // Update Buttons
            document.getElementById('btn-edit').onclick = function() { editPolicy(policy.id); };
            document.getElementById('btn-delete').onclick = function() { deletePolicy(policy.id); };
            
            // Update Info Grid
            document.getElementById('detail-datatype').textContent = policy.data_type.toUpperCase();
            document.getElementById('detail-conditiontype').textContent = policy.condition_type.toUpperCase();
            document.getElementById('detail-createdby').textContent = policy.created_by_name || 'System';
            
            // Update Badges (Action)
            const actionEl = document.getElementById('detail-action');
            const actionClass = policy.action === 'block' ? 'danger' : 'warning';
            actionEl.innerHTML = `<span class="badge badge-${actionClass}">${policy.action.toUpperCase()}</span>`;
            
            // Update Badges (Severity)
            const severityEl = document.getElementById('detail-severity');
            const severityClass = policy.severity === 'critical' ? 'danger' : (policy.severity === 'high' ? 'warning' : 'info');
            severityEl.innerHTML = `<span class="badge badge-${severityClass}">${policy.severity.toUpperCase()}</span>`;
            
            // Update Badges (Status)
            const statusEl = document.getElementById('detail-status');
            const statusClass = policy.status === 'active' ? 'safe' : 'info';
            statusEl.innerHTML = `<span class="badge badge-${statusClass}">${policy.status.toUpperCase()}</span>`;
            
            // Update Logic
            document.getElementById('logic-condition').textContent = policy.condition_type.toUpperCase();
            document.getElementById('detail-conditionvalue').textContent = policy.condition_value;
            document.getElementById('logic-action').textContent = policy.action.toUpperCase();
            
            // Update Timestamps
            const created = new Date(policy.created_at).toLocaleString('en-US', {month:'short', day:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit', hour12: false});
            const updated = new Date(policy.updated_at).toLocaleString('en-US', {month:'short', day:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit', hour12: false});
            document.getElementById('detail-createdat').textContent = 'Created: ' + created;
            document.getElementById('detail-updatedat').textContent = 'Updated: ' + updated;
        }
        
        function openCreateModal() {
            document.getElementById('modalTitle').textContent = 'Create New Policy';
            document.getElementById('policyForm').reset();
            document.getElementById('policyId').value = '';
            document.getElementById('policyModal').classList.add('show');
        }
        
        function closeModal() {
            document.getElementById('policyModal').classList.remove('show');
        }
        
        function editPolicy(policyId) {
            const policy = allPolicies.find(p => p.id == policyId);
            if (!policy) return;
            
            document.getElementById('modalTitle').textContent = 'Edit Policy';
            document.getElementById('policyId').value = policy.id;
            document.getElementById('policyName').value = policy.policy_name;
            document.getElementById('policyDescription').value = policy.description;
            document.getElementById('dataType').value = policy.data_type;
            document.getElementById('conditionType').value = policy.condition_type;
            document.getElementById('conditionValue').value = policy.condition_value;
            document.getElementById('policyAction').value = policy.action;
            document.getElementById('policySeverity').value = policy.severity;
            document.getElementById('policyStatus').value = policy.status;
            
            document.getElementById('policyModal').classList.add('show');
        }
        
        function deletePolicy(policyId) {
            if (confirm('Are you sure you want to delete this policy?')) {
                // In production, this would use fetch
                alert('Delete functionality simulated for ID: ' + policyId);
            }
        }
        
        document.getElementById('policyForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            // Simulation
            alert('Policy saved successfully! (Simulation)');
            location.reload();
        });
    </script>
</body>
</html>
