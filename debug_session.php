<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

echo "Status Atual da Sessão:<br>";
echo "SESSION_ID: " . session_id() . "<br>";
foreach ($_SESSION as $key => $value) {
    echo "$key: " . print_r($value, true) . "<br>";
}

echo "<br>Atualizando sessão...<br>";
$_SESSION['logged_in'] = true;
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';

echo "<br>Nova Sessão:<br>";
foreach ($_SESSION as $key => $value) {
    echo "$key: " . print_r($value, true) . "<br>";
}

// Verificar arquivo de login
$login_file = __DIR__ . '/auth/login.php';
if (file_exists($login_file)) {
    echo "<br>Conteúdo do login.php:<br>";
    $login_content = file_get_contents($login_file);
    echo "<pre>" . htmlspecialchars($login_content) . "</pre>";
}

echo "<br><a href='/modules/orders/index.php'>Ir para Pedidos</a>";
?>
