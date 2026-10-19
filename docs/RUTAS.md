# Rutas

Las rutas se cargan desde `routes/web.php` y se separan por módulo.

## Públicas y autenticación

| Método | URI | Nombre | Uso |
| --- | --- | --- | --- |
| GET | `/catalogo` | `catalog.index` | Catálogo general y filtros |
| GET | `/catalogo/negocio/{slug}` | `catalog.business` | Catálogo de un negocio |
| GET | `/catalogo/productos/{producto}` | `catalog.show` | Detalle público |
| GET/POST | `/login` | `login`, `login.store` | Inicio de sesión |
| GET/POST | `/registro` | `register`, `register.store` | Registro de cliente |
| POST | `/logout` | `logout` | Cierre de sesión |

## Cliente

El grupo exige `auth`, `active` y `role:cliente`.

- `/carrito`: consulta, alta, cantidad, eliminación y vaciado.
- `/checkout`: formulario, creación y confirmación.
- `/pedidos` y `/pedidos/{pedido}`: pedidos propios.
- `/perfil`: consulta y actualización de nombre, teléfono y dirección.

## Emprendedor

Prefijo `/emprendedor`, nombre `entrepreneur.*`, middleware `auth`, `active` y `role:emprendedor`.

- Panel y negocio: `/emprendedor`, `/emprendedor/negocio`.
- Categorías: listado, creación, actualización y baja lógica.
- Productos: listado, alta, edición y activación/desactivación.
- Pedidos: listado, detalle y transición de estado.
- Ventas y cuenta mensual: consultas del emprendimiento autenticado. `/emprendedor/comisiones` usa un selector `YYYY-MM` y no permite modificaciones.

## Administración

Prefijo `/admin`, nombre `admin.*`, middleware `auth`, `active` y `role:admin`.

- Dashboard, usuarios y creación de emprendedor.
- Emprendimientos y asignación de plan.
- CRUD de planes sin borrado físico y endpoint dedicado de activación/desactivación.
- Consulta global de pedidos.
- `/admin/comisiones`: cargos mensuales consolidados por emprendimiento.
- `PATCH /admin/estados-cuenta/{estadoCuenta}/pagar`: pago atómico de un mes cerrado completo.

No existe un endpoint para pagar o anular una comisión individual.

Ejecute `php artisan route:list` para el inventario exacto y sus middlewares.
