<?php
// Primeiro, verificar a senha de acesso
if (!isset($_POST['debug_password'])) {
    // Mostrar formulário de senha
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Debug Login</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .container { max-width: 500px; margin: 0 auto; }
            .form-group { margin-bottom: 15px; }
            input[type="password"] { width: 100%; padding: 8px; margin: 5px 0; }
            button { padding: 10px 15px; background: #007bff; color: white; border: none; cursor: pointer; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Debug Login</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Senha de Debug:</label>
                    <input type="password" name="debug_password" required>
                </div>
                <button type="submit">Acessar</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Verificar se a senha está correta
if ($_POST['debug_password'] !== 'Debug@2024') {
    die('Senha incorreta');
}

// Se chegou aqui, a senha está correta
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurações do banco de dados
$host = "localhost";
$dbname = "wpglppro_sisglppro";
$username = "wpglppro_sisglppro";
$password = "s}Zv[chZIMn&";

echo "<pre>";
try {
    // Tenta conectar ao banco de dados
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexão com banco de dados OK!\n\n";

    // Tenta buscar o usuário
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $username = "admin";
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "✅ Usuário encontrado!\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Password hash: " . substr($user['password'], 0, 20) . "...\n\n";
        
        // Testa a senha
        $senha = "Master1";
        if (password_verify($senha, $user['password'])) {
            echo "✅ Senha está correta!\n";
        } else {
            echo "❌ Senha está incorreta!\n";
            
            // Gerar novo hash para referência
            echo "\nNovo hash para senha 'Master1':\n";
            echo password_hash("Master1", PASSWORD_DEFAULT) . "\n";
            
            echo "\nSQL para atualizar a senha:\n";
            echo "UPDATE users SET password = '" . password_hash("Master1", PASSWORD_DEFAULT) . "' WHERE username = 'admin';\n";
        }
    } else {
        echo "❌ Usuário não encontrado!\n";
        echo "\nConteúdo da tabela users:\n";
        $stmt = $conn->query("SELECT id, username FROM users");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "ID: {$row['id']}, Username: {$row['username']}\n";
        }
    }

} catch(PDOException $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    
    // Informações adicionais de debug
    echo "\nInformações do PDO:\n";
    echo "PDO::ATTR_CONNECTION_STATUS: " . $conn->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "\n";
    echo "PDO::ATTR_DRIVER_NAME: " . $conn->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
    echo "PDO::ATTR_SERVER_VERSION: " . $conn->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
}
echo "</pre>";
?>
