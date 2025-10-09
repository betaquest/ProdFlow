# 🔄 Flujo Automático de Proceso

## 📖 Descripción

El sistema ahora crea **automáticamente** el avance de la primera fase cuando se crea un programa nuevo. Esto significa que **NO necesitas crear manualmente** los avances de fase.

---

## ✨ ¿Cómo Funciona?

### Flujo Completo Automático

```
1. INGENIERÍA crea:
   ├─ Cliente: "ACME Corp"
   ├─ Proyecto: "Producción 2024"
   └─ Programa: "Lote A-001"

   ↓ 🤖 AUTOMÁTICO

2. Sistema crea automáticamente:
   ├─ AvanceFase
   │  ├─ programa_id: "Lote A-001"
   │  ├─ fase_id: Primera fase (orden = 1)
   │  ├─ responsable_id: Usuario que creó el programa (Ingeniería)
   │  ├─ estado: "pending"
   │  └─ activo: true

   ↓

3. Usuario de Ingeniería ve en "Proceso":
   ├─ Lote A-001 | Primera Fase | Estado: Pendiente
   └─ Botón: [▶ Iniciar]

   ↓

4. Inicia la fase → Trabaja → Finaliza → Libera

   ↓ 🤖 NOTIFICACIÓN AUTOMÁTICA

5. Siguiente rol recibe notificación:
   ├─ 📧 Email
   └─ 🔔 Notificación in-app
```

---

## 🎯 Ejemplo Paso a Paso

### Escenario: Usuario "Juan" (Rol: Ingeniería)

#### **Paso 1: Juan crea un Programa**

```
1. Juan navega a /admin/programas
2. Click "Nuevo Programa"
3. Completa formulario:
   - Cliente: ACME Corp
   - Proyecto: Prod-2024
   - Nombre: Lote-A-001
   - Descripción: Lote inicial
4. Click "Crear"
```

#### **Paso 2: Sistema crea automáticamente el avance**

```sql
-- Lo que hace el sistema automáticamente:
INSERT INTO avance_fases (
    programa_id,
    fase_id,
    responsable_id,
    estado,
    activo,
    created_at,
    updated_at
) VALUES (
    123,                    -- ID del programa recién creado
    1,                      -- ID de la primera fase (orden = 1)
    5,                      -- ID de Juan (quien creó el programa)
    'pending',              -- Estado inicial
    1,                      -- Activo
    NOW(),
    NOW()
);
```

#### **Paso 3: Juan ve su fase en "Proceso"**

```
1. Juan navega a /admin/proceso (antes "Mis Fases")
2. Ve automáticamente:
   ┌────────────────────────────────────────────────────────┐
   │ Cliente  │ Proyecto  │ Programa    │ Fase      │ Estado│
   ├──────────┼───────────┼─────────────┼───────────┼───────┤
   │ ACME     │ Prod-2024 │ Lote-A-001  │ Ingeniería│ 🕐 Pend│
   │          │           │             │           │ [▶ Iniciar]│
   └────────────────────────────────────────────────────────┘
```

#### **Paso 4: Juan trabaja en la fase**

```
3. Click "▶ Iniciar"
   ✅ Estado → "En Progreso"
   ✅ fecha_inicio → NOW()

4. Trabaja en la fase...
   (Diseño, especificaciones, etc.)

5. Click "✓ Finalizar"
   - Agrega notas: "Diseño completado, 150 piezas"
   ✅ Estado → "Finalizado"
   ✅ fecha_fin → NOW()
   ✅ notas guardadas
```

#### **Paso 5: Juan libera siguiente fase**

```
6. Click "➡ Liberar Siguiente"
7. Sistema:
   - Identifica siguiente fase: "Captura" (orden = 2)
   - Busca usuarios con rol "Captura"
   - Envía notificaciones:
     📧 Email a usuarios de Captura
     🔔 Notificación in-app
```

#### **Paso 6: Usuario de Captura recibe y continúa**

```
Usuario "María" (Rol: Captura):

1. Recibe email:
   "Nueva Fase Liberada - Captura
    Programa: Lote-A-001
    Es tu turno de trabajar"

2. Ve notificación en panel 🔔

3. Navega a /admin/proceso

4. Ve automáticamente:
   ┌────────────────────────────────────────────────────────┐
   │ ACME │ Prod-2024 │ Lote-A-001 │ Captura │ 🕐 Pendiente │
   │      │           │            │         │ [▶ Iniciar]  │
   └────────────────────────────────────────────────────────┘

   NOTA: Esta fase fue creada manualmente o por Administrador
   para asignar a María. En el futuro, podría auto-crearse también.
```

---

## 🔧 Configuración Técnica

### Observer Creado: `ProgramaObserver`

**Archivo**: `app/Observers/ProgramaObserver.php`

```php
public function created(Programa $programa): void
{
    // Obtener la primera fase (orden = 1 o la menor)
    $primeraFase = Fase::orderBy('orden', 'asc')->first();

    if ($primeraFase) {
        // Crear avance automáticamente
        AvanceFase::create([
            'programa_id' => $programa->id,
            'fase_id' => $primeraFase->id,
            'responsable_id' => Auth::id(), // ← Usuario que creó el programa
            'estado' => 'pending',
            'activo' => true,
        ]);
    }
}
```

**Registrado en**: `app/Providers/AppServiceProvider.php`

```php
public function boot(): void
{
    Programa::observe(ProgramaObserver::class);
}
```

---

## 📋 Requisitos Previos

### 1. **Fases deben estar configuradas**

Antes de crear programas, asegúrate de tener fases con orden:

```sql
SELECT * FROM fases ORDER BY orden ASC;

-- Ejemplo:
+----+-------------+-------+---------------------+
| id | nombre      | orden | requiere_aprobacion |
+----+-------------+-------+---------------------+
|  1 | Ingeniería  |     1 | true                |
|  2 | Captura     |     2 | true                |
|  3 | Corte       |     3 | true                |
|  4 | Ensamblado  |     4 | true                |
|  5 | Instalación |     5 | true                |
|  6 | Finalizado  |     6 | false               |
+----+-------------+-------+---------------------+
```

### 2. **Roles deben coincidir con nombres de fases**

Para que las notificaciones funcionen automáticamente:

```php
// Roles en el sistema:
'Ingeniería'   → corresponde a fase "Ingeniería"
'Captura'      → corresponde a fase "Captura"
'Corte'        → corresponde a fase "Corte"
'Ensamblado'   → corresponde a fase "Ensamblado"
// etc.
```

Si no existe el rol, se notifica a **Administradores**.

---

## 🎨 Cambios en la Interfaz

### Antes: "Mis Fases"
- Nombre del menú: "Mis Fases"
- Título: "Mis Fases Asignadas"

### Ahora: "Proceso"
- Nombre del menú: **"Proceso"**
- Título: **"Mi Proceso de Trabajo"**
- Más intuitivo y profesional

---

## 🔄 Flujo de Roles

### Rol: Ingeniería

**Permisos**:
- ✅ Clientes: Ver, Crear, Editar
- ✅ Proyectos: Ver, Crear, Editar, Eliminar
- ✅ Programas: Ver

**Flujo**:
```
1. Crea Cliente → Crea Proyecto → Crea Programa
2. Automáticamente se le asigna primera fase "Ingeniería"
3. Va a "Proceso" → Ve su fase pendiente
4. Inicia → Trabaja → Finaliza → Libera a "Captura"
```

### Rol: Captura

**Permisos**:
- ✅ Programas: Ver, Crear, Editar

**Flujo**:
```
1. Recibe notificación de Ingeniería
2. Va a "Proceso" → Ve fase "Captura" pendiente
   (Administrador debe crear este avance o hacerlo manual)
3. Inicia → Trabaja → Finaliza → Libera a "Corte"
```

### Roles Operativos (Corte, Ensamblado, etc.)

**Permisos**:
- ✅ Dashboards: Ver
- ✅ Fases: Ver

**Flujo**:
```
1. Reciben notificación de fase anterior
2. Van a "Proceso" → Ven su fase pendiente
3. Inician → Trabajan → Finalizan → Liberan
```

---

## 💡 Mejora Futura Sugerida

### Auto-creación de TODAS las fases

Actualmente solo se crea la **primera fase** automáticamente.

**Sugerencia**: Al crear un programa, crear **todas las fases** de una vez:

```php
public function created(Programa $programa): void
{
    $fases = Fase::orderBy('orden', 'asc')->get();

    foreach ($fases as $index => $fase) {
        AvanceFase::create([
            'programa_id' => $programa->id,
            'fase_id' => $fase->id,
            'responsable_id' => $index === 0 ? Auth::id() : null, // Solo primera con responsable
            'estado' => 'pending',
            'activo' => true,
        ]);
    }
}
```

**Ventajas**:
- ✅ Todas las fases ya existen desde el inicio
- ✅ No necesitas crear manualmente cada avance
- ✅ Solo falta asignar responsables

**Desventajas**:
- ❌ Crea registros que quizás no se usen (si el programa se cancela)
- ❌ Menos flexible para flujos variables

**Decisión**: Por ahora, solo primera fase. Evaluar según necesidad.

---

## ✅ Checklist de Implementación

- [x] ProgramaObserver creado
- [x] Observer registrado en AppServiceProvider
- [x] Auto-creación de primera fase al crear programa
- [x] Asignación automática del creador como responsable
- [x] Nombre cambiado de "Mis Fases" a "Proceso"
- [x] Título actualizado en página
- [x] Descripción actualizada en vista
- [x] Documentación completa

---

## 🧪 Cómo Probarlo

### Prueba Completa:

```bash
# 1. Asegúrate de tener fases configuradas
php artisan tinker
>>> Fase::orderBy('orden')->get(['id', 'nombre', 'orden']);

# 2. Login como usuario con rol "Ingeniería"

# 3. Ir a /admin/programas → "Nuevo Programa"

# 4. Crear programa:
   - Cliente: Test Corp
   - Proyecto: Test Project
   - Nombre: Test-001
   - Descripción: Prueba de flujo automático

# 5. Guardar

# 6. Ir a /admin/proceso
   → Deberías ver automáticamente:
     "Test-001 | Ingeniería | Pendiente"

# 7. Probar flujo:
   - Click "Iniciar"
   - Click "Finalizar"
   - Click "Liberar Siguiente"

# 8. Si hay usuario con rol "Captura":
   → Recibirá notificación automáticamente
```

---

## 🎯 Beneficios del Flujo Automático

### Para Usuarios:
✅ **No necesitan crear avances manualmente**
✅ **Automáticamente ven su trabajo en "Proceso"**
✅ **Flujo más natural e intuitivo**
✅ **Menos pasos, más productividad**

### Para el Sistema:
✅ **Consistencia garantizada**
✅ **Menos errores humanos**
✅ **Trazabilidad desde el inicio**
✅ **Flujo predecible y auditable**

### Para Administradores:
✅ **Menos trabajo administrativo**
✅ **Sistema más autónomo**
✅ **Fácil de monitorear**
✅ **Escalable**

---

## 📊 Resumen Visual

```
┌─────────────────────────────────────────────────────────────┐
│                     FLUJO AUTOMÁTICO                        │
└─────────────────────────────────────────────────────────────┘

ANTES (Manual):
─────────────
1. Usuario crea Programa
2. Usuario navega a "Avances de Fase"
3. Usuario crea manualmente AvanceFase
4. Usuario se asigna como responsable
5. Usuario cambia estado a "En Progreso"
6. Usuario trabaja...
7. Usuario finaliza
8. Usuario crea siguiente AvanceFase para otro usuario
9. Usuario notifica manualmente al siguiente

   ❌ 9 pasos manuales
   ❌ Muchos errores posibles
   ❌ Ineficiente


AHORA (Automático):
──────────────────
1. Usuario crea Programa
   └─ 🤖 Sistema auto-crea primera fase y asigna usuario
2. Usuario ve en "Proceso" su fase pendiente
3. Usuario inicia → trabaja → finaliza
4. Usuario libera siguiente
   └─ 🤖 Sistema notifica automáticamente

   ✅ 4 pasos
   ✅ Automático
   ✅ Sin errores
   ✅ Eficiente
```

---

**Fecha:** Octubre 2025
**Versión:** 2.0.0
**Estado:** ✅ Implementado y Funcional
