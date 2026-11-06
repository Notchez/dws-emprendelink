# Guía para asistentes de código

## Producto y referencia

EmprendeLink es un monolito Laravel 12 con MySQL, Blade y AdminLTE. La versión publicada
de referencia es `v1.0.0-rc.1`. El catálogo puede consultarse sin sesión; carrito,
checkout y pedidos son exclusivos para clientes activos.

La lectura mínima para cualquier cambio es:

1. `AGENTS.md`;
2. `README.md`;
3. `docs/ARQUITECTURA.md`;
4. `docs/MODELO_NEGOCIO.md`;
5. `docs/DECISIONES_TECNICAS.md`;
6. `docs/ROLES_PERMISOS.md`;
7. `docs/INSTALACION_LOCAL.md` cuando la tarea afecte instalación o entorno.

Los documentos de Fase 2 conservan contexto histórico. No deben utilizarse para
revertir contratos consolidados en Fase 3.

## Forma de colaborar

- Inspeccionar el repositorio y Git antes de proponer cambios.
- Trabajar únicamente dentro del alcance autorizado.
- Reutilizar servicios, Form Requests, Policies y componentes existentes.
- Mantener controladores pequeños y reglas de negocio en servicios.
- Proteger recursos por rol y propietario en el servidor.
- Usar transacciones para operaciones relacionadas.
- Usar datos sintéticos y una base aislada para pruebas.
- No solicitar ni mostrar credenciales, tokens, `APP_KEY` o el contenido completo de
  `.env`.
- No publicar cambios sin autorización.

Cuando una solicitud contradiga el código o una decisión documentada, se identifica el
contrato afectado, se presenta la evidencia y se solicita la decisión necesaria antes
de alterar datos o interfaces compartidas.

## Instalación asistida

La fuente operativa es `docs/INSTALACION_LOCAL.md`. El acompañamiento debe conservar la
configuración local y seguir este orden:

1. comprobar sistema operativo, terminal, rama y estado Git;
2. comprobar PHP, extensiones, Composer, Node.js, npm y MySQL;
3. instalar con `composer install` y `npm ci`;
4. crear `.env` desde `.env.example` solo cuando no exista;
5. confirmar la base local antes de migrar;
6. ejecutar migraciones y seeders documentados;
7. crear `storage:link`, compilar con Vite e iniciar Laravel;
8. comprobar la aplicación sin debilitar controles de seguridad.

No se crea un proyecto Laravel nuevo, no se actualizan dependencias de forma general y
no se usan comandos destructivos como solución de instalación.

## Validación

Las comprobaciones se seleccionan según el cambio. El cierre completo utiliza:

```powershell
composer validate --strict
composer check-platform-reqs
composer audit
php artisan route:list
php artisan test
npm run build
npm audit
git diff --check
```

`php artisan test` solo se ejecuta cuando la conexión efectiva apunta a una base cuyo
nombre termina en `_test` o `_testing`. Si una comprobación no puede ejecutarse, se
informa la causa y no se declara aprobada.

## Comunicación del resultado

El informe final identifica el resultado, los archivos modificados, las decisiones
técnicas relevantes, los comandos ejecutados y sus resultados. También explica
limitaciones materiales y verificaciones manuales no realizadas. No inventa evidencia,
observaciones académicas, responsables ni estados de herramientas externas.
