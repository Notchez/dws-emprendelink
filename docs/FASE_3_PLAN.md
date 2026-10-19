# EmprendeLink — Fase 3: Integración Full Stack y Release Candidate

**Materia:** DWS901  
**Entrega objetivo:** 8 de noviembre de 2026 (la ruta indica 7 u 8)  
**Ponderación:** 25%  
**Fuente de verdad:** Ruta de Aprendizaje DWS901, pp. 18–23, Documento Maestro y Product Backlog aprobado.  
**Rama base:** `develop`  
**Estado inicial (19-10-2026):** Fase 2 reconstruida y fusionada a `develop` (`bf083902`), con CI satisfactorio en ese commit.

## Objetivo

Entregar un sistema Full Stack completamente integrado entre vistas Blade/AdminLTE, Laravel 12 y MySQL; con autenticación y roles, módulos y flujos completos, archivos, validaciones, reportes, gráficos, exportación y pruebas de integración. Identificar un Release Candidate reproducible en el repositorio.

## Límites

- **Dentro:** observaciones del Sprint II, interfaz coherente con prototipo, módulos acordados, búsqueda/filtros/detalles, imágenes, validaciones cliente/servidor, reportes con datos reales, gráficos, exportación PDF/Excel, pruebas, registro de defectos y RC.
- **Fuera salvo solicitud de cambio aprobada:** pagos en línea, facturación fiscal, chat, app móvil nativa y funcionalidades no previstas en el Documento Maestro.
- **No fusionar automáticamente:** `feature/emprendimientos-productos`; sus commits exclusivos no forman parte de la rama base.

## Estado inicial y prioridades

1. **P0 — Validar requisitos y observaciones:** registrar feedback de Sprint II, ajustar backlog y definir criterios de aceptación comprobables por módulo.
2. **P0 — Cobertura Full Stack:** recorrer flujo administrador, emprendedor y cliente, comprobar CRUD, roles, integridad, archivos y vista responsive.
3. **P0 — Reportes:** implementar métricas y filtros con consultas MySQL reales; graficar y exportar (preferible ambos PDF y Excel para evidencia más fuerte).
4. **P0 — QA/RC:** matriz de integración, defectos trazados, regresión, CI satisfactorio, RC identificable y evidencias para PDF.
5. **P1 — Mejoras visuales y usabilidad:** alinear todas las vistas con el prototipo aprobado, estados vacíos y mensajes.

## Responsables principales (asignación propuesta)

| Integrante | Responsabilidad | Evidencia de terminado |
|---|---|---|
| 1 — Tito | Tech Lead, integración, CI, ramas/PR, release, README y PDF | RC etiquetado, CI verde, documentación y defensa lista |
| 2 | Datos, consultas y agregaciones de reportes | Métricas correctas, consultas filtradas y pruebas |
| 3 | Autenticación, perfiles, permisos y validaciones | Flujo protegido y errores controlados cliente/servidor |
| 4 | Negocio, categorías, productos, catálogo y archivos | CRUD, filtros, imágenes y límites de plan comprobados |
| 5 | Carrito, checkout, pedidos, estados y comisiones | Flujo completo, transaccional e idempotente |
| 6 | Administración, dashboards, exportaciones y coordinación QA | Gráficos/exportación + matriz y defectos actualizados |

**Regla:** cada responsable prueba su módulo; QA no reemplaza la prueba del autor. Cambios sobre servicios, modelos y contratos compartidos se coordinan con sus responsables.

## Criterios Estratégico (rúbrica oficial)

| Criterio | Peso | Evidencia mínima objetivo |
|---|---:|---|
| Integración Full Stack e interfaz | 20% | Flujos desde vista hasta MySQL, UX consistente y responsive |
| Cobertura funcional | 25% | Todos los roles/módulos/acciones y excepciones del alcance |
| Validaciones y errores | 20% | Cliente y servidor, permisos y reglas robustas |
| Reportes, gráficos y exportación | 20% | Filtros, métricas correctas, gráficas y PDF/Excel |
| Pruebas, RC y Sprint Review | 15% | Matriz trazable, defectos, CI, RC y defensa colectiva |

## Cronograma de coordinación

| Fechas | Hito | Criterio de salida |
|---|---|---|
| 19–20 oct | Backlog/contratos, observaciones, ramas | Asignaciones y requisitos confirmados |
| 21–26 oct | Integración y módulos | Flujos básicos completos por rol |
| 27 oct–1 nov | Reportes/gráficos/exportación | Indicadores y archivos generados con datos reales |
| 2–4 nov | QA y regresión | Matriz completa y defectos P0 resueltos |
| 5 nov | RC1 | Congelación de alcance, solo correcciones |
| 6 nov | RC final | CI verde, pruebas y evidencia cerradas |
| 7 nov | Ensayo | Demo reproducible por los seis integrantes |
| 8 nov | Sprint Review | Defensa y envío de PDF + enlaces |

## Convenciones de integración

1. Crear ramas `feature/fase3-*` desde `develop` actualizado.
2. Abrir PR hacia `develop`; revisar diferencias y ejecutar checks exigidos.
3. Evitar cambios directos en `main` o `develop`; no usar `push --force`.
4. Registrar cambios de alcance en Product Backlog y decisiones técnicas.
5. Para cada PR documentar requerimiento, captura o demo, casos positivos/negativos, comandos de prueba y defectos conocidos.
6. Antes del RC ejecutar `php artisan test`, `npm run build`, `php artisan route:list`, revisar migraciones y comprobar módulos con MySQL.
7. La rama de respaldo anterior es solo un punto histórico: no desarrollarle encima.

## Definition of Done por historia

- [ ] Referencia a requerimiento e historia de usuario.
- [ ] UI, servidor y MySQL conectados.
- [ ] Validaciones y permisos probados; errores mostrados adecuadamente.
- [ ] Caso positivo y negativo registrados en `FASE_3_MATRIZ_PRUEBAS.md`.
- [ ] CI y compilación sin errores.
- [ ] Compatibilidad responsive comprobada.
- [ ] Documentación/backlog actualizados.
- [ ] PR integrado y demostrable.

## Entregables para Sprint Review

- [ ] Sistema integrado y Release Candidate identificable en GitHub.
- [ ] Autenticación, roles, CRUD, archivos, validaciones, reportes, gráficos y exportaciones.
- [ ] Matriz de pruebas y registro de correcciones.
- [ ] PDF con portada, integrantes, evidencias, repo y enlace al Product Backlog.
- [ ] Demo de flujos de administrador, emprendedor y cliente.
- [ ] Lista honesta de pendientes para Sprint IV.

**Pendiente de completar por el equipo:** nombres de integrantes 2–6, URL del Product Backlog, observaciones recibidas en Sprint II, fecha efectiva de defensa (7 u 8), IDs de historias y prototipo aprobado.
