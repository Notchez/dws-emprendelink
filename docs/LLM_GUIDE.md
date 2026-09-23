# Guía de trabajo para LLM y asistentes de código

## Contexto

EmprendeLink es una aplicación web monolítica para que microemprendedores administren su catálogo y sus pedidos.

El cliente final puede consultar productos y registrar pedidos sin crear una cuenta.

El proyecto corresponde a DWS901 de la Universidad Don Bosco.

## Tecnologías

- PHP 8.5.
- Laravel 12.
- MySQL 8.
- Blade.
- AdminLTE 4.
- Bootstrap.
- Vite.
- Composer.
- npm.
- PHPUnit.

## Lectura inicial

Antes de programar, leer:

1. `AGENTS.md`.
2. `README.md`.
3. `docs/ARQUITECTURA_FASE_2.md`.
4. `docs/RESPONSABILIDADES_FASE_2.md`.
5. `docs/DECISIONES_TECNICAS.md`.
6. Documento Maestro y Ruta de Aprendizaje, cuando estén disponibles.

Si un documento de referencia no está disponible, indicarlo. No afirmar que fue revisado.

## Información que debe proporcionar el integrante

- Número de integrante.
- Módulo asignado.
- Requerimiento o tarea concreta.
- Rama de trabajo.
- Archivos que ya implementó.
- Error o comportamiento observado, si existe.
- Decisiones del equipo que afecten la tarea.

## Forma de colaborar

- Inspeccionar el repositorio antes de proponer una estructura nueva.
- Respetar los nombres de clases, métodos, rutas y campos ya acordados.
- Trabajar dentro del módulo asignado.
- Coordinar cualquier cambio necesario en archivos compartidos.
- Buscar servicios, validaciones y componentes existentes antes de duplicarlos.
- No inventar reglas de negocio.
- No cambiar el esquema de datos para acomodar una vista sin registrar la decisión.
- Usar datos sintéticos en pruebas.
- No solicitar credenciales reales.
- No publicar ni integrar cambios sin autorización del integrante.

## Decisiones faltantes

Cuando falte una decisión:

1. Explicar qué información falta.
2. Indicar qué parte de la implementación depende de ella.
3. Proponer una opción técnicamente justificada.
4. Avanzar con las tareas independientes.
5. Registrar la decisión cuando el responsable la confirme.

## Reglas de implementación

- Mantener controladores pequeños.
- Validar entradas mediante Form Requests.
- Centralizar reglas de negocio en servicios.
- Utilizar Eloquent para persistencia.
- Aplicar Policies y restricciones de propietario.
- Mantener CSRF en formularios.
- Utilizar transacciones para operaciones relacionadas.
- Manejar errores con mensajes comprensibles.
- Evitar datos sensibles en logs y respuestas.

## Pruebas

Cada módulo debe comprobar:

- Un caso válido.
- Validaciones relevantes.
- Acceso no autorizado.
- Acceso a recursos de otro emprendimiento, cuando corresponda.
- Errores que puedan dejar información inconsistente.
- Reintentos o duplicados en operaciones críticas.

Las pruebas deben comprobar comportamiento, no limitarse a verificar que existen clases o archivos.

## Antes de terminar

Ejecutar las verificaciones que correspondan:

```bash
php artisan test
php artisan route:list
php artisan migrate:status
npm run build
```

Cuando cambien dependencias PHP:

```bash
composer audit
```

Si el entorno no permite ejecutar un comando, informar la limitación y dejar el paso pendiente.

## Respuesta final del asistente

La entrega debe incluir:

1. Resumen del cambio.
2. Archivos modificados.
3. RF, RNF o criterios de aceptación cubiertos.
4. Pruebas ejecutadas y resultados.
5. Pendientes o limitaciones.
6. Pasos manuales necesarios.
7. Mensaje de commit en español.

## Prompt inicial para el integrante

> Lee AGENTS.md, README.md y la documentación de docs antes de modificar código. Soy el integrante [número] y mi módulo es [módulo]. Necesito implementar [tarea o RF]. Revisa lo que ya existe, respeta la arquitectura y los nombres acordados, y evita modificar otros módulos sin justificarlo. Si falta una regla de negocio, señálala. Implementa la tarea con sus pruebas relevantes y explica los resultados. No publiques ni integres cambios sin mi autorización.
