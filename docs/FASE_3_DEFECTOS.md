# Fase 3 — Defectos confirmados y correcciones

Este registro contiene únicamente problemas observados durante la integración y la
corrección aplicada. P0 identifica riesgos de seguridad, datos o entrega; P1 identifica
requisitos relevantes afectados.

| ID | Módulo | Prioridad | Problema confirmado | Corrección aplicada | Evidencia técnica |
| --- | --- | --- | --- | --- | --- |
| DEF-001 | Reportes administrativos | P1 | No existían indicadores, filtros, gráficos ni exportaciones globales para ADMIN. | Se incorporaron servicio, controlador, validación, vistas, gráficos y exportadores PDF/XLSX restringidos al rol ADMIN. | `AdminReportTest`: 7 pruebas aprobadas. |
| DEF-002 | Categorías | P1 | Los modales estaban dentro de `tbody` y usaban iconos Font Awesome no cargados. | Los modales se movieron fuera de la tabla y se sustituyeron los iconos por Bootstrap Icons. | `CatalogProfileOwnershipTest` valida la estructura HTML y los iconos. |
| DEF-003 | Perfil del negocio | P1 | El formulario invocaba `formatPhone()` inexistente y enviaba `codigo_area`, campo ajeno al backend. | Se eliminó el contrato inexistente y el formulario se alineó con `UpdateBusinessRequest`. | Prueba de contratos de formularios en `CatalogProfileOwnershipTest`. |
| DEF-004 | Formularios | P1 | Los límites HTML de descripción contradecían la validación Laravel de 2000 caracteres. | Los `maxlength` se alinearon y el contador se centralizó en el JavaScript compartido. | Prueba de contratos de formularios en `CatalogProfileOwnershipTest`. |
| DEF-005 | Categorías | P1 | Desvincular productos y eliminar una categoría no era una operación atómica. | `CategoryManagementService::delete()` ejecuta bloqueo, desvinculación y borrado lógico dentro de una transacción. | Prueba de CRUD y relaciones de categorías. |
| DEF-006 | Productos e historial | P1 | El CRUD no exponía eliminación lógica y un producto eliminado podía desaparecer del historial del pedido. | Se añadió ruta DELETE, autorización, SoftDeletes y relación `DetallePedido::producto()->withTrashed()`. | Prueba de eliminación lógica y conservación del historial. |
| DEF-007 | Reportes del emprendedor | P1 | Faltaba el filtro por estado del pedido. | El filtro se incorporó a pantalla, servicio y exportaciones con validación de Enum. | `EntrepreneurReportStatusFilterTest`: 3 pruebas aprobadas. |
| DEF-008 | Dependencias PHP | P0 | El lockfile incluía avisos de seguridad en componentes Symfony. | Se actualizaron los parches compatibles y se conservaron las restricciones del proyecto. | `composer audit` sin avisos en el cierre de Fase 3 y en CI. |
| DEF-009 | Dependencias frontend | P0 | npm reportaba vulnerabilidades altas y críticas en dependencias transitivas. | Se fijaron versiones compatibles de Vite y concurrently y overrides para dependencias transitivas. | `npm audit`: 0 vulnerabilidades; compilación Vite aprobada. |
| DEF-010 | Documentación de acceso | P1 | Documentos antiguos permitían checkout sin cuenta, en contradicción con la implementación. | La documentación se alineó con el contrato vigente: catálogo público y carrito, checkout y pedidos exclusivos para CLIENTE activo. | Pruebas de autorización y carrito para visitante, cliente y otros roles. |

La suite completa de la Release Candidate ejecutó 109 pruebas y 910 aserciones. No se
registraron observaciones docentes sin evidencia ni incidencias hipotéticas.
