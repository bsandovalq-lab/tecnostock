<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/header.php';

$productos = $pdo->query('SELECT id_producto, codigo, nombre, stock_actual FROM producto WHERE estado = 1 ORDER BY nombre')->fetchAll();
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = $_POST['id_producto'] ?? '';
    $tipo        = $_POST['tipo'] ?? '';
    $cantidad    = $_POST['cantidad'] ?? '';
    $observacion = trim($_POST['observacion'] ?? '');

    if (!ctype_digit((string)$id_producto)) $errores[] = 'Selecciona un producto válido.';
    if (!in_array($tipo, ['ENTRADA', 'SALIDA'], true)) $errores[] = 'Tipo de movimiento inválido.';
    if (!ctype_digit((string)$cantidad) || (int)$cantidad <= 0) $errores[] = 'La cantidad debe ser un número entero mayor a 0.';

    if (empty($errores)) {
        try {
            $pdo->beginTransaction();

            $stmtProd = $pdo->prepare('SELECT id_producto, nombre, stock_actual, estado FROM producto WHERE id_producto = ? FOR UPDATE');
            $stmtProd->execute([(int)$id_producto]);
            $prod = $stmtProd->fetch();

            if (!$prod || (int)$prod['estado'] !== 1) {
                throw new RuntimeException('El producto seleccionado no existe o está desactivado.');
            }

            $stockActual = (int)$prod['stock_actual'];
            $cantInt     = (int)$cantidad;

            if ($tipo === 'SALIDA' && $cantInt > $stockActual) {
                throw new RuntimeException("Stock insuficiente para '{$prod['nombre']}'. Disponible: {$stockActual}, solicitado: {$cantInt}.");
            }

            $nuevoStock = ($tipo === 'ENTRADA') ? ($stockActual + $cantInt) : ($stockActual - $cantInt);

            $stmtMov = $pdo->prepare(
                'INSERT INTO movimiento (fecha_hora, tipo, cantidad, observacion, id_producto, id_usuario) 
                 VALUES (NOW(), ?, ?, ?, ?, ?)'
            );
            $stmtMov->execute([
                $tipo,
                $cantInt,
                $observacion,
                (int)$id_producto,
                (int)$_SESSION['id_usuario']
            ]);

            $stmtUpd = $pdo->prepare('UPDATE producto SET stock_actual = ? WHERE id_producto = ?');
            $stmtUpd->execute([$nuevoStock, (int)$id_producto]);

            $pdo->commit();
            header('Location: ../productos/index.php?msg=' . urlencode("Movimiento ({$tipo}) registrado. Nuevo stock: {$nuevoStock}."));
            exit;

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errores[] = $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Registrar Entrada o Salida de Existencias</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errores as $err): ?>
                                <li><?= e($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" id="formMovimiento" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Producto activo *</label>
                        <select name="id_producto" id="id_producto" class="form-select" required>
                            <option value="" data-stock="0">-- Seleccionar producto --</option>
                            <?php foreach ($productos as $p): ?>
                                <option value="<?= $p['id_producto'] ?>"
                                        data-stock="<?= (int)$p['stock_actual'] ?>"
                                        <?= (($_POST['id_producto'] ?? '') == $p['id_producto']) ? 'selected' : '' ?>>
                                    [<?= e($p['codigo']) ?>] <?= e($p['nombre']) ?> (Stock actual: <?= (int)$p['stock_actual'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de movimiento *</label>
                            <select name="tipo" id="tipo" class="form-select" required>
                                <option value="ENTRADA" <?= (($_POST['tipo'] ?? '') === 'ENTRADA') ? 'selected' : '' ?>>ENTRADA (+)</option>
                                <option value="SALIDA" <?= (($_POST['tipo'] ?? '') === 'SALIDA') ? 'selected' : '' ?>>SALIDA (-)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cantidad *</label>
                            <input type="number" name="cantidad" id="cantidad" class="form-control" min="1" step="1" required
                                   value="<?= e($_POST['cantidad'] ?? '1') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observación</label>
                        <input type="text" name="observacion" class="form-control" placeholder="Ej: Compra a proveedor, venta boleta #104..."
                               value="<?= e($_POST['observacion'] ?? '') ?>">
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="../productos/index.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success">Registrar Movimiento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formMovimiento').addEventListener('submit', function(e) {
    const selectProd = document.getElementById('id_producto');
    const tipo = document.getElementById('tipo').value;
    const cantidad = parseInt(document.getElementById('cantidad').value, 10);
    const stockDisponible = parseInt(selectProd.options[selectProd.selectedIndex].dataset.stock || '0', 10);

    if (!selectProd.value) {
        e.preventDefault();
        alert('Debes seleccionar un producto.');
        return;
    }
    if (isNaN(cantidad) || cantidad <= 0) {
        e.preventDefault();
        alert('La cantidad debe ser mayor a 0.');
        return;
    }
    if (tipo === 'SALIDA' && cantidad > stockDisponible) {
        e.preventDefault();
        alert(`No puedes retirar ${cantidad} unidades. Stock disponible: ${stockDisponible}.`);
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>