# Decisiones técnicas — Fase 2

Este documento registra acuerdos y asuntos pendientes que afectan contratos compartidos.

Una propuesta no debe implementarse como si ya estuviera aprobada.

## DT-01 — Plantilla visual

Estado: acordado.

Usar AdminLTE 4 con Blade y Vite.

Mantener un layout compartido y componentes reutilizables para formularios, tablas, navegación, mensajes y estados vacíos.

## DT-02 — Paleta y tipografía

Estado: confirmado en el Documento Maestro.

Referencia visual: páginas 44 a 61.

Paleta de la página 44:

| Uso              | Valor     |
| ---------------- | --------- |
| Primario         | `#2563EB` |
| Primario claro   | `#EFF6FF` |
| Texto principal  | `#111827` |
| Texto secundario | `#6B7280` |
| Bordes           | `#D1D5DB` |
| Fondo            | `#F8FAFC` |
| Superficies      | `#FFFFFF` |

Tipografía: Inter.

Centralizar los estilos en `resources/css/adminlte.css`.

## DT-03 — Usuarios, roles y suspensión

Estado: resuelto por DT-12.

El Diccionario referencia usuarios, pero no define completamente su tabla.

Laravel incluye la tabla `users`.

Decisión implementada:

- Mantener `users`.
- Agregar el campo `rol` con Enum para `admin`, `emprendedor` y `cliente`.
- Agregar un campo de estado de cuenta.
- Conectar `emprendimientos.usuario_id` con la tabla de usuarios adoptada.
- Restringir a un emprendimiento por usuario.

Responsables: integrantes 1, 2 y 3.

El acuerdo debe reflejarse en migraciones, modelos, middleware, Policies, seeders y documentación.

## DT-04 — Transiciones de pedidos

Estado: resuelto por DT-12.

Estados presentes en el Diccionario:

- Pendiente.
- Confirmado.
- En preparación.
- Listo.
- Entregado.
- Cancelado.

La matriz aprobada define:

- Qué transiciones están permitidas.
- Desde qué estados puede cancelarse.
- Si Entregado es un estado terminal.
- Qué debe ocurrir ante un reintento del mismo cambio.
- Qué información se registra en el historial.

No asumir que todas las combinaciones son válidas.

Responsables: integrantes 1 y 5.

## DT-05 — Generación y cancelación de comisiones

Estado: resuelto por DT-12.

El Diccionario indica:

- `comision_plataforma` se calcula al entregar.
- La comisión se registra cuando el pedido pasa a Entregado.

Reglas confirmadas para la implementación:

- Generar una sola comisión por pedido.
- Conservar monto base y porcentaje aplicado.
- Conservar el resultado histórico.
- Evitar recalcular una comisión pasada usando un porcentaje nuevo.

Decisión aplicada:

- Solo se cancela antes de Entregado.
- Entregado es terminal y genera una comisión pendiente.
- El admin puede pagar o anular una comisión; ambos resultados son terminales.

No inventar ese comportamiento a partir del nombre de un estado.

Responsables: integrantes 1, 2 y 5.

## DT-06 — Datos del cliente

Estado: resuelto por DT-12.

El Diccionario establece:

- Nombre del cliente.
- Teléfono del cliente.
- Modalidad de entrega.
- Dirección cuando corresponda.

Se conservan correo y notas como snapshots del pedido, según DT-12.

Responsables: integrantes 2 y 5.

## DT-07 — Identificación pública y reintentos de pedidos

Estado: resuelto por DT-12.

Los requerimientos exigen:

- Identificador único de pedido.
- Confirmación.
- Consulta de estado.
- Prevención de duplicados ante reenvíos.

Contrato implementado:

- Formato del identificador público.
- Información que puede exponerse en la consulta pública.
- Mecanismo para evitar enumeración de pedidos.
- Campo o mecanismo de idempotencia.
- Restricciones e índices necesarios.

Responsables: integrantes 2, 3 y 5.

## DT-08 — Restricciones adicionales

Estado: resuelto por DT-12.

Revisar restricciones para:

- Unicidad de comisión por pedido.
- Relación usuario-emprendimiento.
- Suscripción activa aplicable.
- Identificador público del pedido.
- Prevención de solicitudes duplicadas.

Las restricciones deben responder a reglas del proyecto y quedar documentadas.

Responsables: integrantes 1 y 2, con el dueño de cada módulo.

## DT-09 — Script SQL y recuperación

Estado: acordado como entregable.

El Integrante 2 entregará:

- Script de creación o respaldo MySQL.
- Datos de demostración sintéticos.
- Instrucciones de importación.
- Verificación de correspondencia con las migraciones.

El respaldo no debe contener información personal real ni secretos.

## DT-10 — Indicadores y gráficos

Estado: alcance visual acordado.

Preparar la estructura de AdminLTE durante Fase 2.

Los indicadores funcionales deben consultar datos reales.

Los gráficos, reportes y exportaciones se implementarán cuando estén priorizados y sus fuentes de datos estén disponibles.

## DT-11 — Registro de cambios

Cada nuevo acuerdo debe incluir:

- Identificador.
- Tema.
- Estado.
- Decisión.
- Motivo.
- Responsables.
- Archivos o módulos afectados.
- Fecha de confirmación.

Mantener trazabilidad cuando una decisión cambie.

## DT-12 — Contratos funcionales de la reconstrucción

Estado: aprobado para la implementación de la rama `feature/reconstruccion-funcional`.

Decisión confirmada el 16 de octubre de 2026:

- Mantener la tabla `users` y los roles `admin`, `emprendedor` y `cliente` mediante un backed Enum.
- Usar `estado` para bloquear cuentas suspendidas, incluso cuando ya exista una sesión.
- Permitir como máximo un emprendimiento por usuario emprendedor mediante la restricción única de `emprendimientos.usuario_id`.
- El registro público crea solamente clientes; los emprendedores son creados por administración junto con su negocio y plan dentro de una transacción.
- Asociar cada pedido al cliente autenticado y conservar snapshots de sus datos de contacto.
- Un carrito con varios negocios produce un pedido por emprendimiento dentro de una sola transacción.
- Aplicar la matriz `Pendiente -> Confirmado/Cancelado`, `Confirmado -> En preparación/Cancelado`, `En preparación -> Listo/Cancelado`, `Listo -> Entregado/Cancelado`; Entregado y Cancelado son terminales.
- Generar una sola comisión al llegar a Entregado. Al ser Entregado terminal, no se admite una cancelación posterior que requiera anular la comisión.
- Conservar en el pedido el plan, nombre y porcentaje aplicados para evitar recálculos retroactivos.
- Mantener UUID y tokens para garantías técnicas e idempotencia; mostrar `Pedido #ID` como referencia visible y proteger cada consulta mediante autenticación y Policies.

Motivo: estas reglas fueron autorizadas expresamente para completar el flujo de los cuatro actores y cerrar los contratos que figuraban como pendientes en DT-03 a DT-08.

Módulos afectados: usuarios, autenticación, emprendimientos, carrito, pedidos, historial, comisiones, migraciones, middleware, Policies y seeders.
