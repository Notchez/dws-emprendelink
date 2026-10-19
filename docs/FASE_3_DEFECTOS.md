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

| ID  | Historia Trello | Módulo | Descripción                      | Prioridad | Responsable | Estado | PR/commit | Evidencia |
| --- | --------------- | ------ | -------------------------------- | --------- | ----------- | ------ | --------- | --------- |
| —   | —               | —      | Sin defectos registrados todavía | —         | —           | —      | —         | —         |

Los identificadores utilizarán el formato:

DEF-001, DEF-002, DEF-003, etc.

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
