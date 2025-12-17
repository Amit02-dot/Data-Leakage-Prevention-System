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
    <title>Upload & Scan - DLPS Enterprise</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .upload-zone {
            border: 3px dashed var(--border-light);
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            background: var(--tertiary-bg);
            transition: all var(--transition-base);
            cursor: pointer;
            margin-bottom: 2rem;
        }
        
        .upload-zone:hover, .upload-zone.dragover {
            border-color: var(--accent-blue);
            background: var(--hover-bg);
        }
        
        .upload-icon {
            font-size: 4rem;
            color: var(--accent-blue);
            margin-bottom: 1rem;
        }
        
        .upload-text {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .upload-hint {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .file-preview {
            display: none;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .file-preview.show {
            display: block;
        }
        
        .file-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .file-icon-large {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }
        
        .scan-progress {
            margin-top: 1.5rem;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--tertiary-bg);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent-blue), var(--accent-purple));
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .scan-result {
            display: none;
            margin-top: 2rem;
        }
        
        .scan-result.show {
            display: block;
        }
        
        .result-card {
            background: var(--card-bg);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
        }
        
        .result-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }
        
        .result-icon.safe {
            color: var(--status-safe);
        }
        
        .result-icon.blocked {
            color: var(--status-danger);
        }
        
        .result-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .result-details {
            background: var(--tertiary-bg);
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Upload & Scan Files</h1>
                <p class="page-subtitle">Upload files to scan for sensitive data and policy violations</p>
            </div>
        </div>
        
        <!-- Upload Zone -->
        <div class="upload-zone" id="uploadZone">
            <div class="upload-icon">
                <i class="fas fa-cloud-upload-alt"></i>
            </div>
            <div class="upload-text">Drag & Drop Files Here</div>
            <div class="upload-hint">or click to browse (PDF, DOCX, TXT, CSV, Images - Max 10MB)</div>
            <input type="file" id="fileInput" style="display: none;" 
                   accept=".pdf,.doc,.docx,.txt,.csv,.jpg,.jpeg,.png">
        </div>
        
        <!-- File Preview -->
        <div class="file-preview" id="filePreview">
            <div class="file-info">
                <div class="file-icon-large">
                    <i class="fas fa-file" id="fileIconPreview"></i>
                </div>
                <div style="flex: 1;">
                    <h3 id="fileName" style="margin-bottom: 0.5rem;"></h3>
                    <div style="color: var(--text-secondary); font-size: 0.9rem;">
                        <span id="fileSize"></span> • <span id="fileType"></span>
                    </div>
                </div>
                <button class="btn btn-danger" onclick="cancelUpload()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
            
            <button class="btn btn-primary" style="width: 100%;" onclick="startScan()">
                <i class="fas fa-shield-alt"></i> Start Security Scan
            </button>
            
            <div class="scan-progress" id="scanProgress" style="display: none;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="color: var(--text-secondary); font-size: 0.9rem;">Scanning...</span>
                    <span style="color: var(--text-accent); font-weight: 600;" id="progressText">0%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.5rem;" id="scanStatus">
                    Initializing scan...
                </div>
            </div>
        </div>
        
        <!-- Scan Result -->
        <div class="scan-result" id="scanResult">
            <div class="result-card" id="resultCard">
                <div class="result-icon" id="resultIcon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2 class="result-title" id="resultTitle">Scan Complete</h2>
                <p style="color: var(--text-secondary);" id="resultMessage"></p>
                
                <div class="result-details" id="resultDetails"></div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem; justify-content: center;">
                    <button class="btn btn-primary" onclick="uploadAnother()">
                        <i class="fas fa-upload"></i> Upload Another File
                    </button>
                    <a href="scan-history.php" class="btn btn-secondary">
                        <i class="fas fa-history"></i> View History
                    </a>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('fileInput');
        const filePreview = document.getElementById('filePreview');
        let selectedFile = null;
        
        // Click to upload
        uploadZone.addEventListener('click', () => fileInput.click());
        
        // Drag and drop
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });
        
        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });
        
        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                handleFile(e.dataTransfer.files[0]);
            }
        });
        
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFile(e.target.files[0]);
            }
        });
        
        function handleFile(file) {
            // Validate file size (10MB max)
            if (file.size > 10 * 1024 * 1024) {
                alert('File size exceeds 10MB limit');
                return;
            }
            
            selectedFile = file;
            
            // Show preview
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = formatBytes(file.size);
            document.getElementById('fileType').textContent = file.type || 'Unknown';
            
            // Set icon based on file type
            const icon = getFileIcon(file.name);
            document.getElementById('fileIconPreview').className = icon;
            
            uploadZone.style.display = 'none';
            filePreview.classList.add('show');
        }
        
        function cancelUpload() {
            selectedFile = null;
            fileInput.value = '';
            uploadZone.style.display = 'block';
            filePreview.classList.remove('show');
            document.getElementById('scanProgress').style.display = 'none';
            document.getElementById('scanResult').classList.remove('show');
        }
        
        async function startScan() {
            if (!selectedFile) return;
            
            const scanProgress = document.getElementById('scanProgress');
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            const scanStatus = document.getElementById('scanStatus');
            
            scanProgress.style.display = 'block';
            
            // Upload file
            const formData = new FormData();
            formData.append('file', selectedFile);
            
            try {
                // Simulate upload progress
                updateProgress(20, 'Uploading file...', progressFill, progressText, scanStatus);
                
                const response = await fetch('../api/scan/upload.php', {
                    method: 'POST',
                    body: formData
                });
                
                updateProgress(40, 'Analyzing file content...', progressFill, progressText, scanStatus);
                await sleep(800);
                
                updateProgress(60, 'Checking DLP policies...', progressFill, progressText, scanStatus);
                await sleep(800);
                
                updateProgress(80, 'Detecting sensitive data...', progressFill, progressText, scanStatus);
                await sleep(800);
                
                updateProgress(100, 'Finalizing scan...', progressFill, progressText, scanStatus);
                await sleep(500);
                
                const result = await response.json();
                
                if (result.success) {
                    showResult(result.data);
                } else {
                    alert('Scan failed: ' + result.message);
                }
            } catch (error) {
                alert('Error during scan: ' + error.message);
            }
        }
        
        function updateProgress(percent, status, fillEl, textEl, statusEl) {
            fillEl.style.width = percent + '%';
            textEl.textContent = percent + '%';
            statusEl.textContent = status;
        }
        
        function showResult(data) {
            const scanResult = document.getElementById('scanResult');
            const resultIcon = document.getElementById('resultIcon');
            const resultTitle = document.getElementById('resultTitle');
            const resultMessage = document.getElementById('resultMessage');
            const resultDetails = document.getElementById('resultDetails');
            const resultCard = document.getElementById('resultCard');
            
            filePreview.style.display = 'none';
            scanResult.classList.add('show');
            
            if (data.risk_level === 'safe') {
                resultIcon.className = 'result-icon safe';
                resultIcon.innerHTML = '<i class="fas fa-check-circle"></i>';
                resultTitle.textContent = 'File is Safe';
                resultMessage.textContent = 'No sensitive data or policy violations detected.';
                resultCard.style.borderColor = 'var(--status-safe)';
            } else if (data.risk_level === 'blocked') {
                resultIcon.className = 'result-icon blocked';
                resultIcon.innerHTML = '<i class="fas fa-ban"></i>';
                resultTitle.textContent = 'File Blocked';
                resultMessage.textContent = 'This file contains sensitive data and has been blocked.';
                resultCard.style.borderColor = 'var(--status-danger)';
            } else {
                resultIcon.className = 'result-icon';
                resultIcon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                resultIcon.style.color = 'var(--status-warning)';
                resultTitle.textContent = 'Warning Detected';
                resultMessage.textContent = 'Potential sensitive data found. Review carefully.';
                resultCard.style.borderColor = 'var(--status-warning)';
            }
            
            // Build details
            let detailsHTML = `
                <div class="detail-row">
                    <span style="color: var(--text-secondary);">Risk Level</span>
                    <span class="badge badge-${data.risk_level === 'safe' ? 'safe' : 'danger'}">${data.risk_level.toUpperCase()}</span>
                </div>
                <div class="detail-row">
                    <span style="color: var(--text-secondary);">Scan Duration</span>
                    <span style="font-weight: 600;">${data.scan_duration_ms}ms</span>
                </div>
                <div class="detail-row">
                    <span style="color: var(--text-secondary);">Policies Checked</span>
                    <span style="font-weight: 600;">${data.policies_checked || 0}</span>
                </div>
            `;
            
            if (data.sensitive_data_found && data.sensitive_data_found.length > 0) {
                detailsHTML += `
                    <div class="detail-row">
                        <span style="color: var(--text-secondary);">Sensitive Data Types</span>
                        <span style="font-weight: 600; color: var(--status-danger);">${data.sensitive_data_found.join(', ')}</span>
                    </div>
                `;
            }
            
            resultDetails.innerHTML = detailsHTML;
        }
        
        function uploadAnother() {
            cancelUpload();
        }
        
        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            const iconMap = {
                'pdf': 'fas fa-file-pdf',
                'doc': 'fas fa-file-word',
                'docx': 'fas fa-file-word',
                'txt': 'fas fa-file-alt',
                'csv': 'fas fa-file-csv',
                'jpg': 'fas fa-file-image',
                'jpeg': 'fas fa-file-image',
                'png': 'fas fa-file-image'
            };
            return iconMap[ext] || 'fas fa-file';
        }
        
        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
        
        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }
    </script>
</body>
</html>
