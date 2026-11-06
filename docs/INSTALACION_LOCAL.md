# Instalación local de EmprendeLink

Esta guía reproduce la Release Candidate `v1.0.0-rc.1` en Windows con PowerShell y una
base MySQL local.

## 1. Requisitos

| Herramienta | Requisito |
| --- | --- |
| PHP | `^8.4.1`; la RC fue validada con PHP 8.5 |
| Composer | 2.x |
| Laravel | 12, instalado por Composer |
| MySQL | 8.x |
| Node.js y npm | Versiones compatibles con `package-lock.json` |
| Git | Versión estable |

PHP requiere `pdo_mysql`, `mbstring`, `openssl`, `ctype`, `fileinfo`, `xml`, `dom`,
`gd`, `zip`, `iconv`, `libxml`, `SimpleXML`, `XMLReader`, `XMLWriter` y `zlib`.

```powershell
php -v
php -m
composer --version
node -v
npm -v
git --version
```

## 2. Obtener exactamente la RC

Una copia nueva puede obtenerse directamente desde el tag:

```powershell
git clone --branch v1.0.0-rc.1 --depth 1 https://github.com/Notchez/dws-emprendelink.git
Set-Location dws-emprendelink
git describe --tags --exact-match
```

El último comando debe mostrar `v1.0.0-rc.1`. En una copia existente:

```powershell
git fetch origin --tags
git switch --detach v1.0.0-rc.1
```

El modo detached conserva el tag inmutable. Para desarrollar cambios se crea una rama
nueva desde la referencia autorizada; no se mueve el tag.

## 3. Instalar dependencias

```powershell
composer install
npm ci
composer check-platform-reqs
```

Estos comandos respetan `composer.lock` y `package-lock.json`. La instalación normal no
usa `composer update` ni `npm audit fix --force`.

## 4. Configurar el entorno

```powershell
if (-not (Test-Path -LiteralPath '.env')) {
    Copy-Item -LiteralPath '.env.example' -Destination '.env'
    php artisan key:generate
}
```

Configure una base exclusivamente local:

```dotenv
APP_NAME=EmprendeLink
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dws_emprendelink
DB_USERNAME=TU_USUARIO_LOCAL
DB_PASSWORD="TU_CONTRASENA_LOCAL"

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
MAIL_MAILER=log
```

`.env` y las credenciales permanecen fuera de Git.

## 5. Crear las bases MySQL

```sql
CREATE DATABASE IF NOT EXISTS dws_emprendelink
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

CREATE DATABASE IF NOT EXISTS dws_emprendelink_test
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

La primera base es para uso manual. La segunda es exclusiva para PHPUnit.

```powershell
php artisan config:clear
php artisan env
php artisan migrate:status
```

## 6. Migraciones y datos base

En una instalación nueva y vacía:

```powershell
php artisan migrate
php artisan db:seed
```

`DatabaseSeeder` crea planes, cuentas base, Manos Creativas y un pedido mínimo. El
escenario ampliado se ejecuta por separado:

```powershell
php artisan db:seed --class="Database\Seeders\DemoShowcaseSeeder"
```

`DemoShowcaseSeeder` solo admite `local` y `testing`, conserva registros existentes y
es idempotente. Genera 20 pedidos históricos y 22 SVG. Una segunda ejecución no
duplica pedidos ni comisiones.

## 7. Almacenamiento y frontend

```powershell
php artisan storage:link
npm run build
```

Después de ejecutar el showcase, los SVG se encuentran en
`storage/app/public/demo-showcase` y se sirven mediante `public/storage`:

```powershell
(Get-ChildItem -LiteralPath 'storage\app\public\demo-showcase' -Recurse -File -Filter '*.svg').Count
Test-Path -LiteralPath 'public\storage\demo-showcase'
```

Los resultados esperados son `22` y `True`.

## 8. Iniciar la aplicación

```powershell
php artisan serve --no-reload
```

La aplicación queda disponible en `http://127.0.0.1:8000`. Para desarrollo de recursos
se ejecuta `npm run dev` en otra terminal. Ambos procesos se detienen con `Ctrl+C`.

## 9. Cuentas locales

Las cuentas base usan la contraseña `password`:

| Rol | Correo |
| --- | --- |
| Administrador | `admin@emprendelink.test` |
| Emprendedor | `emprendedor@emprendelink.test` |
| Cliente | `cliente@emprendelink.test` |

Las cuentas adicionales están en [DEMO_SHOWCASE.md](DEMO_SHOWCASE.md). Estas
credenciales son públicas y se usan únicamente en entornos locales de demostración.

## 10. Pruebas y protección de datos

`phpunit.xml` configura `dws_emprendelink_test`; `Tests\TestCase` rechaza cualquier
nombre que no termine en `_test` o `_testing`.

```powershell
php artisan test
```

No se usa la base de desarrollo o producción para PHPUnit. Tampoco se ejecutan
`migrate:fresh`, `migrate:refresh` o `db:wipe` sobre bases con información que deba
conservarse. El showcase está prohibido en producción.

## 11. Errores comunes

### Extensiones PHP ausentes

`php --ini` identifica el archivo cargado y `php -m` lista extensiones. Después de
activar `gd`, `zip` u otra extensión, se abre una terminal nueva y se ejecuta
`composer check-platform-reqs`.

### MySQL rechaza la conexión

Confirme que MySQL esté activo, que `DB_HOST=127.0.0.1`, que la base exista y que el
usuario tenga permisos. Luego ejecute `php artisan config:clear`.

### Falta la clave de aplicación

```powershell
php artisan key:generate
php artisan config:clear
```

### Las imágenes devuelven 404

Ejecute `php artisan storage:link` y compruebe que `public/storage` apunte a
`storage/app/public`.

### Vite o esbuild no compila

Elimine únicamente un `node_modules` local incompatible mediante el procedimiento de su
sistema, ejecute `npm ci` y repita `npm run build`. El permiso de esbuild está declarado
en `package.json`.

### El servidor se reinicia en Windows

Use `php artisan serve --no-reload`.

### El showcase no encuentra datos base

Ejecute primero `php artisan db:seed`; el showcase no reconstruye los seeders base.

## 12. Validación técnica

```powershell
composer validate --strict
composer check-platform-reqs
composer audit
php artisan route:list
php artisan test
npm run build
npm audit
git diff --check
```
