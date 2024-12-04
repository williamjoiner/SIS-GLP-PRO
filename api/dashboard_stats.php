<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    // Get today's sales
    $today = date('Y-m-d');
    $query = "SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = :today";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':today', $today);
    $stmt->execute();
    $today_sales = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get pending orders
    $query = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $pending_orders = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get total clients
    $query = "SELECT COUNT(*) as count FROM clients";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $total_clients = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get products with low stock (less than 10 items)
    $query = "SELECT COUNT(*) as count FROM products WHERE stock < 10";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $low_stock = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $response = [
        'today_sales' => $today_sales,
        'pending_orders' => $pending_orders,
        'total_clients' => $total_clients,
        'low_stock' => $low_stock
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
