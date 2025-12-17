<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Enterprise Data Leakage Prevention System - Reset Password">
    <title>Forgot Password - DLPS Enterprise</title>
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
            padding: 2rem 0;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            top: -250px;
            right: -250px;
            animation: pulse 8s ease-in-out infinite;
        }
        
        .login-container::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, transparent 70%);
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
            padding: 2.5rem;
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
            width: 70px;
            height: 70px;
            margin: 0 auto 1.25rem;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            box-shadow: var(--shadow-glow);
        }
        
        .login-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        
        .input-group {
            position: relative;
            margin-bottom: 1.25rem;
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
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .login-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
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
            box-shadow: var(--shadow-lg), var(--shadow-glow);
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-lock-open"></i>
                </div>
                <h1 class="login-title">Reset Password</h1>
                <p class="login-subtitle">Enter your email to receive a reset link</p>
            </div>
            
            <div id="alertBox" class="alert"></div>
            
            <form id="forgotForm">
                <div class="input-group">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" name="email" class="login-input" placeholder="Enter your email address" required>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-paper-plane"></i> Send Reset Link
                </button>
            </form>
            
            <div style="margin-top: 2rem; text-align: center; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                <p style="color: var(--text-secondary);">
                    Remember your password? <a href="index.php" class="login-link">Sign In</a>
                </p>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('forgotForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const alertBox = document.getElementById('alertBox');
            
            // Add loading state
            const btn = e.target.querySelector('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            btn.disabled = true;
            
            try {
                // Simulate API call delay
                await new Promise(r => setTimeout(r, 1500));
                
                // Since this is a detailed simulation without an SMTP server:
                // We'll show a success message as if an email was sent.
                
                alertBox.className = 'alert alert-success show';
                alertBox.innerHTML = '<i class="fas fa-check-circle"></i> Password reset link has been sent to your email!';
                
                btn.innerHTML = '<i class="fas fa-check"></i> Sent';
                e.target.reset();
                
            } catch (error) {
                alertBox.className = 'alert alert-error show';
                alertBox.innerHTML = '<i class="fas fa-exclamation-triangle"></i> An error occurred. Please try again.';
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>
