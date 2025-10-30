@echo off
cd /d "C:\xampp\htdocs\IAEDU1"
echo Iniciando IAEDU1 en puerto 8000...
php artisan serve --port=8000 --host=127.0.0.1
pause


