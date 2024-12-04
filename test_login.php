<?php
require_once "config/database.php";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Testar a senha do usuário admin
    $query = "SELECT id, username, password, role FROM users WHERE username = 'admin'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Teste de senha para o usuário admin:<br>";
    echo "Hash atual da senha: " . $user['password'] . "<br><br>";
    
    // Testar se a senha 'Master1' funciona
    $testPassword = 'Master1';
    if(password_verify($testPassword, $user['password'])) {
        echo "A senha 'Master1' está correta!<br>";
    } else {
        echo "A senha 'Master1' não está correta.<br>";
        
        // Atualizar a senha para Master1
        echo "Atualizando a senha...<br>";
        $newPassword = password_hash('Master1', PASSWORD_DEFAULT);
        
        $query = "UPDATE users SET password = :password WHERE username = 'admin'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':password', $newPassword);
        
        if($stmt->execute()) {
            echo "Senha atualizada com sucesso!<br>";
            echo "Nova hash: " . $newPassword . "<br>";
        } else {
            echo "Erro ao atualizar senha<br>";
        }
    }
    
} catch(PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
