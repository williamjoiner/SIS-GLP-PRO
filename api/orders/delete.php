<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth_check.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception('ID da venda não fornecido ou inválido');
    }

    $database = new Database();
    $db = $database->getConnection();

    // Iniciar transação
    $db->beginTransaction();

    // Primeiro excluir os itens da venda
    $query = "DELETE FROM order_items WHERE order_id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
    $stmt->execute();

    // Depois excluir a venda
    $query = "DELETE FROM orders WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        throw new Exception('Venda não encontrada');
    }

    // Confirmar transação
    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Venda excluída com sucesso'
    ]);

} catch(Exception $e) {
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
