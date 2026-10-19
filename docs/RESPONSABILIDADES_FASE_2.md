# Responsabilidades completas — EmprendeLink, Fase 2

## Objetivo

Apuntar al nivel Estratégico de la rúbrica, correspondiente a 9.00–10.00.

El incremento debe poder instalarse, ejecutarse y demostrarse con:

- Arquitectura organizada.
- Persistencia real en MySQL.
- Autenticación y autorización.
- Operaciones de los módulos prioritarios.
- Reglas de negocio verificables.
- Pruebas y documentación.
- Participación y dominio técnico del equipo.

La implementación y la defensa determinan la calificación final.

## Integrante 1 — DevOps, arquitectura base y AdminLTE

Responsable: Tito.

### Responsabilidades

- Mantener el proyecto Laravel 12 existente.
- Preparar el esqueleto compartido del proyecto.
- Definir nombres, namespaces y ubicaciones de clases.
- Organizar rutas, controladores, Form Requests, servicios, modelos, Policies, excepciones y vistas.
- Preparar la estructura de pruebas, migraciones, factories y seeders.
- Definir convenciones de nombres de rutas.
- Preparar la configuración de middleware y manejo de errores que completarán los módulos.
- Integrar AdminLTE 4 con Blade y Vite.
- Aplicar la paleta y tipografía del Documento Maestro, página 44.
- Preparar layout, navegación, componentes visuales y estados vacíos reutilizables.
- Coordinar que el menú se adapte al rol autenticado.
- Mantener `.env.example` y la configuración documentada de MySQL.
- Mantener dependencias y archivos lock.
- Mantener `.gitignore` y verificar que no se incluyan secretos.
- Escribir el README de instalación y ejecución.
- Mantener `AGENTS.md` y `docs/LLM_GUIDE.md`.
- Definir el flujo de ramas y Pull Requests.
- Coordinar integración de módulos en `develop`.
- Coordinar la configuración de CI con el Integrante 6.
- Verificar una instalación desde una copia limpia del repositorio.
- Preparar el PDF de entrega con portada, integrantes y enlaces requeridos.

### Entregables

- Esqueleto de clases y carpetas.
- Contratos técnicos documentados.
- Base visual AdminLTE.
- README reproducible.
- Guía para LLM.
- Configuración de entorno.
- Flujo Git.
- PDF final de entrega.

### Evidencia de terminado

Otro integrante puede instalar y ejecutar el proyecto siguiendo la documentación, sin depender de explicaciones privadas del creador.

## Integrante 2 — Base de datos y persistencia

### Responsabilidades

- Revisar el Diccionario de Datos y el modelo entidad-relación.
- Resolver con integrantes 1 y 3 los campos faltantes de usuarios, roles y suspensión.
- Acordar la relación entre usuarios y emprendimientos.
- Crear las migraciones del esquema.
- Implementar llaves primarias y foráneas.
- Respetar tipos, longitudes, precisión decimal y nullabilidad.
- Implementar restricciones de unicidad y valores predeterminados.
- Definir acciones de borrado referencial que preserven el historial.
- Implementar SoftDeletes en las entidades indicadas.
- Completar los modelos Eloquent.
- Definir `$table`, `$fillable` o `$guarded`, casts y relaciones.
- Crear factories y seeders con datos sintéticos.
- Preparar usuarios de demostración para ambos roles.
- Preparar planes y datos necesarios para las pruebas.
- Verificar las migraciones en MySQL desde una base vacía.
- Entregar un script SQL de creación o respaldo.
- Documentar restauración y carga de datos.
- Probar integridad referencial, unicidad y relaciones.
- Registrar cualquier ampliación necesaria del Diccionario.

### Tablas

- `users` o adaptación aprobada de usuarios.
- `planes`.
- `emprendimientos`.
- `suscripciones`.
- `categorias`.
- `productos`.
- `pedidos`.
- `detalle_pedidos`.
- `historial_estados`.
- `comisiones`.
- `estados_cuenta`, conforme al alcance acordado.

La inclusión de una tabla no exige terminar toda la interfaz de su módulo durante este sprint.

### Entregables

- Migraciones.
- Modelos y relaciones.
- Factories y seeders.
- Script SQL.
- Instrucciones de creación y restauración.
- Pruebas de integridad.

### Evidencia de terminado

La base puede reconstruirse y cargarse desde cero en MySQL; sus restricciones impiden datos inválidos o relaciones rotas.

## Integrante 3 — Registro, autenticación y autorización

### Requerimientos principales

- RF-01 a RF-05.
- RNF-05 a RNF-10.
- RNF-20.

### Responsabilidades

- Implementar el registro de emprendedores.
- Coordinar la creación de usuario y emprendimiento.
- Crear la suscripción inicial cuando se acuerde el plan de registro.
- Procesar el registro relacionado dentro de una transacción.
- Implementar inicio de sesión para administrador y emprendedor.
- Utilizar hashing de Laravel.
- Regenerar la sesión al iniciar sesión.
- Implementar cierre de sesión e invalidación segura.
- Regenerar el token CSRF al cerrar sesión.
- Implementar roles y protección de rutas.
- Completar `EnsureUserHasRole`.
- Registrar el middleware en `bootstrap/app.php`.
- Bloquear el acceso de cuentas suspendidas.
- Impedir que una sesión existente siga usando funciones restringidas después de una suspensión.
- Implementar Policies para propiedad y permisos.
- Coordinar con cada módulo consultas restringidas al propietario.
- Mantener CSRF y validación del servidor.
- Aplicar límites de intentos de autenticación.
- Mostrar mensajes claros sin revelar datos sensibles.
- Probar autenticación, autorización y accesos cruzados.

### Entregables

- Registro funcional.
- Login y logout.
- Middleware de rol.
- Policies.
- Manejo de sesiones.
- Pruebas positivas y negativas.

### Evidencia de terminado

Un emprendedor puede gestionar su información y recibe una respuesta controlada cuando intenta acceder a datos privados de otro.

## Integrante 4 — Emprendimiento, productos, categorías y catálogo

### Requerimientos principales

- RF-12 a RF-22.
- RF-38, cuando se priorice su resumen básico.
- RNF-02, RNF-03, RNF-06 y RNF-09.
- RNF-13 a RNF-17.

### Responsabilidades

- Implementar consulta y edición del perfil del emprendimiento.
- Mostrar el plan asociado y su límite de productos.
- Implementar gestión de categorías.
- Implementar registro, consulta y edición de productos.
- Implementar activación y desactivación.
- Aplicar autorización y propiedad en cada operación.
- Validar con Form Requests.
- Validar formato y tamaño de imágenes.
- Guardar imágenes mediante Storage y persistir sus rutas.
- Centralizar la validación del límite de productos activos.
- Comprobar el límite tanto al registrar como al activar.
- Evitar que solicitudes simultáneas superen el límite cuando corresponda.
- Generar y mostrar el enlace público del catálogo.
- Implementar catálogo por slug.
- Mostrar únicamente productos disponibles según las reglas del negocio.
- Implementar búsqueda, filtros, paginación y detalle dentro del alcance priorizado.
- Coordinar con el Integrante 5 la selección de productos para pedidos.
- Integrar el resumen básico del emprendedor cuando se priorice, utilizando consultas reales.
- Probar propiedad, validaciones, imágenes y límite del plan.

### Entregables

- Perfil del emprendimiento.
- Gestión de categorías.
- Gestión de productos.
- Catálogo público.
- Reglas del plan.
- Pruebas del módulo.

### Evidencia de terminado

Los cambios se guardan en MySQL, los productos inactivos dejan de aparecer en el catálogo y el sistema impide superar el límite del plan.

## Integrante 5 — Pedidos, estados e historial de comisiones

### Requerimientos principales

- RF-23 a RF-36.
- Soporte de consultas para RF-11, RF-37, RF-38 y RF-39.
- RNF-04, RNF-18, RNF-19 y RNF-20.

### Responsabilidades

- Implementar selección de productos y cantidades.
- Preparar el resumen previo a la confirmación.
- Implementar registro de pedidos sin cuenta de cliente.
- Validar datos de contacto.
- Validar modalidad de entrega.
- Exigir dirección cuando la modalidad sea domicilio.
- Verificar que los productos pertenezcan al catálogo correcto.
- Verificar que los productos puedan pedirse.
- Calcular precios y subtotales en el servidor.
- Conservar el precio unitario aplicado en cada detalle.
- Registrar pedido y detalles en una transacción.
- Generar el identificador de confirmación.
- Prevenir pedidos duplicados ante reenvíos.
- Mostrar confirmación del registro.
- Implementar consulta de pedidos y detalles para el dueño.
- Implementar consulta pública de estado mediante el mecanismo acordado.
- Aplicar la matriz aprobada de transiciones.
- Registrar estado anterior, nuevo estado, actor y fecha.
- Generar comisión cuando el pedido pase a Entregado.
- Conservar monto base, porcentaje aplicado y comisión calculada.
- Evitar una segunda comisión para el mismo pedido.
- Aplicar la regla acordada de cancelación sin perder historial.
- Implementar consulta de comisiones del emprendedor.
- Proporcionar consultas reutilizables para administración.
- Probar transacciones, reintentos, accesos y estados.

### Pruebas críticas

- Un fallo al registrar un detalle revierte el pedido completo.
- Un producto de otro catálogo es rechazado.
- Los totales enviados por el cliente no sustituyen el cálculo del servidor.
- Un emprendedor no consulta pedidos ajenos.
- Una transición inválida se rechaza.
- Un reintento no duplica el pedido ni la comisión.
- La cancelación conserva el historial correspondiente.

### Entregables

- Flujo de pedido anónimo.
- Consulta y detalle.
- Historial de estados.
- Cálculo de comisión.
- Historial de comisiones.
- Pruebas críticas.

### Evidencia de terminado

Se demuestra un pedido completo, su persistencia, una transición válida, una transición rechazada y la generación de una única comisión.

## Integrante 6 — Administración y QA

### Requerimientos principales

- RF-06 a RF-11.
- RF-37.
- RF-39, conforme a la prioridad del sprint.
- RNF-11, RNF-14, RNF-25 y RNF-26.

### Responsabilidades de administración

- Consultar usuarios registrados.
- Consultar emprendimientos.
- Habilitar y suspender cuentas.
- Coordinar con autenticación el bloqueo efectivo de suspendidos.
- Crear, consultar y modificar planes.
- Configurar precios, porcentajes y límites autorizados.
- Evitar borrar físicamente planes relacionados con operaciones históricas.
- Consultar pedidos y comisiones dentro de los permisos administrativos.
- Integrar indicadores básicos priorizados con datos reales.
- Registrar auditoría de operaciones sensibles mediante el mecanismo acordado.

### Responsabilidades de QA

- Mantener una matriz de pruebas por requerimiento.
- Coordinar pruebas de servidor, rutas y MySQL.
- Verificar operaciones CRUD.
- Verificar validaciones y mensajes de error.
- Verificar autenticación, roles y propiedad.
- Revisar los casos críticos de pedidos y comisiones.
- Registrar incidencias con pasos para reproducir.
- Asignar responsable y estado a cada incidencia.
- Documentar correcciones, limitaciones y pendientes.
- Coordinar con el Integrante 1 los checks automáticos.
- Revisar resultados de CI antes de integrar.
- Preparar el guion de defensa.
- Comprobar que cada integrante explique y demuestre su módulo.

### Entregables

- Administración funcional.
- Consultas administrativas.
- Matriz de pruebas.
- Registro de incidencias.
- Evidencia de verificaciones.
- Guion de demostración.

### Evidencia de terminado

Las operaciones administrativas respetan permisos y el equipo puede reproducir las pruebas y explicar el incremento entregado.

## Trabajo transversal

- Cada integrante trabaja en una rama creada desde `develop`.
- Cada integrante prueba su propio módulo.
- El Integrante 6 coordina QA; no reemplaza la responsabilidad de prueba de los demás.
- El Integrante 2 mantiene la propiedad de migraciones y modelos.
- El Integrante 3 mantiene la base de autenticación y autorización.
- Los demás integrantes coordinan cambios necesarios en esas capas.
- El Integrante 1 mantiene el contrato técnico y coordina integración.
- Las decisiones compartidas se registran en `docs/DECISIONES_TECNICAS.md`.

## Definición de terminado

Una tarea se considera terminada cuando:

1. Cumple el criterio de aceptación.
2. Persiste correctamente en MySQL.
3. Valida entradas.
4. Respeta permisos y propiedad.
5. Maneja un caso de error relevante.
6. Cuenta con pruebas apropiadas.
7. Utiliza los nombres y contratos acordados.
8. Tiene documentación actualizada cuando corresponde.
9. Puede demostrarse.
10. Está preparada para integración mediante Pull Request.

## Relación con la rúbrica

| Criterio                                 | Peso | Responsables principales | Evidencia                                                   |
| ---------------------------------------- | ---: | ------------------------ | ----------------------------------------------------------- |
| Configuración, estructura y organización |  20% | 1 y equipo               | Instalación reproducible, capas y dependencias documentadas |
| Lógica, ruteo y POO                      |  25% | 1 y 3 a 6                | Servicios, rutas, validación, errores y trazabilidad        |
| Persistencia, integridad y CRUD          |  25% | 2 y dueños de módulos    | MySQL, restricciones, migraciones, script SQL y operaciones |
| Autenticación, acceso y seguridad        |  15% | 3 y dueños de módulos    | Sesiones, roles, Policies y pruebas negativas               |
| Versiones, pruebas y Sprint Review       |  15% | 1, 6 y equipo            | Commits, integración, pruebas y defensa reproducible        |

## Alcance visual

AdminLTE se incorpora ahora como plantilla común.

La prioridad sigue siendo la Fase 2.

La existencia de un mockup de gráficos, reportes, pagos o estados de cuenta no obliga a completar ese módulo en este sprint. Su implementación debe corresponder al Product Backlog priorizado.
