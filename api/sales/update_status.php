<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || !isset($data['status'])) {
        throw new Exception('Dados inválidos');
    }

    $valid_statuses = ['pending', 'paid', 'cancelled'];
    if (!in_array($data['status'], $valid_statuses)) {
        throw new Exception('Status inválido');
    }

    $conn->begin_transaction();

    // Atualizar status da venda
    $stmt = $conn->prepare("UPDATE sales SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $data['status'], $data['id']);
    $stmt->execute();

    // Se a venda foi cancelada, devolver os produtos ao estoque
    if ($data['status'] === 'cancelled') {
        // Buscar itens da venda
        $stmt = $conn->prepare("
            SELECT product_id, quantity 
            FROM sale_items 
            WHERE sale_id = ?
        ");
        $stmt->bind_param("i", $data['id']);
        $stmt->execute();
        $result = $stmt->get_result();

        // Atualizar estoque
        $stmt = $conn->prepare("
            UPDATE products 
            SET stock = stock + ? 
            WHERE id = ?
        ");

        while ($item = $result->fetch_assoc()) {
            $stmt->bind_param("di", $item['quantity'], $item['product_id']);
            $stmt->execute();
        }
    }

    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Status da venda atualizado com sucesso'
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar status: ' . $e->getMessage()
    ]);
}

$conn->close();
