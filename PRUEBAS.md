# Plan y Evidencia de Pruebas - TecnoStock

| ID | Caso de Prueba | Datos de Entrada | Resultado Esperado | Estado |
| :--- | :--- | :--- | :--- | :--- |
| CP-01 | Inicio de sesión válido | `admin@tecnostock.cl` / clave válida | Acceso al catálogo y creación de sesión activa | Aprobado |
| CP-02 | Inicio de sesión inválido | `admin@tecnostock.cl` / clave errónea | Mensaje de error sin revelar detalles internos | Aprobado |
| CP-03 | Registro de producto válido | Código `ACC-010`, Precio `5000`, Stock `10`, Mín `3` | Producto insertado y visible en listado | Aprobado |
| CP-04 | Código duplicado (Regla 1) | Código `ACC-001` (ya existente) | Bloqueo en PHP y mensaje de código duplicado | Aprobado |
| CP-05 | Valores negativos (Regla 2) | Precio `-100` o Stock `-5` | Bloqueado por validación HTML/JS y rechazado en PHP | Aprobado |
| CP-06 | Entrada de inventario (Regla 6) | Producto `ACC-002` (stock 3), Entrada `5` | Movimiento guardado y `stock_actual` actualizado a `8` | Aprobado |
| CP-07 | Salida mayor a stock (Regla 4) | Producto `ACC-002` (stock 8), Salida `10` | Transacción revertida (`rollBack`), stock intacto (`8`) | Aprobado |
| CP-08 | Alerta de stock bajo | Producto con `stock_actual = 3` y `stock_minimo = 5` | Fila resaltada en rojo (`table-danger`) y alerta superior | Aprobado |
| CP-09 | Desactivación lógica (Regla 5) | Desactivar producto con movimientos previos | Cambia `estado = 0`, mantiene historial en `movimiento` | Aprobado |