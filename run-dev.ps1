# ============================================
# PROZONE - Development Server Launcher (PowerShell)
# Ensures npm run web runs from correct folder
# ============================================

# Get the script directory
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path

# Function to check if folder is correct root
function Test-RootFolder {
    param([string]$path)
    
    if (Test-Path "$path\package.json") {
        $content = Get-Content "$path\package.json" -Raw
        if ($content -match 'prozone-1') {
            return $true
        }
    }
    return $false
}

# Check if already in correct folder
if (Test-RootFolder $scriptDir) {
    Write-Host "✓ Already in correct project folder: $scriptDir" -ForegroundColor Green
    $rootDir = $scriptDir
}
# Check parent folder
elseif (Test-RootFolder (Split-Path -Parent $scriptDir)) {
    $rootDir = Split-Path -Parent $scriptDir
    Write-Host "✓ Found root folder: $rootDir" -ForegroundColor Green
}
# Check two levels up
elseif (Test-RootFolder (Split-Path -Parent (Split-Path -Parent $scriptDir))) {
    $rootDir = Split-Path -Parent (Split-Path -Parent $scriptDir)
    Write-Host "✓ Found root folder: $rootDir" -ForegroundColor Green
}
else {
    Write-Host "✗ ERROR: Could not find Prozone root folder!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Expected to find package.json with 'prozone-1' in:" -ForegroundColor Yellow
    Write-Host "- Current directory"
    Write-Host "- Parent directory"
    Write-Host "- Two levels up"
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

# Start server
Set-Location $rootDir
Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Starting PROZONE Development Server" -ForegroundColor Cyan
Write-Host "Folder: $rootDir" -ForegroundColor Cyan
Write-Host "URL: http://localhost:8000" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

npm run web
Read-Host "Press Enter to exit"
