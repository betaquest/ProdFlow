# ============================================
# Setup de Scheduler en Task Scheduler de Windows
# ============================================
# Ejecutar COMO ADMINISTRADOR:
# Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
# PowerShell -ExecutionPolicy Bypass -File setup-scheduler.ps1

# Detectar PHP dinámicamente
Write-Host "================================================" -ForegroundColor Cyan
Write-Host " CONFIGURAR SCHEDULER DE PRODFLOW" -ForegroundColor Green
Write-Host "================================================`n" -ForegroundColor Cyan

# 1. Buscar PHP en variables de entorno
Write-Host "[1] Detectando PHP..." -ForegroundColor Yellow
$phpPath = $null

# Opción A: PHP en PATH
try {
    $phpVersion = php --version 2>$null
    if ($LASTEXITCODE -eq 0) {
        $phpPath = (Get-Command php -ErrorAction Stop).Source
        Write-Host "✓ PHP encontrado en PATH: $phpPath" -ForegroundColor Green
    }
}
catch {
    Write-Host "✗ PHP no encontrado en PATH" -ForegroundColor Yellow
}

# Opción B: Buscar en carpetas comunes
if (-not $phpPath) {
    $commonPaths = @(
        "C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe",
        "C:\laragon\bin\php\php-8.3-Win32-vs16-x64\php.exe",
        "C:\Program Files\PHP\php.exe",
        "C:\xampp\php\php.exe",
        "C:\wamp\bin\php\php*.exe"
    )
    
    foreach ($path in $commonPaths) {
        if (Test-Path $path) {
            $phpPath = $path
            Write-Host "✓ PHP encontrado en: $phpPath" -ForegroundColor Green
            break
        }
    }
}

# Opción C: Buscar en todo el sistema (lento)
if (-not $phpPath) {
    Write-Host "Buscando PHP en el sistema (esto puede tardar)..." -ForegroundColor Yellow
    $search = Get-ChildItem -Path "C:\" -Filter "php.exe" -Recurse -ErrorAction SilentlyContinue -Force | Select-Object -First 1
    if ($search) {
        $phpPath = $search.FullName
        Write-Host "✓ PHP encontrado en: $phpPath" -ForegroundColor Green
    }
}

# Validar que encontramos PHP
if (-not $phpPath -or -not (Test-Path $phpPath)) {
    Write-Host "`n✗ ERROR: No se encontró PHP en el sistema" -ForegroundColor Red
    Write-Host "Soluciones:" -ForegroundColor Yellow
    Write-Host "1. Instala Laragon desde https://laragon.org" -ForegroundColor White
    Write-Host "2. O agrega PHP a la variable de entorno PATH" -ForegroundColor White
    Write-Host "3. O edita este script y especifica la ruta de PHP manualmente" -ForegroundColor White
    pause
    exit 1
}

# 2. Detectar ruta del proyecto
Write-Host "`n[2] Detectando ruta del proyecto..." -ForegroundColor Yellow
$projectPath = Split-Path -Parent $MyInvocation.MyCommand.Path

# Validar que estamos en la carpeta correcta
if (-not (Test-Path "$projectPath\artisan")) {
    Write-Host "✗ ERROR: No se encontró artisan en $projectPath" -ForegroundColor Red
    Write-Host "Este script debe ejecutarse desde la raíz de ProdFlow" -ForegroundColor Yellow
    pause
    exit 1
}

Write-Host "✓ Proyecto encontrado en: $projectPath" -ForegroundColor Green

# 3. Crear acción programada
Write-Host "`n[3] Creando tarea en Task Scheduler..." -ForegroundColor Yellow

# Verificar permisos de administrador
$admin = [bool]([Security.Principal.WindowsIdentity]::GetCurrent().Groups -match 'S-1-5-32-544')
if (-not $admin) {
    Write-Host "`n✗ ERROR: Este script requiere permisos de ADMINISTRADOR" -ForegroundColor Red
    Write-Host "Por favor, ejecuta PowerShell como administrador y vuelve a intentar" -ForegroundColor Yellow
    pause
    exit 1
}

Write-Host "✓ Permisos de administrador detectados" -ForegroundColor Green

# Crear la acción
$action = New-ScheduledTaskAction `
    -Execute $phpPath `
    -Argument "artisan schedule:run" `
    -WorkingDirectory $projectPath

# Crear el trigger (cada minuto)
$trigger = New-ScheduledTaskTrigger `
    -Once `
    -At (Get-Date) `
    -RepetitionInterval (New-TimeSpan -Minutes 1) `
    -RepetitionDuration (New-TimeSpan -Days 365)

# Crear la configuración
$settings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -StartWhenAvailable `
    -RunOnlyIfNetworkAvailable

# Registrar la tarea
try {
    $existingTask = Get-ScheduledTask -TaskName "ProdFlow-Scheduler" -ErrorAction SilentlyContinue
    if ($existingTask) {
        Write-Host "Eliminando tarea existente..." -ForegroundColor Yellow
        Unregister-ScheduledTask -TaskName "ProdFlow-Scheduler" -Confirm:$false
    }
    
    Register-ScheduledTask `
        -Action $action `
        -Trigger $trigger `
        -Settings $settings `
        -TaskName "ProdFlow-Scheduler" `
        -Description "Ejecuta el scheduler de Laravel ProdFlow cada minuto" `
        -ErrorAction Stop | Out-Null
    
    Write-Host "✓ Tarea 'ProdFlow-Scheduler' creada exitosamente" -ForegroundColor Green
}
catch {
    Write-Host "✗ ERROR al crear la tarea: $_" -ForegroundColor Red
    pause
    exit 1
}

# 4. Verificar creación
Write-Host "`n[4] Verificando configuración..." -ForegroundColor Yellow
$task = Get-ScheduledTask -TaskName "ProdFlow-Scheduler" -ErrorAction SilentlyContinue
if ($task) {
    Write-Host "✓ Tarea creada:" -ForegroundColor Green
    Write-Host "  - Nombre: $($task.TaskName)" -ForegroundColor White
    Write-Host "  - Estado: $($task.State)" -ForegroundColor White
    Write-Host "  - Próxima ejecución: $(Get-ScheduledTaskInfo -TaskName 'ProdFlow-Scheduler' | Select-Object -ExpandProperty NextRunTime)" -ForegroundColor White
}

# Resumen final
Write-Host "`n================================================" -ForegroundColor Cyan
Write-Host " ✓ SETUP COMPLETADO" -ForegroundColor Green
Write-Host "================================================`n" -ForegroundColor Cyan

Write-Host "PHP Path:       $phpPath" -ForegroundColor White
Write-Host "Proyecto:       $projectPath" -ForegroundColor White
Write-Host "Tarea:          ProdFlow-Scheduler" -ForegroundColor White
Write-Host "Frecuencia:     Cada minuto" -ForegroundColor White
Write-Host "`nLa tarea de scheduler se ejecutará automáticamente cada minuto." -ForegroundColor Yellow
Write-Host "Puedes verla en: Panel de Control > Tareas Programadas > Tareas de Biblioteca`n" -ForegroundColor Yellow

pause
