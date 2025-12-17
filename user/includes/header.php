
<!-- Top Navigation -->
<nav class="top-nav">
    <div class="top-nav-brand">
        <div class="top-nav-logo">D</div>
        <div>
            <div class="top-nav-title">DLPS Enterprise</div>
            <div class="top-nav-subtitle">Data Leakage Prevention</div>
        </div>
    </div>
    
    <div class="top-nav-center">
        <div class="role-toggle">
            <button class="role-toggle-btn active">
                <i class="fas fa-user"></i> User
            </button>
            <button class="role-toggle-btn" onclick="alert('Please login as Admin to access Admin panel')">
                <i class="fas fa-user-shield"></i> Admin
            </button>
        </div>
    </div>
    
    <div class="top-nav-actions">
        <button class="nav-btn" id="themeToggle" onclick="toggleTheme()" title="Toggle Theme">
            <i class="fas fa-moon"></i>
        </button>
        <button class="nav-btn" onclick="window.location.href='../help.php'">
            <i class="fas fa-question-circle"></i> Help & Support
        </button>
        <button class="nav-btn" onclick="window.location.href='../about.php'">
            <i class="fas fa-info-circle"></i> About
        </button>
        <button class="nav-btn" onclick="openLogoutModal()">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </div>
</nav>

<!-- Logout Confirmation Modal -->
<div id="logoutModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 400px; max-width: 90%; animation: slideIn 0.3s ease-out;">
        <div class="card-header" style="border-bottom: none; padding-bottom: 0;">
            <h3 class="card-title text-center" style="width: 100%;">Confirm Logout</h3>
        </div>
        <div class="card-body text-center" style="padding: 1rem 0 2rem;">
            <div style="font-size: 3rem; color: #f59e0b; margin-bottom: 1rem;">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <p>Are you sure you want to end your session?</p>
        </div>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <button class="btn btn-secondary" onclick="closeLogoutModal()">Cancel</button>
            <button class="btn btn-danger" onclick="window.location.href='../api/auth/logout.php'">Yes, Logout</button>
        </div>
    </div>
</div>

<script>
    // Theme Management
    function initTheme() {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);
    }

    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
    }

    function updateThemeIcon(theme) {
        const btn = document.getElementById('themeToggle');
        if (btn) {
            if (theme === 'light') {
                btn.innerHTML = '<i class="fas fa-sun" style="color: var(--accent-yellow);"></i>';
            } else {
                btn.innerHTML = '<i class="fas fa-moon" style="color: var(--accent-blue);"></i>';
            }
        }
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', initTheme);

    function openLogoutModal() {
        document.getElementById('logoutModal').style.display = 'flex';
    }
    
    function closeLogoutModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('logoutModal');
        if (event.target == modal) {
            closeLogoutModal();
        }
    });
</script>

<style>
@keyframes slideIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>
