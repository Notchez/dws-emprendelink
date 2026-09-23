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

Antes de instalar, diagnosticar errores o programar, leer:

1. `AGENTS.md`.
2. `README.md`.
3. `docs/INSTALACION_LOCAL.md`.
4. `docs/ARQUITECTURA_FASE_2.md`.
5. `docs/RESPONSABILIDADES_FASE_2.md`.
6. `docs/DECISIONES_TECNICAS.md`.
7. Documento Maestro y Ruta de Aprendizaje, cuando estén disponibles.

Si un documento de referencia no está disponible, indicarlo. No afirmar que fue revisado.

Si solo se recibe un enlace al repositorio y no se puede consultar su contenido, solicitar los archivos necesarios. No deducir su contenido a partir del nombre del proyecto.

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

## Asistencia para instalar y ejecutar el proyecto

### Objetivo y referencia

Ayudar al integrante a ejecutar la copia existente de EmprendeLink en su equipo antes de comenzar su módulo.

Usar `docs/INSTALACION_LOCAL.md` como referencia para las herramientas, versiones y comandos. Evitar mantener instrucciones de instalación duplicadas en otros documentos.

No crear un proyecto Laravel nuevo ni sustituir la estructura compartida.

### Diagnóstico inicial

Identificar únicamente la información que todavía no esté disponible:

- Sistema operativo y terminal utilizada.
- Si el repositorio ya está clonado y en qué carpeta.
- Rama actual y cambios locales pendientes.
- Herramientas instaladas y sus versiones.
- Disponibilidad del servidor MySQL.
- Paso alcanzado y mensaje de error, si existe.

La guía de instalación contiene comandos para Windows PowerShell. Adaptarlos cuando el integrante utilice otra plataforma, conservando las decisiones técnicas del proyecto.

No pedir contraseñas, tokens, APP_KEY ni el contenido completo de `.env`. El integrante introduce sus credenciales directamente en su equipo.

### Forma de acompañar

- Explicar brevemente para qué sirve cada paso.
- Dar bloques pequeños de instrucciones.
- Indicar en qué carpeta y terminal se ejecuta cada comando.
- Distinguir comandos de terminal, consultas SQL y contenido de archivos.
- Comprobar el resultado de cada etapa antes de continuar con pasos que dependan de ella.
- Ante un error, revisar su mensaje y resolver la causa antes de avanzar.
- Aprovechar la información ya proporcionada; no repetir comprobaciones sin motivo.

Si el asistente tiene acceso al equipo y autorización para ejecutar comandos, puede realizar las comprobaciones directamente. Si solo puede orientar por chat, solicitar los resultados necesarios sin afirmar que ejecutó los comandos.

### Reglas durante la instalación

- Seguir el orden de `docs/INSTALACION_LOCAL.md`.
- Si el repositorio ya existe, revisar su estado antes de actualizarlo o cambiar de rama. Conservar los cambios locales.
- Instalar las dependencias con `composer install` y `npm ci`.
- No usar actualizaciones generales de dependencias como solución automática a errores de instalación.
- AdminLTE y las demás dependencias visuales se instalan desde los archivos del proyecto; no descargar otra plantilla ni agregar otro framework visual.
- Revisar cualquier aviso de scripts de instalación de npm y aplicar el procedimiento documentado para el paquete correspondiente. No habilitar todos los scripts indiscriminadamente.
- Crear `.env` desde `.env.example` únicamente si no existe.
- Conservar una APP_KEY existente. Generarla si falta o está vacía.
- Confirmar que MySQL esté iniciado y que la conexión apunte a la base local prevista antes de ejecutar migraciones.
- No ejecutar `migrate:fresh`, `migrate:refresh`, `db:wipe` ni borrar bases como parte de una instalación normal.
- Ejecutar únicamente los seeders existentes y documentados para ese propósito.
- No modificar reglas de negocio, implementar módulos ni desactivar controles de acceso para lograr que la aplicación arranque.

### Comprobación del resultado

Confirmar que:

1. Las dependencias se instalaron sin errores pendientes.
2. La configuración local permite conectar con MySQL.
3. Las migraciones disponibles se ejecutaron correctamente.
4. El frontend compila con `npm run build`.
5. Laravel inicia y la página carga con los estilos de AdminLTE.
6. El menú lateral responde.
7. Git mantiene excluidos `.env`, `vendor`, `node_modules` y los recursos compilados.

La instalación puede estar completa aunque existan módulos pendientes. Las opciones deshabilitadas y los esqueletos sin lógica no deben presentarse como funcionalidades terminadas.

Antes de ejecutar pruebas que puedan escribir o eliminar datos, comprobar que utilicen una base exclusiva de pruebas. `APP_ENV=testing` por sí solo no garantiza una base separada: revisar `phpunit.xml` y la configuración efectiva. Nunca usar la base de trabajo del integrante para pruebas destructivas.

### Cierre de la asistencia

Informar:

- Qué quedó instalado y verificado.
- Cómo iniciar y detener Laravel y Vite.
- Qué pendientes existen, si los hay.
- Qué archivos cambiaron durante la instalación.
- Cuál es el siguiente paso para comenzar el módulo asignado.

No hacer commit, push ni merge sin autorización del integrante.

### Prompt para solicitar ayuda con la instalación

> Necesito instalar y ejecutar EmprendeLink en mi equipo. Lee AGENTS.md, README.md, docs/LLM_GUIDE.md y docs/INSTALACION_LOCAL.md. Primero identifica mi sistema operativo, terminal, herramientas instaladas y estado del repositorio. Guíame paso a paso con bloques pequeños y comprueba los resultados antes de avanzar. Respeta la estructura y las versiones del proyecto, conserva mi configuración local y no me pidas credenciales. Por ahora el objetivo es dejar la aplicación funcionando; todavía no implementes mi módulo. Al terminar, explica cómo iniciar los servidores y qué quedó pendiente. No publiques ni integres cambios sin mi autorización.
