<?php
require_once __DIR__ . '/../../auth/check_auth.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Iniciar transação
    $db->beginTransaction();
    
    // Inserir a venda
    $query = "INSERT INTO orders (client_id, deliverer_id, total_amount, status, created_at) 
              VALUES (:client_id, :deliverer_id, :total_amount, :status, NOW())";
    
    $stmt = $db->prepare($query);
    
    // Calcular o total
    $total = 0;
    $products = $_POST['products'];
    $quantities = $_POST['quantities'];
    
    // Buscar preços dos produtos
    $productPrices = [];
    $placeholders = str_repeat('?,', count($products) - 1) . '?';
    $query = "SELECT id, price FROM products WHERE id IN ($placeholders)";
    $stmt2 = $db->prepare($query);
    $stmt2->execute($products);
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        $productPrices[$row['id']] = $row['price'];
    }
    
    // Calcular total
    foreach ($products as $i => $product_id) {
        if (isset($productPrices[$product_id])) {
            $total += $productPrices[$product_id] * $quantities[$i];
        }
    }
    
    // Inserir a venda
    $stmt->execute([
        ':client_id' => $_POST['client_id'],
        ':deliverer_id' => $_POST['deliverer_id'],
        ':total_amount' => $total,
        ':status' => $_POST['status']
    ]);
    
    $order_id = $db->lastInsertId();
    
    // Inserir os itens da venda
    $query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
              VALUES (:order_id, :product_id, :quantity, :price)";
    $stmt = $db->prepare($query);
    
    foreach ($products as $i => $product_id) {
        if (isset($productPrices[$product_id])) {
            $stmt->execute([
                ':order_id' => $order_id,
                ':product_id' => $product_id,
                ':quantity' => $quantities[$i],
                ':price' => $productPrices[$product_id]
            ]);
        }
    }
    
    // Commit da transação
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Venda criada com sucesso',
        'order_id' => $order_id
    ]);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    if (isset($db)) {
        $db->rollBack();
    }
    
    error_log("Erro ao criar venda: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao criar venda: ' . $e->getMessage()
    ]);
}
