<?php
require_once __DIR__ . '/../../auth/check_auth.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Obter dados do POST
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da venda não informado']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Iniciar transação
    $db->beginTransaction();
    
    // Excluir os itens da venda
    $query = "DELETE FROM order_items WHERE order_id = :order_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':order_id' => $data['id']]);
    
    // Excluir a venda
    $query = "DELETE FROM orders WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $data['id']]);
    
    // Commit da transação
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Venda excluída com sucesso'
    ]);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    if (isset($db)) {
        $db->rollBack();
    }
    
    error_log("Erro ao excluir venda: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao excluir venda: ' . $e->getMessage()
    ]);
}
