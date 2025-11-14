@echo off
title IAEDU1 - Servidor para Red Local
color 0B

echo.
echo ========================================
echo    IAEDU1 - Servidor para Red Local
echo ========================================
echo.

echo [1/4] Obteniendo IP local...
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /C:"IPv4"') do (
    set IP=%%a
    goto :found_ip
)
:found_ip
set IP=%IP: =%
echo Tu IP local es: %IP%
echo.

echo [2/4] Verificando PHP...
php --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP no está instalado o no está en el PATH
    pause
    exit /b 1
)
echo ✓ PHP encontrado
echo.

echo [3/4] Verificando directorio...
if not exist "artisan" (
    echo ERROR: No se encontró artisan
    pause
    exit /b 1
)
echo ✓ Directorio correcto
echo.

echo [4/4] Iniciando servidor...
echo.
echo ========================================
echo    URLs de Acceso:
echo ========================================
echo.
echo 🖥️  Local:           http://localhost:8000
echo 📱 Red Local:       http://%IP%:8000
echo 🌐 Red:             http://0.0.0.0:8000
echo.
echo ========================================
echo    Instrucciones:
echo ========================================
echo.
echo 1. Para acceder desde otro dispositivo:
echo    - Conecta a la misma red WiFi
echo    - Abre: http://%IP%:8000
echo.
echo 2. Para detener el servidor: Ctrl+C
echo.
echo ========================================
echo.

php artisan serve --host=0.0.0.0 --port=8000

echo.
echo Servidor detenido.
pause


