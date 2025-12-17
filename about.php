<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - DLPS Enterprise</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div style="min-height: 100vh; padding: 2rem;">
        <div style="max-width: 1000px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 3rem;">
                <div style="width: 100px; height: 100px; margin: 0 auto 2rem; background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple)); border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white; box-shadow: var(--shadow-glow);">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">DLPS Enterprise</h1>
                <p style="color: var(--text-secondary); font-size: 1.1rem;">Data Leakage Prevention System</p>
                <p style="color: var(--text-muted); margin-top: 0.5rem;">Version 1.0.0</p>
            </div>
            
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h2 class="card-title">About the System</h2>
                </div>
                <div class="card-body">
                    <p style="color: var(--text-secondary); line-height: 1.8; margin-bottom: 1rem;">
                        DLPS Enterprise is a production-ready, enterprise-grade Data Leakage Prevention System designed for academic evaluation and placement-oriented review. The system demonstrates professional cybersecurity architecture with strict role-based access control, comprehensive audit logging, and advanced DLP scanning capabilities.
                    </p>
                    <p style="color: var(--text-secondary); line-height: 1.8;">
                        Built with modern web technologies and following industry-standard security practices, DLPS Enterprise provides a complete solution for detecting and preventing sensitive data leakage through automated file scanning and policy enforcement.
                    </p>
                </div>
            </div>
            
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h2 class="card-title">Key Features</h2>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                        <div>
                            <h4 style="color: var(--accent-blue); margin-bottom: 0.5rem;">
                                <i class="fas fa-lock"></i> Secure Authentication
                            </h4>
                            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                                Separate login flows for users and administrators with role-based access control
                            </p>
                        </div>
                        
                        <div>
                            <h4 style="color: var(--accent-purple); margin-bottom: 0.5rem;">
                                <i class="fas fa-shield-alt"></i> DLP Scanning
                            </h4>
                            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                                Advanced scanning engine with keyword and regex pattern detection
                            </p>
                        </div>
                        
                        <div>
                            <h4 style="color: var(--accent-green); margin-bottom: 0.5rem;">
                                <i class="fas fa-clipboard-list"></i> Audit Trail
                            </h4>
                            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                                Immutable logs tracking all user and admin activities
                            </p>
                        </div>
                        
                        <div>
                            <h4 style="color: var(--accent-yellow); margin-bottom: 0.5rem;">
                                <i class="fas fa-cogs"></i> Policy Management
                            </h4>
                            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                                Dynamic policy creation and management with flexible rules
                            </p>
                        </div>
                        
                        <div>
                            <h4 style="color: var(--accent-cyan); margin-bottom: 0.5rem;">
                                <i class="fas fa-chart-line"></i> Analytics
                            </h4>
                            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                                Real-time dashboards with comprehensive security metrics
                            </p>
                        </div>
                        
                        <div>
                            <h4 style="color: var(--accent-red); margin-bottom: 0.5rem;">
                                <i class="fas fa-exclamation-triangle"></i> Risk Detection
                            </h4>
                            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                                Multi-level risk classification and automated responses
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h2 class="card-title">Technology Stack</h2>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                        <div style="padding: 1rem; background: var(--tertiary-bg); border-radius: 8px;">
                            <strong style="color: var(--text-primary);">Backend:</strong>
                            <span style="color: var(--text-secondary);"> PHP 8+ with PDO</span>
                        </div>
                        <div style="padding: 1rem; background: var(--tertiary-bg); border-radius: 8px;">
                            <strong style="color: var(--text-primary);">Database:</strong>
                            <span style="color: var(--text-secondary);"> MySQL 8.0+</span>
                        </div>
                        <div style="padding: 1rem; background: var(--tertiary-bg); border-radius: 8px;">
                            <strong style="color: var(--text-primary);">Frontend:</strong>
                            <span style="color: var(--text-secondary);"> HTML5, CSS3, JavaScript ES6+</span>
                        </div>
                        <div style="padding: 1rem; background: var(--tertiary-bg); border-radius: 8px;">
                            <strong style="color: var(--text-primary);">Charts:</strong>
                            <span style="color: var(--text-secondary);"> Chart.js 4.4.0</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 3rem;">
                <p style="color: var(--text-muted); margin-bottom: 1rem;">
                    © 2024 DLPS Enterprise. All rights reserved.
                </p>
                <button class="btn btn-secondary" onclick="window.history.back()">
                    <i class="fas fa-arrow-left"></i> Go Back
                </button>
            </div>
        </div>
    </div>
</body>
</html>
