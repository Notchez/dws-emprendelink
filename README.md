# EmprendeLink

EmprendeLink es una plataforma académica de DWS901 para que emprendimientos publiquen productos, administren pedidos y consulten ventas y comisiones. El catálogo es público; el carrito, checkout y seguimiento de pedidos requieren una cuenta de cliente.

## Tecnología

- PHP 8.2 o superior (entorno objetivo: PHP 8.5) y Laravel 12.
- MySQL 8, Eloquent, Blade, Form Requests, Services y Policies.
- Bootstrap 5, AdminLTE 4, Bootstrap Icons, Inter y Vite.
- PHPUnit 11 sobre una base MySQL de pruebas aislada.

## Requisitos

PHP con PDO MySQL, Mbstring, OpenSSL, XML, Ctype, Fileinfo y Tokenizer; Composer 2; MySQL 8; Node.js/npm; y Git.

## Instalación local

```bash
composer install
copy .env.example .env
php artisan key:generate
npm install
```

Configure en `.env` la conexión MySQL (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`) y después ejecute:

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
npm run build
php artisan serve
```

Para desarrollo con recarga de recursos, use `npm run dev` en otra terminal. Las imágenes se guardan en `storage/app/public` y la base conserva únicamente su ruta relativa.

## Datos de demostración

`php artisan db:seed` crea usuarios de demostración con contraseña `password`:

| Rol | Correo |
| --- | --- |
| Administrador | `admin@emprendelink.test` |
| Emprendedor | `emprendedor@emprendelink.test` |
| Cliente | `cliente@emprendelink.test` |

Son datos locales; no deben utilizarse en producción.

## Pruebas

La suite usa `dws_emprendelink_test` como valor local predeterminado en `phpunit.xml`, para impedir que `RefreshDatabase` opere sobre la base de desarrollo. Cree esa base vacía y conceda al usuario configurado permisos sobre ella. Las variables de entorno explícitas tienen prioridad, por lo que CI utiliza su base aislada `emprendelink_testing`.

```bash
php artisan test
```

Nunca apunte la configuración de testing a la base normal. La clase base de pruebas rechaza nombres que no terminen en `_test` o `_testing`. Las pruebas cubren autenticación, roles, propiedad de recursos, catálogo, carrito, checkout transaccional e idempotente, pedidos, comisiones, administración e imágenes.

## Estructura

| Ubicación | Responsabilidad |
| --- | --- |
| `routes/` | Endpoints públicos, de autenticación, admin y emprendedor |
| `app/Http/Controllers` | Coordinación HTTP |
| `app/Http/Requests` | Validación y autorización de entradas |
| `app/Http/Middleware` | Sesión activa y control de roles |
| `app/Policies` | Propiedad y permisos sobre recursos |
| `app/Services` | Transacciones y reglas de negocio |
| `app/Models` y `app/Enums` | Modelo Eloquent y estados del dominio |
| `resources/views` | Interfaz Blade responsive |
| `resources/css/adminlte.css` | Estilos compartidos de EmprendeLink |
| `database/` | Migraciones, factories y seeders |
| `tests/` | Pruebas unitarias y funcionales |

## Verificación

```bash
composer dump-autoload
php artisan optimize:clear
php artisan route:list
php artisan migrate:status
php artisan test
npm run build
```

## Documentación

- [Arquitectura](docs/ARQUITECTURA.md)
- [Roles y permisos](docs/ROLES_PERMISOS.md)
- [Rutas](docs/RUTAS.md)
- [Modelo de negocio](docs/MODELO_NEGOCIO.md)
- [Matriz de pruebas](docs/MATRIZ_PRUEBAS.md)
- [Decisiones técnicas](docs/DECISIONES_TECNICAS.md)
- [Instalación local ampliada](docs/INSTALACION_LOCAL.md)

## Alcance

La Fase 2 incluye catálogo, cuentas y roles, perfiles, negocios, planes, categorías, productos, carrito, checkout, pedidos, historial, ventas y comisiones. No incluye pagos en línea, facturación fiscal, chat, aplicación móvil nativa ni exportaciones avanzadas.
