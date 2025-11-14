@echo off
title IAEDU1 - Servidor Completo (Laravel + Vite)
color 0E

echo.
echo ========================================
echo    IAEDU1 - Servidor Completo
echo    (Laravel + Vite + Assets)
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
    echo ADVERTENCIA: Node.js no encontrado
    echo Los assets no se compilarán automáticamente
    echo.
) else (
    echo ✓ Node.js encontrado
    echo.
)

echo [3/5] Verificando directorio...
if not exist "artisan" (
    echo ERROR: No se encontró artisan
    pause
    exit /b 1
)
echo ✓ Directorio correcto
echo.

echo [4/6] Limpiando cache...
php artisan config:clear >nul 2>&1
php artisan cache:clear >nul 2>&1
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
echo 🤖 ML Service:       http://localhost:5000
echo.
if exist "node_modules" (
    echo 🎨 Vite:            http://localhost:5173
    echo.
    echo Iniciando Vite en segundo plano...
    start "Vite Dev Server" cmd /c "npm run dev"
    timeout /t 2 >nul
) else (
    echo ⚠️  Vite: No disponible (node_modules no encontrado)
    echo    Ejecuta: npm install
    echo.
)

echo ========================================
echo    Notas Importantes:
echo ========================================
echo.
echo - Presiona Ctrl+C para detener Laravel
echo - El servicio ML se ejecuta en una ventana separada
echo - Si el servicio ML no está disponible, el sistema usará reglas heurísticas
echo.
echo ========================================
echo.

php artisan serve --host=127.0.0.1 --port=8000

echo.
echo Servidor detenido.
pause

