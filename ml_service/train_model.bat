@echo off
echo ========================================
echo Entrenamiento del Modelo ML
echo ========================================
echo.

cd /d %~dp0

echo Verificando Python...
python --version
if errorlevel 1 (
    echo ERROR: Python no encontrado. Instala Python 3.8 o superior.
    pause
    exit /b 1
)

echo.
echo Instalando dependencias si es necesario...
pip install -r requirements.txt

echo.
echo Iniciando entrenamiento...
python train_model.py

echo.
echo ========================================
echo Entrenamiento completado
echo ========================================
pause


