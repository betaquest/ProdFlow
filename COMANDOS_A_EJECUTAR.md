# 🔧 COMANDOS PARA EJECUTAR - Guía Rápida

## ⚡ PASO 1: BACKUP INMEDIATO (5 MINUTOS)

### Windows PowerShell:
```powershell
# Ir a carpeta del proyecto
cd c:\laragon\www\ProdFlow

# Hacer backup de BD (si es MySQL)
$timestamp = Get-Date -Format "yyyy_MM_dd_HHmmss"
mysqldump -u root prodflow | Out-File "backup_$timestamp.sql" -Encoding UTF8

# Hacer backup de git
git add -A
git commit -m "Pre-optimization backup - $(Get-Date -Format 'yyyy-MM-dd HH:mm')"

# Ver status
git status
```

---

## 🎯 PASO 2: CREAR ÍNDICES EN BASE DE DATOS

### Opción A: Usando Migración Laravel (RECOMENDADO)
```bash
cd c:\laragon\www\ProdFlow

# Ver estado de migraciones
php artisan migrate:status

# Ejecutar migración de índices
php artisan migrate

# Confirmar que se ejecutó
php artisan migrate:status
```

### Opción B: Ejecutar SQL Directo (Si prefieres)
```bash
# Abrir Laragon Control Panel
# Click en "Database" 
# Ir a tab "Tools" → "PhpMyAdmin"
# Seleccionar BD "prodflow"
# Click en "SQL"
# Copiar contenido de INDICES_CRITICOS.sql
# Ejecutar

# O por terminal:
mysql -u root prodflow < INDICES_CRITICOS.sql
```

---

## 🔐 PASO 3: CAMBIAR CONFIGURACIÓN DE CACHÉ

### 3.1 Instalar Predis (para Redis)
```bash
cd c:\laragon\www\ProdFlow
composer require predis/predis
```

### 3.2 Editar Archivo .env
```bash
# Abrir archivo .env con tu editor favorito
notepad .env

# BUSCAR esta línea:
CACHE_STORE=database

# REEMPLAZAR POR:
CACHE_STORE=redis

# GUARDAR archivo
```

**O si no tienes Redis disponible**, usar archivo:
```
CACHE_STORE=file
```

### 3.3 Limpiar Caché
```bash
php artisan cache:clear
php artisan config:clear
php artisan cache:forget dashboard_stats
```

---

## 📝 PASO 4: ACTUALIZAR CÓDIGO (CÓDIGO LISTO PARA COPIAR)

### 4.1 Agregar Trait a Modelos

**Archivo: `app/Models/Programa.php`**

Agregar al inicio del archivo (después de `namespace`):
```php
use App\Traits\HasCommonScopes;
```

En la declaración de clase:
```php
class Programa extends Model
{
    use SoftDeletes, HasCommonScopes;  // ← AGREGAR HasCommonScopes
```

Agregar este método completo en la clase:
```php
    /**
     * Scope para optimizar carga con relaciones
     */
    public function scopeWithOptimizations($query)
    {
        return $query->with([
            'proyecto.cliente',
            'avances.fase',
            'perfilPrograma.areas',
            'responsableInicial',
            'creador'
        ]);
    }

    /**
     * Obtener fases configuradas sin lazy loading
     */
    public function getFasesConfiguradasIds()
    {
        // Si tiene perfil y está precargado, usarlo
        if ($this->perfilPrograma && $this->relationLoaded('perfilPrograma')) {
            return $this->perfilPrograma->getFasesIds();
        }

        // Si tiene fases configuradas manualmente
        if ($this->fases_configuradas && count($this->fases_configuradas) > 0) {
            return $this->fases_configuradas;
        }

        // Fallback: cargar solo si es necesario
        return Fase::where('activo', true)
            ->orderBy('orden')
            ->pluck('id')
            ->toArray();
    }
```

### 4.2 Actualizar DashboardView

**Archivo: `app/Livewire/DashboardView.php`**

Reemplazar método `loadData()` completo con:

```php
public function loadData()
{
    // NUEVA LÍNEA: Usar scope optimizado
    $query = Programa::query()
        ->withOptimizations()
        ->where('programas.activo', true);

    // Filtrar por clientes si no se muestran todos
    if (!$this->dashboard->todos_clientes && $this->dashboard->clientes_ids) {
        $query->whereHas('proyecto.cliente', function ($q) {
            $q->whereIn('clientes.id', $this->dashboard->clientes_ids);
        });
    }

    // Filtrar por perfiles si no se muestran todos
    if (!$this->dashboard->todos_perfiles && $this->dashboard->perfiles_ids) {
        $query->whereIn('perfil_programa_id', $this->dashboard->perfiles_ids);
    }

    // Aplicar criterios adicionales
    if ($this->dashboard->criterios) {
        foreach ($this->dashboard->criterios as $campo => $valor) {
            $query->where($campo, $valor);
        }
    }

    // Filtrar solo programas en proceso
    if ($this->dashboard->mostrar_solo_en_proceso) {
        $query->whereHas('avances', function ($q) {
            $q->where('estado', 'progress');
        });
    }

    // Aplicar ordenamiento
    $ordenamiento = $this->dashboard->orden_programas ?? 'nombre';
    match ($ordenamiento) {
        'cliente' => $query->join('proyectos', 'programas.proyecto_id', '=', 'proyectos.id')
                           ->join('clientes', 'proyectos.cliente_id', '=', 'clientes.id')
                           ->orderBy('clientes.nombre', 'asc')
                           ->select('programas.*'),
        'proyecto' => $query->join('proyectos', 'programas.proyecto_id', '=', 'proyectos.id')
                            ->orderBy('proyectos.nombre', 'asc')
                            ->select('programas.*'),
        default => $query->orderBy('nombre', 'asc'),
    };

    // PRECARGA: Traer todos los programas de una vez
    $programas = $query->get();
    
    // PRECARGA: Precalcular avances
    $avancesByPrograma = AvanceFase::whereIn(
        'programa_id',
        $programas->pluck('id')
    )
    ->with('fase')
    ->get()
    ->groupBy('programa_id');

    // Procesar filtros en memoria
    $programasFiltrados = collect();

    foreach ($programas as $programa) {
        // Ya tenemos todo precargado
        if ($this->dashboard->ocultar_completamente_finalizados) {
            $fasesPrograma = $programa->getFasesConfiguradasIds();
            $fasesProgramaObjs = $this->fases->whereIn('id', $fasesPrograma);

            $todasFasesCompletadas = true;
            foreach ($fasesProgramaObjs as $fase) {
                $programaAvances = $avancesByPrograma->get($programa->id, collect());
                $avance = $programaAvances->firstWhere('fase_id', $fase->id);
                
                if (!$avance || $avance->estado !== 'done') {
                    $todasFasesCompletadas = false;
                    break;
                }
            }

            if ($todasFasesCompletadas && $fasesProgramaObjs->isNotEmpty()) {
                continue; // Saltar programas completados
            }
        }

        $programasFiltrados->push($programa);
    }

    $this->programas = $programasFiltrados;

    // Calcular alertas de antigüedad
    $this->programasConAlerta = [];
    if ($this->dashboard->alerta_antiguedad_activa && $this->dashboard->alerta_antiguedad_dias > 0) {
        $fechaLimite = now()->subDays($this->dashboard->alerta_antiguedad_dias);

        foreach ($this->programas as $programa) {
            if ($programa->created_at < $fechaLimite) {
                $this->programasConAlerta[] = $programa->id;
            }
        }
    }

    // NUEVA FUNCIÓN: Calcular estadísticas una sola vez
    $this->calcularEstadisticas($programasFiltrados, $avancesByPrograma);
}

// NUEVA FUNCIÓN: Extraída para claridad
private function calcularEstadisticas($programas, $avancesByPrograma)
{
    $this->totalDone = 0;
    $this->totalProgress = 0;
    $this->totalPending = 0;
    $totalFases = 0;

    foreach ($programas as $programa) {
        $avances = $avancesByPrograma->get($programa->id, collect());
        
        foreach ($this->fases as $fase) {
            $avance = $avances->firstWhere('fase_id', $fase->id);
            $estado = $avance?->estado ?? 'pending';
            $totalFases++;

            match ($estado) {
                'done' => $this->totalDone++,
                'progress' => $this->totalProgress++,
                default => $this->totalPending++,
            };
        }
    }

    $this->porcentaje = $totalFases > 0 
        ? round(($this->totalDone / $totalFases) * 100, 1) 
        : 0;
}
```

### 4.3 Optimizar Widget

**Archivo: `app/Filament/Widgets/DashboardGeneral.php`**

Cambiar línea 14 de:
```php
protected static ?string $pollingInterval = '30s';
```

A:
```php
protected static ?string $pollingInterval = '60s';
```

Y cambiar la tabla para agregar lazy loading:
```php
->striped()
->defaultPaginationPageOption(25)  // Cambiar de 50 a 25
->paginated([25, 50])
->lazy();  // AGREGAR esta línea
```

### 4.4 Optimizar EstadísticasGenerales

**Archivo: `app/Filament/Widgets/EstadisticasGenerales.php`**

Agregar esta línea al inicio después de namespace:
```php
use Illuminate\Support\Facades\Cache;
```

Reemplazar el método getStats() completo con:
```php
protected function getStats(): array
{
    return Cache::remember('dashboard_stats', 300, function () {
        $totalClientes = Cliente::where('activo', true)->count();
        $totalProyectos = Proyecto::count();
        $totalProgramas = Programa::count();
        $avancesCompletados = AvanceFase::where('estado', 'done')->count();
        $totalAvances = AvanceFase::count();
        $porcentajeCompletado = $totalAvances > 0 
            ? round(($avancesCompletados / $totalAvances) * 100, 1) 
            : 0;

        return [
            Stat::make('Clientes Activos', $totalClientes)
                ->description('Total de clientes activos')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Proyectos', $totalProyectos)
                ->description('Total de proyectos')
                ->descriptionIcon('heroicon-m-folder')
                ->color('info'),

            Stat::make('Programas', $totalProgramas)
                ->description('Total de programas')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'),

            Stat::make('Progreso Global', $porcentajeCompletado . '%')
                ->description("{$avancesCompletados} de {$totalAvances} avances completados")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
        ];
    });
}
```

---

## ✅ PASO 5: VALIDAR CAMBIOS

### 5.1 Verificar Sintaxis
```bash
php artisan tinker

# Ejecutar:
>>> App\Models\Programa::withOptimizations()->limit(1)->get()
# Debe retornar colección sin errores

>>> App\Models\Programa::active()->count()
# Debe retornar número sin errores

# Salir:
>>> exit
```

### 5.2 Ver Queries Ejecutadas
```bash
# Si tienes Debugbar instalado
composer require barryvdh/laravel-debugbar --dev

# Acceder a dashboard
# Abrir Debugbar (esquina derecha)
# Ver pestaña "Queries"
# Debe mostrar ~10-20 queries (antes: 100-150)
```

### 5.3 Testing Manual
```bash
# 1. Abrir navegador y cargar dashboard
# 2. Verificar que carga sin errores
# 3. Medir tiempo con DevTools (F12 → Network)
# 4. Debe ser < 2 segundos (antes: 8-12s)
```

---

## 🚀 PASO 6: DEPLOY A PRODUCCIÓN

### SOLO SI TODO FUNCIONA EN LOCAL:

```bash
# 1. Commit de cambios
git add -A
git commit -m "Performance optimization: N+1 queries, indexes, caching"

# 2. Push a repositorio
git push origin main

# 3. En servidor de producción:
cd /home/prodflow/public_html

# 4. Pull cambios
git pull origin main

# 5. Ejecutar migraciones
php artisan migrate --force

# 6. Limpiar caché
php artisan cache:clear
php artisan config:clear

# 7. Reiniciar servicios (si es necesario)
systemctl restart php-fpm  # O tu servicio PHP

# 8. Verificar status
php artisan up  # Si está en maintenance
```

---

## 📊 PASO 7: MONITOREO POST-DEPLOY

### Verificar Rendimiento
```bash
# Ver queries lentas
tail -f /var/log/mysql/slow-query.log

# Ver CPU/Memoria en servidor
top
# O con htop
htop

# Ver logs de aplicación
tail -f storage/logs/laravel.log
```

### Métricas a Monitorear por 24 Horas
- ✓ CPU: debe bajar de 85% a 20-30%
- ✓ Tiempo de carga: debe bajar de 8s a 1-2s
- ✓ Queries: debe bajar de 100-150 a 10-20
- ✓ Memoria: debe bajar de 500MB a 50-100MB
- ✓ Errores: debe mantenerse en 0 o muy bajo

---

## 🆘 ROLLBACK (Si Algo Sale Mal)

### Opción 1: Git Rollback
```bash
# Ver commits recientes
git log --oneline -5

# Revertir a versión anterior
git revert HEAD

# O ir a un commit específico
git reset --hard <commit_hash>
```

### Opción 2: Base de Datos Rollback
```bash
# Revertir migraciones
php artisan migrate:rollback

# O rollback hasta specific:
php artisan migrate:rollback --step=1
```

### Opción 3: Restaurar desde Backup
```bash
# Si tenías BD en backup
mysql -u root prodflow < backup_2025_01_14.sql
```

---

## 📞 SOPORTE

Si tienes problemas durante implementación:

1. **Verificar logs**: `storage/logs/laravel.log`
2. **Verificar .env**: Asegurar valores correctos
3. **Limpiar todo**: 
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   composer dump-autoload
   ```
4. **Reiniciar**: Reiniciar servidor/PHP
5. **Ayuda**: Ver archivo de error específico

---

**Tiempo estimado total**: 2-3 horas  
**Dificultad**: Media  
**Riesgo**: Bajo (totalmente reversible)

✅ **¡ÉXITO CON LA OPTIMIZACIÓN!**
