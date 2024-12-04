<?php
require_once __DIR__ . '/../../auth/check_auth.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Receber dados do POST
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        throw new Exception('ID da venda não fornecido');
    }

    // Iniciar transação
    $db->beginTransaction();

    // Atualizar dados básicos da venda
    $query = "UPDATE orders SET 
              client_id = :client_id,
              deliverer_id = :deliverer_id,
              payment_method = :payment_method,
              notes = :notes,
              updated_at = NOW()
              WHERE id = :id";
              
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':id' => $data['id'],
        ':client_id' => $data['client_id'],
        ':deliverer_id' => $data['deliverer_id'],
        ':payment_method' => $data['payment_method'],
        ':notes' => $data['notes'] ?? null
    ]);

    // Remover produtos antigos
    $query = "DELETE FROM order_items WHERE order_id = :order_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':order_id' => $data['id']]);

    // Inserir novos produtos
    $query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
              SELECT :order_id, :product_id, :quantity, price 
              FROM products WHERE id = :product_id";
              
    $stmt = $db->prepare($query);
    
    foreach ($data['products'] as $product) {
        $stmt->execute([
            ':order_id' => $data['id'],
            ':product_id' => $product['product_id'],
            ':quantity' => $product['quantity']
        ]);
    }

    // Commit da transação
    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Venda atualizada com sucesso'
    ]);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar venda: ' . $e->getMessage()
    ]);
}
