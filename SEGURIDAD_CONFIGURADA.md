# 🔒 Configuración de Seguridad - Control de Acceso

## ✅ Implementación Completada

Se ha configurado un sistema de seguridad robusto que restringe el acceso a Usuarios, Roles y Permisos **exclusivamente al Administrador**.

---

## 📋 Políticas Creadas

### 1. **UserPolicy**
**Archivo:** `app/Policies/UserPolicy.php`

**Restricción:** Solo usuarios con rol `Administrador` pueden:
- ✅ Ver lista de usuarios
- ✅ Ver detalles de un usuario
- ✅ Crear nuevos usuarios
- ✅ Editar usuarios
- ✅ Eliminar usuarios
- ✅ Restaurar usuarios eliminados
- ✅ Eliminar permanentemente usuarios

### 2. **RolePolicy**
**Archivo:** `app/Policies/RolePolicy.php`

**Restricción:** Solo usuarios con rol `Administrador` pueden:
- ✅ Ver lista de roles
- ✅ Ver detalles de un rol
- ✅ Crear nuevos roles
- ✅ Editar roles
- ✅ Eliminar roles
- ✅ Restaurar roles eliminados
- ✅ Eliminar permanentemente roles

### 3. **PermissionPolicy**
**Archivo:** `app/Policies/PermissionPolicy.php`

**Restricción:** Solo usuarios con rol `Administrador` pueden:
- ✅ Ver lista de permisos
- ✅ Ver detalles de un permiso
- ✅ Crear nuevos permisos
- ✅ Editar permisos
- ✅ Eliminar permisos
- ✅ Restaurar permisos eliminados
- ✅ Eliminar permanentemente permisos

---

## 🎯 Resources de Filament Protegidos

### UserResource
**Archivo:** `app/Filament/Resources/UserResource.php`

```php
public static function canViewAny(): bool
{
    return auth()->user()?->hasRole('Administrador') ?? false;
}
```
- ❌ Usuarios sin rol Administrador **NO VEN** el menú "Usuarios"
- ❌ Usuarios sin rol Administrador **NO PUEDEN ACCEDER** a la URL directamente

### RoleResource
**Archivo:** `app/Filament/Resources/RoleResource.php`

```php
public static function canViewAny(): bool
{
    return auth()->user()?->hasRole('Administrador') ?? false;
}
```
- ❌ Usuarios sin rol Administrador **NO VEN** el menú "Roles"
- ❌ Usuarios sin rol Administrador **NO PUEDEN ACCEDER** a la URL directamente

### PermissionResource
**Archivo:** `app/Filament/Resources/PermissionResource.php`

```php
public static function canViewAny(): bool
{
    return auth()->user()?->hasRole('Administrador') ?? false;
}
```
- ❌ Usuarios sin rol Administrador **NO VEN** el menú "Permisos"
- ❌ Usuarios sin rol Administrador **NO PUEDEN ACCEDER** a la URL directamente

---

## 🔐 Cómo Funciona la Seguridad

### Capa 1: Visibilidad en Navegación
El método `canViewAny()` oculta el menú del panel de navegación para usuarios no autorizados.

```php
// Si el usuario NO es Administrador:
- No ve "Usuarios" en el menú
- No ve "Roles" en el menú
- No ve "Permisos" en el menú
```

### Capa 2: Políticas de Autorización
Las políticas validan cada acción antes de permitirla.

```php
// Ejemplo: Intentar editar un usuario
if (!$user->hasRole('Administrador')) {
    // Lanza error 403 Forbidden
    abort(403, 'No tienes permiso para realizar esta acción');
}
```

### Capa 3: Protección de URLs
Aunque un usuario intente acceder directamente a la URL:
- `/admin/users` → ❌ Bloqueado si no es Administrador
- `/admin/roles` → ❌ Bloqueado si no es Administrador
- `/admin/permissions` → ❌ Bloqueado si no es Administrador

---

## 👥 Permisos por Rol (Actualizado)

### Administrador
- ✅ **Control Total del Sistema**
- ✅ Gestión de Usuarios
- ✅ Gestión de Roles
- ✅ Gestión de Permisos
- ✅ Clientes: Ver, Crear, Editar, Eliminar
- ✅ Proyectos: Ver, Crear, Editar, Eliminar
- ✅ Programas: Ver, Crear, Editar, Eliminar
- ✅ Fases: Ver, Editar
- ✅ Dashboards: Ver

### Ingeniería
- ❌ **NO puede gestionar Usuarios**
- ❌ **NO puede gestionar Roles**
- ❌ **NO puede gestionar Permisos**
- ✅ Clientes: Ver, Crear, Editar
- ✅ Proyectos: Ver, Crear, Editar, **Eliminar** (actualizado)
- ✅ Programas: Ver

### Captura
- ❌ **NO puede gestionar Usuarios**
- ❌ **NO puede gestionar Roles**
- ❌ **NO puede gestionar Permisos**
- ✅ Programas: Ver, Crear, Editar

### Roles Operativos (Corte, Ensamblado, Instalación, Finalizado)
- ❌ **NO puede gestionar Usuarios**
- ❌ **NO puede gestionar Roles**
- ❌ **NO puede gestionar Permisos**
- ✅ Dashboards: Ver
- ✅ Fases: Ver

---

## 🧪 Cómo Probar

### 1. Como Administrador:
```bash
# Iniciar sesión como administrador
# Ir a /admin
# Deberías ver en el menú "Configuración":
- Usuarios ✅
- Roles ✅
- Permisos ✅
```

### 2. Como Ingeniería:
```bash
# Iniciar sesión como usuario con rol Ingeniería
# Ir a /admin
# NO deberías ver en el menú:
- Usuarios ❌
- Roles ❌
- Permisos ❌
```

### 3. Intentar Acceso Directo:
```bash
# Como usuario Ingeniería, intentar:
/admin/users
/admin/roles
/admin/permissions

# Resultado esperado: Error 403 Forbidden
```

---

## 📊 Matriz de Permisos Actualizada

| Recurso | Administrador | Ingeniería | Captura | Operativos |
|---------|---------------|------------|---------|------------|
| **Usuarios** | ✅ Total | ❌ | ❌ | ❌ |
| **Roles** | ✅ Total | ❌ | ❌ | ❌ |
| **Permisos** | ✅ Total | ❌ | ❌ | ❌ |
| **Clientes** | ✅ Total | ✅ Ver, Crear, Editar | ❌ | ❌ |
| **Proyectos** | ✅ Total | ✅ Ver, Crear, Editar, Eliminar | ❌ | ❌ |
| **Programas** | ✅ Total | ✅ Ver | ✅ Ver, Crear, Editar | ❌ |
| **Fases** | ✅ Total | ❌ | ❌ | ✅ Ver |
| **Dashboards** | ✅ Total | ❌ | ❌ | ✅ Ver |

---

## 🔄 Actualizar Permisos

Si necesitas actualizar los permisos, ejecuta el seeder:

```bash
php artisan db:seed --class=RolePermissionSeeder
```

Esto aplicará:
- ✅ Todos los permisos al Administrador
- ✅ Permisos de Proyectos (incluyendo eliminar) a Ingeniería
- ✅ Permisos de Programas a Captura
- ✅ Permisos limitados a Roles Operativos

---

## 🛡️ Mejores Prácticas

### 1. **No Eliminar el Rol Administrador**
Siempre debe existir al menos un usuario con rol Administrador.

### 2. **Probar con Usuarios Reales**
Crea usuarios de prueba con diferentes roles y verifica que las restricciones funcionen.

### 3. **Auditoría de Cambios**
Gracias a Spatie Activity Log, todos los cambios en usuarios quedan registrados.

### 4. **Protección contra Auto-Bloqueo**
No permitas que el último Administrador se quite su propio rol o se desactive.

---

## ✅ Checklist de Seguridad

- [x] UserPolicy creada
- [x] RolePolicy creada
- [x] PermissionPolicy creada
- [x] UserResource protegido con `canViewAny()`
- [x] RoleResource protegido con `canViewAny()`
- [x] PermissionResource protegido con `canViewAny()`
- [x] Seeder actualizado con permisos de Ingeniería
- [x] Sistema de auditoría activo
- [x] Documentación completa

---

## 🎯 Resultado Final

**Solo el Administrador puede:**
- Crear, editar y eliminar usuarios
- Asignar roles a usuarios
- Crear y modificar roles
- Crear y modificar permisos
- Ver el menú de "Configuración" completo

**Otros roles:**
- Solo ven y acceden a los recursos según sus permisos específicos
- No pueden modificar la estructura de seguridad del sistema
- Tienen acceso restringido y auditable

---

**Fecha de Configuración:** 2025-10-05
**Estado:** ✅ Implementado y Funcional
**Nivel de Seguridad:** Alto 🔒
