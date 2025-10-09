# 🔒 Seguridad Adicional - Fases y Dashboards

## ✅ Implementación Completada

Se ha restringido el acceso a **Fases** y **Dashboards** exclusivamente para **Administradores**.

---

## 📋 Nuevas Políticas Creadas

### 4. **FasePolicy**
**Archivo:** `app/Policies/FasePolicy.php`

**Restricción:** Solo usuarios con rol `Administrador` pueden:
- ✅ Ver lista de fases
- ✅ Ver detalles de una fase
- ✅ Crear nuevas fases
- ✅ Editar fases (nombre, orden, requiere_aprobacion)
- ✅ Eliminar fases
- ✅ Restaurar fases eliminadas
- ✅ Eliminar permanentemente fases

**Razón:** Las fases son la **estructura base** del sistema de producción. Solo administradores deben poder modificar el flujo de trabajo.

---

### 5. **DashboardPolicy**
**Archivo:** `app/Policies/DashboardPolicy.php`

**Restricción:** Solo usuarios con rol `Administrador` pueden:
- ✅ Ver lista de dashboards
- ✅ Ver detalles de un dashboard
- ✅ Crear nuevos dashboards
- ✅ Editar dashboards (nombre, slug, criterios, tiempo de actualización)
- ✅ Eliminar dashboards
- ✅ Restaurar dashboards eliminados
- ✅ Eliminar permanentemente dashboards

**Razón:** Los dashboards son configuraciones de visualización. Solo administradores deben poder crear y gestionar vistas públicas.

---

## 🎯 Resources Protegidos

### FaseResource
**Archivo:** `app/Filament/Resources/FaseResource.php`

```php
// Restringir acceso solo a Administradores
public static function canViewAny(): bool
{
    return auth()->user()?->hasRole('Administrador') ?? false;
}
```

**Resultado:**
- ❌ Usuarios sin rol Administrador **NO VEN** el menú "Fases"
- ❌ Usuarios sin rol Administrador **NO PUEDEN ACCEDER** a `/admin/fases`
- ✅ Solo Administradores ven y gestionan fases

---

### DashboardResource
**Archivo:** `app/Filament/Resources/DashboardResource.php`

```php
// Restringir acceso solo a Administradores
public static function canViewAny(): bool
{
    return auth()->user()?->hasRole('Administrador') ?? false;
}
```

**Resultado:**
- ❌ Usuarios sin rol Administrador **NO VEN** el menú "Dashboards"
- ❌ Usuarios sin rol Administrador **NO PUEDEN ACCEDER** a `/admin/dashboards`
- ✅ Solo Administradores crean y configuran dashboards

**Nota Importante:** Los **dashboards públicos** (ej. `/dashboards/produccion`) siguen siendo **accesibles para todos** sin login. Solo la **gestión** está restringida.

---

## 🔐 Cómo Funciona la Seguridad

### Capa 1: Visibilidad en Navegación
El método `canViewAny()` oculta el menú del panel de navegación.

```php
// Si el usuario NO es Administrador:
- No ve "Fases" en el menú
- No ve "Dashboards" en el menú
```

### Capa 2: Políticas de Autorización
Las políticas validan cada acción antes de permitirla.

```php
// Ejemplo: Intentar editar una fase
if (!$user->hasRole('Administrador')) {
    // Lanza error 403 Forbidden
    abort(403, 'No tienes permiso para realizar esta acción');
}
```

### Capa 3: Protección de URLs
Aunque un usuario intente acceder directamente a la URL:
- `/admin/fases` → ❌ Bloqueado si no es Administrador
- `/admin/dashboards` → ❌ Bloqueado si no es Administrador

---

## 👥 Permisos Actualizados por Rol

### Administrador
- ✅ **Control Total del Sistema**
- ✅ Gestión de Usuarios
- ✅ Gestión de Roles
- ✅ Gestión de Permisos
- ✅ **Gestión de Fases** (crear, editar, eliminar, reordenar)
- ✅ **Gestión de Dashboards** (crear, configurar, eliminar)
- ✅ Clientes: Ver, Crear, Editar, Eliminar
- ✅ Proyectos: Ver, Crear, Editar, Eliminar
- ✅ Programas: Ver, Crear, Editar, Eliminar
- ✅ Avances de Fase: Ver, Editar
- ✅ Proceso: Ver su propio proceso

### Ingeniería
- ❌ **NO puede gestionar Fases**
- ❌ **NO puede gestionar Dashboards**
- ❌ NO puede gestionar Usuarios
- ❌ NO puede gestionar Roles
- ❌ NO puede gestionar Permisos
- ✅ Clientes: Ver, Crear, Editar
- ✅ Proyectos: Ver, Crear, Editar, Eliminar
- ✅ Programas: Ver
- ✅ Proceso: Ver y gestionar su propio proceso

### Captura
- ❌ **NO puede gestionar Fases**
- ❌ **NO puede gestionar Dashboards**
- ❌ NO puede gestionar Usuarios
- ❌ NO puede gestionar Roles
- ❌ NO puede gestionar Permisos
- ✅ Programas: Ver, Crear, Editar
- ✅ Proceso: Ver y gestionar su propio proceso

### Roles Operativos (Corte, Ensamblado, Instalación, Finalizado)
- ❌ **NO pueden gestionar Fases**
- ❌ **NO pueden gestionar Dashboards** (pero SÍ ven dashboards públicos)
- ❌ NO pueden gestionar Usuarios
- ❌ NO pueden gestionar Roles
- ❌ NO pueden gestionar Permisos
- ✅ **Dashboards Públicos**: Ver (sin login)
- ✅ Proceso: Ver y gestionar su propio proceso

---

## 🧪 Cómo Probar

### 1. Como Administrador:
```bash
# Iniciar sesión como administrador
# Ir a /admin
# Deberías ver en el menú:
- Usuarios ✅
- Roles ✅
- Permisos ✅
- Fases ✅ (en grupo "Producción")
- Dashboards ✅ (en grupo "Configuración")
```

### 2. Como Ingeniería:
```bash
# Iniciar sesión como usuario con rol Ingeniería
# Ir a /admin
# NO deberías ver en el menú:
- Usuarios ❌
- Roles ❌
- Permisos ❌
- Fases ❌
- Dashboards ❌

# SÍ deberías ver:
- Clientes ✅
- Proyectos ✅
- Programas ✅
- Proceso ✅
```

### 3. Intentar Acceso Directo:
```bash
# Como usuario Ingeniería, intentar:
/admin/fases
/admin/dashboards

# Resultado esperado: Error 403 Forbidden
```

### 4. Dashboards Públicos (Sin Login):
```bash
# Sin estar logueado, acceder a:
/dashboards/produccion

# Resultado esperado: ✅ Acceso permitido
# Los dashboards públicos NO requieren autenticación
```

---

## 📊 Matriz de Permisos Completa

| Recurso | Administrador | Ingeniería | Captura | Operativos |
|---------|---------------|------------|---------|------------|
| **Usuarios** | ✅ Total | ❌ | ❌ | ❌ |
| **Roles** | ✅ Total | ❌ | ❌ | ❌ |
| **Permisos** | ✅ Total | ❌ | ❌ | ❌ |
| **Fases** | ✅ Total | ❌ | ❌ | ❌ |
| **Dashboards** | ✅ Total | ❌ | ❌ | ❌ |
| **Clientes** | ✅ Total | ✅ Ver, Crear, Editar | ❌ | ❌ |
| **Proyectos** | ✅ Total | ✅ Ver, Crear, Editar, Eliminar | ❌ | ❌ |
| **Programas** | ✅ Total | ✅ Ver | ✅ Ver, Crear, Editar | ❌ |
| **Avances de Fase** | ✅ Total | ❌ | ❌ | ❌ |
| **Proceso** | ✅ Ver su trabajo | ✅ Ver su trabajo | ✅ Ver su trabajo | ✅ Ver su trabajo |
| **Dashboards Públicos** | ✅ Sin login | ✅ Sin login | ✅ Sin login | ✅ Sin login |

---

## 🛡️ Razones de Seguridad

### ¿Por qué solo Administradores?

#### **Fases**
Las fases definen la **estructura completa** del flujo de producción:
- Cambiar el orden puede romper el proceso
- Eliminar una fase puede dejar programas huérfanos
- Modificar `requiere_aprobacion` puede permitir saltos indebidos
- **Impacto crítico** en todo el sistema

#### **Dashboards**
Los dashboards son **vistas públicas** del sistema:
- Configuraciones incorrectas pueden exponer datos sensibles
- Criterios mal definidos pueden mostrar información errónea
- URLs públicas deben ser controladas
- **Impacto en visualización externa**

---

## 🔄 Flujo de Trabajo Actualizado

### Configuración Inicial (Solo Administrador):

```
1. Administrador crea Fases:
   - Ingeniería (orden 1)
   - Captura (orden 2)
   - Corte (orden 3)
   - Ensamblado (orden 4)
   - Instalación (orden 5)
   - Finalizado (orden 6)

2. Administrador crea Dashboards:
   - Dashboard "Producción" → slug: produccion
   - Dashboard "Calidad" → slug: calidad
   - Configura tiempo de actualización, criterios, etc.

3. Administrador crea Usuarios:
   - Asigna roles según responsabilidades
   - Configura permisos específicos si es necesario
```

### Operación Diaria (Todos los Usuarios):

```
1. Ingeniería:
   - Crea Clientes, Proyectos, Programas
   - Sistema auto-crea primera fase
   - Ve en "Proceso" su trabajo
   - Inicia → Finaliza → Libera

2. Captura (y otros roles):
   - Reciben notificación
   - Ven en "Proceso" su trabajo
   - Inician → Finalizan → Liberan

3. Operarios de producción:
   - Consultan dashboards públicos en pantallas
   - Ven estado actual sin login
   - Trabajan en sus fases cuando les toca
```

---

## ✅ Checklist de Seguridad

- [x] UserPolicy creada y funcional
- [x] RolePolicy creada y funcional
- [x] PermissionPolicy creada y funcional
- [x] **FasePolicy creada y funcional** ✅
- [x] **DashboardPolicy creada y funcional** ✅
- [x] UserResource protegido con `canViewAny()`
- [x] RoleResource protegido con `canViewAny()`
- [x] PermissionResource protegido con `canViewAny()`
- [x] **FaseResource protegido con `canViewAny()`** ✅
- [x] **DashboardResource protegido con `canViewAny()`** ✅
- [x] Seeder actualizado con permisos correctos
- [x] Sistema de auditoría activo
- [x] Documentación completa

---

## 🎯 Resultado Final

**Solo el Administrador puede:**
- Crear, editar y eliminar usuarios
- Asignar roles a usuarios
- Crear y modificar roles y permisos
- **Crear, editar, eliminar y reordenar fases**
- **Crear, configurar y eliminar dashboards**
- Ver el menú de "Configuración" completo
- Ver el grupo de "Producción" con todas sus opciones

**Otros roles:**
- Solo ven y acceden a los recursos según sus permisos específicos
- No pueden modificar la estructura del sistema (fases, dashboards)
- No pueden modificar la seguridad del sistema (usuarios, roles, permisos)
- Tienen acceso restringido y auditable a sus funciones específicas
- **Pueden ver dashboards públicos** sin restricciones

---

## 📝 Notas Importantes

### Dashboards Públicos vs Gestión de Dashboards

**Dashboards Públicos** (`/dashboards/{slug}`):
- ✅ Accesibles para **todos** (sin login)
- ✅ Ideales para pantallas de producción
- ✅ Solo lectura
- ✅ Actualización automática

**Gestión de Dashboards** (`/admin/dashboards`):
- ❌ Solo **Administradores**
- ✅ Crear, editar, eliminar
- ✅ Configurar criterios y tiempos
- ✅ Control total de visualización

### Fases y Proceso

**Gestión de Fases** (`/admin/fases`):
- ❌ Solo **Administradores**
- ✅ Definir estructura del flujo
- ✅ Reordenar secuencia
- ✅ Configurar validaciones

**Módulo "Proceso"** (`/admin/proceso`):
- ✅ **Todos los usuarios** (ven solo su trabajo)
- ✅ Gestionar su propio proceso
- ✅ Iniciar, finalizar, liberar fases
- ✅ Sin poder modificar la estructura

---

## 🚀 Mejores Prácticas

### 1. **No Eliminar Fases con Avances Activos**
Si una fase tiene avances asociados, eliminarla puede causar problemas. Laravel protegerá con foreign keys, pero es mejor:
```php
// En FasePolicy, agregar validación adicional:
public function delete(User $user, Fase $fase): bool
{
    if ($user->hasRole('Administrador') && $fase->avances()->count() === 0) {
        return true;
    }
    return false;
}
```

### 2. **Respaldar Configuración de Fases**
Antes de modificar fases:
```sql
-- Exportar estructura actual
SELECT * FROM fases ORDER BY orden;
```

### 3. **Probar Cambios en Dashboards**
Siempre verificar dashboards públicos después de modificar criterios:
```bash
# Abrir en navegador incógnito
/dashboards/produccion
```

### 4. **Auditar Cambios Críticos**
Todos los cambios en Fases y Dashboards quedan registrados si agregas `LogsActivity` a los modelos.

---

**Fecha de Configuración:** Octubre 2025
**Estado:** ✅ Implementado y Funcional
**Nivel de Seguridad:** Alto 🔒
