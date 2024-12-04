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
    
    $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    $status = $_GET['status'] ?? null;
    $deliverer_id = $_GET['deliverer_id'] ?? null;
    
    // Base query
    $query = "SELECT 
                DATE(o.created_at) as date,
                COUNT(*) as total_orders,
                SUM(o.total) as total_amount,
                COUNT(CASE WHEN o.status = 'delivered' THEN 1 END) as delivered_orders,
                COUNT(CASE WHEN o.status = 'cancelled' THEN 1 END) as cancelled_orders
              FROM orders o
              WHERE DATE(o.created_at) BETWEEN :start_date AND :end_date";
    
    $params = [
        ':start_date' => $start_date,
        ':end_date' => $end_date
    ];
    
    if ($status) {
        $query .= " AND o.status = :status";
        $params[':status'] = $status;
    }
    
    if ($deliverer_id) {
        $query .= " AND o.deliverer_id = :deliverer_id";
        $params[':deliverer_id'] = $deliverer_id;
    }
    
    $query .= " GROUP BY DATE(o.created_at)
                ORDER BY date DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $daily_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get summary stats
    $query = "SELECT 
                COUNT(*) as total_orders,
                SUM(total) as total_amount,
                AVG(total) as average_order_value,
                COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered_orders,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders
              FROM orders
              WHERE DATE(created_at) BETWEEN :start_date AND :end_date";
    
    if ($status) {
        $query .= " AND status = :status";
    }
    
    if ($deliverer_id) {
        $query .= " AND deliverer_id = :deliverer_id";
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get top products
    $query = "SELECT 
                p.name,
                SUM(op.quantity) as total_quantity,
                SUM(op.quantity * p.price) as total_amount
              FROM order_products op
              JOIN products p ON op.product_id = p.id
              JOIN orders o ON op.order_id = o.id
              WHERE DATE(o.created_at) BETWEEN :start_date AND :end_date";
    
    if ($status) {
        $query .= " AND o.status = :status";
    }
    
    if ($deliverer_id) {
        $query .= " AND o.deliverer_id = :deliverer_id";
    }
    
    $query .= " GROUP BY p.id
                ORDER BY total_quantity DESC
                LIMIT 5";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'daily_stats' => $daily_stats,
            'summary' => $summary,
            'top_products' => $top_products
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
    ]);
}
