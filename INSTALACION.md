    `   # 📦 Guía de Instalación y Despliegue - ProdFlow

## 📋 Tabla de Contenidos

1. [Requisitos del Sistema](#-requisitos-del-sistema)
2. [Instalación en Windows (Desarrollo)](#-instalación-en-windows-desarrollo)
3. [Instalación en Linux (Producción)](#-instalación-en-linux-producción)
4. [Configuración Inicial](#-configuración-inicial)
5. [Configuración de Base de Datos](#-configuración-de-base-de-datos)
6. [Configuración de Email](#-configuración-de-email)
7. [Despliegue en Producción](#-despliegue-en-producción)
8. [Troubleshooting](#-troubleshooting)

---

## 🔧 Requisitos del Sistema

### Requisitos Mínimos

| Componente            | Versión Mínima | Versión Recomendada |
| --------------------- | -------------- | ------------------- |
| PHP                   | 8.2+           | 8.3+                |
| Composer              | 2.5+           | 2.7+                |
| Node.js               | 18.x           | 20.x LTS            |
| NPM                   | 9.x            | 10.x                |
| MySQL / MariaDB       | 8.0+ / 10.6+   | 8.3+ / 11.0+        |
| SQL Server (opcional) | 2017+          | 2022+               |

### Extensiones de PHP Requeridas

```ini
php_openssl
php_pdo
php_mbstring
php_tokenizer
php_xml
php_ctype
php_json
php_bcmath
php_fileinfo
php_zip
php_curl
php_gd
php_intl
```

### Extensiones Adicionales (Opcional)

```ini
php_redis       # Para caché en producción
php_opcache     # Mejora rendimiento
php_pdo_sqlsrv  # Si usas SQL Server
```

---

## 🪟 Instalación en Windows (Desarrollo)

### Opción 1: Usando Laragon (Recomendado)

#### 1. Instalar Laragon

1. Descarga [Laragon Full](https://laragon.org/download/)
2. Instala en `C:\laragon`
3. Inicia Laragon

#### 2. Clonar el Proyecto

```bash
# Abre Laragon Terminal (Click derecho en Laragon > Terminal)
cd C:\laragon\www

# Clonar repositorio
git clone https://github.com/tu-usuario/ProdFlow.git
cd ProdFlow
```

#### 3. Instalar Dependencias

```bash
# Instalar dependencias de PHP
composer install

# Instalar dependencias de Node.js
npm install
```

#### 4. Configurar Entorno

```bash
# Copiar archivo de ejemplo
copy .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

#### 5. Configurar Base de Datos

Edita el archivo `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prodflow
DB_USERNAME=root
DB_PASSWORD=
```

#### 6. Crear Base de Datos

```bash
# Abrir MySQL desde Laragon
# Click derecho en Laragon > MySQL > MySQL

# O desde terminal
mysql -u root -e "CREATE DATABASE prodflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

#### 7. Ejecutar Migraciones

```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders (datos de prueba)
php artisan db:seed
```

#### 8. Compilar Assets (Opcional)

```bash
# Compilar CSS/JS para desarrollo
npm run dev

# O usar el CSS precompilado
# (Ya incluido en public/css/tailwind.css)
```

#### 9. Iniciar Servidor

```bash
# Laragon inicia automáticamente Apache
# Accede a: http://prodflow.test
# O http://localhost/ProdFlow
```

---

### Opción 2: Usando XAMPP

#### 1. Instalar XAMPP

1. Descarga [XAMPP](https://www.apachefriends.org/)
2. Instala en `C:\xampp`
3. Inicia Apache y MySQL

#### 2. Configurar Virtual Host (Opcional)

Edita `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/ProdFlow/public"
    ServerName prodflow.local
    <Directory "C:/xampp/htdocs/ProdFlow/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Edita `C:\Windows\System32\drivers\etc\hosts` (como administrador):

```
127.0.0.1 prodflow.local
```

#### 3. Seguir pasos 2-7 de Laragon

```bash
cd C:\xampp\htdocs
git clone https://github.com/tu-usuario/ProdFlow.git
cd ProdFlow
composer install
npm install
copy .env.example .env
php artisan key:generate
```

---

## 🐧 Instalación en Linux (Producción)

### Ubuntu/Debian 22.04+

#### 1. Actualizar Sistema

```bash
sudo apt update && sudo apt upgrade -y
```

#### 2. Instalar PHP 8.3

```bash
# Agregar repositorio de PHP
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Instalar PHP y extensiones
sudo apt install -y php8.3 php8.3-cli php8.3-fpm php8.3-mysql \
    php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip \
    php8.3-gd php8.3-intl php8.3-bcmath php8.3-redis \
    php8.3-opcache
```

#### 3. Instalar Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

#### 4. Instalar Node.js

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

#### 5. Instalar Nginx

```bash
sudo apt install -y nginx
```

#### 6. Instalar MySQL

```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

#### 7. Crear Base de Datos

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE prodflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'prodflow'@'localhost' IDENTIFIED BY 'tu_password_seguro';
GRANT ALL PRIVILEGES ON prodflow.* TO 'prodflow'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 8. Clonar Proyecto

```bash
cd /var/www
sudo git clone https://github.com/tu-usuario/ProdFlow.git
cd ProdFlow
```

#### 9. Configurar Permisos

```bash
sudo chown -R www-data:www-data /var/www/ProdFlow
sudo chmod -R 755 /var/www/ProdFlow
sudo chmod -R 775 /var/www/ProdFlow/storage
sudo chmod -R 775 /var/www/ProdFlow/bootstrap/cache
```

#### 10. Instalar Dependencias

```bash
composer install --no-dev --optimize-autoloader
npm install --production
```

#### 11. Configurar Entorno

```bash
cp .env.example .env
nano .env
```

Edita el archivo `.env`:

```env
APP_NAME="ProdFlow"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prodflow
DB_USERNAME=prodflow
DB_PASSWORD=tu_password_seguro

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-password-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tu-dominio.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### 12. Generar Clave y Migrar

```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan optimize
```

#### 13. Configurar Nginx

```bash
sudo nano /etc/nginx/sites-available/prodflow
```

Contenido:

```nginx
server {
    listen 80;
    server_name tu-dominio.com www.tu-dominio.com;
    root /var/www/ProdFlow/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### 14. Activar Sitio

```bash
sudo ln -s /etc/nginx/sites-available/prodflow /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### 15. Configurar SSL con Let's Encrypt (Recomendado)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d tu-dominio.com -d www.tu-dominio.com
```

---

## ⚙️ Configuración Inicial

### 1. Crear Usuario Administrador

```bash
php artisan tinker
```

```php
$user = App\Models\User::create([
    'name' => 'Administrador',
    'email' => 'admin@prodflow.com',
    'password' => bcrypt('password123'),
]);

$user->assignRole('Administrador');
exit;
```

### 2. Crear Roles Base

Los roles se crean automáticamente con el seeder, pero si necesitas crearlos manualmente:

```bash
php artisan tinker
```

```php
use Spatie\Permission\Models\Role;

Role::create(['name' => 'Administrador', 'guard_name' => 'web']);
Role::create(['name' => 'Ingeniería', 'guard_name' => 'web']);
Role::create(['name' => 'Captura', 'guard_name' => 'web']);
Role::create(['name' => 'Liberación', 'guard_name' => 'web']);
Role::create(['name' => 'Ejecución Planta', 'guard_name' => 'web']);
exit;
```

### 3. Crear Fases del Proceso

```bash
php artisan tinker
```

```php
use App\Models\Fase;

Fase::create(['nombre' => 'Ingeniería', 'descripcion' => 'Diseño y planificación', 'orden' => 1]);
Fase::create(['nombre' => 'Captura', 'descripcion' => 'Captura de datos', 'orden' => 2]);
Fase::create(['nombre' => 'Liberación', 'descripcion' => 'Liberación para producción', 'orden' => 3]);
Fase::create(['nombre' => 'Ejecución Planta', 'descripcion' => 'Ejecución en planta', 'orden' => 4]);
exit;
```

---

## 🗄️ Configuración de Base de Datos

### MySQL/MariaDB

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prodflow
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

### SQL Server

```env
DB_CONNECTION=sqlsrv
DB_HOST=localhost
DB_PORT=1433
DB_DATABASE=prodflow
DB_USERNAME=sa
DB_PASSWORD=tu_password
```

Instalar driver SQL Server para PHP (Linux):

```bash
curl https://packages.microsoft.com/keys/microsoft.asc | sudo apt-key add -
curl https://packages.microsoft.com/config/ubuntu/22.04/prod.list | sudo tee /etc/apt/sources.list.d/mssql-release.list
sudo apt update
sudo ACCEPT_EULA=Y apt install -y msodbcsql18 mssql-tools18
sudo apt install -y php8.3-sqlsrv php8.3-pdo-sqlsrv
```

---

## 📧 Configuración de Email

### Gmail

1. Habilita "Verificación en 2 pasos" en tu cuenta de Google
2. Genera una [Contraseña de aplicación](https://myaccount.google.com/apppasswords)
3. Configura `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-password-de-aplicacion
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tuempresa.com
MAIL_FROM_NAME="ProdFlow"
```

### SendGrid

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=tu-api-key-de-sendgrid
MAIL_ENCRYPTION=tls
```

### Mailgun

```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=tu-dominio.mailgun.org
MAILGUN_SECRET=tu-api-key
```

---

## 🚀 Despliegue en Producción

### Checklist de Producción

-   [ ] **APP_ENV=production** en `.env`
-   [ ] **APP_DEBUG=false** en `.env`
-   [ ] Configurar URL correcta en `.env`
-   [ ] Configurar base de datos de producción
-   [ ] Configurar email correctamente
-   [ ] Ejecutar `php artisan optimize`
-   [ ] Configurar caché (Redis recomendado)
-   [ ] Configurar backups automáticos
-   [ ] Configurar SSL/HTTPS
-   [ ] Configurar firewall
-   [ ] Configurar logs rotation

### Optimizar para Producción

```bash
# Optimizar autoload de Composer
composer install --no-dev --optimize-autoloader

# Cachear configuración
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas
php artisan view:cache

# Optimizar todo
php artisan optimize
```

### Configurar Cron Jobs

Edita crontab:

```bash
sudo crontab -e -u www-data
```

Agrega:

```cron
* * * * * cd /var/www/ProdFlow && php artisan schedule:run >> /dev/null 2>&1
```

### Configurar Supervisor (Para Queues)

```bash
sudo apt install -y supervisor
sudo nano /etc/supervisor/conf.d/prodflow-worker.conf
```

Contenido:

```ini
[program:prodflow-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ProdFlow/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/ProdFlow/storage/logs/worker.log
stopwaitsecs=3600
```

Recargar:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start prodflow-worker:*
```

---

## 🔧 Troubleshooting

### Error: "Class not found"

```bash
composer dump-autoload
php artisan clear-compiled
php artisan optimize:clear
```

### Error: "Permission denied" (Storage/Bootstrap)

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Error: "Vite manifest not found"

Ya está solucionado - usa el CSS precompilado:

```bash
# El archivo public/css/tailwind.css ya está incluido
```

### Error: "SQLSTATE[HY000] [2002] Connection refused"

```bash
# Verificar que MySQL esté corriendo
sudo systemctl status mysql

# Verificar credenciales en .env
```

### Error: "500 Internal Server Error"

```bash
# Ver logs
tail -f storage/logs/laravel.log

# Verificar permisos
sudo chmod -R 775 storage bootstrap/cache

# Limpiar caché
php artisan optimize:clear
```

### Actualizar Aplicación

```bash
cd /var/www/ProdFlow
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
sudo systemctl restart php8.3-fpm
sudo systemctl reload nginx
```

---

## 📊 Monitoreo y Logs

### Ver Logs en Tiempo Real

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Nginx access logs
sudo tail -f /var/log/nginx/access.log

# Nginx error logs
sudo tail -f /var/log/nginx/error.log

# PHP-FPM logs
sudo tail -f /var/log/php8.3-fpm.log
```

### Configurar Log Rotation

Edita `/etc/logrotate.d/prodflow`:

```
/var/www/ProdFlow/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

---

## 🔒 Seguridad

### Firewall (UFW)

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### Fail2Ban

```bash
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

---

## 📚 Documentación Adicional

-   [README.md](README.md) - Información general del proyecto
-   [FILTROS_DASHBOARD.md](FILTROS_DASHBOARD.md) - Configuración de filtros para dashboards
-   [GUIA_COMPLETA_FLUJO_AUTOMATICO.md](GUIA_COMPLETA_FLUJO_AUTOMATICO.md) - Flujo automático de fases
-   [Laravel Documentation](https://laravel.com/docs)
-   [Filament Documentation](https://filamentphp.com/docs)

---

## 💬 Soporte

Para soporte técnico o preguntas:

-   Email: soporte@tuempresa.com
-   Issues: [GitHub Issues](https://github.com/tu-usuario/ProdFlow/issues)

---

**Versión:** 1.0
**Última actualización:** {{ date('Y-m-d') }}
