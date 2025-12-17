<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support - DLPS Enterprise</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div style="min-height: 100vh; padding: 2rem;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 3rem;">
                <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">Help & Support</h1>
                <p style="color: var(--text-secondary); font-size: 1.1rem;">Get help with DLPS Enterprise</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
                <div class="card">
                    <div style="text-align: center; padding: 2rem;">
                        <i class="fas fa-book" style="font-size: 3rem; color: var(--accent-blue); margin-bottom: 1rem;"></i>
                        <h3 style="margin-bottom: 1rem;">Documentation</h3>
                        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                            Complete guides and tutorials for using DLPS
                        </p>
                        <button class="btn btn-primary" onclick="window.open('README.md')">
                            View Docs
                        </button>
                    </div>
                </div>
                
                <div class="card">
                    <div style="text-align: center; padding: 2rem;">
                        <i class="fas fa-question-circle" style="font-size: 3rem; color: var(--accent-purple); margin-bottom: 1rem;"></i>
                        <h3 style="margin-bottom: 1rem;">FAQ</h3>
                        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                            Frequently asked questions and answers
                        </p>
                        <button class="btn btn-primary">
                            View FAQ
                        </button>
                    </div>
                </div>
                
                <div class="card">
                    <div style="text-align: center; padding: 2rem;">
                        <i class="fas fa-envelope" style="font-size: 3rem; color: var(--accent-green); margin-bottom: 1rem;"></i>
                        <h3 style="margin-bottom: 1rem;">Contact Support</h3>
                        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                            Get in touch with our support team
                        </p>
                        <button class="btn btn-primary">
                            Contact Us
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quick Start Guide</h2>
                </div>
                <div class="card-body">
                    <h3 style="margin-bottom: 1rem;">For Users:</h3>
                    <ol style="color: var(--text-secondary); line-height: 2;">
                        <li>Login with your user credentials</li>
                        <li>Navigate to "Upload & Scan"</li>
                        <li>Drag and drop or browse to select a file</li>
                        <li>Click "Start Security Scan"</li>
                        <li>Review the scan results</li>
                        <li>Check "Scan History" for past scans</li>
                    </ol>
                    
                    <h3 style="margin-top: 2rem; margin-bottom: 1rem;">For Admins:</h3>
                    <ol style="color: var(--text-secondary); line-height: 2;">
                        <li>Login via the Admin portal</li>
                        <li>Review the security dashboard</li>
                        <li>Manage DLP policies in "Policy Management"</li>
                        <li>Monitor user activity in "Audit Trail"</li>
                        <li>Configure scanning rules in "Scanning Engine"</li>
                    </ol>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 3rem;">
                <button class="btn btn-secondary" onclick="window.history.back()">
                    <i class="fas fa-arrow-left"></i> Go Back
                </button>
            </div>
        </div>
    </div>
</body>
</html>
