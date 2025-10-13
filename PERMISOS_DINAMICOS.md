# Sistema Dinámico de Permisos y Roles

## Descripción

Este sistema permite gestionar permisos y roles de forma dinámica sin necesidad de modificar código. Los permisos se definen en un archivo de configuración y se sincronizan automáticamente con la base de datos.

## Arquitectura

### 1. Archivo de Configuración: `config/permissions.php`

Este archivo define:
- **Recursos**: Módulos del sistema (clientes, proyectos, programas, etc.)
- **Acciones**: Operaciones permitidas (ver, crear, editar, eliminar)
- **Roles**: Perfiles de usuario con sus permisos asignados

```php
'resources' => [
    'clientes' => [
        'actions' => ['ver', 'crear', 'editar', 'eliminar'],
        'label' => 'Clientes',
    ],
    // ... más recursos
],

'roles' => [
    'Administrador' => [
        'permissions' => '*', // Todos los permisos
        'label' => 'Administrador del Sistema',
    ],
    'Abastecimiento' => [
        'permissions' => [
            'programas.ver',
            'abastecimiento.ver',
            'abastecimiento.crear',
            'abastecimiento.editar',
        ],
        'label' => 'Abastecimiento',
    ],
    // ... más roles
],
```

### 2. Trait: `HasDynamicPermissions`

Un trait genérico que se usa en las Policies para verificar permisos automáticamente.

**Ejemplo de uso en una Policy:**

```php
<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;
use App\Traits\HasDynamicPermissions;

class ClientePolicy
{
    use HasDynamicPermissions;

    protected string $resource = 'clientes';
}
```

### 3. Comando Artisan: `permissions:sync`

Sincroniza permisos y roles desde el archivo de configuración a la base de datos.

```bash
php artisan permissions:sync
```

Este comando:
- Crea permisos basados en recursos y acciones
- Crea roles definidos en la configuración
- Asigna permisos a cada rol
- Limpia la caché de permisos

## Uso del Sistema

### Agregar un Nuevo Recurso

1. Edita `config/permissions.php`
2. Agrega el nuevo recurso:

```php
'resources' => [
    // ... recursos existentes
    'inventario' => [
        'actions' => ['ver', 'crear', 'editar', 'eliminar'],
        'label' => 'Inventario',
    ],
],
```

3. Sincroniza los permisos:

```bash
php artisan permissions:sync
```

4. Crea la Policy usando el trait:

```php
<?php

namespace App\Policies;

use App\Models\Inventario;
use App\Models\User;
use App\Traits\HasDynamicPermissions;

class InventarioPolicy
{
    use HasDynamicPermissions;

    protected string $resource = 'inventario';
}
```

### Crear un Nuevo Rol

1. Edita `config/permissions.php`
2. Agrega el nuevo rol:

```php
'roles' => [
    // ... roles existentes
    'Supervisor' => [
        'permissions' => [
            'clientes.ver',
            'proyectos.ver',
            'programas.ver',
            'dashboards.ver',
        ],
        'label' => 'Supervisor',
    ],
],
```

3. Sincroniza:

```bash
php artisan permissions:sync
```

### Modificar Permisos de un Rol

1. Edita `config/permissions.php`
2. Actualiza el array de permisos del rol:

```php
'Abastecimiento' => [
    'permissions' => [
        'programas.ver',
        'abastecimiento.ver',
        'abastecimiento.crear',
        'abastecimiento.editar',
        'abastecimiento.eliminar', // ← Nuevo permiso
        'dashboards.ver',
    ],
    'label' => 'Abastecimiento',
],
```

3. Sincroniza:

```bash
php artisan permissions:sync
```

## Ventajas del Sistema Dinámico

### ✅ Sin Hardcode
- Los permisos no están en el código PHP
- Todo se configura desde un archivo
- Fácil de mantener y actualizar

### ✅ Centralizado
- Un solo archivo de configuración
- Vista clara de todos los permisos
- Fácil auditoría

### ✅ Escalable
- Agregar nuevos recursos es simple
- Agregar nuevos roles es rápido
- No requiere modificar múltiples archivos

### ✅ Sincronización Automática
- Un comando sincroniza todo
- No hay riesgo de inconsistencias
- Fácil de integrar en deployment

### ✅ Reutilizable
- El trait se usa en todas las Policies
- Código DRY (Don't Repeat Yourself)
- Menos propenso a errores

## Integración en Deployment

Puedes agregar el comando de sincronización en tu proceso de deployment:

```bash
# En tu script de deployment
php artisan migrate --force
php artisan permissions:sync
php artisan optimize
```

## Testing

Para verificar que los permisos funcionan correctamente:

```bash
# Ver todos los permisos
php artisan tinker
>>> Spatie\Permission\Models\Permission::all()->pluck('name');

# Ver permisos de un rol
>>> Spatie\Permission\Models\Role::findByName('Abastecimiento')->permissions->pluck('name');

# Ver roles de un usuario
>>> App\Models\User::find(1)->roles->pluck('name');
```

## Rol de Abastecimiento

El rol de Abastecimiento ha sido creado con los siguientes permisos:

- `programas.ver` - Ver programas
- `abastecimiento.ver` - Ver módulo de abastecimiento
- `abastecimiento.crear` - Crear registros de abastecimiento
- `abastecimiento.editar` - Editar registros de abastecimiento
- `dashboards.ver` - Ver dashboards
- `fases.ver` - Ver fases

## Migración desde Sistema Hardcoded

Si tienes Policies con permisos hardcoded, puedes migrarlas fácilmente:

**Antes:**
```php
class ClientePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('clientes.ver') || $user->hasRole('Administrador');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('clientes.crear') || $user->hasRole('Administrador');
    }
    // ... más métodos
}
```

**Después:**
```php
class ClientePolicy
{
    use HasDynamicPermissions;

    protected string $resource = 'clientes';
}
```

## Soporte

Para agregar nuevas funcionalidades al sistema dinámico de permisos, edita:
- `config/permissions.php` - Configuración
- `app/Traits/HasDynamicPermissions.php` - Lógica del trait
- `app/Console/Commands/SyncPermissions.php` - Comando de sincronización
