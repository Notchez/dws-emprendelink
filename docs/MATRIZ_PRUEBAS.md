# Inventario de pruebas automatizadas

La suite utiliza MySQL. En local opera sobre `dws_emprendelink_test`; GitHub Actions
usa `emprendelink_testing`. La clase base rechaza cualquier nombre que no termine en
`_test` o `_testing`.

| Archivo | Cobertura real |
| --- | --- |
| `tests/Unit/BusinessRulesTest.php` | Cálculo de comisión y transiciones permitidas del pedido |
| `tests/Feature/AuthenticationAuthorizationTest.php` | Registro, login, logout, roles, suspensión, middleware y acceso a pedidos |
| `tests/Feature/CatalogProfileOwnershipTest.php` | Catálogo, perfil, categorías, productos, eliminación lógica, HTML y ownership |
| `tests/Feature/CartCheckoutTest.php` | Carrito, disponibilidad, checkout multi-negocio, snapshots, idempotencia y rollback |
| `tests/Feature/OrderCommissionTest.php` | Historial, comisiones, estados de cuenta mensuales, pagos y permisos |
| `tests/Feature/AdministrationAndMediaTest.php` | Usuarios, emprendimientos, planes, límites, slugs e imágenes |
| `tests/Feature/DatabaseIntegrityTest.php` | Claves foráneas y restricciones de unicidad |
| `tests/Feature/EntrepreneurReportTest.php` | Consultas MySQL, filtros temporales, gráficos, aislamiento, PDF y XLSX |
| `tests/Feature/EntrepreneurReportStatusFilterTest.php` | Filtro de estado y validación en pantalla y exportaciones |
| `tests/Feature/AdminReportTest.php` | Reportes globales, filtros, planes, autorización, gráficos y exportaciones |
| `tests/Feature/DemoShowcaseSeederTest.php` | Idempotencia, períodos, relaciones, totales, comisiones y SVG del showcase |
| `tests/Concerns/CreatesDomainData.php` | Fábrica de datos de dominio compartida por las pruebas funcionales |
| `tests/TestCase.php` | Protección obligatoria de la base MySQL de testing |

La ejecución publicada en `v1.0.0-rc.1` contiene 109 pruebas y 910 aserciones. Las
pruebas de archivos usan `Storage::fake('public')` y no escriben en el almacenamiento
real.

```powershell
php artisan test
```
