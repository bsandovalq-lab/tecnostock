<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/header.php';

$sql = 'SELECT m.id_movimiento, m.fecha_hora, m.tipo, m.cantidad, m.observacion,
               p.codigo, p.nombre AS producto_nombre, u.nombre AS usuario_nombre
        FROM movimiento m
        INNER JOIN producto p ON m.id_producto = p.id_producto
        INNER JOIN usuario u ON m.id_usuario = u.id_usuario
        ORDER BY m.fecha_hora DESC, m.id_movimiento DESC';
$movimientos = $pdo->query($sql)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Historial de Movimientos de Inventario</h3>
    <a href="registrar.php" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i>Nuevo Movimiento</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha y Hora</th>
                    <th>Tipo</th>
                    <th>Producto</th>
                    <th class="text-center">Cantidad</th>
                    <th>Responsable</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($movimientos)): ?>
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">Aún no se registran movimientos de inventario.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($movimientos as $m): ?>
                <tr>
                    <td><?= e($m['fecha_hora']) ?></td>
                    <td>
                        <?php if ($m['tipo'] === 'ENTRADA'): ?>
                            <span class="badge bg-success">ENTRADA</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">SALIDA</span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= e($m['codigo']) ?></strong> - <?= e($m['producto_nombre']) ?></td>
                    <td class="text-center fw-bold"><?= (int)$m['cantidad'] ?></td>
                    <td><?= e($m['usuario_nombre']) ?></td>
                    <td><?= e($m['observacion'] ?: '—') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>