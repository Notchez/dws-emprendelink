# Instrucciones para asistentes de código

Este archivo define las convenciones compartidas del repositorio EmprendeLink.

Debe leerse antes de generar o modificar código.

## Contexto del proyecto

- Asignatura: DWS901.
- Proyecto: EmprendeLink.
- Fase: Desarrollo Back-End y Persistencia, Sprint II.
- Tecnologías: PHP 8.5, Laravel 12, MySQL 8, Blade, AdminLTE 4 y Vite.
- El cliente final puede consultar catálogos y registrar pedidos sin crear una cuenta.

## Documentos de referencia

1. Documento Maestro aprobado.
2. Ruta de Aprendizaje de DWS.
3. `docs/ARQUITECTURA_FASE_2.md`.
4. `docs/RESPONSABILIDADES_FASE_2.md`.
5. `docs/DECISIONES_TECNICAS.md`.

Si existe una contradicción:

- Identificarla.
- Explicar qué comportamiento afecta.
- Solicitar la decisión necesaria al responsable.
- Registrar el acuerdo antes de modificar contratos compartidos.

Los cambios de alcance autorizados expresamente por el responsable deben reflejarse en la documentación.

## Alcance de Fase 2

Priorizar:

- Configuración reproducible.
- Arquitectura organizada.
- Rutas y controladores.
- Validaciones.
- Servicios y reglas de negocio.
- Persistencia MySQL.
- Autenticación y autorización.
- CRUD de módulos prioritarios.
- Pruebas y evidencia de funcionamiento.

AdminLTE 4 es la plantilla visual acordada.

No adelantar reportes completos, exportaciones, pagos o funcionalidades adicionales sin una prioridad explícita del sprint.

Mantener las exclusiones del Documento Maestro.

## Arquitectura obligatoria

- Las rutas declaran endpoints y middleware.
- Los controladores coordinan solicitudes y respuestas.
- Los Form Requests validan entradas.
- Los servicios implementan operaciones y reglas de negocio.
- Los modelos representan entidades, relaciones, casts y consultas reutilizables.
- Las Policies autorizan operaciones sobre recursos.
- Las vistas Blade presentan información.

No duplicar reglas de negocio entre controladores, vistas y JavaScript.

## Nombres y contratos

- Respetar las clases y ubicaciones de `docs/ARQUITECTURA_FASE_2.md`.
- Buscar implementaciones existentes antes de crear otra clase.
- No renombrar rutas, campos, tablas o servicios compartidos sin registrar la decisión.
- Conservar exactamente los nombres de tablas del Diccionario de Datos.
- Declarar `$table` cuando el nombre de una tabla no coincida con la convención de Eloquent.
- Usar PascalCase para clases PHP y snake_case para tablas y columnas.

## Seguridad y propiedad de datos

- Proteger rutas privadas con autenticación y autorización.
- Verificar el rol en el servidor.
- Restringir consultas y modificaciones al emprendimiento autorizado.
- No confiar en un `emprendimiento_id` enviado por el navegador.
- Usar Policies y consultas restringidas por propietario.
- Mantener protección CSRF.
- Validar las entradas antes de procesarlas.
- Utilizar el hashing de Laravel para contraseñas.
- No crear accesos alternativos que omitan controles de seguridad.

## Persistencia

- Utilizar MySQL para verificar el comportamiento definitivo del proyecto.
- Usar transacciones cuando una operación escriba varias tablas relacionadas.
- Registrar pedido y detalles como una sola operación atómica.
- Mantener la integridad de pedidos, historial y comisiones.
- Evitar pedidos y comisiones duplicados ante reintentos.
- Conservar los precios del pedido y el porcentaje aplicado a la comisión.
- Aplicar SoftDeletes únicamente en las entidades que lo requieran.
- Evitar borrado físico de información necesaria para el historial.

## Dependencias y configuración

- No subir `.env`, secretos, tokens ni credenciales reales.
- Mantener `.env.example` con valores de ejemplo.
- Gestionar dependencias PHP mediante Composer.
- Actualizar `composer.lock` cuando se modifiquen dependencias PHP.
- Mantener `package-lock.json` sincronizado con `package.json`.
- Centralizar AdminLTE, Bootstrap y recursos compartidos mediante Vite.
- Evitar incorporar otra plantilla o framework frontend sin autorización.

## Interfaz

- Usar el layout y los componentes compartidos.
- Respetar la paleta y tipografía del Documento Maestro, página 44.
- Usar mensajes claros de éxito y error.
- Asociar etiquetas con campos.
- Mostrar errores cerca del campo correspondiente.
- Mantener contraste, foco visible y navegación por teclado.
- Utilizar datos reales en indicadores funcionales.
- Identificar claramente cualquier vista temporal de preparación.

## Flujo Git

- No trabajar directamente en `main`.
- No trabajar directamente en `develop`.
- Crear ramas temporales desde `develop` actualizado.
- Preparar Pull Requests hacia `develop`.
- Usar Conventional Commits con descripción en español.
- Publicar o integrar cambios únicamente cuando el integrante lo autorice.

## Protocolo de trabajo

1. Inspeccionar el estado Git y los archivos relacionados.
2. Identificar los RF, RNF y criterios de aceptación afectados.
3. Revisar las decisiones pendientes.
4. Explicar brevemente qué se implementará.
5. Realizar cambios dentro del módulo asignado.
6. Añadir pruebas relevantes para comportamientos críticos.
7. Ejecutar las verificaciones disponibles.
8. Informar resultados y limitaciones reales.

Nunca declarar que una prueba pasó si no fue ejecutada.

## Entrega del asistente

Al finalizar, informar:

- Archivos modificados.
- Requerimientos cubiertos.
- Pruebas y comandos ejecutados.
- Resultado de cada verificación.
- Decisiones pendientes.
- Pasos manuales necesarios.
- Mensaje de commit sugerido.

## Decisiones que requieren atención

El Diccionario de Datos detalla las tablas del negocio, pero no define completamente la tabla de usuarios.

Antes de fijar las migraciones definitivas deben acordarse:

- Campos de rol y estado de cuenta.
- Correspondencia entre `users` y las referencias a `usuarios`.
- Restricción de la relación usuario-emprendimiento.
- Matriz de transiciones de pedidos.
- Tratamiento de comisiones ante cancelaciones.

No inventar esas reglas ni presentarlas como decisiones aprobadas.
