<?php
require_once '../includes/auth.php';
$user_id = Auth::requireUser();
$user = Auth::getUser();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get full profile stats
$stmt = $db->prepare("SELECT COUNT(*) as total_scans, MAX(scanned_at) as last_scan FROM file_scans WHERE user_id = :uid");
$stmt->execute([':uid' => $user_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get user info again to be sure
$stmt = $db->prepare("SELECT * FROM users WHERE id = :uid");
$stmt->execute([':uid' => $user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - DLPS Enterprise</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">My Profile</h1>
                <p class="page-subtitle">Manage your account information</p>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
            <!-- Profile Card -->
            <div class="card" style="text-align: center;">
                <div style="width: 120px; height: 120px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white; box-shadow: var(--shadow-lg);">
                    <?= strtoupper(substr($profile['full_name'], 0, 1)) ?>
                </div>
                <h2 class="card-title" style="font-size: 1.5rem; margin-bottom: 0.5rem;"><?= htmlspecialchars($profile['full_name']) ?></h2>
                <p style="color: var(--text-secondary); margin-bottom: 2rem;"><?= htmlspecialchars($profile['email']) ?></p>
                
                <div class="badge badge-safe" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                    Active Account
                </div>
                
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color); text-align: left;">
                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.8rem; color: var(--text-muted);">Joined</label>
                        <div style="font-weight: 500;"><?= date('F d, Y', strtotime($profile['created_at'])) ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Activity Summary -->
            <div>
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <h3 class="card-title">Account Statistics</h3>
                    </div>
                    <div class="card-body">
                        <div class="stats-grid" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 0;">
                            <div class="stat-card" style="margin-bottom: 0;">
                                <div class="stat-label">Total Uploads</div>
                                <div class="stat-value"><?= $stats['total_scans'] ?></div>
                            </div>
                            <div class="stat-card" style="margin-bottom: 0;">
                                <div class="stat-label">Last Activity</div>
                                <div class="stat-value" style="font-size: 1.2rem;">
                                    <?= $stats['last_scan'] ? date('M d, H:i', strtotime($stats['last_scan'])) : 'Never' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Security Tips</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
                            <i class="fas fa-check-circle" style="color: var(--status-safe); font-size: 1.2rem; margin-top: 2px;"></i>
                            <div>
                                <h4 style="font-weight: 600; margin-bottom: 0.25rem;">Avoid uploading PII</h4>
                                <p style="color: var(--text-secondary); font-size: 0.9rem;">Never upload files containing Social Security Numbers or personal financial information unless encrypted.</p>
                            </div>
                        </div>
                        <div style="display: flex; gap: 1rem;">
                            <i class="fas fa-check-circle" style="color: var(--status-safe); font-size: 1.2rem; margin-top: 2px;"></i>
                            <div>
                                <h4 style="font-weight: 600; margin-bottom: 0.25rem;">Classify your data</h4>
                                <p style="color: var(--text-secondary); font-size: 0.9rem;">Always check if your document contains "Confidential" or "Internal Use Only" markings before uploading.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
