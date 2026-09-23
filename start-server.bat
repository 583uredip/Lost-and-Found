@echo off
echo ================================================
echo   AIUB Lost ^& Found - Server Starter
echo ================================================
echo.

SET PHP=%USERPROFILE%\Downloads\php\php.exe
SET MARIADB=%USERPROFILE%\Downloads\mariadb\bin
SET PROJECT=C:\Users\USER\Downloads\aiub-lost-and-found\aiub-lost-and-found
SET DB_DIR=%USERPROFILE%\Downloads\mariadb-data
SET PORT=8080
SET DB_PORT=3306

echo [1/4] Checking PHP...
"%PHP%" --version
if errorlevel 1 (
    echo ERROR: PHP not found!
    pause
    exit /b 1
)

echo.
echo [2/4] Starting MariaDB (MySQL-compatible)...
if not exist "%DB_DIR%" (
    echo Initializing database for first time...
    "%MARIADB%\mariadb-install-db.exe" --datadir="%DB_DIR%" --password=mysql --user=root
    echo Database initialized!
)

start "MariaDB Server" /B "%MARIADB%\mariadbd.exe" --console --datadir="%DB_DIR%" --port=%DB_PORT%
echo Waiting for database to start...
timeout /t 4 /nobreak >nul

echo.
echo [3/4] Importing database schema...
"%MARIADB%\mariadb.exe" -u root -pmysql -e "CREATE DATABASE IF NOT EXISTS aiub_lost_found CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
"%MARIADB%\mariadb.exe" -u root -pmysql aiub_lost_found < "%PROJECT%\database.sql" 2>nul
echo Database ready!

echo.
echo [4/4] Starting PHP Development Server...
echo.
echo ================================================
echo   Site running at: http://localhost:%PORT%
echo   Press Ctrl+C to stop
echo ================================================
echo.

start "" "http://localhost:%PORT%"
"%PHP%" -S localhost:%PORT% -t "%PROJECT%"
