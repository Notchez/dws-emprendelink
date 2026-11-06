# EmprendeLink

EmprendeLink es una aplicación web académica de DWS901 para publicar catálogos,
gestionar pedidos y consultar ventas, planes y comisiones. El catálogo es público; el
carrito, checkout y seguimiento de pedidos requieren una cuenta activa de cliente.

## Release Candidate

La versión de referencia es
[v1.0.0-rc.1](https://github.com/Notchez/dws-emprendelink/releases/tag/v1.0.0-rc.1),
publicada desde el commit `3ed062322a023e246546ed9a5b6e733fd3954042` después de
integrar los Pull Requests #21 y #22.

El workflow [“Pruebas de Laravel” #50](https://github.com/Notchez/dws-emprendelink/actions/runs/42273453688)
finalizó correctamente con 109 pruebas y 910 aserciones.

## Funcionalidad

- Autenticación con roles ADMIN, EMPRENDEDOR y CLIENTE.
- Administración de usuarios, emprendimientos, planes y estados de cuenta.
- Catálogo público, categorías, productos, imágenes y límites por plan.
- Carrito y checkout autenticado, transaccional e idempotente.
- Pedidos, historial de estados y comisiones históricas.
- Reportes de administrador y emprendedor con filtros, Chart.js, PDF y XLSX.
- Datos showcase reproducibles con pedidos históricos y SVG originales.

## Tecnología

- PHP `^8.4.1`, Laravel 12 y MySQL 8.
- Blade, Bootstrap 5, AdminLTE 4, Bootstrap Icons y Chart.js.
- Composer, Node.js, npm y Vite.
- PHPUnit 11 y GitHub Actions.

## Instalación resumida

Para reproducir la RC, siga [Instalación local](docs/INSTALACION_LOCAL.md). El flujo
principal es:

```powershell
composer install
npm ci
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
npm run build
php artisan serve --no-reload
```

La conexión MySQL debe configurarse en `.env` antes de ejecutar migraciones. `.env`,
`vendor`, `node_modules`, compilados y archivos generados no se versionan.

## Datos de demostración

`DatabaseSeeder` crea las cuentas base. Todas usan la contraseña local `password`:

| Rol | Correo |
| --- | --- |
| Administrador | `admin@emprendelink.test` |
| Emprendedor | `emprendedor@emprendelink.test` |
| Cliente | `cliente@emprendelink.test` |

El escenario ampliado se carga manualmente con:

```powershell
php artisan db:seed --class="Database\Seeders\DemoShowcaseSeeder"
```

El seeder solo admite `local` y `testing`, es idempotente y genera 20 pedidos
históricos y 22 SVG. Las cuentas adicionales y su contenido están documentados en
[Datos showcase](docs/DEMO_SHOWCASE.md).

## Pruebas y seguridad de datos

```powershell
composer validate --strict
composer audit
php artisan test
npm run build
npm audit
```

PHPUnit utiliza una base separada cuyo nombre termina en `_test` o `_testing`. La clase
base detiene la suite si la conexión apunta a otro nombre. Nunca se ejecutan pruebas
destructivas ni seeders de demostración sobre bases con datos reales.

## Documentación

- [Implementación y resultados de Fase 3](docs/FASE_3_PLAN.md)
- [Validaciones de Fase 3](docs/FASE_3_MATRIZ_PRUEBAS.md)
- [Defectos corregidos](docs/FASE_3_DEFECTOS.md)
- [Instalación local](docs/INSTALACION_LOCAL.md)
- [Arquitectura](docs/ARQUITECTURA.md)
- [Modelo de negocio](docs/MODELO_NEGOCIO.md)
- [Roles y permisos](docs/ROLES_PERMISOS.md)
- [Rutas](docs/RUTAS.md)
- [Decisiones técnicas](docs/DECISIONES_TECNICAS.md)

## Exclusiones

La RC no incluye pagos en línea, facturación fiscal, chat ni aplicación móvil nativa.
