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

El registro conserva monto base y porcentaje históricos. Su estado inicia `pendiente`; únicamente el admin puede pasarla a `pagada` o `anulada`. Al pagar se registra `pagada_at`. Ambos estados finales son terminales. El emprendedor solo consulta las comisiones de su negocio.
