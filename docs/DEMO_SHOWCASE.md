# Datos de demostración de Fase 3

`DemoShowcaseSeeder` agrega un escenario reproducible para revisar catálogos, pedidos,
reportes y comisiones sin borrar la información local existente. No está registrado en
`DatabaseSeeder` y rechaza su ejecución fuera de los entornos `local` y `testing`.

## Ejecución local

Antes de ejecutar, confirme que `.env` apunta a una base MySQL local de desarrollo y
que los seeders base ya fueron ejecutados. Luego use:

```powershell
php artisan db:seed --class="Database\Seeders\DemoShowcaseSeeder"
```

El comando es idempotente: los registros se identifican por correos, slugs, nombres
dentro de cada emprendimiento y UUID deterministas. Una segunda ejecución actualiza
únicamente el conjunto de demostración y no duplica pedidos ni comisiones.

## Contenido

- Cuatro emprendimientos activos, incluido `Manos Creativas`, con planes variados.
- Dieciocho productos activos agrupados en once categorías.
- Veinte pedidos históricos entre julio y el 6 de noviembre de 2026.
- Catorce pedidos entregados con sus comisiones y estados de cuenta mensuales.
- Cuentas adicionales de emprendedores y clientes con contraseña local `password`.

## Imágenes y procedencia

El seeder genera 22 ilustraciones SVG locales: cuatro logotipos y dieciocho imágenes de
producto. Son composiciones vectoriales originales creadas específicamente para este
proyecto; no incorporan fotografías, marcas ni recursos de terceros. Se guardan en
`storage/app/public/demo-showcase` y se sirven mediante el enlace público de Laravel.

Estas imágenes son datos locales generados y no se obtienen desde URLs externas en
tiempo de ejecución.
