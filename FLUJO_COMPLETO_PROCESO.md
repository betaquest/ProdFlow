# 🔄 Flujo Completo del Proceso - Paso a Paso

## 📖 Descripción

Este documento explica **exactamente** qué pasa después de cada acción en el módulo "Proceso", desde que Ingeniería crea un programa hasta que finaliza todo el ciclo.

---

## 🎯 Flujo Completo con Ejemplo Real

### Escenario: Producción de un Lote

**Participantes:**
- **Juan** (Rol: Ingeniería)
- **María** (Rol: Captura)
- **Pedro** (Rol: Corte)
- **Ana** (Rol: Ensamblado)

**Configuración de Fases (orden):**
1. Ingeniería (orden 1)
2. Captura (orden 2)
3. Corte (orden 3)
4. Ensamblado (orden 4)
5. Finalizado (orden 5)

---

## 📋 PASO 1: Ingeniería Crea el Programa

### Acción de Juan (Ingeniería):

```
1. Login → /admin/programas
2. Click "Nuevo Programa"
3. Llena formulario:
   - Cliente: ACME Corp
   - Proyecto: Prod-2024
   - Nombre: Lote-A-001
   - Descripción: 150 piezas modelo X
4. Click "Crear"
```

### ✨ Lo que pasa AUTOMÁTICAMENTE:

```
🤖 Sistema ejecuta ProgramaObserver:

1. Detecta: Nuevo programa creado (ID: 123)
2. Busca: Primera fase (orden = 1) → "Ingeniería"
3. Crea automáticamente:

   AvanceFase {
     programa_id: 123
     fase_id: 1 (Ingeniería)
     responsable_id: 5 (Juan)
     estado: "pending"
     fecha_inicio: null
     fecha_fin: null
     activo: true
   }

✅ Juan es asignado automáticamente como responsable
```

---

## 📋 PASO 2: Juan Va a "Proceso"

### Lo que ve Juan:

```
Navega a: /admin/proceso

Tabla muestra:
┌─────────────────────────────────────────────────────────────┐
│ Cliente  │ Proyecto  │ Programa   │ Fase       │ Estado    │
├──────────┼───────────┼────────────┼────────────┼───────────┤
│ ACME     │ Prod-2024 │ Lote-A-001 │ Ingeniería │ 🕐 Pendiente│
│          │           │            │            │ [▶ Iniciar]│
└─────────────────────────────────────────────────────────────┘

Características de la vista:
✅ Sin filtro por defecto (ve TODOS sus estados)
✅ Orden: Más recientes primero (descendente por created_at)
✅ Solo ve botón "Iniciar" (no "Finalizar" porque no está iniciado)
```

---

## 📋 PASO 3: Juan Inicia la Fase

### Acción de Juan:

```
1. Click botón "▶ Iniciar"
2. Modal: "¿Deseas iniciar esta fase ahora?"
3. Click "Sí, iniciar"
```

### ✨ Lo que pasa:

```
Sistema actualiza registro:

UPDATE avance_fases SET
  estado = 'progress',
  fecha_inicio = '2025-10-08 10:30:00'
WHERE id = 456;

✅ Notificación: "Fase iniciada"
✅ Estado cambia a: ⚙️ En Progreso
✅ Se registra fecha/hora exacta de inicio
```

### Lo que ve Juan AHORA:

```
┌─────────────────────────────────────────────────────────────┐
│ ACME │ Prod-2024 │ Lote-A-001 │ Ingeniería │ ⚙️ En Progreso│
│      │           │            │            │ [✓ Finalizar] │
└─────────────────────────────────────────────────────────────┘

Cambios:
❌ Botón "Iniciar" desapareció
✅ Botón "Finalizar" ahora SÍ aparece (porque está en progreso)
```

---

## 📋 PASO 4: Juan Trabaja en la Fase

### Actividades de Juan:

```
Durante el día Juan:
- Revisa especificaciones
- Crea diseños
- Valida con cliente
- Prepara documentación

Estado permanece: ⚙️ En Progreso
```

### Opcional - Agregar Notas Durante el Trabajo:

```
Juan puede:
1. Click botón "✏️ Editar Notas"
2. Agrega: "Revisión con cliente completada, diseño aprobado"
3. Guardar

✅ Notas se actualizan sin cambiar estado
```

---

## 📋 PASO 5: Juan Finaliza la Fase

### Acción de Juan (al terminar su trabajo):

```
1. Click botón "✓ Finalizar"
2. Modal: "¿Estás seguro de marcar esta fase como finalizada?"
3. Formulario aparece:
   ┌────────────────────────────────────────┐
   │ Notas finales (opcional)               │
   │ ┌────────────────────────────────────┐ │
   │ │ "Diseño completado.                │ │
   │ │  150 piezas especificadas.         │ │
   │ │  Planos listos para producción."   │ │
   │ └────────────────────────────────────┘ │
   └────────────────────────────────────────┘
4. Click "Sí, finalizar"
```

### ✨ Lo que pasa:

```
Sistema actualiza registro:

UPDATE avance_fases SET
  estado = 'done',
  fecha_fin = '2025-10-08 16:45:00',
  notas = 'Diseño completado. 150 piezas especificadas...'
WHERE id = 456;

✅ Notificación: "¡Fase completada!"
✅ Estado cambia a: ✓ Finalizado
✅ Se registra fecha/hora exacta de finalización
✅ Notas quedan guardadas permanentemente
```

### Lo que ve Juan AHORA:

```
┌──────────────────────────────────────────────────────────────┐
│ ACME │ Prod-2024 │ Lote-A-001 │ Ingeniería │ ✓ Finalizado  │
│      │           │            │            │ [➡ Liberar Siguiente]│
└──────────────────────────────────────────────────────────────┘

Cambios:
❌ Botón "Finalizar" desapareció
✅ Botón "Liberar Siguiente" ahora SÍ aparece
✅ Fechas registradas: Inicio y Fin
✅ Notas guardadas
```

---

## 📋 PASO 6: Juan Libera la Siguiente Fase

### Acción de Juan:

```
1. Click botón "➡ Liberar Siguiente"
2. Modal: "¿Deseas liberar la siguiente fase del proceso? Los usuarios responsables serán notificados."
3. Click "Sí, liberar fase"
```

### ✨ Lo que pasa AUTOMÁTICAMENTE:

```
Sistema ejecuta:

1. Identifica fase actual: "Ingeniería" (orden 1)
2. Busca siguiente fase: orden > 1 → "Captura" (orden 2)
3. Busca usuarios con rol "Captura" → Encuentra a María
4. Envía notificación:

   📧 Email a María:
   ────────────────────────────────────────
   Asunto: Nueva Fase Liberada - Captura

   Hola María,

   La fase "Ingeniería" ha sido completada.
   Programa: Lote-A-001

   Ahora puedes trabajar en: "Captura"

   [Ver Programa]

   ¡Es tu turno de trabajar en esta fase!
   ────────────────────────────────────────

   🔔 Notificación in-app a María (campana en panel)

5. Muestra notificación a Juan:
   ✅ "Fase liberada exitosamente"
   ✅ "Se ha notificado a los usuarios de la fase: Captura"
```

### ❓ ¿Y ahora qué?

```
IMPORTANTE:
- Juan ya terminó su trabajo en esta fase
- Juan YA NO tiene que hacer nada más con Lote-A-001
- María (Captura) es la siguiente en el proceso
- María debe crear/recibir el avance de su fase
```

---

## 📋 PASO 7: María (Captura) Recibe y Continúa

### Lo que ve María:

```
1. Recibe email 📧
2. Ve notificación 🔔 en panel
3. Navega a: /admin/proceso
```

### ⚠️ IMPORTANTE - Creación de Siguiente Avance:

**Opción A - Manual (Actual):**
Un **Administrador** debe crear manualmente el avance de Captura:

```
Admin navega a: /admin/avance-fases
Click "Nuevo Avance"
Formulario:
  - Programa: Lote-A-001
  - Fase: Captura
  - Responsable: María
  - Estado: pending
Click "Crear"
```

**Opción B - Automática (Sugerencia Futura):**
Al liberar fase, sistema podría auto-crear siguiente avance:

```php
// En la acción "liberar_siguiente":
AvanceFase::create([
    'programa_id' => $record->programa_id,
    'fase_id' => $siguienteFase->id,
    'responsable_id' => null, // Admin asigna después
    'estado' => 'pending',
    'activo' => true,
]);
```

### Una vez creado el avance, María ve:

```
┌──────────────────────────────────────────────────────────────┐
│ ACME │ Prod-2024 │ Lote-A-001 │ Captura │ 🕐 Pendiente     │
│      │           │            │         │ [▶ Iniciar]      │
└──────────────────────────────────────────────────────────────┘
```

---

## 📋 PASO 8: María Repite el Ciclo

### María sigue el mismo flujo:

```
1. Click "Iniciar" → Estado: En Progreso
2. Trabaja en captura de datos
3. Click "Finalizar" → Agrega notas
4. Click "Liberar Siguiente" → Notifica a Pedro (Corte)
```

---

## 📋 PASO 9: Pedro (Corte) Continúa

```
Pedro recibe notificación
Admin crea su avance
Pedro: Inicia → Trabaja → Finaliza → Libera
Notifica a Ana (Ensamblado)
```

---

## 📋 PASO 10: Ana (Ensamblado) Continúa

```
Ana recibe notificación
Admin crea su avance
Ana: Inicia → Trabaja → Finaliza → Libera
Notifica a siguiente fase (Finalizado)
```

---

## 📋 PASO 11: Última Fase

```
Si es la última fase:
- Click "Liberar Siguiente"
- Sistema detecta: No hay siguiente fase
- Notificación: "Esta es la última fase del proceso"
- Programa completado ✅
```

---

## 🔄 Resumen del Flujo Completo

```
┌─────────────────────────────────────────────────────────────┐
│                    CICLO COMPLETO                           │
└─────────────────────────────────────────────────────────────┘

INGENIERÍA (Juan):
  Crear Programa → Auto-asignado → Proceso
  ↓
  Iniciar → Trabajar → Finalizar → Liberar
  ↓
  📧 Notificación a Captura

CAPTURA (María):
  Recibe notificación → Admin crea avance → Proceso
  ↓
  Iniciar → Trabajar → Finalizar → Liberar
  ↓
  📧 Notificación a Corte

CORTE (Pedro):
  Recibe notificación → Admin crea avance → Proceso
  ↓
  Iniciar → Trabajar → Finalizar → Liberar
  ↓
  📧 Notificación a Ensamblado

ENSAMBLADO (Ana):
  Recibe notificación → Admin crea avance → Proceso
  ↓
  Iniciar → Trabajar → Finalizar → Liberar
  ↓
  📧 Notificación a Finalizado (o ninguna si es última)

FINALIZADO:
  ✅ Programa completado
```

---

## 🎯 Estados y Botones

### Matriz de Estados vs Botones Visibles:

| Estado | Botón Iniciar | Botón Finalizar | Botón Liberar | Botón Editar Notas |
|--------|---------------|-----------------|---------------|-------------------|
| **Pendiente** | ✅ Visible | ❌ Oculto | ❌ Oculto | ✅ Visible |
| **En Progreso** | ❌ Oculto | ✅ Visible | ❌ Oculto | ✅ Visible |
| **Finalizado** | ❌ Oculto | ❌ Oculto | ✅ Visible | ✅ Visible |

### Lógica de Visibilidad:

```php
// Iniciar: Solo si está pendiente
->visible(fn ($record) => $record->estado === 'pending')

// Finalizar: Solo si está en progreso
->visible(fn ($record) => $record->estado === 'progress')

// Liberar: Solo si está finalizado
->visible(fn ($record) => $record->estado === 'done')

// Editar Notas: Siempre visible
->visible(true)
```

---

## ✅ Ajustes Implementados

### 1. **Sin Filtro por Defecto**
Antes:
```php
->default('progress')  // Solo mostraba "En Progreso"
```

Ahora:
```php
// Sin default
// Muestra TODOS los estados (Pendiente, En Progreso, Finalizado)
```

### 2. **Orden Descendente**
Antes:
```php
->orderBy('fecha_inicio', 'desc')  // Ordenaba por fecha inicio
```

Ahora:
```php
->defaultSort('created_at', 'desc')  // Más recientes primero
```

### 3. **Lógica de Botón Finalizar**
Antes:
```php
->visible(fn ($record) => in_array($record->estado, ['pending', 'progress']))
// Permitía finalizar sin iniciar ❌
```

Ahora:
```php
->visible(fn ($record) => $record->estado === 'progress')
// Solo finaliza si ya está iniciado ✅
```

---

## 💡 Preguntas Frecuentes

### ¿Qué pasa si cierro sesión durante una fase?
- El estado se mantiene
- Puedes volver después y continuar
- Fecha de inicio ya está registrada

### ¿Puedo cambiar de "Finalizado" a "En Progreso"?
- No desde "Proceso" (solo avanza)
- Administrador puede editar en "Avances de Fase"

### ¿Qué pasa si libero sin que exista siguiente fase?
- Sistema detecta automáticamente
- Notificación: "Esta es la última fase del proceso"
- No envía emails

### ¿Qué pasa si no existe rol de la siguiente fase?
- Sistema busca rol con nombre de fase
- Si no existe, notifica a Administradores
- Fallback automático

### ¿Puedo ver fases de programas donde NO soy responsable?
- No en "Proceso" (solo tus fases)
- Administradores ven todo en "Avances de Fase"

### ¿Las fechas se pueden modificar?
- No desde "Proceso"
- Administrador puede editar en "Avances de Fase"
- Se registran automáticamente al Iniciar/Finalizar

---

## 🚀 Mejora Futura Sugerida

### Auto-creación de Siguiente Avance

Al liberar fase, crear automáticamente el siguiente avance:

```php
// En MisFases.php, acción "liberar_siguiente":
$siguienteFase = $faseActual->siguienteFase();

if ($siguienteFase) {
    // Crear siguiente avance automáticamente
    AvanceFase::create([
        'programa_id' => $record->programa_id,
        'fase_id' => $siguienteFase->id,
        'responsable_id' => null, // Sin asignar aún
        'estado' => 'pending',
        'activo' => true,
    ]);

    // Admin puede asignar responsable después
}
```

**Ventaja:**
- Siguiente usuario ya ve su fase en "Proceso"
- Solo falta que Admin asigne responsable

---

**Fecha:** Octubre 2025
**Versión:** 2.0.0
**Estado:** ✅ Documentado
