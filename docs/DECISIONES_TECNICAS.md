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

Estado: pendiente de cierre.

El Diccionario referencia usuarios, pero no define completamente su tabla.

Laravel incluye la tabla `users`.

Propuesta que debe revisarse:

- Mantener `users`.
- Agregar un campo `role`.
- Utilizar valores acordados para administrador y emprendedor.
- Agregar un campo de estado de cuenta.
- Conectar `emprendimientos.usuario_id` con la tabla de usuarios adoptada.
- Definir la restricción de un emprendimiento por usuario cuando se confirme esa regla.

Responsables: integrantes 1, 2 y 3.

El acuerdo debe reflejarse en migraciones, modelos, middleware, Policies, seeders y documentación.

## DT-04 — Transiciones de pedidos

Estado: pendiente de matriz aprobada.

Estados presentes en el Diccionario:

- Pendiente.
- Confirmado.
- En preparación.
- Listo.
- Entregado.
- Cancelado.

Debe definirse:

- Qué transiciones están permitidas.
- Desde qué estados puede cancelarse.
- Si Entregado es un estado terminal.
- Qué debe ocurrir ante un reintento del mismo cambio.
- Qué información se registra en el historial.

No asumir que todas las combinaciones son válidas.

Responsables: integrantes 1 y 5.

## DT-05 — Generación y cancelación de comisiones

Estado: parcialmente confirmado.

El Diccionario indica:

- `comision_plataforma` se calcula al entregar.
- La comisión se registra cuando el pedido pasa a Entregado.

Reglas confirmadas para la implementación:

- Generar una sola comisión por pedido.
- Conservar monto base y porcentaje aplicado.
- Conservar el resultado histórico.
- Evitar recalcular una comisión pasada usando un porcentaje nuevo.

Pendiente:

- Precisar el tratamiento de una cancelación según la etapa.
- Resolver su relación con las transiciones permitidas.
- Definir cuándo corresponde conservar, anular o mantener pendiente una comisión.

No inventar ese comportamiento a partir del nombre de un estado.

Responsables: integrantes 1, 2 y 5.

## DT-06 — Datos del cliente

Estado: parcialmente definido.

El Diccionario establece:

- Nombre del cliente.
- Teléfono del cliente.
- Modalidad de entrega.
- Dirección cuando corresponda.

Correo, notas u otros campos deben justificarse y registrarse antes de ampliar el esquema.

Responsables: integrantes 2 y 5.

## DT-07 — Identificación pública y reintentos de pedidos

Estado: pendiente de contrato técnico.

Los requerimientos exigen:

- Identificador único de pedido.
- Confirmación.
- Consulta de estado.
- Prevención de duplicados ante reenvíos.

Debe definirse:

- Formato del identificador público.
- Información que puede exponerse en la consulta pública.
- Mecanismo para evitar enumeración de pedidos.
- Campo o mecanismo de idempotencia.
- Restricciones e índices necesarios.

Responsables: integrantes 2, 3 y 5.

## DT-08 — Restricciones adicionales

Estado: pendiente de cierre.

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
