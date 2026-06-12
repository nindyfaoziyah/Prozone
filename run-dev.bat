@echo off
REM ============================================
REM PROZONE - Development Server Launcher
REM Ensures npm run web runs from correct folder
REM ============================================

setlocal enabledelayedexpansion

REM Get the script directory (where this .bat file is)
set "SCRIPT_DIR=%~dp0"
set "SCRIPT_DIR=%SCRIPT_DIR:~0,-1%"

REM Check if we're already in the correct root folder (contains package.json with "prozone-1")
if exist "%SCRIPT_DIR%\package.json" (
    findstr /M "prozone-1" "%SCRIPT_DIR%\package.json" >nul
    if !errorlevel! equ 0 (
        echo ✓ Already in correct project folder: %SCRIPT_DIR%
        goto :start_server
    )
)

REM If not, try to navigate up to find the correct root folder
echo ⚠ Searching for correct root folder...

REM Check if we're inside a nested Prozone/Prozone folder
if exist "%SCRIPT_DIR%\..\package.json" (
    findstr /M "prozone-1" "%SCRIPT_DIR%\..\package.json" >nul
    if !errorlevel! equ 0 (
        set "SCRIPT_DIR=%SCRIPT_DIR%\.."
        echo ✓ Found root folder: !SCRIPT_DIR!
        goto :start_server
    )
)

REM Check two levels up
if exist "%SCRIPT_DIR%\..\..\package.json" (
    findstr /M "prozone-1" "%SCRIPT_DIR%\..\..\package.json" >nul
    if !errorlevel! equ 0 (
        set "SCRIPT_DIR=%SCRIPT_DIR%\..\.."
        echo ✓ Found root folder: !SCRIPT_DIR!
        goto :start_server
    )
)

echo ✗ ERROR: Could not find Prozone root folder!
echo.
echo Expected to find package.json with "prozone-1" in:
echo - Current directory
echo - Parent directory
echo - Two levels up
echo.
pause
exit /b 1

:start_server
cd /d "%SCRIPT_DIR%"
echo.
echo ============================================
echo Starting PROZONE Development Server
echo Folder: %SCRIPT_DIR%
echo URL: http://localhost:8000
echo ============================================
echo.
npm run web
pause
