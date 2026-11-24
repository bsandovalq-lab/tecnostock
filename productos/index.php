<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/header.php';

$busqueda      = trim($_GET['q'] ?? '');
$id_categoria  = $_GET['categoria'] ?? '';
$ver_inactivos = isset($_GET['inactivos']) && $_GET['inactivos'] === '1';

$categorias = $pdo->query('SELECT id_categoria, nombre FROM categoria WHERE estado = 1 ORDER BY nombre')->fetchAll();

$sql = 'SELECT p.*, c.nombre AS categoria_nombre 
        FROM producto p 
        INNER JOIN categoria c ON p.id_categoria = c.id_categoria 
        WHERE 1=1';
$params = [];

if (!$ver_inactivos) {
    $sql .= ' AND p.estado = 1';
}

if ($busqueda !== '') {
    $sql .= ' AND (p.nombre LIKE ? OR p.codigo LIKE ? OR c.nombre LIKE ?)';
    $like = "%{$busqueda}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($id_categoria !== '' && ctype_digit($id_categoria)) {
    $sql .= ' AND p.id_categoria = ?';
    $params[] = (int)$id_categoria;
}

$sql .= ' ORDER BY p.estado DESC, p.nombre ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

$bajo_stock_count = 0;
foreach ($productos as $prod) {
    if ((int)$prod['estado'] === 1 && (int)$prod['stock_actual'] < (int)$prod['stock_minimo']) {
        $bajo_stock_count++;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Catálogo de Productos</h3>
    <div>
        <a href="crear.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Registrar Producto</a>
        <a href="../movimientos/registrar.php" class="btn btn-success"><i class="bi bi-arrow-left-right me-1"></i>Entrada / Salida</a>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= e($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($bajo_stock_count > 0): ?>
    <div class="alert alert-warning d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>
            <strong>Alerta de inventario:</strong> Hay <strong><?= $bajo_stock_count ?></strong> producto(s) activo(s) con stock actual por debajo del stock mínimo definido.
        </div>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted">Buscar por nombre, código o categoría</label>
                <input type="text" name="q" class="form-control" placeholder="Ej: ACC-001, Cable, Audio..." value="<?= e($busqueda) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Filtrar por categoría</label>
                <select name="categoria" class="form-select">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id_categoria'] ?>" <?= ($id_categoria == $cat['id_categoria']) ? 'selected' : '' ?>>
                            <?= e($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="inactivos" value="1" id="chkInactivos" <?= $ver_inactivos ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="chkInactivos">Mostrar inactivos</label>
                </div>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-dark w-100">Buscar</button>
                <a href="index.php" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Código</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th class="text-end">Precio</th>
                    <th class="text-center">Stock Actual</th>
                    <th class="text-center">Stock Mínimo</th>
                    <th class="text-center">Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($productos)): ?>
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">No se encontraron productos con los criterios seleccionados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($productos as $p): 
                    $activo = (int)$p['estado'] === 1;
                    $bajo_stock = $activo && ((int)$p['stock_actual'] < (int)$p['stock_minimo']);
                ?>
                <tr class="<?= !$activo ? 'table-secondary text-muted' : ($bajo_stock ? 'table-danger' : '') ?>">
                    <td class="fw-semibold"><?= e($p['codigo']) ?></td>
                    <td>
                        <div><?= e($p['nombre']) ?></div>
                        <?php if (!empty($p['descripcion'])): ?>
                            <small class="text-muted"><?= e($p['descripcion']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= e($p['categoria_nombre']) ?></td>
                    <td class="text-end">$<?= number_format((float)$p['precio'], 0, ',', '.') ?></td>
                    <td class="text-center fw-bold">
                        <?= (int)$p['stock_actual'] ?>
                        <?php if ($bajo_stock): ?>
                            <span class="badge bg-danger ms-1"><i class="bi bi-exclamation-circle"></i> Bajo</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= (int)$p['stock_minimo'] ?></td>
                    <td class="text-center">
                        <?php if ($activo): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Desactivado</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <a href="editar.php?id=<?= (int)$p['id_producto'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <?php if ($activo): ?>
                            <form method="POST" action="desactivar.php" class="d-inline" onsubmit="return confirm('¿Desactivar el producto <?= e($p['codigo']) ?>? No se eliminará el historial.');">
                                <input type="hidden" name="id_producto" value="<?= (int)$p['id_producto'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Desactivar">
                                    <i class="bi bi-slash-circle"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>