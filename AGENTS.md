# Instrucciones para asistentes de código

## Contexto vigente

EmprendeLink es una aplicación Laravel 12 integrada como Release Candidate
`v1.0.0-rc.1`. Usa PHP `^8.4.1`, MySQL 8, Blade, AdminLTE 4, Bootstrap, Chart.js y
Vite. La RC corresponde al commit `3ed062322a023e246546ed9a5b6e733fd3954042`.

El catálogo es público. El carrito, checkout y seguimiento de pedidos requieren una
cuenta activa con rol CLIENTE.

Antes de modificar código, consultar `README.md`, `docs/ARQUITECTURA.md`,
`docs/MODELO_NEGOCIO.md`, `docs/DECISIONES_TECNICAS.md` y
`docs/ROLES_PERMISOS.md`. Los documentos identificados como Fase 2 son históricos; la
implementación y la documentación vigente prevalecen cuando describen un contrato más
reciente.

## Arquitectura

- Las rutas declaran endpoints y middleware.
- Los controladores coordinan solicitudes y respuestas.
- Los Form Requests validan y autorizan entradas.
- Las Policies y consultas restringidas protegen propiedad y rol.
- Los servicios concentran reglas, cálculos y transacciones.
- Los modelos representan relaciones, casts y consultas Eloquent.
- Las vistas Blade presentan datos ya autorizados.

No duplicar reglas de negocio entre controladores, vistas y JavaScript. Buscar una
implementación existente antes de crear otra clase o contrato.

## Contratos funcionales

- Roles: `admin`, `emprendedor` y `cliente`.
- Un usuario emprendedor posee como máximo un emprendimiento.
- El registro público crea clientes; administración crea emprendedores con negocio y
  plan dentro de una transacción.
- Un carrito multi-negocio genera un pedido por emprendimiento en una sola transacción.
- Los precios, subtotales, plan y porcentaje de comisión se determinan en servidor.
- El checkout es idempotente y conserva snapshots históricos.
- Las transiciones son `Pendiente -> Confirmado/Cancelado`,
  `Confirmado -> En preparación/Cancelado`, `En preparación -> Listo/Cancelado` y
  `Listo -> Entregado/Cancelado`.
- `Entregado` y `Cancelado` son terminales.
- Un pedido entregado genera una sola comisión. Los pagos se realizan por estado de
  cuenta mensual cerrado, nunca por comisión individual.
- Productos y categorías usan eliminación lógica cuando corresponde; el historial de
  pedidos conserva productos eliminados.

## Seguridad y datos

- Mantener autenticación, middleware de cuenta activa, roles, Policies y CSRF.
- No confiar en identificadores de propiedad enviados por el navegador.
- Validar entradas antes de procesarlas y usar hashing de Laravel.
- Usar transacciones y bloqueos para escrituras relacionadas.
- No registrar secretos ni datos personales en logs, fixtures o documentación.
- No versionar `.env`, tokens, credenciales reales, `vendor`, `node_modules`,
  compilados ni archivos generados.
- Ejecutar pruebas solo sobre una base MySQL cuyo nombre termine en `_test` o
  `_testing`.
- No ejecutar `migrate:fresh`, `migrate:refresh` ni `db:wipe` sobre bases con datos que
  deban conservarse.

## Dependencias e interfaz

- Gestionar PHP con Composer y frontend con npm; mantener ambos lockfiles sincronizados.
- Centralizar AdminLTE, Bootstrap, estilos y JavaScript compartidos mediante Vite.
- Mantener la paleta, tipografía, contraste, foco visible y navegación por teclado.
- No incorporar otro framework visual sin autorización.
- Los indicadores y reportes usan datos reales de MySQL.

## Flujo Git

- No trabajar directamente en `main` ni `develop`.
- Crear ramas desde `develop` actualizado y preparar Pull Requests hacia `develop`.
- Usar Conventional Commits con descripción en español.
- No publicar, fusionar, etiquetar ni eliminar ramas sin autorización explícita.
- Preservar cambios locales ajenos.

## Protocolo de trabajo

1. Inspeccionar Git y los archivos relacionados.
2. Confirmar los contratos vigentes y el alcance autorizado.
3. Realizar cambios pequeños dentro del módulo afectado.
4. Añadir pruebas para comportamientos críticos cuando cambie lógica.
5. Ejecutar verificaciones proporcionales al riesgo.
6. Informar archivos, resultados reales, limitaciones y pasos manuales necesarios.

Nunca declarar que una prueba pasó si no fue ejecutada. Una limitación material debe
explicarse de forma descriptiva; no debe ocultarse ni presentarse como resultado
aprobado.
