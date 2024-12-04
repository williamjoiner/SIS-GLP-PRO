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
        throw new Exception('ID do cliente não fornecido');
    }

    $query = "SELECT * FROM clients WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();

    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($client) {
        echo json_encode([
            'success' => true,
            'data' => $client
        ]);
    } else {
        throw new Exception('Cliente não encontrado');
    }
} catch(Exception $e) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
