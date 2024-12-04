<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurações do banco de dados
$host = "localhost";
$dbname = "wpglppro_sisglppro";
$username = "wpglppro_sisglppro";
$password = "s}Zv[chZIMn&";

try {
    // Tenta conectar ao banco de dados
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexão com banco de dados OK!<br>";

    // Tenta buscar o usuário
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $username = "admin";
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "Usuário encontrado!<br>";
        echo "ID: " . $user['id'] . "<br>";
        echo "Username: " . $user['username'] . "<br>";
        echo "Password hash: " . substr($user['password'], 0, 20) . "...<br>";
        
        // Testa a senha
        $senha = "Master1";
        if (password_verify($senha, $user['password'])) {
            echo "Senha está correta!";
        } else {
            echo "Senha está incorreta!";
        }
    } else {
        echo "Usuário não encontrado!";
    }

} catch(PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
