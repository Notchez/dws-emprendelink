# EmprendeLink — Fase 3: Integración Full Stack y Release Candidate

**Materia:** DWS901
**Proyecto:** EmprendeLink
**Entrega objetivo:** 8 de noviembre de 2026
**Ponderación:** 25%
**Repositorio:** https://github.com/Notchez/dws-emprendelink
**Rama base:** develop
**Product Backlog:** Trello — EmprendeLink Scrum Backlog

## 1. Objetivo

Completar la integración Full Stack de EmprendeLink utilizando Laravel 12, Blade, AdminLTE y MySQL.

La Fase 3 debe entregar una aplicación funcional, con autenticación, roles, operaciones CRUD, archivos, validaciones, reportes, gráficos, exportaciones y pruebas de integración.

El objetivo es alcanzar el nivel Estratégico de la rúbrica: 9.0–10.0.

## 2. Estado actual

La Fase 2 ya incorporó:

- Autenticación y autorización por roles.
- Administración de usuarios, planes y emprendimientos.
- Gestión de categorías y productos.
- Catálogo público.
- Carrito y checkout.
- Pedidos e historial de estados.
- Ventas y comisiones.
- Persistencia con MySQL.
- Validaciones y reglas de negocio.
- Pruebas automatizadas y GitHub Actions.

Estos módulos deben aprovecharse y completarse cuando sea necesario.

No se reconstruirán funcionalidades existentes sin una justificación técnica.

La rama feature/emprendimientos-productos permanecerá separada y no será integrada automáticamente.

## 3. Product Backlog

Herramienta oficial: Trello.

Tablero: EmprendeLink Scrum Backlog.

El enlace normal de visualización del tablero está pendiente de confirmar. No publicar enlaces privados de invitación.

### Responsable adicional

El integrante que creó originalmente el tablero será responsable de mantenerlo actualizado.

Nombre: pendiente de confirmar.

Responsabilidades:

- Actualizar historias y tareas.
- Registrar prioridades.
- Asignar responsables.
- Mantener estados de las historias.
- Evitar tarjetas duplicadas.
- Vincular funcionalidades con commits o PR.
- Registrar evidencias.
- Comprobar que el tablero represente el estado real del proyecto.

Esta responsabilidad será adicional al módulo técnico asignado.

## 4. Historias de Fase 3

Los siguientes identificadores son provisionales.

Se deben vincular con historias existentes de Trello cuando corresponda, evitando duplicarlas.

| ID        | Área                        | Resultado esperado                             |
| --------- | --------------------------- | ---------------------------------------------- |
| DWS-F3-01 | Integración e interfaz      | Flujos completos y diseño responsive           |
| DWS-F3-02 | Seguridad y validaciones    | Roles, permisos y errores controlados          |
| DWS-F3-03 | Catálogo y productos        | CRUD, imágenes, filtros y límites de plan      |
| DWS-F3-04 | Pedidos y comisiones        | Checkout, pedidos, estados y cálculos          |
| DWS-F3-05 | Reportes administrativos    | Indicadores, gráficos y exportaciones          |
| DWS-F3-06 | Reportes del emprendedor    | Ventas, productos, comisiones y gráficos       |
| DWS-F3-07 | Administración              | Usuarios, negocios, planes y estados de cuenta |
| DWS-F3-08 | Calidad y Release Candidate | Pruebas, defectos, evidencia y versión final   |

Los responsables específicos se definirán durante la distribución de trabajo.

## 5. Criterios de aceptación

Cada historia deberá indicar qué condiciones debe cumplir para considerarse terminada.

Como mínimo:

- La funcionalidad realiza la operación esperada.
- Utiliza datos reales de MySQL cuando corresponde.
- Respeta los roles y permisos.
- Valida los datos ingresados.
- Muestra errores comprensibles.
- Funciona desde la interfaz.
- Cuenta con pruebas y evidencia.
- No presenta defectos críticos conocidos.

### Ejemplo: reporte de ventas

Una historia de reporte de ventas estará terminada cuando:

1. El usuario autorizado pueda abrirla.
2. Los datos provengan de MySQL.
3. Los filtros funcionen correctamente.
4. Los totales sean correctos.
5. Los usuarios sin permiso no puedan acceder.
6. Los resultados vacíos se manejen adecuadamente.
7. Las exportaciones coincidan con los resultados mostrados.

## 6. Reportes del administrador

El administrador podrá consultar información general de la plataforma.

### Indicadores

- Pedidos por estado.
- Ventas de pedidos entregados.
- Comisiones generadas.
- Emprendimientos activos.
- Distribución de planes.
- Evolución de ventas.

### Gráficos

- Barras: evolución de ventas.
- Circular: distribución de pedidos por estado.
- Tablas: comisiones, emprendimientos y planes.

### Filtros

- Fecha inicial.
- Fecha final.
- Emprendimiento, cuando corresponda.
- Estado del pedido, cuando corresponda.

## 7. Reportes del emprendedor

Cada emprendedor podrá consultar únicamente información de su negocio.

### Indicadores

- Ventas de pedidos entregados.
- Pedidos por estado.
- Productos más vendidos.
- Comisiones generadas.
- Evolución de ventas.

### Gráficos

- Barras: ventas por período.
- Ranking: productos más vendidos.
- Circular: pedidos por estado.

### Filtros

- Fecha inicial.
- Fecha final.
- Estado del pedido, cuando corresponda.

El servidor deberá determinar qué emprendimiento pertenece al usuario autenticado.

No debe permitirse consultar datos ajenos modificando parámetros de la URL.

## 8. Reglas de cálculo

Los reportes deberán utilizar definiciones consistentes.

### Pedidos

Contar los registros de pedidos y agruparlos por su estado actual.

Los filtros temporales utilizarán inicialmente la fecha de creación del pedido.

### Ventas entregadas

Sumar el subtotal de los pedidos cuyo estado actual sea Entregado.

Si se filtra por fechas de creación, el indicador representa pedidos creados en ese período que actualmente están entregados.

No debe confundirse con ventas efectivamente entregadas durante el período, que requerirían utilizar fechas del historial de estados.

### Comisiones

Sumar los valores históricos registrados en las comisiones.

Distinguir entre comisiones generadas y comisiones pagadas.

No recalcular pedidos históricos utilizando el porcentaje de un plan nuevo.

### Productos más vendidos

Sumar las cantidades de los detalles de pedidos entregados, aplicando los filtros del reporte.

### Distribución de planes

Considerar únicamente las suscripciones o planes vigentes correspondientes.

No contar suscripciones antiguas como si fueran actuales.

### Validación de períodos

- La fecha inicial no puede superar la final.
- El período debe aplicarse de forma consistente.
- Los filtros no deben permitir consultar datos de otro emprendimiento.

## 9. Exportaciones

Se propone generar reportes en:

- PDF.
- Excel (XLSX).

Las exportaciones deberán:

- Utilizar los mismos filtros que la pantalla.
- Mostrar los mismos resultados y totales.
- Respetar los permisos del usuario.
- Incluir títulos y encabezados claros.
- Manejar correctamente períodos sin resultados.

Antes de instalar dependencias, verificar compatibilidad con Laravel y PHP.

## 10. Pruebas de integración

Se utilizará:

docs/FASE_3_MATRIZ_PRUEBAS.md

Cada funcionalidad deberá comprobar:

- Caso positivo.
- Caso negativo.
- Validaciones.
- Permisos.
- Persistencia correcta.
- Mensajes de error.
- Comportamiento responsive, cuando corresponda.

Los resultados deberán registrarse con evidencia real.

## 11. Gestión de defectos

Se utilizará:

docs/FASE_3_DEFECTOS.md

Cada defecto confirmado deberá registrar:

- Identificador.
- Historia relacionada.
- Módulo.
- Descripción.
- Prioridad.
- Responsable.
- Estado.
- Corrección.
- Evidencia.

Las observaciones del docente en Fase 2 deberán incorporarse cuando se conozcan.

## 12. Flujo de integración

1. Trabajar desde ramas creadas a partir de develop.
2. Utilizar Pull Requests hacia develop.
3. Mantener GitHub Actions funcionando.
4. No exigir aprobación manual obligatoria de otro integrante en cada PR.
5. No utilizar push --force en las ramas oficiales.
6. Mantener Trello actualizado.
7. Respetar la arquitectura y los contratos existentes.
8. Registrar los cambios importantes de alcance.

## 13. Definition of Done

Una historia estará terminada cuando:

- [ ] Tiene identificador en Trello.
- [ ] Tiene responsable.
- [ ] Tiene criterios de aceptación.
- [ ] Cumple los criterios definidos.
- [ ] Utiliza datos reales cuando corresponde.
- [ ] Aplica permisos y validaciones.
- [ ] Tiene pruebas positivas y negativas.
- [ ] Cuenta con evidencia.
- [ ] Pasa los controles automáticos aplicables.
- [ ] Está integrada en develop.

## 14. Rúbrica de Fase 3

| Criterio                                   | Peso |
| ------------------------------------------ | ---: |
| Integración Full Stack e interfaz          |  20% |
| Cobertura funcional                        |  25% |
| Validaciones y manejo de errores           |  20% |
| Reportes, gráficos y exportaciones         |  20% |
| Pruebas, Release Candidate y Sprint Review |  15% |

## 15. Entregables

- [ ] Sistema Full Stack integrado.
- [ ] Autenticación y roles funcionales.
- [ ] CRUD y módulos del alcance terminados.
- [ ] Gestión de archivos y validaciones.
- [ ] Reportes, gráficos y exportaciones.
- [ ] Pruebas de integración.
- [ ] Registro de defectos y correcciones.
- [ ] Release Candidate identificable en GitHub.
- [ ] PDF con portada, integrantes y evidencias.
- [ ] Enlace al repositorio.
- [ ] Enlace al Product Backlog de Trello.
- [ ] Demostración de los tres roles.
- [ ] Lista de pendientes para Sprint IV.

## 16. Información pendiente

- Confirmar responsable de Trello.
- Confirmar enlace normal del tablero.
- Vincular historias existentes con los IDs propuestos.
- Registrar observaciones oficiales de Fase 2.
- Confirmar prototipo aprobado.
- Formalizar responsabilidades de los seis integrantes.

No se utilizarán cronogramas internos.

El seguimiento se realizará mediante tareas, responsables, estados, criterios de aceptación y evidencias.
