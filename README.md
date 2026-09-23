# EmprendeLink

Plataforma web para que microemprendedores administren su perfil comercial, publiquen un catálogo y gestionen pedidos. Los clientes pueden consultar el catálogo y registrar pedidos sin crear una cuenta.

Proyecto académico de DWS901 — Desarrollo de Aplicaciones Web con Software Interpretado en el Servidor, Universidad Don Bosco.

## Fase actual

Fase 2 — Desarrollo Back-End y Persistencia (Sprint II).

El trabajo de esta fase consiste en implementar:

- Arquitectura organizada y configuración reproducible.
- Base de datos MySQL.
- Registro, autenticación y autorización.
- Operaciones de los módulos prioritarios.
- Reglas de negocio del Documento Maestro.
- Pruebas y evidencia para la defensa.

La estructura compartida y los nombres de clases se documentan en `docs/ARQUITECTURA_FASE_2.md`.

La distribución del trabajo se encuentra en `docs/RESPONSABILIDADES_FASE_2.md`.

## Estado del esqueleto

La base preparada contiene clases iniciales para organizar el trabajo del equipo.

Una clase vacía o una pantalla de preparación no representa una funcionalidad terminada. Cada integrante debe implementar y probar la lógica de su módulo.

AdminLTE 4 será la plantilla visual común, integrada con Blade y Vite. La paleta y la tipografía se toman del Documento Maestro, página 44.

## Tecnologías

| Área                  | Tecnología               |
| --------------------- | ------------------------ |
| Lenguaje del servidor | PHP 8.5                  |
| Framework             | Laravel 12               |
| Arquitectura          | MVC con servicios        |
| Base de datos         | MySQL 8                  |
| Plantillas            | Blade                    |
| Interfaz              | AdminLTE 4 y Bootstrap 5 |
| Iconos                | Bootstrap Icons          |
| Tipografía            | Inter                    |
| Recursos frontend     | Vite y npm               |
| Dependencias PHP      | Composer                 |
| Pruebas               | PHPUnit                  |
| Control de versiones  | Git y GitHub             |

Las versiones instaladas se conservan en `composer.lock` y `package-lock.json`.

## Requisitos del entorno

- PHP compatible con `composer.json`; versión objetivo del proyecto: PHP 8.5.
- Extensiones PHP necesarias para Laravel y MySQL, incluyendo PDO MySQL, Mbstring, OpenSSL, XML, Ctype, Fileinfo y Tokenizer.
- Composer 2.x.
- MySQL 8.
- Node.js y npm compatibles con las dependencias del proyecto.
- Git.

## Instalación y ejecución local

Consulta la [guía de instalación local](docs/INSTALACION_LOCAL.md).

Incluye las herramientas necesarias, instalación de dependencias,
configuración de MySQL y pasos para ejecutar Laravel y AdminLTE.

### Compilación del frontend

```bash
npm run build
```

## Comandos de verificación

Ejecutar las pruebas:

```bash
php artisan test
```

Consultar rutas:

```bash
php artisan route:list
```

Consultar migraciones:

```bash
php artisan migrate:status
```

Revisar dependencias PHP:

```bash
composer audit
```

Compilar recursos:

```bash
npm run build
```

## Organización del proyecto

| Ubicación                    | Responsabilidad                            |
| ---------------------------- | ------------------------------------------ |
| `app/Http/Controllers`       | Recibir solicitudes y coordinar respuestas |
| `app/Http/Requests`          | Validar y autorizar entradas               |
| `app/Http/Middleware`        | Controles compartidos sobre solicitudes    |
| `app/Models`                 | Entidades, relaciones y acceso a datos     |
| `app/Policies`               | Autorización sobre recursos                |
| `app/Services`               | Reglas y operaciones de negocio            |
| `app/Exceptions`             | Errores previstos del dominio              |
| `database/migrations`        | Esquema reproducible                       |
| `database/factories`         | Generación de datos de prueba              |
| `database/seeders`           | Datos iniciales y demostración             |
| `resources/views`            | Vistas Blade                               |
| `resources/css/adminlte.css` | Tema y colores de EmprendeLink             |
| `resources/js/adminlte.js`   | Recursos JavaScript compartidos            |
| `routes`                     | Rutas organizadas por módulo               |
| `tests/Feature`              | Pruebas de solicitudes e integración       |
| `tests/Unit`                 | Pruebas de reglas aisladas                 |
| `docs`                       | Documentación técnica y del equipo         |

## Diseño visual

Referencia: Documento Maestro, páginas 44 a 61.

| Uso              | Color     |
| ---------------- | --------- |
| Primario         | `#2563EB` |
| Primario claro   | `#EFF6FF` |
| Texto principal  | `#111827` |
| Texto secundario | `#6B7280` |
| Bordes           | `#D1D5DB` |
| Fondo            | `#F8FAFC` |
| Superficies      | `#FFFFFF` |

Tipografía: Inter.

Los estilos compartidos deben centralizarse. Los módulos reutilizan el layout y los componentes existentes.

## Guía para LLM y asistentes de código

Antes de generar o editar código, el asistente debe leer:

1. `AGENTS.md`.
2. `docs/LLM_GUIDE.md`.
3. `docs/ARQUITECTURA_FASE_2.md`.
4. `docs/RESPONSABILIDADES_FASE_2.md`.
5. `docs/DECISIONES_TECNICAS.md`.

El integrante debe indicar:

- Su número y módulo asignado.
- Los RF, RNF o criterios de aceptación que implementará.
- La rama de trabajo.
- Los archivos relacionados.
- Las decisiones pendientes que afecten su tarea.

### Prompt inicial sugerido

> Lee primero AGENTS.md, docs/LLM_GUIDE.md, docs/ARQUITECTURA_FASE_2.md, docs/RESPONSABILIDADES_FASE_2.md y docs/DECISIONES_TECNICAS.md. Soy el integrante [número] y trabajaré en [módulo y requerimientos]. Inspecciona el código existente antes de modificarlo. Respeta los nombres, las rutas, las tablas y los contratos compartidos. Si falta una regla de negocio, identifícala y no la inventes. Implementa mi módulo y sus pruebas relevantes. Al terminar, explica los archivos modificados, los requerimientos cubiertos, los comandos ejecutados, sus resultados y los pendientes. No publiques ni integres cambios sin mi autorización.

## Flujo Git

- `main`: versión estable de entrega.
- `develop`: integración del equipo.
- Ramas temporales: trabajo de cada integrante.

Prefijos permitidos:

- `feature/*`
- `fix/*`
- `docs/*`
- `test/*`
- `ci/*`
- `chore/*`

Flujo:

1. Actualizar `develop`.
2. Crear la rama de trabajo.
3. Implementar y probar el cambio.
4. Registrar commits descriptivos.
5. Publicar la rama.
6. Abrir Pull Request hacia `develop`.
7. Comprobar los resultados de CI antes de integrar.

Las funcionalidades se integran en `main` cuando el incremento esté listo para entrega.

## Convención de commits

Usar Conventional Commits con descripción en español.

Ejemplos:

```text
feat(productos): validar límite del plan
fix(auth): bloquear el acceso de cuentas suspendidas
test(pedidos): comprobar rollback del registro
docs(readme): actualizar instalación con MySQL
ci(actions): ejecutar pruebas del proyecto
```

## Seguridad

- No subir `.env`, credenciales ni tokens.
- No utilizar datos personales reales en seeders o respaldos de demostración.
- Proteger rutas privadas con autenticación y autorización.
- Restringir consultas y modificaciones al emprendimiento autorizado.
- Validar datos en el servidor.
- Mantener CSRF en formularios.
- No exponer trazas ni información sensible en mensajes para usuarios.

## Alcance

La Fase 2 prioriza back-end, persistencia, autenticación, autorización y reglas de negocio.

La incorporación de AdminLTE es el adelanto visual acordado.

Los gráficos, reportes, exportaciones y estados de cuenta se desarrollarán conforme a la priorización del Product Backlog. Su aparición en un mockup no implica que deban quedar completos en este sprint.

Se mantienen las exclusiones del Documento Maestro, incluyendo pagos en línea, facturación fiscal, chat, aplicación móvil nativa y marketplace general.

## Documentación del equipo

- `AGENTS.md`: reglas para asistentes.
- `docs/LLM_GUIDE.md`: contexto y protocolo para LLM.
- `docs/ARQUITECTURA_FASE_2.md`: estructura y nombres compartidos.
- `docs/RESPONSABILIDADES_FASE_2.md`: responsabilidades y rúbrica.
- `docs/DECISIONES_TECNICAS.md`: decisiones y asuntos pendientes.
