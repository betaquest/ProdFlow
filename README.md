# 🏭 ProdFlow - Sistema de Gestión de Producción

Sistema integral de gestión de proyectos y fases de producción para empresas de manufactura, desarrollado con Laravel 12, Filament 3 y Livewire 3.

## 📋 Descripción

ProdFlow es un sistema robusto que permite gestionar clientes, proyectos, programas y fases de producción con un control secuencial, notificaciones automáticas, auditoría completa y sistema de roles y permisos granular.

## ✨ Características Principales

### 🔐 Sistema de Seguridad
- **Sistema de Roles y Permisos** (Spatie Laravel Permission)
- **Políticas de Autorización** para cada módulo
- **Control de Acceso Basado en Roles** (RBAC)
- **Auditoría Completa** con Spatie Activity Log

### 📊 Gestión de Datos
- **Gestión de Clientes** con información completa (RFC, contacto, teléfono)
- **Proyectos** vinculados a clientes
- **Programas** asociados a proyectos
- **Fases Secuenciales** con control de orden y progreso

### 🔄 Sistema de Fases Secuenciales
- **Orden Obligatorio**: Las fases siguen un flujo secuencial configurable
- **Validación de Progreso**: No se puede avanzar sin completar la fase anterior
- **Liberación Automática**: Botón para liberar la siguiente fase al completar
- **Notificaciones en Tiempo Real**: Alertas automáticas a usuarios del siguiente rol

### 🔔 Sistema de Notificaciones
- **Notificaciones Multicanal** (Email + Base de datos)
- **Alertas de Cambio de Estado**
- **Notificaciones de Liberación de Fases**
- **Panel de Notificaciones** integrado en Filament

### 📈 Dashboard y Reportes
- **Dashboard Interactivo** con métricas en tiempo real
- **Widgets Estadísticos** (clientes activos, proyectos, programas, progreso global)
- **Gráficos de Dona** para visualización de estados
- **Exportación a Excel/PDF** con Maatwebsite Excel

### 🔍 Auditoría y Trazabilidad
- **Registro de Actividades** en todos los modelos críticos
- **Historial de Cambios** (quién, cuándo, qué cambió)
- **Log de Eventos** para compliance y debugging

## 🛠️ Stack Tecnológico

### Backend
- **Framework**: Laravel 12
- **PHP**: 8.2+
- **Base de Datos**: SQLite (configurable a MySQL/PostgreSQL)

### Frontend
- **Admin Panel**: Filament 3.3
- **Framework Reactivo**: Livewire 3.6
- **CSS**: Tailwind CSS 4.1
- **Build Tool**: Vite 7

### Paquetes Principales
- `spatie/laravel-permission`: Sistema de roles y permisos
- `spatie/laravel-activitylog`: Auditoría de cambios
- `maatwebsite/excel`: Exportación de datos
- `laravel/sanctum`: Autenticación API

## 📦 Requisitos del Sistema

- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL >= 8.0 / PostgreSQL >= 13 / SQLite
- Extensión PHP: `zip`, `gd`, `mbstring`, `xml`, `curl`

## 🚀 Instalación

### 1. Clonar el Repositorio
```bash
git clone <repository-url>
cd ProdFlow
```

### 2. Instalar Dependencias PHP
```bash
composer install
```

### 3. Instalar Dependencias JavaScript
```bash
npm install
```

### 4. Configurar Variables de Entorno
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configurar Base de Datos
Edita el archivo `.env`:

**Para SQLite (por defecto):**
```env
DB_CONNECTION=sqlite
```

**Para MySQL:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prodflow
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Ejecutar Migraciones
```bash
php artisan migrate
```

### 7. Ejecutar Seeders (Roles y Permisos)
```bash
php artisan db:seed --class=RolePermissionSeeder
```

### 8. Crear Usuario Administrador
```bash
php artisan make:filament-user
```
Ingresa email, nombre y contraseña. Luego asigna el rol Administrador:
```bash
php artisan tinker
>>> $user = User::first();
>>> $user->assignRole('Administrador');
```

### 9. Compilar Assets
```bash
npm run build
```

### 10. Iniciar Servidor de Desarrollo
```bash
php artisan serve
```

Accede al panel en: `http://localhost:8000/admin`

## 🔧 Configuración Adicional

### Configurar Email (Opcional)
Para habilitar notificaciones por email, edita `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@prodflow.com
MAIL_FROM_NAME="ProdFlow"
```

### Configurar Colas (Recomendado)
```bash
php artisan queue:table
php artisan migrate
```

Ejecutar worker de colas:
```bash
php artisan queue:work
```

### Optimización
```bash
php artisan filament:optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 👥 Sistema de Roles y Permisos

### Roles Disponibles

#### Administrador
- ✅ Acceso total al sistema
- ✅ Gestión de usuarios, roles y permisos
- ✅ Clientes: Ver, Crear, Editar, Eliminar
- ✅ Proyectos: Ver, Crear, Editar, Eliminar
- ✅ Programas: Ver, Crear, Editar, Eliminar
- ✅ Fases: Ver, Editar

#### Ingeniería
- ✅ Clientes: Ver, Crear, Editar
- ✅ Proyectos: Ver, Crear, Editar, Eliminar
- ✅ Programas: Ver

#### Captura
- ✅ Programas: Ver, Crear, Editar

#### Roles Operativos (Corte, Ensamblado, Instalación, Finalizado)
- ✅ Dashboards: Ver
- ✅ Fases: Ver

### Permisos Implementados

| Módulo | Permisos |
|--------|----------|
| Clientes | `clientes.ver`, `clientes.crear`, `clientes.editar`, `clientes.eliminar` |
| Proyectos | `proyectos.ver`, `proyectos.crear`, `proyectos.editar`, `proyectos.eliminar` |
| Programas | `programas.ver`, `programas.crear`, `programas.editar`, `programas.eliminar` |
| Fases | `fases.ver`, `fases.editar` |
| Dashboards | `dashboards.ver` |

## 📊 Estructura de Datos

### Modelos Principales

```
Cliente
├── Proyecto
│   ├── Programa
│   │   ├── AvanceFase
│   │   │   ├── Fase
│   │   │   └── User (Responsable)
```

### Relaciones
- Un **Cliente** tiene muchos **Proyectos**
- Un **Proyecto** tiene muchos **Programas**
- Un **Programa** tiene muchos **AvanceFase**
- Un **AvanceFase** pertenece a una **Fase** y un **Usuario** responsable
- Las **Fases** tienen orden secuencial

## 🔄 Flujo de Trabajo de Fases

### 1. Configurar Fases
1. Ir a `/admin/fases`
2. Crear o editar fases
3. Asignar orden secuencial (1, 2, 3...)
4. Activar "Requiere Aprobación" si aplica

### 2. Proceso de Trabajo
1. Usuario asignado completa su fase → Marca como "Finalizado"
2. Aparece botón **"Liberar Siguiente Fase"**
3. Click en el botón → Confirma liberación
4. Sistema notifica a usuarios del siguiente rol
5. Siguiente rol recibe email + notificación in-app
6. Pueden comenzar a trabajar en su fase

### 3. Validación Automática
- No se pueden saltar fases
- Cada etapa debe completarse antes de la siguiente
- Trazabilidad completa del proceso

## 📁 Estructura del Proyecto

```
ProdFlow/
├── app/
│   ├── Filament/
│   │   ├── Resources/      # Recursos de Filament CRUD
│   │   ├── Widgets/        # Widgets de Dashboard
│   │   └── Exports/        # Exportadores Excel
│   ├── Http/
│   │   └── Controllers/
│   ├── Livewire/           # Componentes Livewire
│   ├── Models/             # Modelos Eloquent
│   ├── Notifications/      # Notificaciones
│   ├── Observers/          # Observers de eventos
│   └── Policies/           # Políticas de autorización
├── database/
│   ├── migrations/         # Migraciones de BD
│   └── seeders/            # Seeders
├── resources/
│   ├── views/              # Vistas Blade/Livewire
│   └── css/                # Estilos
├── routes/
│   ├── web.php
│   └── console.php
└── public/
```

## 🎨 Características de la Interfaz

### Panel de Administración (Filament)
- **Dashboard Interactivo** con métricas en tiempo real
- **CRUD Completo** para todos los módulos
- **Búsqueda y Filtros** avanzados
- **Exportación** a Excel/PDF
- **Notificaciones** en tiempo real
- **Dark Mode** incluido

### Dashboard Público
- Vista de **Matriz de Programas x Fases**
- **Actualización Automática** configurable
- **Códigos de Color** por estado (Pendiente, En Progreso, Completado)
- **Responsivo** y optimizado para pantallas grandes

## 🧪 Testing

```bash
php artisan test
```

## 📝 Comandos Útiles

### Desarrollo
```bash
# Servidor de desarrollo + compilación de assets
composer dev

# O separados:
php artisan serve
npm run dev
```

### Base de Datos
```bash
# Resetear BD y seeders
php artisan migrate:fresh --seed

# Crear nueva migración
php artisan make:migration nombre_migracion

# Crear modelo con migración
php artisan make:model NombreModelo -m
```

### Filament
```bash
# Crear resource
php artisan make:filament-resource NombreModelo

# Crear widget
php artisan make:filament-widget NombreWidget

# Crear exporter
php artisan make:filament-exporter NombreModelo

# Optimizar Filament
php artisan filament:optimize
```

### Mantenimiento
```bash
# Limpiar cachés
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Limpiar optimización Filament
php artisan filament:optimize-clear
```

## 🔍 Troubleshooting

### No se envían notificaciones
**Solución:**
- Verificar configuración de email en `.env`
- Verificar que exista rol con nombre de fase
- Iniciar worker de colas: `php artisan queue:work`

### Errores de permisos
**Solución:**
```bash
php artisan db:seed --class=RolePermissionSeeder
php artisan cache:clear
```

### No aparecen fases en orden
**Solución:**
```bash
php artisan cache:clear
# Verificar campo 'orden' en tabla fases
```

### Error en compilación de assets
**Solución:**
```bash
rm -rf node_modules package-lock.json
npm install
npm run build
```

## 📚 Documentación Adicional

- [Laravel Documentation](https://laravel.com/docs)
- [Filament Documentation](https://filamentphp.com/docs)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [Spatie Permission](https://spatie.be/docs/laravel-permission)
- [Spatie Activity Log](https://spatie.be/docs/laravel-activitylog)
- [Laravel Excel](https://docs.laravel-excel.com)

### Documentación Interna

- [MEJORAS_IMPLEMENTADAS.md](MEJORAS_IMPLEMENTADAS.md) - Detalle de mejoras empresariales
- [SEGURIDAD_CONFIGURADA.md](SEGURIDAD_CONFIGURADA.md) - Configuración de seguridad y políticas
- [SISTEMA_FASES_SECUENCIALES.md](SISTEMA_FASES_SECUENCIALES.md) - Sistema de fases secuenciales
- [MEJORAS_TABLAS_NAVEGACION.md](MEJORAS_TABLAS_NAVEGACION.md) - Mejoras de navegación

## 🤝 Contribución

Este es un proyecto privado. Para contribuir:

1. Fork del repositorio
2. Crear branch de feature (`git checkout -b feature/AmazingFeature`)
3. Commit de cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push al branch (`git push origin feature/AmazingFeature`)
5. Abrir Pull Request

## 📄 Licencia

Este proyecto es privado y propietario.

## 👨‍💻 Autor

Desarrollado para sistema de gestión de producción empresarial.

## 📞 Soporte

Para soporte técnico, consultar la documentación interna o contactar al equipo de desarrollo.

---

**Versión:** 1.0.0
**Última Actualización:** Octubre 2025
**Estado:** ✅ Production Ready
