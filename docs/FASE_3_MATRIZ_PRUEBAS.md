# EmprendeLink — Matriz de pruebas de integración (Sprint III)

> Documento inicial. Los casos están **PENDIENTES DE EJECUCIÓN**; no deben marcarse aprobados sin prueba reproducible y evidencia real. Completa cada fila con evidencia (captura, log, referencia de PR o ejecución CI). La cobertura automatizada existente permanece en `docs/MATRIZ_PRUEBAS.md`.

**Entorno:** Laravel 12 + MySQL, vistas Blade/AdminLTE. Usar datos y base de prueba; nunca apuntar PHPUnit a la base de desarrollo.

| ID | Perfil / área | Flujo / caso | Resultado esperado | Estado | Responsable | Evidencia / incidencia |
|---|---|---|---|---|---|---|
| INT-01 | Público | Navegar catálogo, búsqueda y filtros | Productos correctos, filtros coherentes | Pendiente | 4 | — |
| INT-02 | Cliente | Registro/login/logout | Sesiones y mensajes correctos | Pendiente | 3 | — |
| INT-03 | Cliente | Perfil: datos válidos/invalidos | Guarda válidos, rechaza inválidos | Pendiente | 3 | — |
| INT-04 | Cliente | Carrito → checkout → confirmación | Pedido y detalles persistidos, total del servidor | Pendiente | 5 | — |
| INT-05 | Cliente | Reintento del checkout | No duplica pedidos | Pendiente | 5 | — |
| INT-06 | Cliente | Historial y detalle de pedidos | Solo pedidos del cliente | Pendiente | 5 | — |
| INT-07 | Emprendedor | Crear/editar categorías | Datos persistidos y ajenos protegidos | Pendiente | 4 | — |
| INT-08 | Emprendedor | Productos/archivos/estados | Imagen accesible, MIME validado, límite plan aplicado | Pendiente | 4 | — |
| INT-09 | Emprendedor | Pedidos y transición inválida | Solo propios; transición inválida bloqueada | Pendiente | 5 | — |
| INT-10 | Emprendedor | Ventas/comisiones mensuales | Datos reales y totales correctos | Pendiente | 2 y 5 | — |
| INT-11 | Administrador | Usuarios/planes/negocios | Acciones y restricciones coherentes | Pendiente | 6 | — |
| INT-12 | Administrador | Estados de cuenta y cobro | Solo meses cerrados, sin duplicar pagos | Pendiente | 6 | — |
| INT-13 | Seguridad | Acceso cruzado y cuenta suspendida | Denegación de permisos y rutas | Pendiente | 3 | — |
| INT-14 | Reportes | Filtros, métricas y gráficos | Consulta correcta, cifras comparables con MySQL | Pendiente | 2 y 6 | — |
| INT-15 | Reportes | Exportación PDF/Excel | Archivo descargable con filtro y total correctos | Pendiente | 6 | — |
| INT-16 | Compatibilidad | Desktop/móvil y navegadores | Sin desbordes ni bloqueo de acciones | Pendiente | Todos; coordina 6 | — |
| INT-17 | Integración | Suite/CI + instalación limpia | CI verde y flujo instalable | Pendiente | 1 | — |

## Plantilla de ejecución de un caso

- **ID / requisito / historia:**
- **Fecha, ejecutor y entorno:**
- **Precondiciones / datos:**
- **Pasos reproducibles:**
- **Resultado esperado:**
- **Resultado real:**
- **Estado:** Pendiente / Aprobado / Fallido / Bloqueado
- **Evidencia:** URL, captura o log
- **Incidencia relacionada:** `DEF-###` o ninguna

## Cierre para RC

- [ ] 100% de flujos críticos ejecutados.
- [ ] Ningún defecto crítico sin resolver.
- [ ] Pruebas y pendientes trazables por requerimiento.
- [ ] Capturas de reportes, gráficos, exportación y roles listas para PDF.
