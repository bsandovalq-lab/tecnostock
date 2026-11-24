# TecnoStock - Sistema de Control de Inventario (MVP)

## 1. Análisis y Alcance
- **Problema:** TecnoStock gestionaba su catálogo de accesorios tecnológicos mediante planillas, provocando códigos duplicados, descuadres en el stock disponible y falta de trazabilidad en entradas y salidas.
- **Alcance:** Aplicación web local en PHP 8 + MySQL (XAMPP) sin frameworks complejos, orientada a centralizar el catálogo, controlar movimientos de inventario con integridad transaccional y alertar niveles críticos de stock.
- **Actor único:** Encargado de inventario (autenticado mediante sesión).

## 2. Trazabilidad de Requerimientos y Reglas de Negocio
| Requerimiento / Regla | Implementación Técnica | Archivo(s) |
| :--- | :--- | :--- |
| Inicio de sesión seguro | `password_verify()` + `$_SESSION` + `session_regenerate_id()` | `login.php`, `includes/auth.php` |
| Registrar y editar productos | Formularios HTML5/Bootstrap + validación JS y PHP + PDO | `productos/crear.php`, `productos/editar.php` |
| Listado y búsqueda | Filtro por nombre, código o categoría con consultas preparadas `LIKE` | `productos/index.php` |
| Código único (Regla 1) | Restricción `UNIQUE` en MySQL + verificación previa en PHP | `sql/tecnostock.sql`, `productos/crear.php` |
| Valores no negativos (Regla 2) | `min="0"`, `CHECK (>= 0)` en BD y validación en PHP | `productos/crear.php`, `productos/editar.php` |
| Trazabilidad de movimientos (Regla 3) | Registro automático de `fecha_hora`, `tipo`, `cantidad`, `id_producto`, `id_usuario` | `movimientos/registrar.php` |
| Bloqueo de stock bajo cero (Regla 4) | Validación JS + bloqueo `FOR UPDATE` y `rollBack()` en transacción PDO | `movimientos/registrar.php` |
| Borrado lógico (Regla 5) | Actualización `estado = 0` preservando integridad referencial | `productos/desactivar.php` |
| Actualización automática y alerta (Reglas 6 y 8) | `UPDATE producto` atómico + alerta visual si `stock_actual < stock_minimo` | `movimientos/registrar.php`, `productos/index.php` |

## 3. Instalación Local (XAMPP)
1. Copiar la carpeta `tecnostock` dentro de `C:\xampp\htdocs\`.
2. Iniciar **Apache** y **MySQL** desde el Panel de Control de XAMPP.
3. Importar `sql/tecnostock.sql` en `http://localhost/phpmyadmin`.
4. Ingresar en `http://localhost/tecnostock/` con `admin@tecnostock.cl`.