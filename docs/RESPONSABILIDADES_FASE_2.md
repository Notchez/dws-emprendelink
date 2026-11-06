# Distribución histórica de trabajo — Fase 2

Este documento conserva el reparto técnico que originó la base de EmprendeLink. No es
una agenda vigente ni asigna trabajo posterior a la Release Candidate.

## Distribución original

La asignación documentada durante Fase 2 fue la siguiente. Los rangos RF/RNF se
conservan como referencia académica histórica, no como una lista de trabajo actual:

| Integrante | Área asignada | Requerimientos de referencia |
| --- | --- | --- |
| 1 — Tito | DevOps, arquitectura base y AdminLTE | Configuración transversal, integración, interfaz compartida y documentación |
| 2 | Base de datos y persistencia | Esquema, migraciones, integridad, modelos, factories y seeders |
| 3 | Registro, autenticación y autorización | RF-01 a RF-05; RNF-05 a RNF-10 y RNF-20 |
| 4 | Emprendimientos, productos, categorías y catálogo | RF-12 a RF-22 y RF-38; RNF-02, RNF-03, RNF-06, RNF-09 y RNF-13 a RNF-17 |
| 5 | Pedidos, estados y comisiones | RF-23 a RF-36; soporte para RF-11 y RF-37 a RF-39; RNF-04 y RNF-18 a RNF-20 |
| 6 | Administración y QA | RF-06 a RF-11, RF-37 y RF-39; RNF-11, RNF-14, RNF-25 y RNF-26 |

El trabajo transversal se coordinaba mediante ramas desde `develop`, Pull Requests,
pruebas por módulo, decisiones compartidas en `DECISIONES_TECNICAS.md` y controles de
integración continua.

## Áreas de contribución

### Arquitectura, DevOps e interfaz base

Se establecieron la estructura Laravel, convenciones de namespaces, archivos de rutas,
configuración de middleware, layout AdminLTE, Vite, identidad visual, dependencias,
GitHub Actions y documentación de instalación.

### Base de datos y persistencia

Se implementaron migraciones MySQL, relaciones Eloquent, restricciones, SoftDeletes,
factories y seeders. La reconstrucción oficial se realiza con migraciones; los datos de
demostración son sintéticos.

### Autenticación y autorización

Se incorporaron registro de clientes, sesiones, roles, cuentas suspendidas, middleware,
Policies y restricciones de propietario. La creación de emprendedores quedó bajo
administración y se ejecuta junto con negocio y plan dentro de una transacción.

### Emprendimientos y catálogo

Se desarrollaron perfil de negocio, categorías, productos, imágenes, límites por plan,
activación, eliminación lógica, catálogo público, búsquedas y filtros.

### Pedidos y comisiones

Se implementaron carrito, checkout autenticado, pedidos por emprendimiento, snapshots,
idempotencia, historial de estados, comisión única y estados de cuenta mensuales.

### Administración y calidad

Se incorporaron gestión de usuarios y planes, consultas globales, estados de cuenta,
pruebas funcionales, auditorías de dependencias e integración continua.

## Resultado posterior

Fase 3 completó los reportes de administrador y emprendedor, gráficos, filtros,
exportaciones PDF/XLSX, correcciones de catálogo y un escenario showcase. La integración
final se publicó como `v1.0.0-rc.1`.

La atribución académica individual no se amplía aquí porque el repositorio no contiene
evidencia suficiente para reconstruir responsabilidades personales adicionales sin
inventarlas.
