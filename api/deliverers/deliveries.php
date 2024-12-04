<?php
session_start();
require_once "../../config/database.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    if(!isset($_GET['id'])) {
        throw new Exception('ID do entregador não fornecido');
    }

    $query = "SELECT o.id as order_id, 
                     c.name as client_name, 
                     o.created_at,
                     o.status,
                     o.total_amount
              FROM orders o
              JOIN clients c ON o.client_id = c.id
              WHERE o.deliverer_id = :id
              ORDER BY o.created_at DESC
              LIMIT 50";
              
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();

    $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $deliveries
    ]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
