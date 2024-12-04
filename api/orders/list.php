<?php
require_once __DIR__ . '/../../auth/check_auth.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT o.*, 
                     c.name as client_name,
                     d.name as deliverer_name,
                     COALESCE((SELECT SUM(oi.quantity * oi.price)
                      FROM order_items oi
                      WHERE oi.order_id = o.id), 0) as total_amount
              FROM orders o
              LEFT JOIN clients c ON o.client_id = c.id
              LEFT JOIN deliverers d ON o.deliverer_id = d.id
              ORDER BY o.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatar os dados para o DataTables
    $formattedOrders = array_map(function($order) {
        // Garantir que todos os campos necessários existam
        $order['total_amount'] = floatval($order['total_amount']);
        $order['status'] = $order['status'] ?? 'pending';
        $order['client_name'] = $order['client_name'] ?? 'Cliente não encontrado';
        $order['deliverer_name'] = $order['deliverer_name'] ?? 'Entregador não encontrado';
        $order['created_at'] = date('Y-m-d H:i:s', strtotime($order['created_at']));
        
        return $order;
    }, $orders);

    echo json_encode([
        'draw' => isset($_GET['draw']) ? intval($_GET['draw']) : 1,
        'recordsTotal' => count($orders),
        'recordsFiltered' => count($orders),
        'data' => $formattedOrders
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao listar pedidos: ' . $e->getMessage()
    ]);
}
