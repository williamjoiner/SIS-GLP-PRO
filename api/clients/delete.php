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
    if(!isset($_POST['id'])) {
        throw new Exception('ID do cliente não fornecido');
    }

    // Check if client has orders
    $query = "SELECT COUNT(*) as count FROM orders WHERE client_id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_POST['id']);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if($result['count'] > 0) {
        throw new Exception('Não é possível excluir o cliente pois existem pedidos associados');
    }

    $query = "DELETE FROM clients WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_POST['id']);

    if($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Cliente excluído com sucesso'
        ]);
    } else {
        throw new Exception('Erro ao excluir cliente');
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
