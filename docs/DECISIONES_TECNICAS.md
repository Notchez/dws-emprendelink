# Decisiones técnicas

Este registro conserva las decisiones originadas en Fase 2 y consolidadas durante Fase
3. Las referencias a contratos abiertos se describen como historia; no representan
trabajo vigente sin implementar.

## DT-01 — Plantilla visual

AdminLTE 4 se integra con Blade y Vite. Los layouts, navegación, formularios, tablas,
mensajes y estados vacíos reutilizan estilos compartidos.

## DT-02 — Paleta y tipografía

La interfaz utiliza Inter y los tokens del Documento Maestro: primario `#2563EB`,
primario claro `#EFF6FF`, texto `#111827`, texto secundario `#6B7280`, borde
`#D1D5DB`, fondo `#F8FAFC` y superficie `#FFFFFF`.

## DT-03 — Usuarios, roles y suspensión

La aplicación conserva la tabla `users`, con roles `admin`, `emprendedor` y `cliente`
mediante un backed Enum. `estado` bloquea cuentas suspendidas incluso con una sesión
existente. `emprendimientos.usuario_id` limita a un negocio por usuario.

## DT-04 — Transiciones de pedidos

La matriz implementada es:

```text
Pendiente -> Confirmado o Cancelado
Confirmado -> En preparación o Cancelado
En preparación -> Listo o Cancelado
Listo -> Entregado o Cancelado
```

`Entregado` y `Cancelado` son terminales. Cada transición registra estado anterior,
estado nuevo, actor y fecha.

## DT-05 — Comisiones y cierre mensual

Un pedido genera una sola comisión al pasar a `Entregado`. La comisión conserva monto
base, porcentaje y total históricos. `EstadoCuenta` consolida las comisiones de un
emprendimiento por mes; el administrador paga el período completo una vez cerrado. No
existen pagos parciales, pagos por pedido, anulaciones ni reapertura de meses pagados.

## DT-06 — Datos del cliente

El pedido pertenece al cliente autenticado y conserva snapshots de nombre, teléfono,
correo, modalidad, dirección y notas. El catálogo es público, pero carrito, checkout y
consulta de pedidos requieren una cuenta CLIENTE activa.

## DT-07 — Identificación e idempotencia

Los pedidos usan UUID y tokens para identificación e idempotencia. La referencia visible
es `Pedido #ID`; cada consulta privada se protege con autenticación y Policies. Un
carrito multi-negocio produce un pedido por emprendimiento en una sola transacción.

## DT-08 — Restricciones de integridad

MySQL garantiza negocio único por usuario, comisión única por pedido, identificadores
únicos y combinaciones de checkout que evitan duplicados. Las claves foráneas y el
borrado lógico preservan el historial.

## DT-09 — Reconstrucción de MySQL

La propuesta histórica de entregar un dump fue sustituida por un mecanismo reproducible:
las migraciones son la fuente oficial del esquema y los seeders contienen datos
sintéticos. La RC no declara un respaldo SQL externo como evidencia validada.

## DT-10 — Indicadores y gráficos

Los indicadores consultan datos reales. Fase 3 incorporó Chart.js para pedidos por
estado, ventas, productos y distribución de planes, con tablas equivalentes para
lectura exacta y estados vacíos controlados.

## DT-11 — Trazabilidad de acuerdos

Los cambios de contrato compartido se registran en documentación y se relacionan con
el código, las pruebas y los Pull Requests que los materializan.

## DT-12 — Contratos funcionales consolidados

El 16 de octubre de 2026 se aprobaron los contratos que cerraron las decisiones de
usuarios, pedidos y comisiones:

- el registro público crea clientes;
- administración crea emprendedores, negocio y plan en una transacción;
- el checkout autentica al cliente y conserva datos de contacto;
- precios, subtotales, plan y porcentaje se calculan en servidor;
- los snapshots históricos son obligatorios antes de entregar;
- una transición inválida no crea historial ni comisión;
- el reintento de checkout o comisión no duplica registros.

## DT-13 — Exportaciones PDF y XLSX

El 29 de octubre de 2026 se incorporaron `barryvdh/laravel-dompdf` y
`phpoffice/phpspreadsheet`. Pantalla y exportaciones reutilizan el mismo resultado del
servicio de reportes. Las cadenas controladas por usuarios se escriben como texto en
XLSX para evitar inyección de fórmulas. PHP `^8.4.1` y las extensiones de plataforma
requeridas quedaron declaradas y verificadas en CI.

## Resultado de cierre

Los contratos fueron integrados mediante los PR #21 y #22. La Release Candidate
`v1.0.0-rc.1` corresponde al commit
`3ed062322a023e246546ed9a5b6e733fd3954042` y fue validada por GitHub Actions #50.
