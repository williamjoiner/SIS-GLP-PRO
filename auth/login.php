<?php
session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $database = new Database();
        $db = $database->getConnection();

        // Adicionando log para debug
        error_log("Tentativa de login para usuário: " . $username);

        $query = "SELECT id, username, password, role FROM users WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Adicionando log para debug
            error_log("Hash da senha no banco: " . $row['password']);
            
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['logged_in'] = true;
                
                // Adicionando log para debug
                error_log("Login bem-sucedido para: " . $username);
                
                // Redirecionamento direto para o dashboard
                header("Location: ../modules/orders/index.php");
                exit();
            } else {
                // Adicionando log para debug
                error_log("Senha incorreta para: " . $username);
                
                $_SESSION['error'] = "Senha incorreta";
                header("Location: ../index.php");
                exit();
            }
        } else {
            // Adicionando log para debug
            error_log("Usuário não encontrado: " . $username);
            
            $_SESSION['error'] = "Usuário não encontrado";
            header("Location: ../index.php");
            exit();
        }
    } catch (PDOException $e) {
        // Adicionando log para debug
        error_log("Erro de banco de dados: " . $e->getMessage());
        
        $_SESSION['error'] = "Erro ao conectar com o banco de dados";
        header("Location: ../index.php");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>
