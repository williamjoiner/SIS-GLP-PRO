<?php
require_once "config/database.php";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if($db) {
        echo "Conexão com o banco de dados estabelecida com sucesso!<br>";
        
        // Testar se o usuário admin existe
        $query = "SELECT id, username, role FROM users WHERE username = 'admin'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "Usuário admin encontrado!<br>";
            echo "ID: " . $user['id'] . "<br>";
            echo "Role: " . $user['role'] . "<br>";
        } else {
            echo "Usuário admin não encontrado. Criando...<br>";
            
            // Criar usuário admin
            $query = "INSERT INTO users (username, password, role) VALUES 
                     ('admin', :password, 'master')";
            $stmt = $db->prepare($query);
            $password = password_hash('Master1', PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $password);
            
            if($stmt->execute()) {
                echo "Usuário admin criado com sucesso!<br>";
            } else {
                echo "Erro ao criar usuário admin<br>";
            }
        }
        
        // Mostrar todas as tabelas
        $query = "SHOW TABLES";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        echo "<br>Tabelas encontradas:<br>";
        while($row = $stmt->fetch(PDO::FETCH_NUM)) {
            echo "- " . $row[0] . "<br>";
        }
        
    } else {
        echo "Erro: Não foi possível conectar ao banco de dados";
    }
} catch(PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
