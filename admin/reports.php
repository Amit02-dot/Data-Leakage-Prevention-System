<?php
require_once '../includes/auth.php';
$admin_id = Auth::requireAdmin();
$admin = Auth::getAdmin();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Mock data for recent reports (in a real app, this would come from a reports table)
$recent_reports = [
    ['id' => 101, 'type' => 'Executive Summary', 'generated_by' => 'System Administrator', 'date' => '2025-12-15 09:00:00', 'format' => 'PDF', 'size' => '2.4 MB'],
    ['id' => 102, 'type' => 'Weekly Incident Report', 'generated_by' => 'System Administrator', 'date' => '2025-12-14 17:30:00', 'format' => 'CSV', 'size' => '450 KB'],
    ['id' => 103, 'type' => 'User Activity Log', 'generated_by' => 'System Administrator', 'date' => '2025-12-12 11:15:00', 'format' => 'PDF', 'size' => '1.8 MB'],
    ['id' => 104, 'type' => 'Blocked File Analysis', 'generated_by' => 'System', 'date' => '2025-12-10 08:00:00', 'format' => 'PDF', 'size' => '3.1 MB'],
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - DLPS Enterprise</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .report-type-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .report-type-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent-blue);
            box-shadow: var(--shadow-lg);
        }
        
        .report-type-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-blue), var(--accent-purple));
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .report-type-card:hover::before {
            opacity: 1;
        }
        
        .report-icon {
            width: 64px;
            height: 64px;
            background: var(--tertiary-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: var(--accent-blue);
            margin: 0 auto 1.5rem;
        }
        
        .report-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        
        .report-desc {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .format-pdf {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .format-csv {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
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
        
        .date-range-picker {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">System Reports</h1>
                <p class="page-subtitle">Generate and audit security compliance reports</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-secondary">
                    <i class="fas fa-cog"></i> Report Settings
                </button>
            </div>
        </div>
        
        <!-- Report Generators -->
        <h2 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--text-secondary);">Generate New Report</h2>
        <div class="reports-grid">
            <div class="report-type-card" onclick="openGenerateModal('Executive Summary')">
                <div class="report-icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h3 class="report-title">Executive Summary</h3>
                <p class="report-desc">High-level overview of security posture, incidents, and trends for management review.</p>
                <button class="btn btn-primary" style="width: 100%;">Generate</button>
            </div>
            
            <div class="report-type-card" onclick="openGenerateModal('Incident Analysis')">
                <div class="report-icon">
                    <i class="fas fa-shield-virus"></i>
                </div>
                <h3 class="report-title">Incident Analysis</h3>
                <p class="report-desc">Detailed breakdown of policy violations, blocked files, and risk severity distribution.</p>
                <button class="btn btn-primary" style="width: 100%;">Generate</button>
            </div>
            
            <div class="report-type-card" onclick="openGenerateModal('User Activity')">
                <div class="report-icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h3 class="report-title">User Activity Details</h3>
                <p class="report-desc">Audit logs of user actions, login attempts, and file transfer history.</p>
                <button class="btn btn-primary" style="width: 100%;">Generate</button>
            </div>
        </div>
        
        <!-- Recent Reports Table -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="card-title">Recent Reports</h2>
                <div class="search-box" style="width: 250px;">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search reports...">
                </div>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Report Name</th>
                                <th>Generated By</th>
                                <th>Date Generated</th>
                                <th>Format</th>
                                <th>Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_reports as $report): ?>
                            <tr>
                                <td style="font-weight: 500;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <i class="fas fa-file-alt" style="color: var(--text-secondary);"></i>
                                        <?= htmlspecialchars($report['type']) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($report['generated_by']) ?></td>
                                <td><?= date('M d, Y h:i A', strtotime($report['date'])) ?></td>
                                <td>
                                    <span class="status-badge format-<?= strtolower($report['format']) ?>">
                                        <?= $report['format'] ?>
                                    </span>
                                </td>
                                <td><?= $report['size'] ?></td>
                                <td>
                                    <button class="btn btn-secondary" style="padding: 0.375rem 0.75rem;">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="btn btn-secondary" style="padding: 0.375rem 0.75rem; color: var(--status-danger);">
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
    
    <!-- Generate Report Modal -->
    <div class="modal" id="generateModal">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700;">Generate Report</h2>
                <button style="background: none; border: none; color: white; cursor: pointer;" onclick="closeGenerateModal()"><i class="fas fa-times"></i></button>
            </div>
            
            <form id="generateForm">
                <input type="hidden" id="reportType" name="type">
                
                <div class="form-group">
                    <label class="form-label" id="modalTitle">Report Type</label>
                    <div style="padding: 0.75rem; background: var(--tertiary-bg); border-radius: 6px; color: var(--text-primary); margin-bottom: 1rem;" id="selectedReport"></div>
                </div>
                
                <div class="date-range-picker">
                    <div class="form-group">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-input" required value="<?= date('Y-m-01') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-input" required value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Format</label>
                    <select class="form-select">
                        <option value="pdf">PDF Document</option>
                        <option value="csv">CSV / Excel</option>
                        <option value="json">JSON Data</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-file-export"></i> Generate Report
                </button>
            </form>
        </div>
    </div>

    <script>
        function openGenerateModal(type) {
            document.getElementById('reportType').value = type;
            document.getElementById('selectedReport').textContent = type;
            document.getElementById('generateModal').classList.add('show');
        }
        
        function closeGenerateModal() {
            document.getElementById('generateModal').classList.remove('show');
        }
        
        document.getElementById('generateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            btn.disabled = true;
            
            // Simulate generation delay
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                closeGenerateModal();
                alert('Report generated successfully! Download started.');
            }, 2000);
        });
        
        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('generateModal');
            if (event.target == modal) {
                closeGenerateModal();
            }
        }
    </script>
</body>
</html>
