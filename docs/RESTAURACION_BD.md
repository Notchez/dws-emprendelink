# Restauración y estructura de base de datos

Las migraciones de Laravel son la fuente vigente del esquema. Los seeders generan datos sintéticos de demostración; no contienen ni deben copiar datos reales de usuarios.

## Preparar una base local vacía

1. Cree una base MySQL 8 vacía con `utf8mb4`.
2. Configure `.env` para apuntar explícitamente a esa base.
3. Confirme el nombre y el host antes de ejecutar migraciones.
4. Ejecute `php artisan migrate` y, solo si necesita datos de demostración, `php artisan db:seed`.

No use `migrate:fresh`, `migrate:refresh`, `db:wipe` ni una restauración SQL sobre una base que contenga información que deba conservarse.

## Base exclusiva de pruebas

La suite automatizada debe usar una base separada cuyo nombre termine en `_test` o `_testing`. `tests/TestCase.php` rechaza cualquier otro nombre. La base de desarrollo o producción nunca debe utilizarse para `php artisan test`.

## Respaldo SQL

El repositorio no mantiene actualmente un respaldo SQL validado contra todas las migraciones de Fase 3. Por tanto, no debe usarse un dump histórico como mecanismo de instalación ni declararse como evidencia de restauración vigente.

Si el equipo decide entregar un respaldo, debe generarse únicamente desde una base desechable con datos sintéticos, revisar que no incluya credenciales ni información personal y validar su restauración en otra base vacía. Esta revisión es manual y todavía está pendiente.

## Reglas de integridad relevantes

- Usuarios, planes, emprendimientos, categorías y productos usan eliminación lógica cuando corresponde.
- La eliminación de una categoría desvincula sus productos dentro de una transacción.
- Los productos eliminados se conservan para resolver el historial de detalles de pedido.
- Pedidos, detalles, historial, comisiones y snapshots de precios/planes conservan la trazabilidad financiera.
- Las restricciones únicas evitan duplicar comisiones y operaciones idempotentes de checkout.
