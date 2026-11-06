# Reconstrucción y protección de la base de datos

Las migraciones de Laravel son la fuente vigente del esquema. El repositorio no utiliza
un dump SQL como mecanismo oficial de instalación de la Release Candidate.

## Reconstrucción local

Sobre una base MySQL 8 nueva y vacía:

1. configure `.env` con el host y nombre de la base local;
2. confirme la conexión efectiva;
3. ejecute `php artisan migrate`;
4. ejecute `php artisan db:seed` si necesita los datos base;
5. ejecute `DemoShowcaseSeeder` únicamente si necesita el escenario ampliado.

Los seeders contienen datos sintéticos. No copian información real de usuarios.

## Base de pruebas

La suite usa una base separada cuyo nombre termina en `_test` o `_testing`.
`tests/TestCase.php` rechaza otros nombres. La base de desarrollo o producción nunca se
utiliza para `php artisan test`.

## Restricciones de seguridad

No se ejecutan `migrate:fresh`, `migrate:refresh`, `db:wipe` ni restauraciones SQL sobre
bases con información que deba conservarse. Un respaldo externo solo es confiable si
su procedencia, ausencia de datos personales y restauración fueron verificadas fuera de
la base de trabajo; el repositorio no presenta un respaldo de ese tipo como evidencia
de la RC.

## Integridad relevante

- Usuarios, planes, emprendimientos, categorías y productos usan eliminación lógica
  cuando corresponde.
- Eliminar una categoría desvincula productos dentro de una transacción.
- Los productos eliminados permanecen disponibles para resolver detalles históricos.
- Pedidos, detalles, historial, comisiones y snapshots conservan la trazabilidad.
- Restricciones únicas impiden duplicar comisiones y operaciones idempotentes de
  checkout.
