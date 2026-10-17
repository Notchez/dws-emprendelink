# Roles y permisos

Las comprobaciones se realizan en servidor mediante middleware, Form Requests, Policies y consultas restringidas. Ocultar un botón no concede ni revoca permisos.

| Acción | Visitante | Cliente | Emprendedor | Admin |
| --- | :---: | :---: | :---: | :---: |
| Consultar catálogo y detalle público | Sí | Sí | Sí | Sí |
| Registrarse como cliente / iniciar sesión | Sí | No | No | No |
| Usar carrito y checkout | No | Sí | No | No |
| Editar perfil de cliente | No | Propio | No | No |
| Consultar pedidos de cliente | No | Propios | No | Todos |
| Administrar negocio, categorías y productos | No | No | Propios | Negocios según rutas admin |
| Cambiar estado de pedido | No | No | De su negocio | Consulta global |
| Consultar ventas y comisiones | No | No | Propias | Todas |
| Cambiar estado de comisión | No | No | No | Pendiente a pagada/anulada |
| Gestionar usuarios y planes | No | No | No | Sí |

Reglas relevantes:

- `auth` exige sesión, `active` expulsa cuentas suspendidas y `role:*` restringe el área.
- El registro público ignora campos ajenos al contrato y siempre crea un cliente activo.
- Un cliente solo puede abrir pedidos asociados a su `cliente_id`.
- Un emprendedor solo puede modificar recursos cuyo emprendimiento le pertenece.
- El admin no puede suspender su propia cuenta desde el endpoint administrativo.
- Una comisión pagada o anulada es terminal; el emprendedor carece de endpoint de actualización.
