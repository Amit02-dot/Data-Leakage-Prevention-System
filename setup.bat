@echo off
echo ========================================
echo DLPS Enterprise - Database Setup
echo ========================================
echo.

echo Starting MySQL service...
net start MySQL
timeout /t 2 >nul

echo.
echo Importing database schema...
mysql -u root -e "SOURCE database/schema.sql"

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo Database setup completed successfully!
    echo ========================================
    echo.
    echo Default Credentials:
    echo.
    echo USER LOGIN:
    echo   URL: http://localhost/DLPs/
    echo   Username: testuser
    echo   Password: User@123
    echo.
    echo ADMIN LOGIN:
    echo   URL: http://localhost/DLPs/admin-login.php
    echo   Username: admin
    echo   Password: Admin@123
    echo.
    echo ========================================
) else (
    echo.
    echo ERROR: Database setup failed!
    echo Please check your MySQL installation.
)

echo.
pause
