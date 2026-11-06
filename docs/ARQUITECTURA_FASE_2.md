# Arquitectura de Fase 2 — Referencia histórica

Este documento resume la base arquitectónica establecida durante Fase 2. La descripción
vigente está en [ARQUITECTURA.md](ARQUITECTURA.md); las decisiones funcionales cerradas
durante Fase 3 están en [DECISIONES_TECNICAS.md](DECISIONES_TECNICAS.md).

## Base establecida

EmprendeLink se construyó como un monolito Laravel con MySQL, Blade, AdminLTE y Vite.
La separación adoptada fue:

| Capa | Responsabilidad |
| --- | --- |
| Rutas | Endpoints, nombres y middleware |
| Controladores | Coordinación de solicitudes y respuestas |
| Form Requests | Validación y autorización de entradas |
| Middleware | Autenticación, cuenta activa y rol |
| Policies | Autorización sobre recursos y propiedad |
| Servicios | Reglas de negocio, cálculos y transacciones |
| Modelos | Entidades, relaciones y casts de Eloquent |
| Vistas | Presentación Blade |
| Migraciones | Evolución reproducible del esquema |
| Pruebas | Verificación del comportamiento |

Las operaciones que escriben varias tablas se ejecutan dentro de transacciones.

## Modelo de dominio

La base incluyó `users`, `planes`, `emprendimientos`, `suscripciones`, `categorias`,
`productos`, `pedidos`, `detalle_pedidos`, `historial_estados`, `comisiones` y
`estados_cuenta`.

Los nombres españoles que no coinciden con la pluralización de Eloquent declaran
`$table`. Las relaciones principales son:

- usuario emprendedor con un único emprendimiento;
- emprendimiento con suscripciones, categorías, productos, pedidos y comisiones;
- suscripción asociada a un plan;
- pedido con detalles, historial y una comisión como máximo;
- estado de cuenta mensual asociado al emprendimiento.

## Organización modular

Los controladores se separaron en `Admin`, `Auth`, `Emprendedor` y `Storefront`. Los
servicios se agruparon por autenticación, administración, catálogo, pedidos, comisiones
y reportes. `routes/web.php` carga archivos de rutas por módulo.

Los prefijos y nombres consolidados son:

- `/admin`, rutas `admin.*`;
- `/emprendedor`, rutas `entrepreneur.*`;
- `/catalogo`, rutas `catalog.*`;
- rutas privadas de cliente para carrito, checkout, pedidos y perfil.

## Contrato nominal registrado en Fase 2

La documentación de esa fase distribuyó las clases por los siguientes grupos. Este
inventario conserva la trazabilidad de la base inicial; no sustituye el inventario
vigente ni implica que una clase haya conservado exactamente el mismo alcance:

| Grupo | Clases registradas en la base de Fase 2 |
| --- | --- |
| Modelos | `User`, `Plan`, `Emprendimiento`, `Suscripcion`, `Categoria`, `Producto`, `Pedido`, `DetallePedido`, `HistorialEstado`, `Comision`, `EstadoCuenta` |
| Controladores de administración | `DashboardController`, `UserController`, `PlanController`, `CommissionController` |
| Controladores de autenticación | `RegisteredCustomerController`, `AuthenticatedSessionController` |
| Controladores de emprendedor | `DashboardController`, `BusinessController`, `CategoryController`, `ProductController`, `OrderController`, `CommissionController` |
| Controladores de tienda | `CatalogController`, `CartController`, `CheckoutController`, `CustomerOrderController`, `ProfileController` |
| Form Requests | `RegisterCustomerRequest`, `LoginRequest`, `StorePlanRequest`, `UpdatePlanRequest`, `UpdateUserStatusRequest`, `UpdateBusinessRequest`, `StoreProductRequest`, `UpdateProductRequest`, `ChangeOrderStatusRequest`, `StoreCategoryRequest`, `UpdateCategoryRequest`, `UpdateProductStatusRequest`, `StoreOrderRequest` |
| Servicios | `EmprendedorRegistrationService`, `UserAdministrationService`, `PlanManagementService`, `AdminDashboardService`, `BusinessProfileService`, `ProductCatalogService`, `EntrepreneurDashboardService`, `CategoryManagementService`, `OrderPlacementService`, `OrderWorkflowService`, `CommissionCalculator`, `CommissionLedgerService`, `MonthlyAccountStatementService` |
| Policies | `EmprendimientoPolicy`, `ProductoPolicy`, `PedidoPolicy`, `ComisionPolicy`, `PlanPolicy`, `CategoriaPolicy` |
| Excepciones de dominio | `ProductLimitReachedException`, `InvalidOrderTransitionException` |

La autorización combinaba `EnsureUserHasRole`, Policies y consultas restringidas por
propietario. Las excepciones del dominio debían convertirse en respuestas controladas,
sin exponer trazas ni datos sensibles.

## Seguridad e integridad

La arquitectura combinó middleware, Form Requests, Policies y consultas restringidas.
Las Policies no sustituyen el filtrado por propietario. Los precios, snapshots y
comisiones se calculan en servidor, y las restricciones de MySQL respaldan unicidad e
integridad referencial.

Los contratos que estaban abiertos al inicio de Fase 2 quedaron resueltos en DT-12:
tabla `users`, roles, suspensión, negocio único, matriz de estados, identificación de
pedidos, idempotencia y comisión única.

## Base visual

AdminLTE 4, Bootstrap e Inter se integraron mediante Vite. Los tokens visuales se
centralizaron en `resources/css/adminlte.css`:

| Token | Valor |
| --- | --- |
| Primario | `#2563EB` |
| Primario claro | `#EFF6FF` |
| Texto principal | `#111827` |
| Texto secundario | `#6B7280` |
| Borde | `#D1D5DB` |
| Fondo | `#F8FAFC` |
| Superficie | `#FFFFFF` |

Fase 3 amplió esta base con reportes, Chart.js, exportaciones y ajustes responsive sin
cambiar la separación de capas.
