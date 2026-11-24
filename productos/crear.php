<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/header.php';

$categorias = $pdo->query('SELECT id_categoria, nombre FROM categoria WHERE estado = 1 ORDER BY nombre')->fetchAll();
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo       = strtoupper(trim($_POST['codigo'] ?? ''));
    $nombre       = trim($_POST['nombre'] ?? '');
    $descripcion  = trim($_POST['descripcion'] ?? '');
    $id_categoria = $_POST['id_categoria'] ?? '';
    $precio       = $_POST['precio'] ?? '';
    $stock_actual = $_POST['stock_actual'] ?? '';
    $stock_minimo = $_POST['stock_minimo'] ?? '';

    if ($codigo === '') $errores[] = 'El código del producto es obligatorio.';
    if ($nombre === '') $errores[] = 'El nombre del producto es obligatorio.';
    if (!ctype_digit((string)$id_categoria)) $errores[] = 'Selecciona una categoría válida.';
    if (!is_numeric($precio) || (float)$precio < 0) $errores[] = 'El precio no puede ser negativo.';
    if (!ctype_digit((string)$stock_actual) || (int)$stock_actual < 0) $errores[] = 'El stock actual debe ser un entero mayor o igual a 0.';
    if (!ctype_digit((string)$stock_minimo) || (int)$stock_minimo < 0) $errores[] = 'El stock mínimo debe ser un entero mayor o igual a 0.';

    if (empty($errores)) {
        $stmtCheck = $pdo->prepare('SELECT id_producto FROM producto WHERE codigo = ? LIMIT 1');
        $stmtCheck->execute([$codigo]);
        if ($stmtCheck->fetch()) {
            $errores[] = "El código '{$codigo}' ya está registrado en otro producto.";
        }
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare(
            'INSERT INTO producto (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, estado, id_categoria) 
             VALUES (?, ?, ?, ?, ?, ?, 1, ?)'
        );
        $stmt->execute([
            $codigo,
            $nombre,
            $descripcion,
            (float)$precio,
            (int)$stock_actual,
            (int)$stock_minimo,
            (int)$id_categoria
        ]);

        header('Location: index.php?msg=' . urlencode('Producto registrado correctamente.'));
        exit;
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Registrar Nuevo Producto</h5>
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

                <form method="POST" id="formProducto" novalidate>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Código *</label>
                            <input type="text" name="codigo" id="codigo" class="form-control" required
                                   value="<?= e($_POST['codigo'] ?? '') ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nombre del producto *</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" required
                                   value="<?= e($_POST['nombre'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoría *</label>
                            <select name="id_categoria" id="id_categoria" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id_categoria'] ?>" <?= (($_POST['id_categoria'] ?? '') == $cat['id_categoria']) ? 'selected' : '' ?>>
                                        <?= e($cat['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Precio ($) *</label>
                            <input type="number" step="0.01" min="0" name="precio" id="precio" class="form-control" required
                                   value="<?= e($_POST['precio'] ?? '0') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stock Actual *</label>
                            <input type="number" step="1" min="0" name="stock_actual" id="stock_actual" class="form-control" required
                                   value="<?= e($_POST['stock_actual'] ?? '0') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stock Mínimo (Alerta) *</label>
                            <input type="number" step="1" min="0" name="stock_minimo" id="stock_minimo" class="form-control" required
                                   value="<?= e($_POST['stock_minimo'] ?? '5') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2"><?= e($_POST['descripcion'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formProducto').addEventListener('submit', function(e) {
    const codigo = document.getElementById('codigo').value.trim();
    const nombre = document.getElementById('nombre').value.trim();
    const cat = document.getElementById('id_categoria').value;
    const precio = parseFloat(document.getElementById('precio').value);
    const stockActual = parseInt(document.getElementById('stock_actual').value, 10);
    const stockMinimo = parseInt(document.getElementById('stock_minimo').value, 10);

    if (!codigo || !nombre || !cat) {
        e.preventDefault();
        alert('Completa código, nombre y categoría.');
        return;
    }
    if (isNaN(precio) || precio < 0 || isNaN(stockActual) || stockActual < 0 || isNaN(stockMinimo) || stockMinimo < 0) {
        e.preventDefault();
        alert('El precio, stock actual y stock mínimo no pueden ser negativos.');
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>