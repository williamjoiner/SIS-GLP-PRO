<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Se estiver em uma requisição AJAX, retorna erro 401
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('HTTP/1.0 401 Unauthorized');
        exit('Não autorizado');
    }
    
    // Se for uma página normal, redireciona para o login
    header("Location: /index.php");
    exit();
}

// Define variáveis globais úteis
$USER_ID = $_SESSION['user_id'];
$USERNAME = $_SESSION['username'] ?? '';
$USER_ROLE = $_SESSION['role'] ?? '';

function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireAuth() {
    if (!isAuthenticated()) {
        header("Location: /index.php");
        exit();
    }
}

function requireNoAuth() {
    if (isAuthenticated()) {
        if ($_SESSION['role'] == 'admin') {
            header("Location: /admin/dashboard.php");
        } else {
            header("Location: /user/dashboard.php");
        }
        exit();
    }
}
?>
