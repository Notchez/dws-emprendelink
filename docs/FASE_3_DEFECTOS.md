# EmprendeLink — Registro de observaciones y defectos

**Materia:** DWS901
**Fase:** 3 — Sprint III
**Proyecto:** EmprendeLink
**Product Backlog:** Trello

## 1. Objetivo

Registrar errores reales y observaciones recibidas durante el desarrollo, integración y pruebas de Fase 3.

Cada defecto debe relacionarse con la funcionalidad afectada y su corrección.

No se registrarán defectos inventados.

## 2. Prioridades

### P0 — Crítico

- Pérdida o corrupción de datos.
- Acceso no autorizado.
- Fallo en un proceso esencial.
- Problema que bloquea la Release Candidate.

### P1 — Alto

- Error importante en una funcionalidad.
- Cálculos incorrectos.
- Fallos que afectan significativamente el uso del sistema.

### P2 — Medio o bajo

- Defectos visuales menores.
- Problemas de usabilidad.
- Mejoras no críticas.

## 3. Estados

- Nuevo.
- Confirmado.
- En progreso.
- Resuelto.
- Cerrado.
- No reproducible.

## 4. Registro de defectos

| ID | Historia Fase 3 | Módulo | Descripción confirmada | Prioridad | Responsable | Estado | Evidencia real |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DEF-001 | DWS-F3-05 | Reportes administrativos | No existían indicadores, filtros, gráficos ni exportaciones globales para ADMIN. | P1 | Cierre de integración | Resuelto | `AdminReportTest`: 6 pruebas, 58 aserciones aprobadas. |
| DEF-002 | DWS-F3-03 | Categorías | Los modales estaban insertados directamente dentro de `tbody`, produciendo HTML inválido, y usaban iconos Font Awesome no cargados. | P1 | Cierre de integración | Resuelto | Modales fuera de la tabla en `resources/views/entrepreneur/categories.blade.php`; Bootstrap Icons. |
| DEF-003 | DWS-F3-03 | Negocio | El formulario invocaba `formatPhone()` inexistente y enviaba `codigo_area`, campo que el backend no acepta. | P1 | Cierre de integración | Resuelto | `resources/views/entrepreneur/business.blade.php`; contrato alineado con `UpdateBusinessRequest`. |
| DEF-004 | DWS-F3-03 | Formularios | `maxlength=255` contradecía la validación Laravel de 2000 caracteres para descripciones de negocio y producto. | P1 | Cierre de integración | Resuelto | Límites de interfaz alineados a 2000 y contador compartido en `resources/js/adminlte.js`. |
| DEF-005 | DWS-F3-03 | Categorías | Desvincular productos y eliminar la categoría se ejecutaba sin transacción. | P1 | Cierre de integración | Resuelto | `CategoryManagementService::delete()` usa transacción y bloqueo; prueba de catálogo aprobada. |
| DEF-006 | DWS-F3-03/04 | Productos e historial | El CRUD no exponía eliminación y una eliminación lógica podía ocultar el producto al mostrar pedidos históricos. | P1 | Cierre de integración | Resuelto | Ruta DELETE, Policy, servicio con SoftDeletes y relación `DetallePedido::producto()->withTrashed()`; prueba aprobada. |
| DEF-007 | DWS-F3-06 | Reportes del emprendedor | Faltaba el filtro por estado requerido por el plan de Fase 3. | P1 | Cierre de integración | Resuelto | `EntrepreneurReportStatusFilterTest`: 3 pruebas aprobadas; suite existente de reportes: 42 pruebas, 414 aserciones. |
| DEF-008 | DWS-F3-08 | Dependencias PHP | El lockfile contenía siete avisos sobre cuatro componentes Symfony. | P0 | Cierre de integración | Resuelto | Parches Symfony 7.4.23/8.1.11; `composer audit` posterior sin avisos. |
| DEF-009 | DWS-F3-08 | Dependencias frontend | npm reportaba 5 vulnerabilidades: 3 altas y 2 críticas. | P0 | Cierre de integración | Resuelto | Vite 6.4.5, concurrently 9.2.5 y overrides transitivos; `npm audit` posterior: 0 vulnerabilidades. |
| DEF-010 | DWS-F3-02/04 | Documentación | AGENTS y LLM Guide contradecían la implementación vigente al permitir checkout sin cuenta. | P1 | Cierre de integración | Resuelto | Documentación alineada: catálogo público y carrito/checkout solo para CLIENTE autenticado. |

Los identificadores utilizan el formato:

DEF-001, DEF-002, DEF-003, etc. Los IDs DWS-F3 son referencias provisionales de `FASE_3_PLAN.md`; no se afirma una vinculación a Trello.

## 5. Registro de un defecto

Cada defecto debe indicar:

1. Qué sucede.
2. Qué debería suceder.
3. Cómo reproducirlo.
4. Qué datos se utilizaron.
5. Qué módulo está afectado.
6. Qué historia corresponde.
7. Qué prioridad tiene.
8. Quién realizará la corrección.
9. Qué cambio resolvió el problema.
10. Qué evidencia confirma la solución.

## 6. Proceso de corrección

1. Identificar el problema.
2. Reproducirlo.
3. Registrar la incidencia.
4. Relacionarla con Trello.
5. Asignar prioridad y responsable.
6. Implementar la corrección.
7. Ejecutar nuevamente la prueba.
8. Comprobar que no se afectaron otros módulos.
9. Guardar evidencia.
10. Actualizar el estado del defecto.

No se exige aprobación manual obligatoria de otro integrante para cada PR.

Se mantienen los controles automáticos de GitHub Actions.

## 7. Observaciones del docente — Fase 2

Estado: pendiente de confirmar.

Cuando existan observaciones oficiales, registrar:

- Comentario recibido.
- Módulo relacionado.
- Historia afectada.
- Acción correctiva.
- Responsable.
- Evidencia de solución.

No se deben inventar observaciones del docente.

## 8. Cierre de un defecto

- [ ] Problema identificado.
- [ ] Pasos de reproducción registrados.
- [ ] Corrección implementada.
- [ ] Prueba repetida satisfactoriamente.
- [ ] Sin regresiones conocidas.
- [ ] Evidencia disponible.
- [ ] Estado actualizado.

## 9. Pendientes para Sprint IV

Los defectos no críticos que queden pendientes podrán registrarse aquí con una justificación y referencia a Trello.

Ningún defecto P0 podrá considerarse un pendiente aceptable de la Release Candidate.
