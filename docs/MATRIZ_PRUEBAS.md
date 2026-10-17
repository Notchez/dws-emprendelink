# Matriz de pruebas

La suite usa MySQL en `dws_emprendelink_test`; `phpunit.xml` fuerza ese nombre para aislarla de desarrollo.

| Archivo | Cobertura real |
| --- | --- |
| `tests/Unit/BusinessRulesTest.php` | Cálculo y redondeo de comisión; matriz aprobada de estados |
| `tests/Feature/AuthenticationAuthorizationTest.php` | Registro de cliente, login válido/inválido, redirección por rol, cuenta suspendida, logout, middleware y acceso cruzado a pedidos |
| `tests/Feature/CatalogProfileOwnershipTest.php` | Visibilidad del catálogo, filtro por negocio, protección de perfil, CRUD/ownership de categorías y negocio, MIME inválido y acceso al carrito |
| `tests/Feature/CartCheckoutTest.php` | Operaciones del carrito, disponibilidad, varios negocios, snapshots del usuario, precio/subtotal servidor, detalles, dirección, idempotencia y rollback |
| `tests/Feature/OrderCommissionTest.php` | Flujo y cancelaciones válidas, saltos inválidos, terminales, historial, comisión única, estados y permisos administrativos |
| `tests/Feature/AdministrationAndMediaTest.php` | Alta de emprendedor, plan activo, suspensión de usuario, consultas admin, cambio de plan, límite de productos, reemplazo y conservación de archivos |
| `tests/Feature/DatabaseIntegrityTest.php` | Claves foráneas, negocio único por usuario, comisión única y FK de categoría |

Comando:

```bash
php artisan test
```

Si se usa `RefreshDatabase`, nunca se debe cambiar `DB_DATABASE` de PHPUnit a la base normal. Las pruebas de archivos emplean `Storage::fake('public')` y no escriben en el almacenamiento real.
