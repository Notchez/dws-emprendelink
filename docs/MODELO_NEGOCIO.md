# Modelo de negocio

## Planes y emprendimientos

Cada emprendedor posee como máximo un emprendimiento, garantizado por una restricción única sobre `emprendimientos.usuario_id`. Una suscripción activa enlaza el negocio con un plan. El plan determina el límite de productos activos y el porcentaje de comisión; un producto inactivo no consume el límite.

## Catálogo e imágenes

Solo se publican productos activos cuyo negocio, propietario y categoría opcional también estén activos. Productos y logos guardan una ruta relativa; los archivos viven en `storage/app/public`. Desactivar o aplicar SoftDelete no elimina inmediatamente la imagen.

## Carrito y checkout

El carrito reside en sesión y puede mezclar productos de varios negocios. Al confirmar:

1. se vuelve a validar disponibilidad;
2. se bloquean y leen precios actuales desde MySQL;
3. se crea un pedido por emprendimiento;
4. se crean detalles con precio y subtotal calculados en servidor;
5. se copian nombre, correo y teléfono del usuario autenticado;
6. se conserva la dirección específica del pedido;
7. se guarda el plan y porcentaje aplicados como snapshot histórico;
8. se vacía el carrito solo después del éxito total.

Todo ocurre en una transacción. El token de checkout hace idempotente un reintento.

## Ciclo del pedido

```text
Pendiente -> Confirmado -> En preparación -> Listo -> Entregado
     |            |               |           |
     +------------+---------------+-----------+-> Cancelado
```

`Entregado` y `Cancelado` son terminales. Cada cambio válido registra estado anterior, estado nuevo y actor en `historial_estados`.

## Comisiones

La comisión se crea una sola vez cuando el pedido llega a `Entregado`:

```text
total_comision = monto_base * porcentaje_aplicado / 100
```

Cada comisión conserva el monto base y el porcentaje históricos de un pedido. Es un detalle contable interno, no una factura ni una unidad de pago.

Al crearla se recalcula el `EstadoCuenta` del emprendimiento para el mes de `comisiones.created_at`. El total mensual siempre se obtiene sumando las comisiones de ese período, por lo que un reintento no duplica el cargo.

`EstadoCuenta` representa la obligación mensual completa. El admin solo puede marcar como pagado un período anterior al inicio del mes actual. La operación bloquea el estado de cuenta, recalcula su total y, en una misma transacción, marca como pagadas todas sus comisiones y registra la fecha de pago. No existen pagos parciales, pagos por pedido, anulaciones ni reapertura de meses pagados.

El snapshot de plan, nombre y porcentaje debe existir desde checkout. Si falta o es inválido, el pedido no puede pasar de `Listo` a `Entregado` y no se genera historial, comisión ni estado de cuenta.
