@echo off
REM ============================================
REM Optimizar Cache y Vistas de ProdFlow
REM ============================================
REM Ejecutar: optimize.bat

echo.
echo ========================================
echo  OPTIMIZANDO PRODFLOW
echo ========================================
echo.

REM Limpiar cache de aplicación
echo [1/3] Limpiando cache de aplicación...
php artisan cache:clear
if errorlevel 1 (
    echo ERROR: No se pudo limpiar el cache
    pause
    exit /b 1
)
echo ✓ Cache limpio

REM Limpiar vistas compiladas
echo.
echo [2/3] Limpiando vistas compiladas...
php artisan view:clear
if errorlevel 1 (
    echo ERROR: No se pudieron limpiar las vistas
    pause
    exit /b 1
)
echo ✓ Vistas limpias

REM Compilar configuración
echo.
echo [3/3] Compilando configuración...
php artisan config:cache
if errorlevel 1 (
    echo ERROR: No se pudo compilar la configuración
    pause
    exit /b 1
)
echo ✓ Configuración compilada

echo.
echo ========================================
echo  ✓ OPTIMIZACIÓN COMPLETADA
echo ========================================
echo.
pause
