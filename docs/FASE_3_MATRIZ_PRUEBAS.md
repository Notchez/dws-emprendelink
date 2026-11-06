# Fase 3 — Registro de validaciones automatizadas

## Resultado publicado

La Release Candidate `v1.0.0-rc.1` fue validada con PHP 8.5, Laravel 12 y MySQL 8.
La suite final ejecutó 109 pruebas con 910 aserciones.

GitHub Actions ejecutó el workflow “Pruebas de Laravel” #50 sobre el commit
`3ed062322a023e246546ed9a5b6e733fd3954042` y concluyó con `success`:

https://github.com/Notchez/dws-emprendelink/actions/runs/42273453688

## Cobertura automatizada por módulo

| Módulo | Evidencia principal | Comportamientos comprobados |
| --- | --- | --- |
| Reglas del dominio | `BusinessRulesTest` — 2 pruebas | Redondeo de comisiones y matriz de estados de pedido |
| Autenticación y autorización | `AuthenticationAuthorizationTest` — 6 pruebas | Registro, login, logout, roles, cuentas suspendidas y acceso a pedidos |
| Catálogo y propiedad | `CatalogProfileOwnershipTest` — 8 pruebas | Visibilidad, filtros, perfiles, categorías, eliminación lógica, archivos y ownership |
| Carrito y checkout | `CartCheckoutTest` — 7 pruebas | Sesión, disponibilidad, agrupación por negocio, snapshots, idempotencia y rollback |
| Pedidos y comisiones | `OrderCommissionTest` — 16 pruebas | Transiciones, historial, comisión única, estados de cuenta, pago mensual y permisos |
| Administración y archivos | `AdministrationAndMediaTest` — 13 pruebas | Alta de emprendedor, usuarios, planes, límites, slugs, consultas e imágenes |
| Integridad MySQL | `DatabaseIntegrityTest` — 4 pruebas | Claves foráneas, unicidad de negocio y comisión, y relación de categorías |
| Reportes del emprendedor | `EntrepreneurReportTest` — 42 pruebas | Totales MySQL, períodos, aislamiento, gráficos, PDF, XLSX y seguridad de exportaciones |
| Filtro de reportes | `EntrepreneurReportStatusFilterTest` — 3 pruebas | Estado aplicado a pantalla, indicadores y exportaciones; rechazo de valores inválidos |
| Reportes administrativos | `AdminReportTest` — 7 pruebas | Indicadores globales, filtros, planes, autorización, gráficos y exportaciones |
| Datos showcase | `DemoShowcaseSeederTest` — 1 prueba | Dos ejecuciones idempotentes, relaciones, períodos, totales, comisiones y 22 SVG |

## Controles de integración continua

El workflow configura PHP 8.5, Node.js 24, npm 12 y MySQL 8.4. En cada ejecución:

1. valida `composer.json` y `composer.lock`;
2. instala y audita dependencias PHP;
3. instala dependencias frontend con `npm ci`;
4. compila los recursos con Vite;
5. prepara una aplicación de testing;
6. ejecuta migraciones en una base MySQL aislada;
7. ejecuta la suite PHPUnit.

La base de CI es `emprendelink_testing`. En local, `phpunit.xml` usa
`dws_emprendelink_test`. `Tests\TestCase` rechaza bases cuyos nombres no terminen en
`_test` o `_testing`.

## Alcance de la evidencia

Las pruebas automatizadas verifican respuestas HTTP, datos persistidos, autorización,
archivos generados y estructura de las vistas. No se registran como aprobadas revisiones
manuales de percepción visual, compatibilidad con dispositivos físicos ni observaciones
académicas que no tengan evidencia disponible.
