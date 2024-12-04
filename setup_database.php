<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurações do banco de dados
$host = "localhost";
$dbname = "wpglppro_sisglppro";
$username = "wpglppro_sisglppro";
$password = "s}Zv[chZIMn&";

try {
    // Conectar ao banco de dados
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexão com o banco de dados estabelecida com sucesso!<br>";

    // Criar tabela users se não existir
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    echo "Tabela 'users' criada ou já existe!<br>";

    // Criar usuário admin se não existir
    $adminUsername = "admin";
    $adminPassword = password_hash("admin", PASSWORD_DEFAULT); // Senha padrão: admin
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$adminUsername]);
    $userExists = $stmt->fetchColumn() > 0;

    if (!$userExists) {
        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$adminUsername, $adminPassword]);
        echo "Usuário admin criado com sucesso!<br>";
        echo "Username: admin<br>";
        echo "Senha: admin<br>";
    } else {
        echo "Usuário admin já existe!<br>";
    }

} catch(PDOException $e) {
    echo "Erro: " . $e->getMessage() . "<br>";
}
?>
