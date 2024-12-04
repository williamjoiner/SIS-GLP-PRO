<?php
session_start();
require_once "../../config/database.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    $query = "SELECT d.*, COUNT(o.id) as delivery_count 
              FROM deliverers d 
              LEFT JOIN orders o ON d.id = o.deliverer_id 
              GROUP BY d.id 
              ORDER BY d.name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $deliverers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $deliverers
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
