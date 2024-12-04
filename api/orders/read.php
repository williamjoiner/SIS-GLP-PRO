<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID do pedido não fornecido'
    ]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Buscar informações da venda
    $query = "SELECT o.*, 
                     c.name as client_name, 
                     c.phone as client_phone,
                     c.address as client_address,
                     d.name as deliverer_name 
              FROM orders o 
              LEFT JOIN clients c ON o.client_id = c.id 
              LEFT JOIN deliverers d ON o.deliverer_id = d.id 
              WHERE o.id = :id";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $_GET['id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Venda não encontrada']);
        exit;
    }
    
    // Buscar produtos da venda
    $query = "SELECT op.product_id, op.quantity, op.price as unit_price,
                     p.name as product_name, p.price as current_price 
              FROM order_products op 
              JOIN products p ON op.product_id = p.id 
              WHERE op.order_id = :order_id";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':order_id' => $_GET['id']]);
    $order['products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar histórico de status
    $query = "SELECT * FROM order_status_history 
              WHERE order_id = :order_id 
              ORDER BY created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':order_id' => $_GET['id']]);
    $order['status_history'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $order]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar venda: ' . $e->getMessage()
    ]);
}
?>
