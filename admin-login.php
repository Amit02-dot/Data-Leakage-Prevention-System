<?php
session_set_cookie_params(0, '/');
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Enterprise Data Leakage Prevention System - Admin Login">
    <title>Admin Login - DLPS Enterprise</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 100%);
            position: relative;
            overflow: hidden;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(239, 68, 68, 0.1) 0%, transparent 70%);
            top: -250px;
            right: -250px;
            animation: pulse 8s ease-in-out infinite;
        }
        
        .login-container::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.1) 0%, transparent 70%);
            bottom: -200px;
            left: -200px;
            animation: pulse 6s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .login-box {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            box-shadow: var(--shadow-xl);
            position: relative;
            z-index: 1;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, var(--accent-red), var(--accent-yellow));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.3);
        }
        
        .login-title {
            font-size: 1.875rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        
        .role-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--accent-red);
            border-radius: 8px;
            color: var(--accent-red);
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 2rem;
        }
        
        .security-notice {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid var(--accent-yellow);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--accent-yellow);
            font-size: 0.85rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: none;
        }
        
        .alert.show {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--accent-red);
            color: var(--accent-red);
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--accent-green);
            color: var(--accent-green);
        }
        
        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.1rem;
        }
        
        .login-input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            background: var(--tertiary-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: all var(--transition-base);
        }
        
        .login-input:focus {
            outline: none;
            border-color: var(--accent-red);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }
        
        .login-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--accent-red), var(--accent-yellow));
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all var(--transition-base);
            margin-top: 1rem;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg), 0 0 20px rgba(239, 68, 68, 0.3);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-footer {
            margin-top: 2rem;
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }
        
        .login-link {
            color: var(--accent-blue);
            text-decoration: none;
            font-weight: 600;
            transition: color var(--transition-fast);
        }
        
        .login-link:hover {
            color: var(--accent-cyan);
        }
        
        .switch-role {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h1 class="login-title">Admin Portal</h1>
                <p class="login-subtitle">Security Administration Console</p>
                <div style="margin-top: 1.5rem;">
                    <span class="role-indicator">
                        <i class="fas fa-shield-halved"></i>
                        Administrator Access
                    </span>
                </div>
            </div>
            
            <div class="security-notice">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Restricted access. All login attempts are logged and monitored.</span>
            </div>
            
            <div id="alertBox" class="alert"></div>
            
            <form id="adminLoginForm" method="POST">
                <div class="input-group">
                    <i class="fas fa-user-shield input-icon"></i>
                    <input 
                        type="text" 
                        name="username" 
                        id="username" 
                        class="login-input" 
                        placeholder="Admin Username"
                        required
                        autocomplete="username"
                    >
                </div>
                
                <div class="input-group">
                    <i class="fas fa-key input-icon"></i>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        class="login-input" 
                        placeholder="Admin Password"
                        required
                        autocomplete="current-password"
                    >
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-shield-halved"></i> Secure Login
                </button>
            </form>
            
            <div class="login-footer">
                <div class="switch-role">
                    <i class="fas fa-user"></i>
                    <span>Regular User?</span>
                    <a href="index.php" class="login-link">User Login</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('adminLoginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const alertBox = document.getElementById('alertBox');
            
            try {
                const response = await fetch('api/auth/admin-login.php', {
                    method: 'POST',
                    body: formData
                });
                
                let result;
                try {
                    const text = await response.text();
                    result = JSON.parse(text);
                } catch (e) {
                    throw new Error('Invalid server response');
                }
                
                if (result.success) {
                    alertBox.className = 'alert alert-success show';
                    alertBox.innerHTML = '<i class="fas fa-check-circle"></i> Authentication successful! Redirecting...';
                    setTimeout(() => {
                        window.location.href = 'admin/dashboard.php';
                    }, 1000);
                } else {
                    alertBox.className = 'alert alert-error show';
                    alertBox.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + result.message;
                }
            } catch (error) {
                console.error('Login Error:', error);
                alertBox.className = 'alert alert-error show';
                alertBox.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Connection error. Please try again.';
            }
        });
    </script>
</body>
</html>
