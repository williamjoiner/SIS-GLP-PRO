<?php
require_once __DIR__ . '/../../auth/check_auth.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
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
                     d.name as deliverer_name,
                     d.phone as deliverer_phone
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
    $query = "SELECT op.*, p.name, p.price 
              FROM order_products op 
              JOIN products p ON op.product_id = p.id 
              WHERE op.order_id = :order_id";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':order_id' => $_GET['id']]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar mensagem para WhatsApp
    $message = "🛵 *NOVO PEDIDO #" . $order['id'] . "*\n\n";
    
    // Informações do Cliente
    $message .= "*Cliente:* " . $order['client_name'] . "\n";
    $message .= "*Telefone:* " . $order['client_phone'] . "\n";
    $message .= "*Endereço:* " . $order['client_address'] . "\n\n";
    
    // Produtos
    $message .= "*ITENS DO PEDIDO:*\n";
    foreach ($products as $product) {
        $subtotal = $product['quantity'] * $product['price'];
        $message .= "▫️ " . $product['quantity'] . "x " . $product['name'];
        $message .= " - R$ " . number_format($subtotal, 2, ',', '.') . "\n";
    }
    
    // Total
    $message .= "\n*Total: R$ " . number_format($order['total_amount'], 2, ',', '.') . "*\n\n";
    
    // Status
    $statusMap = [
        'pending' => 'Pendente',
        'processing' => 'Em Processamento',
        'out_for_delivery' => 'Saiu para Entrega',
        'delivered' => 'Entregue',
        'cancelled' => 'Cancelado'
    ];
    $message .= "*Status:* " . ($statusMap[$order['status']] ?? $order['status']) . "\n";
    
    // Formatar número do WhatsApp (remover caracteres não numéricos)
    $whatsapp = preg_replace('/[^0-9]/', '', $order['deliverer_phone']);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'message' => $message,
            'whatsapp' => $whatsapp
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar venda: ' . $e->getMessage()
    ]);
}
