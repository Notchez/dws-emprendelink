# Arquitectura y contrato de clases — Fase 2

## 1. Pila tecnológica

- PHP 8.5.
- Laravel 12.
- MySQL 8.
- Blade.
- AdminLTE 4.
- Vite y npm.
- Composer.
- PHPUnit.

La aplicación utiliza una arquitectura monolítica MVC con servicios.

Las páginas se construyen con Blade y utilizan sesiones Laravel para la autenticación.

## 2. Separación de responsabilidades

| Capa                | Responsabilidad                          |
| ------------------- | ---------------------------------------- |
| Rutas               | Declarar endpoints, nombres y middleware |
| Controladores       | Coordinar solicitudes y respuestas       |
| Form Requests       | Validar y autorizar entradas             |
| Middleware          | Aplicar controles generales              |
| Policies            | Autorizar operaciones sobre recursos     |
| Servicios           | Ejecutar reglas y operaciones de negocio |
| Modelos             | Representar entidades y relaciones       |
| Vistas              | Presentar información                    |
| Migraciones         | Versionar el esquema                     |
| Factories y seeders | Preparar datos sintéticos                |
| Pruebas             | Verificar comportamientos                |

Una operación de negocio que modifique varias tablas relacionadas debe ejecutarse dentro de una transacción.

## 3. Modelos Eloquent

| Clase             | Tabla del proyecto                            |
| ----------------- | --------------------------------------------- |
| `User`            | `users`, sujeto al acuerdo con el Diccionario |
| `Plan`            | `planes`                                      |
| `Emprendimiento`  | `emprendimientos`                             |
| `Suscripcion`     | `suscripciones`                               |
| `Categoria`       | `categorias`                                  |
| `Producto`        | `productos`                                   |
| `Pedido`          | `pedidos`                                     |
| `DetallePedido`   | `detalle_pedidos`                             |
| `HistorialEstado` | `historial_estados`                           |
| `Comision`        | `comisiones`                                  |
| `EstadoCuenta`    | `estados_cuenta`                              |

Declarar explícitamente `$table` cuando sea necesario. No depender de que Eloquent pluralice correctamente todos los nombres en español.

Los modelos se ubican en `app/Models`.

## 4. Controladores previstos

### Administración

Ubicación: `app/Http/Controllers/Admin`.

- `DashboardController`.
- `UserController`.
- `PlanController`.
- `CommissionController`.

### Autenticación

Ubicación: `app/Http/Controllers/Auth`.

- `RegisteredEmprendedorController`.
- `AuthenticatedSessionController`.

### Emprendedor

Ubicación: `app/Http/Controllers/Emprendedor`.

- `DashboardController`.
- `BusinessController`.
- `ProductController`.
- `OrderController`.
- `CommissionController`.

### Catálogo y cliente final

Ubicación: `app/Http/Controllers/Storefront`.

- `CatalogController`.
- `OrderController`.
- `OrderStatusController`.

Los controladores iniciales son esqueletos. Cada responsable debe completar sus métodos y pruebas antes de conectar las rutas funcionales.

La gestión de categorías debe mantener una ubicación y un contrato acordados dentro del módulo del Integrante 4.

## 5. Form Requests previstos

### Autenticación

Ubicación: `app/Http/Requests/Auth`.

- `RegisterEmprendedorRequest`.
- `LoginRequest`

### Administración

Ubicación: `app/Http/Requests/Admin`.

- `StorePlanRequest`.
- `UpdatePlanRequest`.
- `UpdateUserStatusRequest`.

### Emprendedor

Ubicación: `app/Http/Requests/Emprendedor`.

- `UpdateBusinessRequest`.
- `StoreProductRequest`.
- `UpdateProductRequest`.
- `ChangeOrderStatusRequest`.
- `CategoryController`.
- `StoreCategoryRequest`
- `UpdateCategoryRequest`
- `UpdateProductStatusRequest`

### Cliente final

Ubicación: `app/Http/Requests/Storefront`.

- `StoreOrderRequest`.
- `LookupOrderStatusRequest`.

Cada Request debe implementar las reglas y la autorización correspondientes antes de utilizarse en un controlador.

## 6. Servicios previstos

### Autenticación

Ubicación: `app/Services/Auth`.

- `EmprendedorRegistrationService`.

### Administración

Ubicación: `app/Services/Admin`.

- `UserAdministrationService`.
- `PlanManagementService`.
- `AdminDashboardService`.

### Emprendedor

Ubicación: `app/Services/Emprendedor`.

- `BusinessProfileService`.
- `ProductCatalogService`.
- `EntrepreneurDashboardService`.
- `CategoryManagementService`

### Pedidos

Ubicación: `app/Services/Orders`.

- `OrderPlacementService`.
- `OrderWorkflowService`.
- `OrderStatusLookupService`.

### Comisiones

Ubicación: `app/Services/Commissions`.

- `CommissionCalculator`.
- `CommissionLedgerService`.

## 7. Autorización

Policies iniciales, ubicadas en `app/Policies`:

- `EmprendimientoPolicy`.
- `ProductoPolicy`.
- `PedidoPolicy`.
- `ComisionPolicy`.
- `PlanPolicy`.
- `CategoriaPolicy`

Middleware previsto:

- `EnsureUserHasRole`.

Ubicación:

```text
app/Http/Middleware/EnsureUserHasRole.php
```

Su alias `role` debe registrarse en `bootstrap/app.php` cuando se implemente.

Las Policies no sustituyen las consultas restringidas por propietario. Ambos controles deben aplicarse cuando corresponda.

## 8. Excepciones previstas

Ubicación: `app/Exceptions`.

- `ProductLimitReachedException`.
- `InvalidOrderTransitionException`.
- `DuplicateCommissionException`.

La implementación debe decidir si una repetición válida devuelve el resultado existente o representa un error. La prevención de comisiones duplicadas debe mantenerse en ambos casos.

Los errores deben presentarse sin exponer trazas ni datos sensibles.

## 9. Rutas

`routes/web.php` carga los archivos de rutas por módulo.

| Archivo                   | Contenido                    |
| ------------------------- | ---------------------------- |
| `routes/auth.php`         | Registro, login y logout     |
| `routes/admin.php`        | Administración               |
| `routes/entrepreneur.php` | Funciones del emprendedor    |
| `routes/public.php`       | Catálogo y flujo del cliente |

Prefijos previstos:

- `/admin`.
- `/emprendedor`.
- `/catalogo/{slug}`.
- `/pedidos`.

Convenciones de nombres:

- `admin.*`.
- `entrepreneur.*`.
- `catalog.*`.
- `orders.*`.

Las rutas estándar de autenticación deben conservar nombres compatibles con las redirecciones de Laravel, como `login`.

Las rutas privadas se protegen por autenticación, rol y autorización sobre el recurso.

No habilitar rutas funcionales que apunten a métodos todavía no implementados.

## 10. Base de datos y relaciones

El Diccionario de Datos es la referencia para:

- Nombres de tablas.
- Nombres de columnas.
- Tipos y longitudes.
- Precisión decimal.
- Campos obligatorios.
- Valores predeterminados.
- Llaves primarias y foráneas.
- Borrado lógico.

Relaciones del modelo:

- Un emprendimiento pertenece a un usuario.
- Un emprendimiento tiene suscripciones.
- Una suscripción pertenece a un plan.
- Un emprendimiento tiene categorías y productos.
- Un producto puede pertenecer a una categoría.
- Un emprendimiento recibe pedidos.
- Un pedido tiene detalles.
- Cada detalle corresponde a un producto.
- Un pedido tiene historial de estados.
- Las comisiones se relacionan con pedidos y emprendimientos.
- Los estados de cuenta pertenecen a emprendimientos.

La regla de un emprendimiento por usuario y la unicidad de comisión por pedido deben reflejarse en las restricciones de base de datos cuando se cierre el acuerdo técnico.

## 11. Decisiones pendientes del esquema

El Diccionario no define completamente la tabla de usuarios.

Antes de fijar migraciones definitivas deben acordarse:

- Uso de `users` o adaptación a `usuarios`.
- Campos de rol.
- Estado de cuenta.
- Relación usuario-emprendimiento.
- Mecanismo de identificación pública del pedido.
- Mecanismo para prevenir pedidos duplicados.
- Restricción de unicidad de comisión por pedido.

Las ampliaciones necesarias deben documentarse; no deben presentarse como columnas que ya existían en el Diccionario.

## 12. Base visual

Referencia: Documento Maestro, páginas 44 a 61.

AdminLTE 4 proporciona la estructura visual compartida.

Paleta:

| Token            | Valor     |
| ---------------- | --------- |
| Primario         | `#2563EB` |
| Primario claro   | `#EFF6FF` |
| Texto principal  | `#111827` |
| Texto secundario | `#6B7280` |
| Borde            | `#D1D5DB` |
| Fondo            | `#F8FAFC` |
| Superficie       | `#FFFFFF` |

Tipografía: Inter.

Los estilos compartidos se centralizan en:

```text
resources/css/adminlte.css
```

Los recursos JavaScript compartidos se centralizan en:

```text
resources/js/adminlte.js
```

Las vistas reutilizan el layout:

```text
resources/views/layouts/adminlte.blade.php
```

## 13. Alcance visual de Fase 2

Implementar:

- Layout compartido.
- Navegación por rol.
- Formularios básicos.
- Tablas de consulta.
- Mensajes de validación.
- Estados vacíos.
- Adaptación básica a diferentes tamaños de pantalla.

Los gráficos, reportes y exportaciones se conectarán conforme al Product Backlog.

Los indicadores funcionales deben utilizar datos reales.
