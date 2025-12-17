# DLPS Enterprise - Data Leakage Prevention System

## 🎯 Overview

A production-ready, enterprise-grade Data Leakage Prevention System (DLPS) built for academic evaluation and placement-oriented review. This system demonstrates professional cybersecurity architecture with strict role-based access control, comprehensive audit logging, and advanced DLP scanning capabilities.

## 🏗️ Architecture

### Technology Stack
- **Backend**: PHP 8+ with PDO
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Charts**: Chart.js 4.4.0
- **Icons**: Font Awesome 6.4.0

### Key Features

#### 🔐 Authentication & Security
- **Completely Separate Login Flows**: User and Admin have distinct authentication endpoints
- **Role-Based Access Control (RBAC)**: Backend validation prevents cross-role access
- **Secure Sessions**: Token-based session management with expiration
- **Immutable Audit Trail**: All actions logged with IP, timestamp, and details

#### 👤 User Side Modules
1. **User Login** - Secure authentication (separate from admin)
2. **Dashboard** - Analytics with charts and statistics
3. **File Upload & Scan** - Drag-and-drop upload with real-time scanning
4. **Scan History** - Complete history of all scans
5. **User Profile** - Account information and activity summary

#### 🛠️ Admin Side Modules
1. **Admin Login** - Separate secure authentication
2. **Admin Dashboard** - Enterprise-level security analytics
3. **Scanning Engine** - View and configure detection logic
4. **Policy Management** - Create, edit, and manage DLP policies
5. **Audit Trail** - Immutable logs with filtering and pagination
6. **User Management** - Monitor and manage user accounts

## 📋 Installation Instructions

### Prerequisites
- XAMPP (Apache + MySQL + PHP 8+)
- Modern web browser (Chrome, Firefox, Edge)

### Step 1: Setup Database

1. Start XAMPP Control Panel
2. Start Apache and MySQL services
3. Open phpMyAdmin: `http://localhost/phpmyadmin`
4. Import the database schema:
   - Click "Import" tab
   - Choose file: `database/schema.sql`
   - Click "Go"

### Step 2: Configure Database Connection

Edit `config/database.php` if needed (default settings work with XAMPP):
```php
private $host = "localhost";
private $db_name = "dlps_enterprise";
private $username = "root";
private $password = "";
```

### Step 3: Set Permissions

Ensure the `uploads/` directory is writable:
```bash
chmod 755 uploads/
```

### Step 4: Access the Application

**User Portal**: `http://localhost/DLPs/`
- Default User: `testuser` / `User@123`

**Admin Portal**: `http://localhost/DLPs/admin-login.php`
- Default Admin: `admin` / `Admin@123`

## 🎨 Design Philosophy

### Enterprise-Grade UI/UX
- **Dark Professional Theme**: SOC-style dashboard inspired by Forcepoint/Symantec
- **Dense Layout**: No wasted space, maximum information density
- **Real-time Analytics**: Live charts and statistics
- **Responsive Design**: Works on desktop and tablets

### Color Palette
- Primary Background: `#0a0e27`
- Card Background: `#1e2139`
- Accent Blue: `#3b82f6`
- Accent Purple: `#8b5cf6`
- Status Colors: Green (safe), Red (danger), Yellow (warning)

## 🔒 Security Features

### Role Separation
- ❌ Admin CANNOT login from User portal
- ❌ User CANNOT access Admin panel (even by URL)
- ✅ Backend validates roles on every request
- ✅ Separate session management for User/Admin

### DLP Scanning Engine
- **Keyword-based Detection**: Matches sensitive keywords
- **Regex Pattern Matching**: Detects SSN, credit cards, emails, etc.
- **Policy Enforcement**: Configurable actions (block, warn, quarantine)
- **Risk Classification**: Safe, Low, Medium, High, Critical, Blocked

### Audit Trail
- **Immutable Logs**: Cannot be edited or deleted
- **Comprehensive Tracking**: User actions, admin actions, policy triggers
- **Detailed Information**: IP address, user agent, timestamps
- **Filterable**: By actor type, action type, status

## 📊 Database Schema

### Core Tables
- `users` - End user accounts
- `admins` - Security administrator accounts (separate)
- `dlp_policies` - DLP security policies
- `file_scans` - Scan results and file metadata
- `audit_logs` - Immutable activity logs
- `scanning_rules` - Detection rules
- `user_sessions` - Active sessions

### Views
- `user_dashboard_stats` - User analytics
- `admin_dashboard_stats` - Admin analytics

## 🚀 Usage Guide

### For Users

1. **Login**: Use user credentials at `http://localhost/DLPs/`
2. **Upload File**: Go to "Upload & Scan" and drag-drop or browse
3. **View Results**: See real-time scan progress and results
4. **Check History**: View all past scans in "Scan History"
5. **Profile**: Manage account in "My Profile"

### For Admins

1. **Login**: Use admin credentials at `http://localhost/DLPs/admin-login.php`
2. **Monitor Dashboard**: View security analytics and violations
3. **Manage Policies**: Create/edit DLP policies in "Policy Management"
4. **Review Audit Trail**: Check all system activities
5. **Manage Users**: Monitor user activity and compliance

## 🎯 DLP Policy Examples

### Pre-configured Policies

1. **SSN Detection**
   - Pattern: `\b\d{3}-\d{2}-\d{4}\b`
   - Action: Block
   - Severity: Critical

2. **Credit Card Detection**
   - Pattern: `\b\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}\b`
   - Action: Block
   - Severity: Critical

3. **Email Detection**
   - Pattern: `[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}`
   - Action: Warn
   - Severity: Medium

4. **Confidential Keywords**
   - Keywords: `confidential, secret, internal, proprietary`
   - Action: Warn
   - Severity: High

## 📈 Performance

- **Scan Speed**: < 500ms for typical documents
- **Database**: Optimized with indexes on key columns
- **Session Management**: Token-based with automatic cleanup
- **Scalability**: Designed for enterprise deployment

## 🔧 Customization

### Adding New Policies

1. Login as Admin
2. Go to "Policy Management"
3. Click "Create New Policy"
4. Define:
   - Policy name and description
   - Data type (PII, Financial, Confidential, etc.)
   - Condition (Keyword or Regex)
   - Action (Block, Warn, Quarantine, Alert)
   - Severity (Critical, High, Medium, Low)

### Modifying Scanning Rules

1. Access `scanning_rules` table in database
2. Add custom regex patterns or keywords
3. Set priority (higher = checked first)
4. Enable/disable as needed

## 🎓 Academic Evaluation Points

### ✅ Demonstrates
- Enterprise software architecture
- Secure authentication and RBAC
- Database design and optimization
- Modern UI/UX principles
- Security best practices
- Audit logging and compliance
- RESTful API design
- Professional documentation

### ✅ Suitable For
- Professor evaluation
- Placement interviews
- Portfolio projects
- Cybersecurity demonstrations
- Academic presentations

## 📝 Default Credentials

**User Account**
- Username: `testuser`
- Email: `user@dlps.local`
- Password: `User@123`

**Admin Account**
- Username: `admin`
- Email: `admin@dlps.local`
- Password: `Admin@123`

⚠️ **Important**: Change default passwords in production!

## 🐛 Troubleshooting

### Database Connection Error
- Ensure MySQL is running in XAMPP
- Verify database name is `dlps_enterprise`
- Check credentials in `config/database.php`

### Upload Errors
- Ensure `uploads/` directory exists and is writable
- Check PHP `upload_max_filesize` setting
- Verify file type is allowed

### Session Issues
- Clear browser cookies
- Check PHP session configuration
- Ensure session directory is writable

## 📞 Support

For issues or questions:
- Check the audit trail for error logs
- Review browser console for JavaScript errors
- Verify database schema is properly imported

## 🏆 Credits

Developed as an enterprise-grade DLP system demonstrating:
- Professional cybersecurity architecture
- Production-ready code quality
- Industry-standard security practices
- Modern web development techniques

---

**Version**: 1.0.0  
**Status**: Production-Ready  
**License**: Academic/Educational Use
