<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
    header('Location: /tecnostock/productos/index.php');
} else {
    header('Location: /tecnostock/login.php');
}
exit;