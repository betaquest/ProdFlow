@echo off
REM ============================================
REM Ejecutar Script de Scheduler
REM ============================================
REM Este archivo ejecuta el script PowerShell como administrador

echo.
echo =========================================
echo  SETUP SCHEDULER DE PRODFLOW
echo =========================================
echo.
echo ⚠  Este script requiere PERMISOS DE ADMINISTRADOR
echo.
echo Presiona una tecla para continuar...
pause

REM Obtener la ruta del script PowerShell
set SCRIPT_PATH=%~dp0setup-scheduler.ps1

REM Ejecutar PowerShell como administrador
powershell -NoProfile -ExecutionPolicy Bypass -Command "& {Start-Process powershell -ArgumentList '-NoProfile -ExecutionPolicy Bypass -File \"%SCRIPT_PATH%\"' -Verb RunAs}"

exit /b 0
