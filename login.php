<?php
session_start();
require_once __DIR__ . '/config/conexion.php';

if (isset($_SESSION['id_usuario'])) {
    header('Location: /tecnostock/productos/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $clave  = $_POST['clave'] ?? '';

    if ($correo === '' || $clave === '') {
        $error = 'Debes ingresar correo y contraseña.';
    } else {
        $stmt = $pdo->prepare('SELECT id_usuario, nombre, correo, clave, estado FROM usuario WHERE correo = ? LIMIT 1');
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();

        if ($usuario && (int)$usuario['estado'] === 1 && password_verify($clave, $usuario['clave'])) {
            session_regenerate_id(true);
            $_SESSION['id_usuario'] = (int)$usuario['id_usuario'];
            $_SESSION['nombre']     = $usuario['nombre'];
            header('Location: /tecnostock/productos/index.php');
            exit;
        } else {
            $error = 'Credenciales inválidas o usuario inactivo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h4 class="text-center mb-1 fw-bold">TecnoStock</h4>
                    <p class="text-center text-muted small mb-4">Control de Inventario</p>

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger py-2"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>

                    <form method="POST" id="formLogin" novalidate>
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="correo" name="correo" required
                                   value="<?= htmlspecialchars($_POST['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="clave" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="clave" name="clave" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('formLogin').addEventListener('submit', function(e) {
    const correo = document.getElementById('correo').value.trim();
    const clave = document.getElementById('clave').value;
    if (!correo || !clave) {
        e.preventDefault();
        alert('Por favor, completa correo y contraseña.');
    }
});
</script>
</body>
</html>