# Instalación local de EmprendeLink

Guía para Windows con PowerShell.

Usar una rama que ya contenga la base compartida de Fase 2. Los comandos
de clonación siguientes asumen que esa base ya fue integrada en develop.

## 1. Herramientas necesarias

| Herramienta  | Versión del entorno de trabajo | Uso                                                                 |
| ------------ | ------------------------------ | ------------------------------------------------------------------- |
| PHP          | 8.5.x                          | Ejecutar Laravel y Artisan.                                         |
| Composer     | 2.x                            | Instalar las dependencias PHP.                                      |
| Node.js      | 24 LTS                         | Ejecutar Vite y compilar los recursos visuales.                     |
| npm          | 12.x                           | Instalar dependencias del frontend y gestionar permisos de scripts. |
| MySQL Server | 8.x; recomendado 8.4 LTS       | Base de datos del proyecto.                                         |
| Git          | Versión estable                | Obtener el repositorio y trabajar con ramas.                        |

Descargas oficiales:

- PHP: https://www.php.net/downloads.php
- Composer: https://getcomposer.org/download/
- Node.js: https://nodejs.org/en/download
- MySQL Server: https://dev.mysql.com/downloads/mysql/8.4.html
- Git: https://git-scm.com/downloads

Node.js incluye npm. Si la instalación trae una versión anterior a npm 12,
actualizar para usar el entorno indicado en esta guía:

```powershell
npm install -g npm@12
```

PHP debe tener las extensiones requeridas por Laravel:
Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE,
PDO, Session, Tokenizer y XML.

Para conectar con MySQL se necesita además pdo_mysql.

Consultar las extensiones activas:

```powershell
php -m
```

VS Code es el editor recomendado. MySQL Workbench es opcional para
administrar la base; se necesita el servidor MySQL funcionando.

Comprobar las herramientas en una terminal nueva:

```powershell
php -v
composer --version
node -v
npm -v
git --version
```

Laravel y PHPUnit se descargan mediante Composer.
AdminLTE, Bootstrap, iconos, tipografía y las demás dependencias visuales
se descargan mediante npm.

## 2. Obtener el proyecto e instalar sus dependencias

Para una copia nueva:

```powershell
git clone --branch develop https://github.com/Notchez/dws-emprendelink.git
cd dws-emprendelink
composer install
npm ci
composer check-platform-reqs
```

composer install y npm ci usan las versiones registradas en composer.lock
y package-lock.json.

Las actualizaciones de dependencias deben acordarse con quien mantiene
la base del proyecto.

El permiso de instalación de esbuild debe estar registrado en allowScripts,
dentro de package.json.

Si npm muestra un aviso para esbuild, revisar y autorizar ese paquete:

```powershell
npm install-scripts ls
npm install-scripts approve esbuild
npm rebuild esbuild
```

## 3. Preparar la configuración local

Crear .env y generar la clave de la aplicación únicamente si todavía
no existe ese archivo:

```powershell
if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
    php artisan key:generate
}
```

Cada integrante conserva su propio .env y sus credenciales locales.
Si ya existe, editarlo sin reemplazarlo.

La clave APP_KEY se genera una vez por instalación.

En .env, establecer estas opciones. Reemplazar DB_USERNAME y DB_PASSWORD
con las credenciales de la instalación local de MySQL:

```dotenv
APP_NAME=EmprendeLink
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_LOCALE=es
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=es_ES

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=emprendelink
DB_USERNAME=TU_USUARIO_MYSQL
DB_PASSWORD="TU_CONTRASENA_MYSQL"

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
MAIL_MAILER=log
PHP_CLI_SERVER_WORKERS=1
```

Conservar la APP_KEY generada.
El archivo .env es local y está excluido de Git.

## 4. Crear la base de datos

Con MySQL Server iniciado, ejecutar este SQL en MySQL Workbench
o en el cliente MySQL:

```sql
CREATE DATABASE IF NOT EXISTS emprendelink
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Luego, desde la raíz del proyecto:

```powershell
php artisan config:clear
php artisan migrate
```

Las migraciones crean las tablas disponibles en esa rama.

La Parte 2 entrega las migraciones y los seeders del negocio.
La base inicial por sí sola no implica que productos, pedidos
o cuentas de demostración estén implementados.

## 5. Compilar y abrir la aplicación

```powershell
npm run build
php artisan serve
```

Abrir http://127.0.0.1:8000 y mantener abierta la terminal del servidor.

Para trabajar en estilos y JavaScript con actualización automática,
abrir una segunda terminal en la raíz del proyecto y ejecutar:

```powershell
npm run dev
```

La página se abre en el puerto de Laravel, normalmente 8000.
La terminal de Vite debe permanecer abierta mientras se usa ese modo
de desarrollo.

Detener los procesos con Ctrl+C.

## 6. Archivos e imágenes de los módulos

Al habilitar la carga pública de imágenes de productos y negocios:

```powershell
php artisan storage:link
```

Los módulos deben guardar las imágenes públicas en el disco public de Laravel.
El enlace permite servirlas desde public/storage.

## 7. Antes de implementar un módulo

Leer:

- AGENTS.md
- docs/LLM_GUIDE.md
- docs/ARQUITECTURA_FASE_2.md
- docs/RESPONSABILIDADES_FASE_2.md

Reglas de trabajo:

- Trabajar en una rama propia creada desde develop actualizado.
- Conservar los nombres y ubicaciones de las clases compartidas.
- La Parte 3 implementa autenticación, roles y autorización.
- Cada módulo aplica esos controles y limita sus consultas al propietario correspondiente.
- Los elementos deshabilitados de la vista inicial indican funciones pendientes.
- Los métodos que lanzan LogicException con el texto "Pendiente" son puntos de implementación del responsable indicado.
- Cada módulo conecta sus rutas cuando su implementación y controles estén listos.

Compartir el código, composer.lock, package-lock.json y la configuración
allowScripts de package.json.

vendor, node_modules y .env se reconstruyen o configuran localmente
y permanecen excluidos de Git.
