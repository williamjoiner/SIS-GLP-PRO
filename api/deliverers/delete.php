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
        throw new Exception('ID do entregador não fornecido');
    }

    // Check if deliverer has any orders
    $query = "SELECT COUNT(*) as count FROM orders WHERE deliverer_id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_POST['id']);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if($result['count'] > 0) {
        throw new Exception('Não é possível excluir o entregador pois existem pedidos associados');
    }

    $query = "DELETE FROM deliverers WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_POST['id']);

    if($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Entregador excluído com sucesso'
        ]);
    } else {
        throw new Exception('Erro ao excluir entregador');
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>