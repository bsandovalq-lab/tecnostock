<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/header.php';

$id = $_GET['id'] ?? '';
if (!ctype_digit((string)$id)) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM producto WHERE id_producto = ?');
$stmt->execute([(int)$id]);
$producto = $stmt->fetch();

if (!$producto) {
    header('Location: index.php');
    exit;
}

$categorias = $pdo->query('SELECT id_categoria, nombre FROM categoria WHERE estado = 1 ORDER BY nombre')->fetchAll();
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre       = trim($_POST['nombre'] ?? '');
    $descripcion  = trim($_POST['descripcion'] ?? '');
    $id_categoria = $_POST['id_categoria'] ?? '';
    $precio       = $_POST['precio'] ?? '';
    $stock_actual = $_POST['stock_actual'] ?? '';
    $stock_minimo = $_POST['stock_minimo'] ?? '';
    $estado       = isset($_POST['estado']) && $_POST['estado'] === '1' ? 1 : 0;

    if ($nombre === '') $errores[] = 'El nombre es obligatorio.';
    if (!ctype_digit((string)$id_categoria)) $errores[] = 'Categoría inválida.';
    if (!is_numeric($precio) || (float)$precio < 0) $errores[] = 'El precio no puede ser negativo.';
    if (!ctype_digit((string)$stock_actual) || (int)$stock_actual < 0) $errores[] = 'El stock actual no puede ser negativo.';
    if (!ctype_digit((string)$stock_minimo) || (int)$stock_minimo < 0) $errores[] = 'El stock mínimo no puede ser negativo.';

    if (empty($errores)) {
        $upd = $pdo->prepare(
            'UPDATE producto 
             SET nombre = ?, descripcion = ?, precio = ?, stock_actual = ?, stock_minimo = ?, estado = ?, id_categoria = ?
             WHERE id_producto = ?'
        );
        $upd->execute([
            $nombre,
            $descripcion,
            (float)$precio,
            (int)$stock_actual,
            (int)$stock_minimo,
            $estado,
            (int)$id_categoria,
            (int)$id
        ]);

        header('Location: index.php?msg=' . urlencode('Producto actualizado correctamente.'));
        exit;
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Editar Producto: <?= e($producto['codigo']) ?></h5>
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

                <form method="POST" id="formEditarProducto" novalidate>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Código (Inmutable)</label>
                            <input type="text" class="form-control bg-light" value="<?= e($producto['codigo']) ?>" readonly>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" required
                                   value="<?= e($_POST['nombre'] ?? $producto['nombre']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoría *</label>
                            <select name="id_categoria" id="id_categoria" class="form-select" required>
                                <?php $catSel = $_POST['id_categoria'] ?? $producto['id_categoria']; ?>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id_categoria'] ?>" <?= ($catSel == $cat['id_categoria']) ? 'selected' : '' ?>>
                                        <?= e($cat['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Precio ($) *</label>
                            <input type="number" step="0.01" min="0" name="precio" id="precio" class="form-control" required
                                   value="<?= e($_POST['precio'] ?? $producto['precio']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stock Actual *</label>
                            <input type="number" step="1" min="0" name="stock_actual" id="stock_actual" class="form-control" required
                                   value="<?= e($_POST['stock_actual'] ?? $producto['stock_actual']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stock Mínimo *</label>
                            <input type="number" step="1" min="0" name="stock_minimo" id="stock_minimo" class="form-control" required
                                   value="<?= e($_POST['stock_minimo'] ?? $producto['stock_minimo']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <?php $estSel = $_POST['estado'] ?? $producto['estado']; ?>
                            <select name="estado" class="form-select">
                                <option value="1" <?= ((int)$estSel === 1) ? 'selected' : '' ?>>Activo</option>
                                <option value="0" <?= ((int)$estSel === 0) ? 'selected' : '' ?>>Desactivado</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2"><?= e($_POST['descripcion'] ?? $producto['descripcion']) ?></textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formEditarProducto').addEventListener('submit', function(e) {
    const nombre = document.getElementById('nombre').value.trim();
    const precio = parseFloat(document.getElementById('precio').value);
    const stockActual = parseInt(document.getElementById('stock_actual').value, 10);
    const stockMinimo = parseInt(document.getElementById('stock_minimo').value, 10);

    if (!nombre || isNaN(precio) || precio < 0 || isNaN(stockActual) || stockActual < 0 || isNaN(stockMinimo) || stockMinimo < 0) {
        e.preventDefault();
        alert('Verifica que el nombre esté completo y que precio y cantidades no sean negativos.');
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>