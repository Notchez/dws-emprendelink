# Instalación local de EmprendeLink

Guía reproducible para Windows con PowerShell. Los ejemplos asumen una instalación
local de MySQL y una rama que ya contiene la Fase 3.

## 1. Requisitos

| Herramienta | Requisito |
| --- | --- |
| PHP | `^8.4.1`; el proyecto fue verificado con PHP 8.5 y Laravel Herd |
| Composer | 2.x |
| Laravel | 12, instalado por Composer |
| MySQL | 8.x |
| Node.js y npm | Versiones compatibles con el `package-lock.json` |
| Git | Versión estable |

PHP necesita las extensiones habituales de Laravel y, como mínimo para este proyecto,
`pdo_mysql`, `mbstring`, `openssl`, `ctype`, `fileinfo`, `xml`, `dom`, `gd`, `zip`,
`iconv`, `libxml`, `SimpleXML`, `XMLReader`, `XMLWriter` y `zlib`.

Compruebe el entorno antes de instalar:

```powershell
php -v
php -m
composer --version
node -v
npm -v
git --version
```

Con Laravel Herd, confirme que la terminal usa su PHP y que GD está disponible:

```powershell
php -r "echo PHP_VERSION, PHP_EOL; var_export(extension_loaded('gd'));"
composer check-platform-reqs
```

## 2. Obtener el proyecto e instalar dependencias

```powershell
git clone https://github.com/Notchez/dws-emprendelink.git
Set-Location dws-emprendelink
git switch develop
composer install
npm ci
```

`composer install` y `npm ci` respetan exactamente `composer.lock` y
`package-lock.json`. No use `composer update` ni `npm audit fix --force` como parte de
la instalación normal.

## 3. Configurar `.env`

```powershell
if (-not (Test-Path -LiteralPath '.env')) {
    Copy-Item -LiteralPath '.env.example' -Destination '.env'
    php artisan key:generate
}
```

Configure una base de desarrollo exclusivamente local:

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

No copie credenciales reales al repositorio. `.env`, `.env.production` y respaldos del
entorno están excluidos por `.gitignore`.

## 4. Crear las bases MySQL

Ejecute en MySQL Workbench o en el cliente MySQL:

```sql
CREATE DATABASE IF NOT EXISTS dws_emprendelink
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

CREATE DATABASE IF NOT EXISTS dws_emprendelink_test
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

La primera es para uso manual. La segunda es exclusiva para PHPUnit. El usuario local
de MySQL debe tener permisos sobre ambas.

Confirme el entorno y la conexión antes de escribir datos:

```powershell
php artisan env
php artisan config:clear
php artisan migrate:status
```

## 5. Migraciones y datos iniciales

En una instalación nueva y vacía:

```powershell
php artisan migrate
php artisan db:seed
```

`DatabaseSeeder` crea los planes, las tres cuentas base, Manos Creativas y un pedido
mínimo. No ejecuta el showcase automáticamente.

Para cargar el escenario completo de Fase 3:

```powershell
php artisan db:seed --class="Database\Seeders\DemoShowcaseSeeder"
```

`DemoShowcaseSeeder`:

- solo funciona con `APP_ENV=local` o `testing`;
- es determinista e idempotente;
- conserva los registros existentes;
- genera 20 pedidos entre julio y el 6 de noviembre de 2026;
- genera comisiones con los servicios reales del dominio;
- crea 22 SVG locales en `storage/app/public/demo-showcase`.

Puede ejecutarlo una segunda vez para comprobar la idempotencia: los pedidos y las
comisiones no deben aumentar. Consulte [DEMO_SHOWCASE.md](DEMO_SHOWCASE.md) para el
detalle del conjunto.

## 6. Almacenamiento público y SVG

```powershell
php artisan storage:link
```

El enlace `public/storage` permite servir logos y productos guardados en el disco
`public`. Después de ejecutar el showcase en una instalación nueva, compruebe:

```powershell
(Get-ChildItem -LiteralPath 'storage\app\public\demo-showcase' -Recurse -File -Filter '*.svg').Count
Test-Path -LiteralPath 'public\storage\demo-showcase'
```

El resultado esperado es `22` y `True`. Los SVG se regeneran desde
`DemoShowcaseSeeder.php`; no es necesario versionar los archivos generados.

## 7. Compilar e iniciar en Windows

```powershell
npm run build
php artisan serve --no-reload
```

Abra `http://127.0.0.1:8000`. En Windows se usa `--no-reload` para evitar que el
servidor de desarrollo reinicie procesos repetidamente al detectar archivos generados.

Para trabajar con recarga de CSS y JavaScript, ejecute en otra terminal:

```powershell
npm run dev
```

Mantenga también `php artisan serve --no-reload` activo. Detenga ambos procesos con
`Ctrl+C`.

## 8. Cuentas de demostración

Todas usan la contraseña `password` y son únicamente para el entorno local.

| Rol | Correo | Seeder |
| --- | --- | --- |
| Administrador | `admin@emprendelink.test` | Base |
| Emprendedor | `emprendedor@emprendelink.test` | Base |
| Cliente | `cliente@emprendelink.test` | Base |
| Emprendedor | `miguel.portillo@emprendelink.test` | Showcase |
| Emprendedor | `rosa.aguilar@emprendelink.test` | Showcase |
| Emprendedor | `diego.morales@emprendelink.test` | Showcase |
| Cliente | `ana.martinez@emprendelink.test` | Showcase |
| Cliente | `carlos.hernandez@emprendelink.test` | Showcase |
| Cliente | `sofia.ramirez@emprendelink.test` | Showcase |

## 9. Pruebas y protección de bases

`phpunit.xml` configura `dws_emprendelink_test`. Además, `Tests\TestCase` rechaza
cualquier nombre que no termine en `_test` o `_testing`.

```powershell
php artisan test
```

Antes de ejecutar pruebas, verifique que ninguna variable de entorno del sistema
sobrescriba `DB_DATABASE`. Nunca use la base de desarrollo o producción para PHPUnit.

No ejecute estos comandos sobre bases con información que deba conservarse:

```text
php artisan migrate:fresh
php artisan db:wipe
php artisan migrate:refresh
```

El showcase también está prohibido en producción y contiene credenciales públicas de
demostración.

## 10. Errores comunes

### `ext-gd` o `ext-zip` no disponible

Compruebe qué PHP usa Composer con `php --ini` y `php -m`. Active la extensión en ese
`php.ini` o seleccione una versión de Herd que la incluya; cierre y abra la terminal y
repita `composer check-platform-reqs`.

### MySQL rechaza la conexión

Confirme que el servicio está iniciado, que `DB_HOST=127.0.0.1`, que la base existe y
que el usuario tiene permisos. Después ejecute `php artisan config:clear`.

### `No application encryption key has been specified`

```powershell
php artisan key:generate
php artisan config:clear
```

### Las imágenes devuelven 404

```powershell
php artisan storage:link
php artisan config:clear
```

Compruebe que el archivo exista bajo `storage/app/public` y que `public/storage` sea un
enlace válido. Si el enlace ya existe y apunta al destino correcto, no necesita crearlo
de nuevo.

### Vite o esbuild no compila

No reutilice un `node_modules` de otra versión de Node. Ejecute `npm ci` y luego
`npm run build`. El permiso de `esbuild` está registrado en `package.json`.

### El servidor Laravel se reinicia continuamente en Windows

Inícielo con:

```powershell
php artisan serve --no-reload
```

### El seeder no encuentra planes o Manos Creativas

Ejecute primero `php artisan db:seed`. El showcase se mantiene separado
intencionalmente y no reconstruye los datos base.

## 11. Validación de una instalación

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

Las pruebas destructivas operan únicamente sobre `dws_emprendelink_test`. Los seeders
de demostración se ejecutan manualmente solo en la base local de desarrollo.
