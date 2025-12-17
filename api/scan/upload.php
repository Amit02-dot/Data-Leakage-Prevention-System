<?php
/**
 * File Upload and Scan API
 * Handles file upload, scanning, and DLP policy enforcement
 */

header('Content-Type: application/json');
session_start();

require_once '../../config/database.php';

// Check authentication
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'user') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Validate file upload
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'File upload failed']);
        exit;
    }
    
    $file = $_FILES['file'];
    $file_name = basename($file['name']);
    $file_size = $file['size'];
    $file_tmp = $file['tmp_name'];
    $file_type = pathinfo($file_name, PATHINFO_EXTENSION);
    
    // Validate file size (10MB max)
    if ($file_size > 10 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds 10MB limit']);
        exit;
    }
    
    // Allowed file types
    $allowed_types = ['pdf', 'doc', 'docx', 'txt', 'csv', 'jpg', 'jpeg', 'png'];
    if (!in_array(strtolower($file_type), $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'File type not allowed']);
        exit;
    }
    
    // Create upload directory
    $upload_dir = '../../uploads/' . $user_id . '/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $file_hash = hash_file('sha256', $file_tmp);
    $unique_name = time() . '_' . $file_hash . '.' . $file_type;
    $file_path = $upload_dir . $unique_name;
    
    // Move uploaded file
    if (!move_uploaded_file($file_tmp, $file_path)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
        exit;
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Insert file scan record
    $query = "INSERT INTO file_scans (user_id, file_name, file_type, file_size, file_hash, file_path, scan_status) 
              VALUES (:uid, :fname, :ftype, :fsize, :fhash, :fpath, 'scanning')";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':uid' => $user_id,
        ':fname' => $file_name,
        ':ftype' => $file_type,
        ':fsize' => $file_size,
        ':fhash' => $file_hash,
        ':fpath' => $file_path
    ]);
    
    $scan_id = $db->lastInsertId();
    
    // Start scan
    $scan_start = microtime(true);
    $scan_result = performScan($file_path, $file_type, $db);
    $scan_duration = round((microtime(true) - $scan_start) * 1000);
    
    // Update scan record
    $query = "UPDATE file_scans SET 
              scan_status = 'completed',
              risk_level = :risk,
              policy_triggered = :policy,
              scan_result = :result,
              sensitive_data_found = :sensitive,
              scan_duration_ms = :duration,
              scanned_at = NOW()
              WHERE id = :id";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':risk' => $scan_result['risk_level'],
        ':policy' => $scan_result['policy_triggered'],
        ':result' => json_encode($scan_result),
        ':sensitive' => json_encode($scan_result['sensitive_data']),
        ':duration' => $scan_duration,
        ':id' => $scan_id
    ]);
    
    // Log audit trail
    logAudit($db, 'user', $user_id, $_SESSION['username'], 
             "File scanned: $file_name", 'scan', 'file', $scan_id, 
             $scan_result['risk_level'] === 'blocked' ? 'blocked' : 'success',
             $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '',
             json_encode(['risk_level' => $scan_result['risk_level']]));
    
    echo json_encode([
        'success' => true,
        'message' => 'Scan completed',
        'data' => [
            'scan_id' => $scan_id,
            'risk_level' => $scan_result['risk_level'],
            'scan_duration_ms' => $scan_duration,
            'policies_checked' => $scan_result['policies_checked'],
            'sensitive_data_found' => $scan_result['sensitive_data'],
            'details' => $scan_result['details']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error during scan']);
}

/**
 * Perform DLP scan on file
 */
function performScan($file_path, $file_type, $db) {
    $content = extractFileContent($file_path, $file_type);
    
    // Get active DLP policies
    $query = "SELECT * FROM dlp_policies WHERE status = 'active' ORDER BY severity DESC";
    $stmt = $db->query($query);
    $policies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $sensitive_data = [];
    $triggered_policy = null;
    $risk_level = 'safe';
    $details = [];
    
    foreach ($policies as $policy) {
        $matches = checkPolicy($content, $policy);
        
        if (!empty($matches)) {
            $sensitive_data[] = $policy['data_type'];
            $details[] = [
                'policy' => $policy['policy_name'],
                'severity' => $policy['severity'],
                'matches' => count($matches)
            ];
            
            // Determine risk level based on policy action
            if ($policy['action'] === 'block') {
                $risk_level = 'blocked';
                $triggered_policy = $policy['id'];
                break; // Stop on first blocking policy
            } elseif ($policy['severity'] === 'critical' || $policy['severity'] === 'high') {
                $risk_level = 'high';
                $triggered_policy = $policy['id'];
            } elseif ($risk_level !== 'high' && $policy['severity'] === 'medium') {
                $risk_level = 'medium';
                $triggered_policy = $policy['id'];
            }
        }
    }
    
    return [
        'risk_level' => $risk_level,
        'policy_triggered' => $triggered_policy,
        'sensitive_data' => array_unique($sensitive_data),
        'policies_checked' => count($policies),
        'details' => $details
    ];
}

/**
 * Check if content matches policy
 */
function checkPolicy($content, $policy) {
    $matches = [];
    
    if ($policy['condition_type'] === 'regex') {
        preg_match_all('/' . $policy['condition_value'] . '/i', $content, $matches);
        return $matches[0] ?? [];
    } elseif ($policy['condition_type'] === 'keyword') {
        $keywords = explode(',', $policy['condition_value']);
        foreach ($keywords as $keyword) {
            if (stripos($content, trim($keyword)) !== false) {
                $matches[] = trim($keyword);
            }
        }
        return $matches;
    }
    
    return [];
}

/**
 * Extract text content from file
 */
function extractFileContent($file_path, $file_type) {
    $content = '';
    
    switch (strtolower($file_type)) {
        case 'txt':
        case 'csv':
            $content = file_get_contents($file_path);
            break;
            
        case 'pdf':
            // Simple PDF text extraction (requires pdftotext or similar)
            // For demo, just read raw content
            $content = file_get_contents($file_path);
            break;
            
        case 'doc':
        case 'docx':
            // For demo, read raw content
            // In production, use PHPWord or similar library
            $content = file_get_contents($file_path);
            break;
            
        case 'jpg':
        case 'jpeg':
        case 'png':
            // For images, you'd use OCR (Tesseract)
            // For demo, return empty
            $content = '';
            break;
    }
    
    return $content;
}

/**
 * Log audit trail
 */
function logAudit($db, $actor_type, $actor_id, $actor_name, $action, $action_type, 
                  $resource_type, $resource_id, $status, $ip, $ua, $details) {
    try {
        $query = "INSERT INTO audit_logs (actor_type, actor_id, actor_name, action, action_type, 
                  resource_type, resource_id, status, ip_address, user_agent, details) 
                  VALUES (:actor_type, :actor_id, :actor_name, :action, :action_type, 
                  :resource_type, :resource_id, :status, :ip, :ua, :details)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':actor_type' => $actor_type,
            ':actor_id' => $actor_id,
            ':actor_name' => $actor_name,
            ':action' => $action,
            ':action_type' => $action_type,
            ':resource_type' => $resource_type,
            ':resource_id' => $resource_id,
            ':status' => $status,
            ':ip' => $ip,
            ':ua' => $ua,
            ':details' => $details
        ]);
    } catch (Exception $e) {
        // Silent fail
    }
}
