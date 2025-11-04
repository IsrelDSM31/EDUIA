@echo off
title IAEDU1 - Servidor de Desarrollo Completo
color 0E

echo.
echo ========================================
echo    IAEDU1 - Servidor de Desarrollo
echo    (Laravel + Vite)
echo ========================================
echo.

echo [1/5] Verificando PHP...
php --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP no está instalado
    pause
    exit /b 1
)
echo ✓ PHP encontrado
echo.

echo [2/5] Verificando Node.js...
node --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Node.js no está instalado
    echo Descarga desde: https://nodejs.org/
    pause
    exit /b 1
)
echo ✓ Node.js encontrado
echo.

echo [3/5] Verificando dependencias...
if not exist "node_modules" (
    echo Instalando dependencias de Node.js...
    call npm install
    if errorlevel 1 (
        echo ERROR: No se pudieron instalar las dependencias
        pause
        exit /b 1
    )
    echo ✓ Dependencias instaladas
) else (
    echo ✓ Dependencias encontradas
)
echo.

echo [4/6] Limpiando cache de Laravel...
php artisan config:clear >nul 2>&1
php artisan cache:clear >nul 2>&1
php artisan view:clear >nul 2>&1
echo ✓ Cache limpiado
echo.

echo [5/6] Iniciando servicio ML...
python --version >nul 2>&1
if errorlevel 1 (
    echo ⚠️  Python no encontrado. El servicio ML no se iniciará.
    echo    El sistema usará reglas heurísticas como fallback.
) else (
    echo ✓ Python encontrado
    if exist "ml_service\models\risk_model.pkl" (
        echo ✓ Modelo ML encontrado
        echo    Iniciando servicio ML en segundo plano (modo producción)...
        start "ML Service - IAEDU1" /D "ml_service" cmd /c "start_service.bat"
        timeout /t 3 >nul
        echo ✓ Servicio ML iniciado en http://localhost:5000
    ) else (
        echo ⚠️  Modelo ML no encontrado. Entrenando modelo...
        echo    Esto puede tomar unos minutos...
        cd ml_service
        python train_model.py
        if errorlevel 1 (
            echo ⚠️  Error al entrenar modelo. El servicio ML no se iniciará.
            echo    El sistema usará reglas heurísticas como fallback.
        ) else (
            echo ✓ Modelo entrenado exitosamente
            echo    Iniciando servicio ML en segundo plano (modo producción)...
            start "ML Service - IAEDU1" /D "ml_service" cmd /c "start_service.bat"
            timeout /t 3 >nul
            echo ✓ Servicio ML iniciado en http://localhost:5000
        )
        cd ..
    )
)
echo.

echo [6/6] Iniciando servidores...
echo.
echo ========================================
echo    Servidores Iniciados:
echo ========================================
echo.
echo 🖥️  Laravel:         http://localhost:8000
echo 🎨 Vite (Dev):       http://localhost:5173
echo 🤖 ML Service:       http://localhost:5000
echo.
echo ========================================
echo    Instrucciones:
echo ========================================
echo.
echo 1. Abre tu navegador en: http://localhost:8000
echo 2. Los cambios en archivos se recargarán automáticamente
echo 3. Presiona Ctrl+C para detener Laravel y Vite
echo 4. El servicio ML se ejecuta en una ventana separada
echo 5. Si el servicio ML no está disponible, el sistema usará reglas heurísticas
echo.
echo ========================================
echo.

REM Iniciar Vite en una ventana separada
start "Vite Dev Server" cmd /k "npm run dev"

REM Esperar un momento para que Vite inicie
timeout /t 3 >nul

REM Iniciar Laravel en esta ventana
php artisan serve --host=127.0.0.1 --port=8000

echo.
echo Servidores detenidos.
pause

