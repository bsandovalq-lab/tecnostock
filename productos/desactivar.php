<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_producto']) && ctype_digit((string)$_POST['id_producto'])) {
    $stmt = $pdo->prepare('UPDATE producto SET estado = 0 WHERE id_producto = ?');
    $stmt->execute([(int)$_POST['id_producto']]);
    header('Location: index.php?msg=' . urlencode('Producto desactivado correctamente (historial preservado).'));
    exit;
}

header('Location: index.php');
exit;