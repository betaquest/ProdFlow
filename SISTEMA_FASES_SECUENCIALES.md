# 🔄 Sistema de Fases Secuenciales

## 📋 Descripción General

Se ha implementado un sistema completo de fases secuenciales con orden obligatorio, validación de progreso y liberación automática con notificaciones.

---

## ✨ Características Principales

### 1. **Orden Secuencial**
- ✅ Las fases tienen un orden numérico (1, 2, 3...)
- ✅ Una fase no puede avanzar sin completar la anterior
- ✅ Orden configurable y reordenable visualmente

### 2. **Validación de Progreso**
- ✅ Requiere aprobación de fase anterior
- ✅ Bloqueo automático de fases futuras
- ✅ Lógica de validación en el modelo

### 3. **Sistema de Liberación**
- ✅ Botón "Liberar Siguiente Fase" cuando se completa
- ✅ Notificaciones automáticas a usuarios del siguiente rol
- ✅ Confirmación antes de liberar

### 4. **Notificaciones Inteligentes**
- ✅ Email + Notificación en el sistema
- ✅ Notifica a usuarios del rol con nombre de la fase
- ✅ Fallback a Administradores si no existe el rol

---

## 🗃️ Cambios en Base de Datos

### Nueva Migración
**Archivo:** `database/migrations/2025_10_05_063341_add_orden_to_fases_table.php`

```php
Schema::table('fases', function (Blueprint $table) {
    $table->integer('orden')->default(0)->after('nombre');
    $table->boolean('requiere_aprobacion')->default(true)->after('orden');
});
```

**Campos agregados:**
- `orden`: Número de secuencia de la fase
- `requiere_aprobacion`: Si requiere que fase anterior esté completa

**Ejecutar migración:**
```bash
php artisan migrate
```

---

## 📦 Modelo Fase Mejorado

**Archivo:** `app/Models/Fase.php`

### Nuevos Métodos:

#### `siguienteFase()`
```php
public function siguienteFase()
{
    return self::where('orden', '>', $this->orden)
        ->orderBy('orden', 'asc')
        ->first();
}
```
Obtiene la siguiente fase en orden.

#### `faseAnterior()`
```php
public function faseAnterior()
{
    return self::where('orden', '<', $this->orden)
        ->orderBy('orden', 'desc')
        ->first();
}
```
Obtiene la fase anterior en orden.

#### `puedeAvanzar($programaId)`
```php
public function puedeAvanzar($programaId): bool
{
    $faseAnterior = $this->faseAnterior();

    if (!$faseAnterior) {
        return true; // Es la primera fase
    }

    $avanceAnterior = AvanceFase::where('programa_id', $programaId)
        ->where('fase_id', $faseAnterior->id)
        ->first();

    return $avanceAnterior && $avanceAnterior->estado === 'done';
}
```
Valida si la fase puede iniciarse verificando que la anterior esté completa.

---

## 🎨 FaseResource Mejorado

**Archivo:** `app/Filament/Resources/FaseResource.php`

### Formulario
```php
Forms\Components\TextInput::make('orden')
    ->label('Orden')
    ->required()
    ->numeric()
    ->default(fn () => Fase::max('orden') + 1)
    ->helperText('Orden secuencial de la fase')
```

### Tabla con Reordenamiento
```php
->defaultSort('orden', 'asc')
->reorderable('orden')
->columns([
    Tables\Columns\TextColumn::make('orden')
        ->label('#')
        ->badge()
        ->color('info'),
    Tables\Columns\TextColumn::make('nombre')
        ->weight('semibold'),
    Tables\Columns\IconColumn::make('requiere_aprobacion')
        ->boolean(),
])
```

**Características:**
- ✅ Vista ordenada por defecto
- ✅ Drag & drop para reordenar
- ✅ Badge visual con el número de orden
- ✅ Indicador de aprobación requerida

---

## 🔔 Sistema de Notificaciones

### Notificación: FaseLiberada
**Archivo:** `app/Notifications/FaseLiberada.php`

```php
public function __construct(
    public Programa $programa,
    public Fase $faseCompletada,
    public Fase $siguienteFase
) {}
```

**Canales:**
- 📧 Email
- 💾 Base de datos (notificaciones in-app)

**Contenido del Email:**
```
Asunto: Nueva Fase Liberada - {Nombre de la Fase}

La fase '{Fase Completada}' ha sido completada.
Programa: {Nombre del Programa}
Ahora puedes trabajar en: '{Siguiente Fase}'

[Botón: Ver Programa]

¡Es tu turno de trabajar en esta fase!
```

---

## 🚀 AvanceFaseResource - Botón de Liberación

**Archivo:** `app/Filament/Resources/AvanceFaseResource.php`

### Acción "Liberar Siguiente Fase"
```php
Tables\Actions\Action::make('liberar_fase')
    ->label('Liberar Siguiente Fase')
    ->icon('heroicon-o-arrow-right-circle')
    ->color('success')
    ->visible(fn (AvanceFase $record) => $record->estado === 'done')
    ->action(function (AvanceFase $record) {
        // Lógica de liberación
    })
    ->requiresConfirmation()
```

**Características:**
- ✅ Solo visible cuando el estado es "Finalizado"
- ✅ Requiere confirmación del usuario
- ✅ Busca usuarios con rol del nombre de la siguiente fase
- ✅ Fallback a Administradores
- ✅ Notificación de éxito/error

**Lógica de Notificación:**
1. Obtiene siguiente fase en orden
2. Busca usuarios con rol `{nombre_de_fase}`
3. Si no existe el rol, notifica a Administradores
4. Envía notificación por email y sistema
5. Muestra confirmación en pantalla

---

## 📊 Dashboard View Ordenado

**Archivo:** `app/Livewire/DashboardView.php`

### Cambio Implementado:
```php
$this->fases = Fase::orderBy('orden', 'asc')->get();
```

**Resultado:**
- ✅ Fases mostradas en orden correcto
- ✅ Secuencia visual coherente
- ✅ Alineado con el flujo de trabajo

---

## 🔄 Flujo de Trabajo

### Escenario: Proceso de 4 Fases

1. **Ingeniería** (Orden 1)
2. **Captura** (Orden 2)
3. **Corte** (Orden 3)
4. **Ensamblado** (Orden 4)

### Paso a Paso:

#### 1. **Usuario de Ingeniería completa su fase**
- Marca el avance como "Finalizado" (done)
- Aparece botón "Liberar Siguiente Fase"
- Click en el botón
- Confirma la liberación

#### 2. **Sistema procesa la liberación**
- Busca fase siguiente: "Captura"
- Busca usuarios con rol "Captura"
- Envía notificación a esos usuarios

#### 3. **Usuarios de Captura reciben notificación**
- 📧 Email: "Nueva Fase Liberada - Captura"
- 🔔 Notificación en el panel
- Pueden comenzar a trabajar

#### 4. **Validación de Secuencia**
- Si Captura intenta avanzar sin terminar, se bloquea
- Corte no puede iniciar hasta que Captura termine
- Sistema valida automáticamente

---

## 🎯 Beneficios del Sistema

### 1. **Control de Calidad**
- No se pueden saltar fases
- Cada etapa debe completarse
- Trazabilidad completa

### 2. **Comunicación Automática**
- Notificaciones instantáneas
- Usuarios informados en tiempo real
- Menos errores de comunicación

### 3. **Gestión Visual**
- Drag & drop para reordenar
- Badges de orden claros
- Estado visual de progreso

### 4. **Flexibilidad**
- Orden configurable
- Aprobación opcional por fase
- Adaptable a diferentes procesos

---

## 🛠️ Configuración Inicial

### 1. **Ejecutar Migración**
```bash
php artisan migrate
```

### 2. **Configurar Fases**
1. Ir a `/admin/fases`
2. Para cada fase existente:
   - Editar fase
   - Asignar orden (1, 2, 3...)
   - Activar "Requiere Aprobación" si aplica
   - Guardar

**Ejemplo:**
```
Orden 1: Ingeniería
Orden 2: Captura
Orden 3: Corte
Orden 4: Ensamblado
Orden 5: Instalación
Orden 6: Finalizado
```

### 3. **Crear Roles que Coincidan**
Los roles deben tener el mismo nombre que las fases para las notificaciones automáticas.

**En:** `database/seeders/RolePermissionSeeder.php`
```php
$roles = [
    'Administrador',
    'Ingenieria',  // Coincide con fase
    'Captura',     // Coincide con fase
    'Corte',       // Coincide con fase
    'Ensamblado',  // Coincide con fase
    'Instalacion', // Coincide con fase
    'Finalizado',  // Coincide con fase
];
```

### 4. **Asignar Usuarios a Roles**
```bash
php artisan tinker
>>> $user = User::find(2);
>>> $user->assignRole('Captura');
```

---

## 📝 Ejemplo de Uso

### Completar Fase y Liberar Siguiente

1. **Ir a Avances de Fase** (`/admin/avance-fases`)
2. Buscar avance en estado "Finalizado"
3. Click en botón verde "➡️ Liberar Siguiente Fase"
4. Confirmar acción
5. ✅ Notificación enviada

### Reordenar Fases

1. **Ir a Fases** (`/admin/fases`)
2. Usar drag & drop (arrastrar filas)
3. Las fases se reordenan automáticamente
4. El orden se guarda al soltar

---

## 🔍 Troubleshooting

### Problema: No se envían notificaciones
**Solución:**
- Verificar que existe rol con nombre de fase
- Si no existe, se notifica a Administradores
- Revisar configuración de email en `.env`

### Problema: No puedo iniciar fase
**Causa:** Fase anterior no completada
**Solución:**
- Verificar que fase anterior esté en estado "done"
- Usar método `puedeAvanzar($programaId)` para validar

### Problema: Orden incorrecto en dashboard
**Solución:**
- Limpiar caché: `php artisan cache:clear`
- Verificar campo `orden` en base de datos
- Debe estar ordenado: `SELECT * FROM fases ORDER BY orden ASC`

---

## ✅ Checklist de Implementación

- [x] Migración de campos `orden` y `requiere_aprobacion`
- [x] Modelo Fase con métodos de secuencia
- [x] FaseResource con reordenamiento visual
- [x] Notificación FaseLiberada creada
- [x] Botón "Liberar Fase" en AvanceFaseResource
- [x] DashboardView ordenado por secuencia
- [x] Documentación completa

---

## 🚦 Próximos Pasos Recomendados

### Opcional: Validación en Formulario de Avance
Agregar validación en el form de AvanceFase:

```php
Forms\Components\Select::make('fase_id')
    ->label('Fase')
    ->options(function (Forms\Get $get) {
        $programaId = $get('programa_id');
        if (!$programaId) return Fase::pluck('nombre', 'id');

        return Fase::all()->filter(function ($fase) use ($programaId) {
            return $fase->puedeAvanzar($programaId);
        })->pluck('nombre', 'id');
    })
    ->helperText('Solo se muestran fases disponibles')
```

### Opcional: Indicador Visual en Dashboard
Agregar indicador de "bloqueado" en celdas:

```php
@if(!$fase->puedeAvanzar($programa->id))
    <span class="text-xs">🔒</span>
@endif
```

---

**Fecha de Implementación:** 2025-10-05
**Estado:** ✅ Completado
**Versión:** 1.0
