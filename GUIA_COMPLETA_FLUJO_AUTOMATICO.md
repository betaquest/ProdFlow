# Guía Completa: Flujo Automático de Fases

## 📋 Resumen del Sistema

ProdFlow ahora cuenta con **creación automática** de avances de fase, eliminando la necesidad de crear manualmente cada fase para cada usuario. El sistema crea automáticamente el siguiente avance cuando se libera una fase.

---

## 🔄 Flujo Completo del Proceso

### 1️⃣ Ingeniería Crea un Programa

**Actor:** Usuario con rol `Ingeniería`

**Pasos:**
1. Ingeniería crea un Cliente
2. Crea un Proyecto asociado al Cliente
3. Crea un Programa asociado al Proyecto

**¿Qué sucede automáticamente?**
- El sistema **crea automáticamente** el primer `AvanceFase`
- Fase asignada: **Ingeniería** (la primera fase según orden)
- Responsable: El usuario que creó el programa
- Estado: `pending` (Pendiente)

**Código responsable:** `app/Observers/ProgramaObserver.php`

```php
public function created(Programa $programa): void
{
    $primeraFase = Fase::orderBy('orden', 'asc')->first();

    if ($primeraFase) {
        AvanceFase::create([
            'programa_id' => $programa->id,
            'fase_id' => $primeraFase->id,
            'responsable_id' => Auth::id(), // Usuario que creó el programa
            'estado' => 'pending',
            'activo' => true,
        ]);
    }
}
```

---

### 2️⃣ Ingeniería Trabaja su Fase

**Actor:** Usuario con rol `Ingeniería`

**Ubicación:** Menú → **Proceso**

**Pasos:**
1. Ve su programa asignado en estado `Pendiente`
2. Hace clic en **"Iniciar"**
   - Estado cambia a `En Progreso`
   - Se registra `fecha_inicio`
3. Realiza su trabajo
4. Hace clic en **"Finalizar"**
   - Puede agregar notas finales (opcional)
   - Estado cambia a `Finalizado`
   - Se registra `fecha_fin`

---

### 3️⃣ Ingeniería Libera la Siguiente Fase

**Actor:** Usuario con rol `Ingeniería`

**Acción:** Hace clic en **"Liberar Siguiente"**

**¿Qué sucede automáticamente?**

1. **Verifica si existe siguiente fase**
   - Si no hay siguiente fase → Muestra mensaje "Esta es la última fase del proceso"
   - Si existe siguiente fase → Continúa

2. **Crea automáticamente el siguiente AvanceFase** (si no existe)
   ```php
   $avanceExistente = AvanceFase::where('programa_id', $record->programa_id)
       ->where('fase_id', $siguienteFase->id)
       ->first();

   if (!$avanceExistente) {
       $rolNombre = $siguienteFase->nombre; // Ej: "Captura"
       $primerUsuarioRol = User::role($rolNombre)->first();

       AvanceFase::create([
           'programa_id' => $record->programa_id,
           'fase_id' => $siguienteFase->id,
           'responsable_id' => $primerUsuarioRol?->id, // Primer usuario con el rol
           'estado' => 'pending',
           'activo' => true,
       ]);
   }
   ```

3. **Notifica a usuarios responsables**
   - Busca todos los usuarios con el rol de la siguiente fase
   - Si no hay usuarios con ese rol → Notifica a Administradores
   - Envía notificación por email y notificación in-app

4. **Muestra confirmación**
   - "Fase liberada exitosamente"
   - "Se ha notificado a los usuarios de la fase: Captura. El avance ha sido creado automáticamente."

**Código responsable:**
- `app/Filament/Pages/MisFases.php` (líneas 166-230)
- `app/Filament/Resources/AvanceFaseResource.php` (líneas 133-193)

---

### 4️⃣ Captura Recibe y Trabaja su Fase

**Actor:** Usuario con rol `Captura`

**¿Qué ve Captura?**

1. **Recibe notificación** (email + campana de notificaciones)
   - "Nueva fase liberada: Captura"
   - Detalles del programa

2. **Ve el programa en su vista "Proceso"**
   - El sistema ya creó automáticamente su `AvanceFase`
   - Estado: `Pendiente`
   - Responsable: Primer usuario con rol Captura (o el asignado)

3. **Trabaja su fase** (mismos pasos que Ingeniería)
   - Iniciar → En Progreso
   - Finalizar → Completado
   - Liberar Siguiente → Automáticamente crea avance para Liberación

---

### 5️⃣ Liberación y Fases Siguientes

El proceso continúa automáticamente:

- **Captura** libera → Se crea avance para **Liberación**
- **Liberación** libera → Se crea avance para **Ejecución Planta**
- **Ejecución Planta** libera → Y así sucesivamente...

Cada liberación:
- ✅ Crea automáticamente el siguiente avance
- ✅ Asigna al primer usuario del rol correspondiente
- ✅ Notifica a todos los usuarios con ese rol
- ✅ Mantiene trazabilidad completa

---

## 👥 Cómo Crear Usuarios para Cada Fase

**Actor:** Usuario con rol `Administrador`

### Ejemplo: Crear un usuario de Captura

1. Ir a **Menú → Usuarios**
2. Clic en **"Nuevo Usuario"**
3. Completar datos:
   - **Nombre:** Juan Pérez
   - **Email:** juan.perez@empresa.com
   - **Contraseña:** (generar segura)
4. **Asignar rol:** Seleccionar **"Captura"**
5. Guardar

**Resultado:**
- Este usuario ahora recibirá automáticamente los avances cuando Ingeniería libere fases
- Verá sus programas asignados en **"Proceso"**

### Roles Típicos del Sistema

| Rol | Descripción |
|-----|-------------|
| **Administrador** | Acceso completo, puede crear usuarios, gestionar fases y dashboards |
| **Ingeniería** | Crea programas, trabaja la primera fase |
| **Captura** | Recibe programas de Ingeniería, captura datos |
| **Liberación** | Libera programas para ejecución |
| **Ejecución Planta** | Ejecuta en planta |
| (otros según tu flujo) | Define según tus fases configuradas |

---

## 🔍 Verificar que Todo Funciona

### Test del Flujo Completo

1. **Como Administrador:**
   - Verificar que existen fases en orden correcto (Menú → Configuración → Fases)
   - Crear usuario de Ingeniería
   - Crear usuario de Captura

2. **Como Ingeniería:**
   - Crear Cliente, Proyecto, Programa
   - Ir a **"Proceso"** → Debe aparecer automáticamente el programa
   - Iniciar → Finalizar → Liberar Siguiente

3. **Como Captura:**
   - Revisar notificaciones (campana)
   - Ir a **"Proceso"** → Debe aparecer el programa recién liberado
   - Estado: Pendiente
   - Trabajar la fase

### Qué Verificar en Base de Datos

```sql
-- Ver todos los avances de un programa
SELECT
    af.id,
    f.nombre as fase,
    u.name as responsable,
    af.estado,
    af.fecha_inicio,
    af.fecha_fin
FROM avance_fases af
JOIN fases f ON af.fase_id = f.id
LEFT JOIN users u ON af.responsable_id = u.id
WHERE af.programa_id = 1
ORDER BY f.orden;
```

**Deberías ver:**
- Primera fila: Ingeniería (Finalizado)
- Segunda fila: Captura (Pendiente o En Progreso)

---

## 🛠️ Troubleshooting

### ❌ Problema: Captura no ve el programa después de liberación

**Posibles causas:**

1. **No existe un usuario con rol "Captura"**
   - Verificar: Menú → Usuarios → Filtrar por rol Captura
   - Solución: Crear al menos un usuario con rol Captura

2. **El nombre del rol no coincide con el nombre de la fase**
   - Verificar: Menú → Configuración → Fases
   - La fase debe llamarse exactamente "Captura" (igual que el rol)
   - Solución: Renombrar la fase o el rol para que coincidan

3. **No se ejecutó la auto-creación**
   - Verificar logs: `storage/logs/laravel.log`
   - Limpiar caché: `php artisan optimize:clear`

### ❌ Problema: No se envían notificaciones

**Verificar configuración de email:**

```bash
# En .env
MAIL_MAILER=smtp
MAIL_HOST=tu-smtp.com
MAIL_PORT=587
MAIL_USERNAME=tu-email
MAIL_PASSWORD=tu-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tuempresa.com
```

**Probar envío:**
```bash
php artisan tinker
>>> \Illuminate\Support\Facades\Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

### ❌ Problema: No aparece el botón "Liberar Siguiente"

**Causas:**
- El estado no es `done` (Finalizado)
- Solución: Primero Iniciar → luego Finalizar → luego aparece Liberar Siguiente

---

## 📊 Vista Técnica: Flujo de Datos

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Ingeniería Crea Programa                                │
│    ↓                                                        │
│ ProgramaObserver::created()                                 │
│    ↓                                                        │
│ Crea AvanceFase (Ingeniería, pending, responsable=creador) │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. Ingeniería Trabaja                                       │
│    - Iniciar (pending → progress)                           │
│    - Finalizar (progress → done)                            │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. Ingeniería Libera Siguiente                              │
│    ↓                                                        │
│ Action "liberar_siguiente"                                  │
│    ↓                                                        │
│ ¿Existe siguiente fase? → Sí                                │
│    ↓                                                        │
│ ¿Existe AvanceFase para esa fase? → No                      │
│    ↓                                                        │
│ Crear AvanceFase (Captura, pending, responsable=1er user)   │
│    ↓                                                        │
│ Notificar usuarios con rol "Captura"                        │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. Captura Ve el Programa en "Proceso"                      │
│    - Ya existe su AvanceFase                                │
│    - Estado: pending                                        │
│    - Puede Iniciar → Finalizar → Liberar Siguiente          │
└─────────────────────────────────────────────────────────────┘
                          ↓
                   (Ciclo continúa)
```

---

## 🎯 Beneficios del Sistema Automático

✅ **Eliminación de trabajo manual:** No es necesario crear manualmente cada avance

✅ **Cero configuración por programa:** Solo se crea el programa, todo lo demás es automático

✅ **Asignación inteligente:** Asigna automáticamente al primer usuario del rol correspondiente

✅ **Notificaciones automáticas:** Todos los usuarios del rol reciben notificación

✅ **Trazabilidad completa:** Cada paso queda registrado con fechas y responsables

✅ **Flujo continuo:** Cada fase liberada activa automáticamente la siguiente

---

## 📝 Notas Importantes

1. **Nombres de Roles y Fases deben coincidir**
   - Si la fase se llama "Captura", el rol debe llamarse "Captura"
   - Si la fase se llama "Ejecución Planta", el rol debe ser "Ejecución Planta"

2. **Al menos un usuario por rol**
   - Para que funcione la asignación automática, debe existir al menos un usuario con el rol correspondiente
   - Si no existe, el campo `responsable_id` será `null` (Administrador puede asignarlo manualmente después)

3. **Orden de las Fases**
   - Las fases deben tener el campo `orden` correctamente configurado
   - El sistema usa este orden para determinar cuál es la "siguiente fase"

4. **Cache**
   - Después de cambios importantes, ejecutar: `php artisan optimize:clear`

---

## 🚀 Próximos Pasos Recomendados

1. **Capacitar a los usuarios** en el uso de "Proceso"
2. **Crear documentación visual** con capturas de pantalla
3. **Definir SLAs** para cada fase (tiempo máximo de respuesta)
4. **Configurar recordatorios automáticos** para fases pendientes
5. **Implementar dashboard de seguimiento** para Administradores

---

**Fecha de creación:** {{ date('Y-m-d') }}
**Versión:** 1.0 - Sistema con Auto-creación de Fases
