@echo off
echo ========================================
echo  Iniciando Servidor Laravel
echo  Para APP-EDUIA
echo ========================================
echo.
echo Puerto: 8000
echo Host: 0.0.0.0 (todas las interfaces)
echo IP Local: 192.168.1.69
echo.
echo IMPORTANTE: Mantén esta ventana abierta
echo Presiona Ctrl+C para detener el servidor
echo.
echo ========================================
echo.

cd /d C:\xampp\htdocs\IAEDU1

REM Limpiar caché de configuración
php artisan config:clear

REM Iniciar servidor en todas las interfaces (permite conexiones externas)
php artisan serve --host=0.0.0.0 --port=8000

pause
