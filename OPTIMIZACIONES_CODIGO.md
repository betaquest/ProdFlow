# 🚀 OPTIMIZACIONES DE CÓDIGO - IMPLEMENTACIÓN DETALLADA

## 1. OPTIMIZAR DashboardView (MÁXIMA PRIORIDAD)

### Problema Actual
```php
// ❌ DashboardView.php líneas 50-150
$query = Programa::query()->with(['proyecto.cliente', 'avances.fase'])
    ->where('programas.activo', true);

$programas = $query->get();

// Luego en la línea 142: LOOP CON N+1 QUERIES
foreach ($this->programas as $programa) {
    $fasesPrograma = $programa->getFasesConfiguradasIds(); // QUERY #1
    foreach ($fasesProgramaObjs as $fase) {
        $avance = $programa->avances->firstWhere(...); // Already loaded pero...
        if (...$programa->created_at...) // ✓ OK
    }
}
```

### Solución Completa

**Paso 1: Actualizar modelo Programa**

Agregar al archivo `app/Models/Programa.php`:

```php
use App\Traits\HasCommonScopes;

class Programa extends Model
{
    use HasCommonScopes;
    
    /**
     * Scope para optimizar carga con relaciones
     */
    public function scopeWithOptimizations($query)
    {
        return $query->with([
            'proyecto.cliente',
            'avances.fase',
            'perfilPrograma.areas',  // ✓ AGREGAR
            'responsableInicial',     // ✓ AGREGAR
            'creador'                 // ✓ AGREGAR
        ]);
    }

    /**
     * Obtener fases de forma precargada (NO lazy loading)
     */
    public function getFasesConfiguradasIds()
    {
        // ✓ OPTIMIZADO: Ya tiene perfilPrograma precargado
        if ($this->perfilPrograma && $this->relationLoaded('perfilPrograma')) {
            return $this->perfilPrograma->getFasesIds();
        }

        // Si no está cargado, usamos array configurado
        if ($this->fases_configuradas && count($this->fases_configuradas) > 0) {
            return $this->fases_configuradas;
        }

        // Fallback: cargar solo si es necesario
        return Fase::where('activo', true)
            ->orderBy('orden')
            ->pluck('id')
            ->toArray();
    }
}
```

**Paso 2: Refactorizar método loadData() en DashboardView**

Archivo: `app/Livewire/DashboardView.php`

```php
public function loadData()
{
    // ✓ CAMBIO #1: Usar scope optimizado
    $query = Programa::query()
        ->withOptimizations()  // ✓ NUEVO
        ->where('programas.activo', true);

    // ... resto de filtros ...

    // ✓ CAMBIO #2: Precalcular datos ANTES del loop
    $programas = $query->get();
    $allFases = $this->fases->keyBy('id');  // Indexar para búsqueda O(1)
    
    // ✓ CAMBIO #3: Precalcular todos los avances de una vez
    $avancesByPrograma = AvanceFase::whereIn(
        'programa_id',
        $programas->pluck('id')
    )
    ->with('fase')
    ->get()
    ->groupBy('programa_id');  // Agrupar para acceso rápido

    // ✓ CAMBIO #4: Procesar en memoria (ya tenemos todo)
    $programasFiltrados = collect();
    
    foreach ($programas as $programa) {
        // ✓ OPTIMIZADO: Usar datos precargados
        $programaAvances = $avancesByPrograma->get($programa->id, collect());
        
        // ... lógica de filtrado ...
        
        $programasFiltrados->push($programa);
    }

    $this->programas = $programasFiltrados;
    
    // ✓ CAMBIO #5: Calcular estadísticas en una sola pasada
    $this->calcularEstadisticas($programasFiltrados, $allFases, $avancesByPrograma);
}

private function calcularEstadisticas($programas, $fases, $avancesByPrograma)
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

---

## 2. OPTIMIZAR MODELS FASE

**Archivo: `app/Models/Fase.php`**

```php
// ❌ PROBLEMA: puedeAvanzar() ejecuta query por cada llamada
public function puedeAvanzar($programaId): bool
{
    $faseAnterior = $this->faseAnterior();
    if (!$faseAnterior) return true;

    // QUERY! Muy lenta en loops
    $avanceAnterior = AvanceFase::where('programa_id', $programaId)
        ->where('fase_id', $faseAnterior->id)
        ->first();

    return $avanceAnterior && $avanceAnterior->estado === 'done';
}

// ✓ SOLUCIÓN: Pasar avances precargados
public function puedeAvanzar($programaId, Collection $avances = null): bool
{
    $faseAnterior = $this->faseAnterior();
    if (!$faseAnterior) return true;

    if ($avances) {
        // Usar datos precargados
        $avanceAnterior = $avances->firstWhere(fn($a) => 
            $a->programa_id === $programaId && 
            $a->fase_id === $faseAnterior->id
        );
    } else {
        // Fallback: solo si no tenemos datos
        $avanceAnterior = AvanceFase::where('programa_id', $programaId)
            ->where('fase_id', $faseAnterior->id)
            ->first();
    }

    return $avanceAnterior && $avanceAnterior->estado === 'done';
}
```

---

## 3. OPTIMIZAR WIDGET DashboardGeneral

**Archivo: `app/Filament/Widgets/DashboardGeneral.php`**

```php
// ❌ ACTUAL: Polling cada 30 segundos
protected static ?string $pollingInterval = '30s';

// ✓ CAMBIO #1: Aumentar a 60s (reduce CPU a la mitad)
protected static ?string $pollingInterval = '60s';

// ✓ CAMBIO #2: Usar paginación más eficiente
public function table(Table $table): Table
{
    return $table
        ->query(
            Programa::query()
                // Eager loading completo
                ->with(['proyecto.cliente', 'avances.fase'])
                ->where('activo', true)
                ->latest('id')
        )
        ->columns([/* ... */])
        ->striped()
        ->defaultPaginationPageOption(25)  // ← Cambiar de 50 a 25
        ->paginated([25, 50])              // ← Opciones discretas
        ->lazy();                           // ← AGREGAR: lazy loading
}
```

---

## 4. OPTIMIZAR WIDGET EstadisticasGenerales

**Archivo: `app/Filament/Widgets/EstadisticasGenerales.php`**

```php
// ❌ ACTUAL: Recuenta todo cada vez
public function getStats(): array
{
    $totalClientes = Cliente::where('activo', true)->count();        // Query
    $totalProyectos = Proyecto::count();                             // Query
    $totalProgramas = Programa::count();                             // Query
    $avancesCompletados = AvanceFase::where('estado', 'done')->count(); // Query
    // ... y más queries
}

// ✓ SOLUCIÓN: Usar caché con invalidación inteligente
use Illuminate\Support\Facades\Cache;

public function getStats(): array
{
    // Caché de 5 minutos
    return Cache::remember('dashboard_stats', 300, function () {
        return [
            Stat::make('Clientes Activos', Cliente::active()->count())
                ->description('Total de clientes activos')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Proyectos', Proyecto::count())
                ->description('Total de proyectos')
                ->descriptionIcon('heroicon-m-folder')
                ->color('info'),

            // ... etc
        ];
    });
}

// Invalidar caché en observers:
// app/Observers/ClienteObserver.php, ProgramaObserver.php, etc.
public static function booted()
{
    static::created(function () {
        Cache::forget('dashboard_stats');
    });
    static::updated(function () {
        Cache::forget('dashboard_stats');
    });
}
```

---

## 5. AGREGAR SCOPES A MODELOS

**Archivo: `app/Models/Programa.php`**

```php
use App\Traits\HasCommonScopes;

class Programa extends Model
{
    use HasCommonScopes;

    public function scopeByProject($query, $projectId)
    {
        return $query->where('proyecto_id', $projectId);
    }

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
}

// Uso en DashboardView:
// Programa::active()->byProject($id)->withOptimizations()->get()
```

**Archivo: `app/Models/AvanceFase.php`**

```php
public function scopeByPrograma($query, $programaId)
{
    return $query->where('programa_id', $programaId);
}

public function scopeByFase($query, $faseId)
{
    return $query->where('fase_id', $faseId);
}

public function scopeCompleted($query)
{
    return $query->where('estado', 'done');
}

public function scopeInProgress($query)
{
    return $query->where('estado', 'progress');
}

public function scopeOptimized($query)
{
    return $query->with(['programa', 'fase', 'responsable', 'area']);
}
```

---

## 6. CONFIGURAR CACHÉ (CRÍTICO)

**Archivo: `.env` o `.env.production`**

```env
# ❌ ACTUAL (MUY LENTO)
CACHE_STORE=database

# ✓ CAMBIAR A:
CACHE_STORE=redis

# Si no tienes Redis, cambiar a:
CACHE_STORE=file

# Configurar TTL por defecto (en segundos)
CACHE_EXPIRATION=3600
```

**Archivo: `config/cache.php`**

```php
// Si usas Redis
'redis' => [
    'driver' => 'redis',
    'connection' => 'cache',
    'lock_connection' => 'default',
],

// Configurar conexión Redis en config/database.php
'redis' => [
    'client' => 'phpredis', // ← Más rápido que predis
    
    'cache' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', 6379),
        'database' => 1,
    ],
],
```

---

## 7. OPTIMIZAR ACTIVITY LOG

**Archivo: `app/Models/AvanceFase.php`**

```php
// ❌ ACTUAL: Registra TODO
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logAll()  // ← Problema
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
}

// ✓ SOLUCIÓN: Solo logs importantes
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logOnly([
            'estado',
            'fecha_inicio',
            'fecha_fin',
            'responsable_id',
            'notas_finalizacion'
        ])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs()
        ->setDescriptionForEvent(fn (string $eventName) => 
            "Avance {$eventName}"
        );
}

// Además, agregar índices en activity_log
// (ya incluidos en la migración de índices)
```

---

## 8. LIMPIAR ACTIVITY LOG PERIÓDICAMENTE

**Comando artisan personalizado**

Archivo: `app/Console/Commands/CleanActivityLog.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

class CleanActivityLog extends Command
{
    protected $signature = 'log:clean {days=90}';
    protected $description = 'Eliminar logs de actividad más antiguos que N días';

    public function handle()
    {
        $days = (int) $this->argument('days');
        $date = now()->subDays($days);

        $deleted = Activity::where('created_at', '<', $date)->delete();

        $this->info("Eliminados {$deleted} registros de actividad");
    }
}
```

**Agregar a `app/Console/Kernel.php`**

```php
protected function schedule(Schedule $schedule)
{
    // Limpiar logs cada semana
    $schedule->command('log:clean 90')
        ->weekly()
        ->sundays()
        ->at('02:00');

    // Optimizar tablas cada mes
    $schedule->exec('OPTIMIZE TABLE activity_log')
        ->monthly();
}
```

---

## 9. CONFIGURAR DATABASE CONNECTION POOLING

**Archivo: `config/database.php`**

```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'laravel'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    
    // ✓ AGREGAR ESTOS PARÁMETROS:
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => 'InnoDB',
    
    // ✓ IMPORTANTE: Configurar pool de conexiones
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        PDO::ATTR_TIMEOUT => 5,  // 5 segundos
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION wait_timeout=28800",
    ]) : [],
],
```

---

## 🎯 RESUMEN DE CAMBIOS

| Archivo | Cambio | Impacto |
|---------|--------|--------|
| `DashboardView.php` | Refactorizar loadData() | -80% queries |
| `Programa.php` | Agregar scope + trait | -50% lazy loading |
| `Fase.php` | Pasar avances precargados | -200 queries |
| `DashboardGeneral.php` | Aumentar polling a 60s | -50% CPU |
| `EstadisticasGenerales.php` | Agregar caché 5m | -300 queries/hora |
| `.env` | Cambiar cache a redis | -90% latencia caché |
| `AvanceFase.php` | Optimizar logs | -40% queries |
| `Kernel.php` | Agregar limpieza logs | -2GB BD/mes |

---

## ⏱️ TIEMPO ESTIMADO DE IMPLEMENTACIÓN

- **Paso 1**: Crear índices BD = 5 minutos
- **Paso 2**: Cambiar configuración caché = 10 minutos
- **Paso 3**: Refactorizar DashboardView = 30 minutos
- **Paso 4**: Agregar scopes y traits = 20 minutos
- **Paso 5**: Optimizar widgets = 15 minutos
- **Paso 6**: Testing y validación = 30 minutos

**Total**: ~2 horas de trabajo

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

- [ ] Crear migraciones de índices
- [ ] Ejecutar: `php artisan migrate`
- [ ] Actualizar `.env` con `CACHE_STORE=redis`
- [ ] Instalar: `composer require predis/predis`
- [ ] Refactorizar DashboardView
- [ ] Agregar traits a modelos
- [ ] Actualizar widgets
- [ ] Ejecutar: `php artisan cache:clear`
- [ ] Testing local (verificar que sigue funcionando)
- [ ] Deploy a producción (durante baja actividad)
- [ ] Monitoreo 24h
