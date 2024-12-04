<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Verificando tabelas...<br><br>";
    
    // Verificar tabela orders
    $stmt = $db->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela 'orders' existe<br>";
        $stmt = $db->query("DESCRIBE orders");
        echo "Estrutura da tabela orders:<br>";
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}<br>";
        }
    } else {
        echo "❌ Tabela 'orders' não existe<br>";
        echo "SQL para criar:<br>";
        echo "CREATE TABLE orders (<br>";
        echo "id INT AUTO_INCREMENT PRIMARY KEY,<br>";
        echo "client_id INT,<br>";
        echo "deliverer_id INT,<br>";
        echo "total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,<br>";
        echo "status ENUM('pending','processing','delivered','cancelled') DEFAULT 'pending',<br>";
        echo "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,<br>";
        echo "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,<br>";
        echo "FOREIGN KEY (client_id) REFERENCES clients(id),<br>";
        echo "FOREIGN KEY (deliverer_id) REFERENCES deliverers(id)<br>";
        echo ");<br><br>";
    }
    
    // Verificar tabela order_items
    $stmt = $db->query("SHOW TABLES LIKE 'order_items'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela 'order_items' existe<br>";
        $stmt = $db->query("DESCRIBE order_items");
        echo "Estrutura da tabela order_items:<br>";
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}<br>";
        }
    } else {
        echo "❌ Tabela 'order_items' não existe<br>";
        echo "SQL para criar:<br>";
        echo "CREATE TABLE order_items (<br>";
        echo "id INT AUTO_INCREMENT PRIMARY KEY,<br>";
        echo "order_id INT NOT NULL,<br>";
        echo "product_id INT NOT NULL,<br>";
        echo "quantity INT NOT NULL DEFAULT 1,<br>";
        echo "price DECIMAL(10,2) NOT NULL,<br>";
        echo "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,<br>";
        echo "FOREIGN KEY (order_id) REFERENCES orders(id),<br>";
        echo "FOREIGN KEY (product_id) REFERENCES products(id)<br>";
        echo ");<br><br>";
    }
    
    // Verificar tabela clients
    $stmt = $db->query("SHOW TABLES LIKE 'clients'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela 'clients' existe<br>";
        $stmt = $db->query("DESCRIBE clients");
        echo "Estrutura da tabela clients:<br>";
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}<br>";
        }
    } else {
        echo "❌ Tabela 'clients' não existe<br>";
        echo "SQL para criar:<br>";
        echo "CREATE TABLE clients (<br>";
        echo "id INT AUTO_INCREMENT PRIMARY KEY,<br>";
        echo "name VARCHAR(100) NOT NULL,<br>";
        echo "phone VARCHAR(20),<br>";
        echo "address TEXT,<br>";
        echo "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP<br>";
        echo ");<br><br>";
    }
    
    // Verificar tabela deliverers
    $stmt = $db->query("SHOW TABLES LIKE 'deliverers'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela 'deliverers' existe<br>";
        $stmt = $db->query("DESCRIBE deliverers");
        echo "Estrutura da tabela deliverers:<br>";
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}<br>";
        }
    } else {
        echo "❌ Tabela 'deliverers' não existe<br>";
        echo "SQL para criar:<br>";
        echo "CREATE TABLE deliverers (<br>";
        echo "id INT AUTO_INCREMENT PRIMARY KEY,<br>";
        echo "name VARCHAR(100) NOT NULL,<br>";
        echo "phone VARCHAR(20),<br>";
        echo "status ENUM('active','inactive') DEFAULT 'active',<br>";
        echo "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP<br>";
        echo ");<br><br>";
    }
    
    // Verificar tabela products
    $stmt = $db->query("SHOW TABLES LIKE 'products'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela 'products' existe<br>";
        $stmt = $db->query("DESCRIBE products");
        echo "Estrutura da tabela products:<br>";
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}<br>";
        }
    } else {
        echo "❌ Tabela 'products' não existe<br>";
        echo "SQL para criar:<br>";
        echo "CREATE TABLE products (<br>";
        echo "id INT AUTO_INCREMENT PRIMARY KEY,<br>";
        echo "name VARCHAR(100) NOT NULL,<br>";
        echo "description TEXT,<br>";
        echo "price DECIMAL(10,2) NOT NULL,<br>";
        echo "stock INT NOT NULL DEFAULT 0,<br>";
        echo "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP<br>";
        echo ");<br><br>";
    }

} catch(PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
