<?php
require_once '../includes/auth.php';
$user_id = Auth::requireUser();
$user = Auth::getUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - DLPS Enterprise</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Settings</h1>
                <p class="page-subtitle">Configure your account preferences</p>
            </div>
        </div>
        
        <div class="card" style="max-width: 600px;">
            <div class="card-header">
                <h2 class="card-title">Change Password</h2>
            </div>
            <div class="card-body">
                <form action="#" onsubmit="alert('Password changing is disabled in this demo.'); return false;">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-input" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </div>
        
        <div class="card" style="max-width: 600px; margin-top: 2rem;">
            <div class="card-header">
                <h2 class="card-title">Notification Preferences</h2>
            </div>
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-bottom: 1px solid var(--border-color);">
                    <div>
                        <div style="font-weight: 600;">Email Alerts</div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">Receive emails when a scan detects high risks</div>
                    </div>
                    <label class="switch" style="position: relative; display: inline-block; width: 50px; height: 24px;">
                        <input type="checkbox" checked>
                        <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--tertiary-bg); transition: .4s; border-radius: 24px;"></span>
                        <span style="position: absolute; content: ''; height: 16px; width: 16px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%;"></span>
                    </label>
                    <style>
                        input:checked + span { background-color: var(--accent-blue); }
                        input:checked + span:before { transform: translateX(26px); }
                    </style>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
