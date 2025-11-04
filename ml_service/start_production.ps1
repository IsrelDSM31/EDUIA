# Script PowerShell para iniciar el servicio ML en producción con Gunicorn
# Uso: .\start_production.ps1

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "🚀 Iniciando Servicio ML en Producción" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Cambiar al directorio del script
Set-Location $PSScriptRoot

# Verificar Python
try {
    $pythonVersion = python --version
    Write-Host "✓ Python encontrado: $pythonVersion" -ForegroundColor Green
} catch {
    Write-Host "❌ Error: Python no encontrado" -ForegroundColor Red
    exit 1
}

# Verificar Gunicorn
try {
    python -c "import gunicorn" 2>$null
    Write-Host "✓ Gunicorn encontrado" -ForegroundColor Green
} catch {
    Write-Host "⚠ Gunicorn no encontrado. Instalando..." -ForegroundColor Yellow
    pip install gunicorn
}

# Verificar modelo
if (-not (Test-Path "models\risk_model.pkl")) {
    Write-Host "⚠ Modelo no encontrado. Entrenando modelo..." -ForegroundColor Yellow
    python train_model.py
}

# Crear directorio de logs
New-Item -ItemType Directory -Force -Path logs | Out-Null

# Variables de entorno
$env:PYTHONUNBUFFERED = "1"
$env:FLASK_ENV = "production"

# Calcular número de workers
$cpuCount = (Get-WmiObject Win32_ComputerSystem).NumberOfLogicalProcessors
$workers = [math]::Min($cpuCount * 2 + 1, 8)

Write-Host ""
Write-Host "✓ Iniciando Gunicorn..." -ForegroundColor Green
Write-Host "📊 Workers: $workers" -ForegroundColor Cyan
Write-Host "🌐 URL: http://127.0.0.1:5000" -ForegroundColor Cyan
Write-Host "📝 Logs: logs\gunicorn-*.log" -ForegroundColor Cyan
Write-Host ""
Write-Host "💡 Para detener: Ctrl+C" -ForegroundColor Yellow
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Iniciar Gunicorn
gunicorn -c gunicorn_config.py predict_service:app


