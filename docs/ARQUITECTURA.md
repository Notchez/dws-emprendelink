# Arquitectura

EmprendeLink usa Laravel MVC con una capa de servicios para las operaciones del dominio.

```text
Request
  -> middleware (auth, active, role, CSRF)
  -> route model binding
  -> Controller
  -> FormRequest (authorize + rules)
  -> Policy / autorización explícita
  -> Service (reglas y transacciones)
  -> Eloquent
  -> MySQL
  -> Blade / RedirectResponse
```

## Responsabilidades

- `routes`: declaran endpoints y middleware; no contienen reglas de negocio.
- `Controllers`: coordinan la solicitud, servicios y respuesta.
- `FormRequests`: autorizan la operación y validan sus datos.
- `Policies`: comprueban rol y propiedad del recurso enlazado.
- `Services`: concentran checkout, estados, comisiones, imágenes, planes y administración.
- `Models`: relaciones, casts, fillable y consultas Eloquent.
- `Enums`: roles y estados válidos de pedidos y comisiones.
- `Blade`: presenta datos ya autorizados; Vite centraliza CSS y JavaScript.

## Integridad y seguridad

- Checkout y cambios de estado usan transacciones; los registros críticos se bloquean con `lockForUpdate`.
- El precio, subtotal, identidad del cliente, plan y comisión se calculan o consultan en servidor.
- La combinación `checkout_token + emprendimiento_id` y la comisión única por pedido evitan duplicados.
- Las imágenes se almacenan en el disco público mediante rutas generadas por Laravel; si falla la persistencia se elimina el archivo nuevo, y el anterior se elimina solo tras guardar correctamente.
- Los pedidos conservan snapshots del plan y porcentaje aplicados.
- SoftDeletes conserva entidades que participan en el historial.

## Interfaz

Hay un layout público y un layout administrativo basado en AdminLTE. Ambos reutilizan componentes Blade, Bootstrap e identidad visual azul/blanco con foco visible, contraste y adaptación responsive.
