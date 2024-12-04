<?php
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /index.php');
    exit;
}

// Verificar se o usuário tem permissão para acessar a página
$allowed_roles = ['admin', 'master'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header('Location: /index.php');
    exit;
}
?>
