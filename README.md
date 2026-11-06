# EmprendeLink

EmprendeLink es una plataforma académica de DWS901 para que emprendimientos publiquen
productos, administren pedidos y consulten ventas y comisiones. El catálogo es público;
el carrito, checkout y seguimiento de pedidos requieren una cuenta activa con rol cliente.

## Tecnología

- PHP `^8.4.1` (entorno verificado: PHP 8.5) y Laravel 12.
- MySQL 8, Composer 2, Eloquent, Blade, Form Requests, Services y Policies.
- Node.js, npm, Vite, Bootstrap 5, AdminLTE 4 y Chart.js.
- PHPUnit 11 sobre una base MySQL de pruebas aislada.

PHP debe incluir las extensiones requeridas por Laravel y por las exportaciones del
proyecto, especialmente `pdo_mysql`, `mbstring`, `xml`, `gd` y `zip`.

## Instalación rápida en Windows

```powershell
composer install
npm ci
Copy-Item .env.example .env
php artisan key:generate
```

Cree una base MySQL local y configure en `.env` sus valores `DB_HOST`, `DB_PORT`,
`DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`. Después ejecute:

```powershell
php artisan config:clear
php artisan migrate
php artisan db:seed
php artisan db:seed --class="Database\Seeders\DemoShowcaseSeeder"
php artisan storage:link
npm run build
php artisan serve --no-reload
```

`DatabaseSeeder` instala planes, cuentas base y los datos mínimos. El seeder showcase es
deliberadamente independiente, idempotente y solo admite los entornos `local` y `testing`.
Genera 20 pedidos históricos y 22 ilustraciones SVG en
`storage/app/public/demo-showcase`; no debe añadirse a `DatabaseSeeder`.

La guía completa, incluidos MySQL, extensiones PHP y solución de errores, está en
[Instalación local](docs/INSTALACION_LOCAL.md).

## Cuentas de demostración

Todas usan la contraseña local `password`.

| Rol | Correo | Datos creados por |
| --- | --- | --- |
| Administrador | `admin@emprendelink.test` | `DatabaseSeeder` |
| Emprendedor | `emprendedor@emprendelink.test` | `DatabaseSeeder` |
| Cliente | `cliente@emprendelink.test` | `DatabaseSeeder` |
| Emprendedor | `miguel.portillo@emprendelink.test` | `DemoShowcaseSeeder` |
| Emprendedor | `rosa.aguilar@emprendelink.test` | `DemoShowcaseSeeder` |
| Emprendedor | `diego.morales@emprendelink.test` | `DemoShowcaseSeeder` |
| Cliente | `ana.martinez@emprendelink.test` | `DemoShowcaseSeeder` |
| Cliente | `carlos.hernandez@emprendelink.test` | `DemoShowcaseSeeder` |
| Cliente | `sofia.ramirez@emprendelink.test` | `DemoShowcaseSeeder` |

Estas credenciales son exclusivamente locales y nunca deben utilizarse en producción.

## Pruebas y protección de datos

La suite usa `dws_emprendelink_test` desde `phpunit.xml`. Cree esa base vacía por
separado y conceda permisos al usuario local de MySQL:

```sql
CREATE DATABASE dws_emprendelink_test
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

```powershell
php artisan test
```

La clase base de pruebas detiene la suite si el nombre no termina en `_test` o
`_testing`. Nunca apunte PHPUnit a la base de desarrollo o producción. No ejecute
`migrate:fresh`, `db:wipe` ni seeders de demostración sobre bases con datos reales.

## Verificación de entrega

```powershell
composer validate --strict
composer check-platform-reqs
composer audit
php artisan test
npm run build
npm audit
git diff --check
```

## Estructura

| Ubicación | Responsabilidad |
| --- | --- |
| `routes/` | Endpoints públicos, cliente, emprendedor y administrador |
| `app/Http` | Controladores, middleware, validación y autorización |
| `app/Policies` | Propiedad y permisos sobre recursos |
| `app/Services` | Transacciones, reportes y reglas de negocio |
| `app/Models` y `app/Enums` | Entidades y estados del dominio |
| `resources/` | Interfaz Blade y recursos compilados con Vite |
| `database/` | Migraciones, factories y seeders |
| `tests/` | Pruebas unitarias y funcionales |

## Documentación

- [Instalación local](docs/INSTALACION_LOCAL.md)
- [Datos showcase](docs/DEMO_SHOWCASE.md)
- [Arquitectura](docs/ARQUITECTURA.md)
- [Plan de Fase 3](docs/FASE_3_PLAN.md)
- [Roles y permisos](docs/ROLES_PERMISOS.md)
- [Rutas](docs/RUTAS.md)
- [Decisiones técnicas](docs/DECISIONES_TECNICAS.md)
- [Matriz de pruebas](docs/FASE_3_MATRIZ_PRUEBAS.md)
- [Defectos](docs/FASE_3_DEFECTOS.md)

## Alcance

La Fase 3 integra autenticación y roles, perfiles, emprendimientos, planes, categorías,
productos, carrito, checkout autenticado, pedidos, historial, ventas, comisiones y
reportes de administrador y emprendedor con gráficos y exportaciones PDF/XLSX. No
incluye pagos en línea, facturación fiscal, chat ni aplicación móvil nativa.
