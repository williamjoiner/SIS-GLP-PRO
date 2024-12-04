<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Verificar se está logado
echo "Status da Sessão:<br>";
echo "SESSION_ID: " . session_id() . "<br>";
echo "Logged_in: " . (isset($_SESSION['logged_in']) ? 'true' : 'false') . "<br>";
if (isset($_SESSION['user_id'])) {
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
}
if (isset($_SESSION['username'])) {
    echo "Username: " . $_SESSION['username'] . "<br>";
}

// Configurações do banco de dados
$host = "localhost";
$dbname = "wpglppro_sisglppro";
$username = "wpglppro_sisglppro";
$password = "s}Zv[chZIMn&";

echo "<br>Teste de Conexão:<br>";
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexão com banco OK<br>";

    // Verificar tabela orders
    $stmt = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela 'orders' existe<br>";
        
        // Mostrar estrutura da tabela
        echo "<br>Estrutura da tabela orders:<br>";
        $stmt = $conn->query("DESCRIBE orders");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}<br>";
        }
        
        // Contar registros
        $stmt = $conn->query("SELECT COUNT(*) FROM orders");
        $count = $stmt->fetchColumn();
        echo "<br>Total de pedidos: $count<br>";
    } else {
        echo "❌ Tabela 'orders' não existe<br>";
        echo "SQL para criar tabela orders:<br>";
        echo "CREATE TABLE orders (<br>";
        echo "id INT AUTO_INCREMENT PRIMARY KEY,<br>";
        echo "customer_name VARCHAR(100) NOT NULL,<br>";
        echo "order_date DATETIME DEFAULT CURRENT_TIMESTAMP,<br>";
        echo "status VARCHAR(20) DEFAULT 'pending',<br>";
        echo "total_amount DECIMAL(10,2) DEFAULT 0.00<br>";
        echo ");<br>";
    }

} catch(PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

// Verificar includes necessários
echo "<br>Verificando arquivos necessários:<br>";
$files_to_check = [
    '/modules/orders/index.php',
    '/config/database.php',
    '/components/header.php',
    '/components/sidebar.php',
    '/components/footer.php'
];

foreach ($files_to_check as $file) {
    $full_path = dirname(__FILE__) . $file;
    if (file_exists($full_path)) {
        echo "✅ " . $file . " existe<br>";
    } else {
        echo "❌ " . $file . " não encontrado<br>";
    }
}

?>
