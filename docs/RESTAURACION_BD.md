# Restauración y Estructura de Base de Datos

Este documento explica cómo recrear la base de datos desde cero utilizando migraciones y cómo restaurar los datos mediante el script SQL.

## 1. Instalación mediante Migraciones y Seeders (Desarrollo)

Laravel incluye el esquema completo en el código. Para recrear la base de datos limpia con datos de prueba:

1. Configura el archivo `.env` apuntando a MySQL.
2. Ejecuta el comando de reconstrucción:

```bash
php artisan migrate:fresh --seed
```

### Datos de Demostración

El comando anterior generará:
- **3 Planes:** Gratuito, Básico, Profesional.
- **1 Administrador:** `admin@emprendelink.test` (password: `password`)
- **3 Emprendedores:** `emp1@emprendelink.test`, `emp2...`, etc. (password: `password`)
- **Datos de prueba:** Cada emprendedor tendrá su tienda configurada con categorías, productos, pedidos (estado: Entregado), historial, comisiones y su respectivo estado de cuenta mensual.

## 2. Restauración mediante Script SQL (Producción / QA)

Si necesitas restaurar la base de datos en otro entorno o gestor visual (como phpMyAdmin o DBeaver) usando el respaldo:

1. Crea la base de datos vacía:
```sql
CREATE DATABASE emprendelink CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Ejecuta el archivo SQL desde la terminal:
```bash
mysql -u root -p emprendelink < database/sql/emprendelink_con_datos.sql
```

## 3. Ampliaciones al Diccionario de Datos

Se han incluido algunas restricciones y campos adicionales para cumplir con las reglas del sistema de forma segura:

| Tabla | Modificación | Motivo (Decisión Técnica) |
|---|---|---|
| `users` | Se agregó `rol` (ENUM) y `estado` (BOOLEAN) | DT-03. Control de acceso y suspensión. |
| `users` | Se agregó `deleted_at` | DT-03. Borrado lógico. |
| `emprendimientos` | Se agregó `slug` (VARCHAR UNIQUE) | Catálogo público (RF-20). |
| `emprendimientos` | Restricción `UNIQUE` en `usuario_id` | DT-08. Un emprendedor = una tienda. |
| `comisiones` | Restricción `UNIQUE` en `pedido_id` | DT-08. Prevenir cobros duplicados por pedido. |
| `estados_cuenta` | Restricción `UNIQUE(emprendimiento_id, mes, anio)` | Evitar duplicación de periodos facturados. |

## 4. Política de Integridad (Borrado)

- **Borrado Lógico (SoftDeletes):** Usuarios, Planes, Emprendimientos, Categorías y Productos **nunca se borran físicamente**.
- **`RESTRICT ON DELETE`:** Impide borrar un registro si tiene datos financieros o ventas enlazadas.
- **`CASCADE ON DELETE`:** Si un pedido se borra (ej. por limpieza del sistema admin), sus detalles y el historial de ese pedido también se borran automáticamente.
