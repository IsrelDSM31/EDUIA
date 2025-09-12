@echo off
cd /d C:\xampp\htdocs\IAEDU1
php artisan schedule:run >> storage/logs/scheduler.log 2>&1 