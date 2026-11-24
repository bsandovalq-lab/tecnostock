<?php require_once __DIR__ . '/auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TecnoStock - Gestión de Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/tecnostock/productos/index.php">
            <i class="bi bi-box-seam me-2"></i>TecnoStock
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/tecnostock/productos/index.php">Productos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/tecnostock/productos/crear.php">Nuevo Producto</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/tecnostock/movimientos/registrar.php">Registrar Movimiento</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/tecnostock/movimientos/index.php">Historial Movimientos</a>
                </li>
            </ul>
            <div class="d-flex align-items-center text-white">
                <span class="me-3 small"><i class="bi bi-person-circle me-1"></i><?= e($_SESSION['nombre']) ?></span>
                <a href="/tecnostock/logout.php" class="btn btn-outline-light btn-sm">Salir</a>
            </div>
        </div>
    </div>
</nav>
<div class="container pb-5">