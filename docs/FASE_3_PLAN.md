# Fase 3 — Implementación y resultados

## Propósito

La Fase 3 integró EmprendeLink como una aplicación Full Stack sobre Laravel 12,
MySQL 8, Blade, AdminLTE 4 y Vite. El incremento consolidó los módulos existentes,
incorporó reportes y exportaciones, corrigió defectos de integración y produjo la
Release Candidate `v1.0.0-rc.1`.

La implementación fue integrada mediante los Pull Requests #21 y #22. La versión
publicada corresponde al commit `3ed062322a023e246546ed9a5b6e733fd3954042`:

https://github.com/Notchez/dws-emprendelink/releases/tag/v1.0.0-rc.1

## Alcance implementado

### Autenticación y seguridad

- Registro público exclusivo para clientes.
- Inicio y cierre de sesión con regeneración e invalidación segura de sesión.
- Roles `admin`, `emprendedor` y `cliente` mediante Enum.
- Middleware para autenticación, cuenta activa y rol.
- Policies y consultas restringidas por propietario.
- Bloqueo efectivo de cuentas suspendidas.

### Emprendimientos y catálogo

- Administración de emprendimientos, planes y suscripciones.
- Perfil del negocio con logo almacenado mediante Laravel Storage.
- CRUD de categorías y productos con validaciones de servidor.
- Límite de productos activos según el plan vigente.
- Eliminación lógica de productos y conservación del historial de pedidos.
- Catálogo público con búsqueda, filtros y visibilidad restringida a entidades activas.

### Carrito, pedidos y comisiones

- Carrito de sesión exclusivo para clientes autenticados.
- Checkout transaccional que separa un pedido por emprendimiento.
- Totales, precios y snapshots calculados en servidor.
- Tokens de idempotencia para evitar pedidos duplicados.
- Flujo controlado de estados con historial de transiciones.
- Comisión única al entregar un pedido y estados de cuenta mensuales por negocio.
- Pago administrativo atómico de períodos mensuales cerrados.

### Reportes

El emprendedor consulta exclusivamente la información de su negocio:

- ventas de pedidos entregados;
- pedidos por estado;
- productos más vendidos;
- evolución de ventas;
- comisiones históricas;
- filtros por fechas y estado;
- exportaciones PDF y XLSX.

El administrador dispone de reportes globales con:

- ventas entregadas y evolución temporal;
- pedidos agrupados por estado;
- comisiones históricas;
- emprendimientos activos;
- distribución de planes vigentes;
- filtros por fechas, emprendimiento y estado;
- gráficos y exportaciones PDF y XLSX.

Las pantallas y exportaciones reutilizan los mismos servicios de reporte para evitar
divergencias en filtros, totales y reglas de cálculo.

## Reglas de cálculo

- Una venta entregada es el subtotal de un pedido creado dentro del período filtrado
  cuyo estado actual es `Entregado`.
- Los productos más vendidos se calculan con las cantidades de detalles pertenecientes
  a pedidos entregados.
- Las comisiones utilizan el monto base y porcentaje históricos almacenados en cada
  pedido; nunca se recalculan con el plan actual.
- La distribución de planes considera suscripciones activas.
- Los filtros de fecha incluyen los límites completos de cada día.
- Los reportes del emprendedor determinan el negocio desde el usuario autenticado y no
  aceptan un `emprendimiento_id` como fuente de autorización.

## Arquitectura aplicada

Las rutas declaran endpoints y middleware; los controladores coordinan solicitudes;
los Form Requests validan entradas; las Policies comprueban permisos; los servicios
concentran reglas, cálculos y transacciones; Eloquent persiste en MySQL; Blade presenta
los resultados. Los estilos y JavaScript compartidos se compilan con Vite.

Las exportaciones usan `barryvdh/laravel-dompdf` y `phpoffice/phpspreadsheet`. Las
cadenas controladas por usuarios se escriben como texto en XLSX para impedir inyección
de fórmulas.

## Datos de demostración

`DemoShowcaseSeeder` prepara un escenario local reproducible sin formar parte de
`DatabaseSeeder`. Genera cuatro emprendimientos del escenario, 18 productos, 20
pedidos históricos y 22 SVG originales. Es idempotente y solo funciona en los entornos
`local` y `testing`.

## Validación y publicación

La suite final contiene 109 pruebas y 910 aserciones. Cubre autenticación, permisos,
catálogo, archivos, checkout, pedidos, comisiones, administración, reportes,
exportaciones, integridad e idempotencia del showcase.

El workflow “Pruebas de Laravel” #50 se ejecutó sobre el commit de la RC y terminó con
resultado satisfactorio:

https://github.com/Notchez/dws-emprendelink/actions/runs/42273453688

La evidencia automatizada no se presenta como sustituto de evaluaciones visuales o
académicas que no estén registradas en el repositorio.

## Exclusiones

La versión no incorpora pagos en línea, facturación fiscal, chat ni aplicación móvil
nativa. Las comisiones y estados de cuenta son registros internos de la plataforma y
no representan documentos fiscales.
