<?php
require_once '../includes/auth.php';
$admin_id = Auth::requireAdmin();
$admin = Auth::getAdmin();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Handle User Actions (Suspend/Activate)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $action = $_POST['action'];
    $user_id = (int)$_POST['user_id'];
    
    if ($action === 'toggle_status') {
        $stmt = $db->prepare("UPDATE users SET status = CASE WHEN status = 'active' THEN 'suspended' ELSE 'active' END WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
    }
    
    // Redirect to avoid resubmission
    header('Location: users.php');
    exit;
}

// Get all users
$query = "SELECT u.*, 
          (SELECT COUNT(*) FROM file_scans WHERE user_id = u.id) as total_scans,
          (SELECT COUNT(*) FROM file_scans WHERE user_id = u.id AND risk_level IN ('high', 'critical', 'blocked')) as violations
          FROM users u 
          ORDER BY u.created_at DESC";
$stmt = $db->query($query);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - DLPS Enterprise</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .user-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .user-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--accent-blue);
        }
        
        .user-card-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 48px;
            height: 48px;
            background: var(--tertiary-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: var(--text-primary);
            font-weight: 700;
        }
        
        .user-info h3 {
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }
        
        .user-email {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }
        
        .user-stats {
            padding: 1.5rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .stat-item {
            text-align: center;
            padding: 0.75rem;
            background: var(--tertiary-bg);
            border-radius: 8px;
        }
        
        .stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 0.25rem;
        }
        
        .user-actions {
            padding: 1rem 1.5rem;
            background: var(--tertiary-bg);
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        
        .status-suspended {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
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
                <h1 class="page-title">User Management</h1>
                <p class="page-subtitle">Manage system access and monitor user activity</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-primary" onclick="openAddUserModal()">
                    <i class="fas fa-user-plus"></i> Add New User
                </button>
            </div>
        </div>
        
        <!-- User Grid -->
        <div class="user-card-grid">
            <?php foreach ($users as $user): ?>
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-avatar">
                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                    </div>
                    <div class="user-info">
                        <h3><?= htmlspecialchars($user['full_name']) ?></h3>
                        <div class="user-email"><?= htmlspecialchars($user['email']) ?></div>
                    </div>
                </div>
                
                <div class="user-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?= number_format($user['total_scans']) ?></div>
                        <div class="stat-label">Total Scans</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" style="color: <?= $user['violations'] > 0 ? 'var(--status-danger)' : 'var(--text-primary)' ?>">
                            <?= number_format($user['violations']) ?>
                        </div>
                        <div class="stat-label">Violations</div>
                    </div>
                </div>
                
                <div class="user-actions">
                    <span class="status-badge status-<?= $user['status'] ?>">
                        <?= ucfirst($user['status']) ?>
                    </span>
                    
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn btn-secondary" style="padding: 0.4rem;" onclick="viewUserLogs(<?= $user['id'] ?>)" title="View Logs">
                            <i class="fas fa-list"></i>
                        </button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button type="submit" class="btn btn-secondary" style="padding: 0.4rem; color: <?= $user['status'] === 'active' ? 'var(--status-danger)' : 'var(--status-safe)' ?>;" title="<?= $user['status'] === 'active' ? 'Suspend User' : 'Activate User' ?>">
                                <i class="fas fa-<?= $user['status'] === 'active' ? 'ban' : 'check' ?>"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
    
    <!-- Add User Modal (Placeholder) -->
    <div class="modal" id="addUserModal">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700;">Add New User</h2>
                <button style="background: none; border: none; color: white; cursor: pointer;" onclick="closeAddUserModal()"><i class="fas fa-times"></i></button>
            </div>
            <form id="addUserForm">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-input" required placeholder="John Doe">
                </div>
                <!-- More fields would go here -->
                <button type="submit" class="btn btn-primary" style="width: 100%;">Create User</button>
            </form>
        </div>
    </div>

    <script>
        function openAddUserModal() {
            document.getElementById('addUserModal').classList.add('show');
        }
        
        function closeAddUserModal() {
            document.getElementById('addUserModal').classList.remove('show');
        }
        
        function viewUserLogs(userId) {
            window.location.href = `audit-trail.php?actor_type=user&actor_id=${userId}`;
        }
        
        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('User creation feature coming soon!');
            closeAddUserModal();
        });
        
        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('addUserModal');
            if (event.target == modal) {
                closeAddUserModal();
            }
        }
    </script>
</body>
</html>
