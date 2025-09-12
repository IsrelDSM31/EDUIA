@echo off
title IAEDU1 - Servidor Móvil
color 0A

echo.
echo ========================================
echo    IAEDU1 - Servidor para Móvil
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

echo [2/4] Verificando que estés en el directorio correcto...
if not exist "artisan" (
    echo ERROR: No se encontró artisan. Asegúrate de estar en C:\xampp\htdocs\IAEDU1
    pause
    exit /b 1
)
echo ✓ Directorio correcto
echo.

echo [3/4] Iniciando servidor para acceso móvil...
echo.
echo ========================================
echo    URLs de Acceso:
echo ========================================
echo.
echo 📱 App Principal:     http://%IP%:8000
echo 🔧 Diagnóstico PWA:   http://%IP%:8000/pwa-diagnostic
echo 🎨 Generar Íconos:    http://%IP%:8000/generate-all-icons
echo 🧪 Test PWA:          http://%IP%:8000/pwa-test
echo.
echo ========================================
echo    Instrucciones para Móvil:
echo ========================================
echo.
echo 1. Conecta tu móvil a la misma red WiFi
echo 2. Abre Chrome/Edge en tu móvil
echo 3. Ve a: http://%IP%:8000
echo 4. Toca menú (⋮) → "Instalar aplicación"
echo 5. ¡Listo! La app aparecerá en tu pantalla de inicio
echo.
echo ========================================
echo    Presiona Ctrl+C para detener
echo ========================================
echo.

php artisan serve --host=0.0.0.0 --port=8000

echo.
echo Servidor detenido.
pause 