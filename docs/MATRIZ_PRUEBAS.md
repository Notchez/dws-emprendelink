# Matriz de pruebas

La suite usa MySQL en `dws_emprendelink_test` por defecto. PHPUnit respeta una variable externa `DB_DATABASE`, por lo que CI utiliza `emprendelink_testing`; ambos nombres pasan la protección de la clase base y la base normal es rechazada.

| Archivo | Cobertura real |
| --- | --- |
| `tests/Unit/BusinessRulesTest.php` | Cálculo y redondeo de comisión; matriz aprobada de estados |
| `tests/Feature/AuthenticationAuthorizationTest.php` | Registro de cliente, login válido/inválido, redirección por rol, cuenta suspendida, logout, middleware y acceso cruzado a pedidos |
| `tests/Feature/CatalogProfileOwnershipTest.php` | Visibilidad del catálogo, filtro por negocio, protección de perfil, CRUD/ownership de categorías y negocio, MIME inválido y acceso al carrito |
| `tests/Feature/CartCheckoutTest.php` | Operaciones del carrito, disponibilidad, varios negocios, snapshots del usuario, precio/subtotal servidor, detalles, dirección, idempotencia y rollback |
| `tests/Feature/OrderCommissionTest.php` | Flujo e historial, snapshot obligatorio, comisión idempotente, suma mensual exacta, separación por negocio/período, bloqueo del mes actual, pago atómico completo, terminalidad, cambio de año, rutas eliminadas y permisos |
| `tests/Feature/AdministrationAndMediaTest.php` | Separación cuenta/negocio, alta de emprendedor, estado y cambio de plan, slugs, límites, suspensión, consultas admin y reemplazo de archivos |
| `tests/Feature/DatabaseIntegrityTest.php` | Claves foráneas, negocio único por usuario, comisión única y FK de categoría |

Comando:

```bash
php artisan test
```

Si se usa `RefreshDatabase`, nunca se debe cambiar `DB_DATABASE` de PHPUnit a la base normal. Las pruebas de archivos emplean `Storage::fake('public')` y no escriben en el almacenamiento real.
