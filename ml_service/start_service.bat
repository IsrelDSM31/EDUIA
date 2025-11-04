@echo off
echo ========================================
echo Iniciando Servicio ML de Prediccion
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
echo Verificando dependencias...
pip show flask >nul 2>&1
if errorlevel 1 (
    echo Instalando dependencias...
    pip install -r requirements.txt
)

echo.
echo Verificando modelo entrenado...
if not exist "models\risk_model.pkl" (
    echo.
    echo ADVERTENCIA: Modelo no encontrado.
    echo Entrenando modelo...
    python train_model.py
    echo.
)

echo.
echo Iniciando servicio en http://localhost:5000
echo (Modo producción con Gunicorn)
echo Presiona Ctrl+C para detener
echo.

REM Verificar si waitress está instalado (mejor para Windows que Gunicorn)
pip show waitress >nul 2>&1
if errorlevel 1 (
    echo Instalando Waitress para producción (servidor WSGI para Windows)...
    pip install waitress
    if errorlevel 1 (
        echo ⚠️  Error al instalar Waitress. Usando servidor Flask de desarrollo...
        python predict_service.py
        goto :end
    )
)

REM Usar Waitress (servidor WSGI nativo de Windows, sin warnings)
REM Sintaxis correcta: waitress-serve --host=HOST --port=PORT MODULO:app
echo Iniciando con Waitress (modo producción)...
waitress-serve --host=0.0.0.0 --port=5000 predict_service:app
if errorlevel 1 (
    echo ⚠️  Waitress falló. Intentando con servidor Flask...
    python predict_service.py
)

:end

pause

