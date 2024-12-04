<?php
session_start();
require_once "config/database.php";

// Dados de teste
$test_username = "admin";
$test_password = "Master1";
$expected_hash = '$2y$10$dP55cW2MywdruqYWgfO53eGbMVHu3Y0dJTtBHELagYRzlItXgimgq';

echo "<h2>Teste de Conexão e Login</h2>";

try {
    // Teste de conexão
    $database = new Database();
    $db = $database->getConnection();
    echo "<p style='color: green;'>✓ Conexão com o banco de dados estabelecida</p>";

    // Teste de consulta do usuário
    $query = "SELECT id, username, password, role FROM users WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":username", $test_username);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        echo "<p style='color: green;'>✓ Usuário admin encontrado no banco</p>";
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Hash atual no banco: " . $row['password'] . "</p>";
        echo "<p>Hash esperado: " . $expected_hash . "</p>";
        
        // Teste de verificação de senha
        if (password_verify($test_password, $row['password'])) {
            echo "<p style='color: green;'>✓ Senha 'Master1' está correta</p>";
            
            // Teste de sessão
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            
            echo "<p>Dados da sessão:</p>";
            echo "<pre>";
            print_r($_SESSION);
            echo "</pre>";
            
        } else {
            echo "<p style='color: red;'>✗ Senha incorreta</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Usuário admin não encontrado</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>✗ Erro de conexão: " . $e->getMessage() . "</p>";
}

// Teste de redirecionamento
echo "<p>URLs de redirecionamento:</p>";
echo "<p>Login: " . realpath("auth/login.php") . "</p>";
echo "<p>Dashboard: " . realpath("dashboard.php") . "</p>";
?>
