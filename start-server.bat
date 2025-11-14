@echo off
title IAEDU1 - Servidor Laravel
color 0A

echo.
echo ========================================
echo    IAEDU1 - Servidor de Desarrollo
echo ========================================
echo.

echo [1/4] Verificando PHP...
php --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP no está instalado o no está en el PATH
    echo.
    echo Por favor instala PHP desde: https://windows.php.net/download/
    echo O agrega PHP al PATH del sistema
    pause
    exit /b 1
)
echo ✓ PHP encontrado
echo.

echo [2/4] Verificando directorio...
if not exist "artisan" (
    echo ERROR: No se encontró artisan. Asegúrate de estar en el directorio del proyecto
    pause
    exit /b 1
)
echo ✓ Directorio correcto
echo.

echo [3/4] Limpiando cache...
php artisan config:clear >nul 2>&1
php artisan cache:clear >nul 2>&1
echo ✓ Cache limpiado
echo.

echo [4/5] Forzando uso de assets compilados
if exist "public\hot" (
    del /q "public\hot" >nul 2>&1
    echo ✓ Archivo 'hot' eliminado (modo desarrollo desactivado)
) else (
    echo ✓ Usando assets compilados
)
echo.

echo [5/6] Verificando assets compilados
if not exist "public\build\manifest.json" (
    echo ⚠️  Assets no compilados. Compilando ahora
    echo    Esto puede tomar 1-2 minutos
    call npm run build
    if errorlevel 1 (
        echo ERROR: No se pudieron compilar los assets
        echo Verifica que Node.js esté instalado: https://nodejs.org/
        pause
        exit /b 1
    )
    echo ✓ Assets compilados
    REM Copiar manifest si está en .vite
    if exist "public\build\.vite\manifest.json" (
        if not exist "public\build\manifest.json" (
            copy "public\build\.vite\manifest.json" "public\build\manifest.json" >nul 2>&1
            echo ✓ Manifest copiado a ubicación correcta
        )
    )
) else (
    echo ✓ Assets compilados encontrados
    REM Asegurar que el manifest esté en la ubicación correcta
    if exist "public\build\.vite\manifest.json" (
        if not exist "public\build\manifest.json" (
            copy "public\build\.vite\manifest.json" "public\build\manifest.json" >nul 2>&1
            echo ✓ Manifest sincronizado
        )
    )
)
echo.

echo [6/7] Iniciando servicio ML...
python --version >nul 2>&1
if errorlevel 1 (
    echo ⚠️  Python no encontrado. El servicio ML no se iniciará.
    echo    El sistema usará reglas heurísticas como fallback.
    goto :skip_ml
)

echo ✓ Python encontrado
if exist "ml_service\models\risk_model.pkl" (
    echo ✓ Modelo ML encontrado
    echo    Iniciando servicio ML en segundo plano (modo producción)
    cd ml_service
    start "ML Service - IAEDU1" cmd /c start_service.bat
    cd ..
    timeout /t 3 >nul
    echo ✓ Servicio ML iniciado en http://localhost:5000
    goto :skip_ml
)

echo ⚠️  Modelo ML no encontrado. Entrenando modelo
echo    Esto puede tomar unos minutos
cd ml_service
python train_model.py
if errorlevel 1 (
    echo ⚠️  Error al entrenar modelo. El servicio ML no se iniciará.
    echo    El sistema usará reglas heurísticas como fallback.
    cd ..
    goto :skip_ml
)

echo ✓ Modelo entrenado exitosamente
echo    Iniciando servicio ML en segundo plano (modo producción)
start "ML Service - IAEDU1" cmd /c start_service.bat
cd ..
timeout /t 3 >nul
echo ✓ Servicio ML iniciado en http://localhost:5000

:skip_ml
echo.

echo [7/7] Iniciando servidor Laravel...
echo.
echo ========================================
echo    URLs de Acceso:
echo ========================================
echo.
echo 🖥️  Laravel:         http://localhost:8000
echo 🌐 Red:             http://0.0.0.0:8000
echo 🤖 ML Service:       http://localhost:5000
echo.
echo ========================================
echo    ⚠️  IMPORTANTE:
echo ========================================
echo.
echo Si ves una página en blanco:
echo 1. Presiona Ctrl + Shift + Delete
echo 2. Limpia el cache del navegador
echo 3. Recarga con Ctrl + F5
echo.
echo ========================================
echo    Presiona Ctrl+C para detener
echo ========================================
echo.

php artisan serve --host=127.0.0.1 --port=8000

echo.
echo Servidor detenido.
pause
