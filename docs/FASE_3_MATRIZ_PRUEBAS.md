# EmprendeLink — Matriz de pruebas de integración

**Materia:** DWS901
**Fase:** 3 — Sprint III
**Proyecto:** EmprendeLink
**Product Backlog:** Trello

## 1. Objetivo

Verificar que las funcionalidades de EmprendeLink trabajan correctamente desde la interfaz hasta Laravel y MySQL.

Se probarán operaciones válidas, operaciones inválidas, restricciones de acceso y reglas de negocio.

Esta matriz complementa las pruebas automatizadas existentes del proyecto.

## 2. Estados

- Pendiente: prueba no ejecutada.
- Aprobado: resultado esperado comprobado.
- Fallido: resultado diferente al esperado.
- Bloqueado: no puede ejecutarse por un problema o dependencia.

Todas las pruebas comienzan pendientes hasta su ejecución real.

## 3. Matriz de integración

Los IDs de historias son provisionales hasta vincularlos con Trello.

| ID     | Historia     | Módulo            | Prueba positiva                      | Prueba negativa                     | Estado    |
| ------ | ------------ | ----------------- | ------------------------------------ | ----------------------------------- | --------- |
| INT-01 | DWS-F3-03    | Catálogo          | Buscar y filtrar productos           | Buscar datos inexistentes           | Pendiente |
| INT-02 | DWS-F3-02    | Autenticación     | Registro, login y logout             | Credenciales inválidas              | Pendiente |
| INT-03 | DWS-F3-02    | Perfil            | Actualizar datos válidos             | Datos inválidos                     | Pendiente |
| INT-04 | DWS-F3-04    | Checkout          | Crear pedido correctamente           | Carrito o datos inválidos           | Pendiente |
| INT-05 | DWS-F3-04    | Checkout          | Procesar envío normal                | Evitar pedidos duplicados           | Pendiente |
| INT-06 | DWS-F3-04    | Pedidos           | Consultar pedidos propios            | Consultar pedidos ajenos            | Pendiente |
| INT-07 | DWS-F3-03    | Categorías        | Crear y editar categorías            | Datos inválidos o ajenos            | Pendiente |
| INT-08 | DWS-F3-03    | Productos         | Crear producto con imagen            | Archivo inválido o límite de plan   | Pendiente |
| INT-09 | DWS-F3-04    | Estados           | Cambio permitido de pedido           | Transición no permitida             | Pendiente |
| INT-10 | DWS-F3-04/06 | Comisiones        | Consultar totales correctos          | Acceso a negocio ajeno              | Pendiente |
| INT-11 | DWS-F3-07    | Administración    | Gestionar usuarios y planes          | Operación no autorizada             | Pendiente |
| INT-12 | DWS-F3-07    | Estados de cuenta | Registrar pago permitido             | Duplicado o período abierto         | Pendiente |
| INT-13 | DWS-F3-02    | Seguridad         | Acceso correcto por rol              | Acceso incorrecto o suspendido      | Pendiente |
| INT-14 | DWS-F3-05/06 | Reportes          | Indicadores y gráficos correctos     | Filtros inválidos o acceso ajeno    | Pendiente |
| INT-15 | DWS-F3-05/06 | Exportaciones     | Descargar PDF y Excel                | Filtros o permisos inválidos        | Pendiente |
| INT-16 | DWS-F3-01    | Responsive        | Flujos en escritorio y móvil         | Desbordes o controles inaccesibles  | Pendiente |
| INT-17 | DWS-F3-08    | Integración       | Pruebas y compilación satisfactorias | Errores de configuración detectados | Pendiente |

## 4. Pruebas positivas

Una prueba positiva verifica el comportamiento correcto de una operación válida.

Ejemplos:

- Registrar un usuario.
- Crear un producto.
- Completar un pedido.
- Consultar un reporte.
- Descargar un archivo.

## 5. Pruebas negativas

Una prueba negativa verifica que el sistema rechaza o controla operaciones incorrectas.

Ejemplos:

- Ingresar datos inválidos.
- Subir archivos no permitidos.
- Acceder a recursos ajenos.
- Repetir operaciones que no deben duplicarse.
- Modificar pedidos con transiciones prohibidas.
- Consultar reportes sin autorización.

## 6. Evidencias

Cada caso ejecutado debe registrar:

- ID de prueba.
- Historia relacionada.
- Responsable.
- Entorno.
- Pasos realizados.
- Resultado esperado.
- Resultado obtenido.
- Estado.
- Evidencia.
- Defecto asociado, cuando exista.

Se aceptan:

- Capturas de pantalla.
- Resultados de PHPUnit.
- GitHub Actions.
- Logs.
- Referencias a commits o PR.

No deben declararse pruebas aprobadas sin ejecución.

## 7. Plantilla de ejecución

**ID:**

**Historia Trello:**

**Responsable:**

**Entorno:**

**Precondiciones:**

**Pasos ejecutados:**

**Resultado esperado:**

**Resultado obtenido:**

**Estado:**

**Evidencia:**

**Defecto relacionado:**

## 8. Pruebas específicas de reportes

Verificar:

- Totales comparados con MySQL.
- Filtros por período.
- Manejo de fechas inválidas.
- Información restringida por emprendimiento.
- Resultados vacíos.
- Cálculos de comisiones.
- Gráficos coherentes con las consultas.
- PDF y Excel con cifras correctas.
- Restricciones de acceso según rol.

## 9. Cierre de pruebas

Antes de declarar lista la Release Candidate:

- [ ] Flujos críticos ejecutados.
- [ ] Ningún defecto P0 pendiente.
- [ ] Pruebas positivas y negativas registradas.
- [ ] Permisos de los tres roles comprobados.
- [ ] Reportes y exportaciones validados.
- [ ] Evidencias disponibles.
- [ ] GitHub Actions satisfactorio.
- [ ] Historias relacionadas con Trello.

Los responsables específicos serán asignados en la distribución oficial de Fase 3.
