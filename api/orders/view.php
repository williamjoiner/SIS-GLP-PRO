<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth_check.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('ID da venda não fornecido ou inválido');
    }

    $database = new Database();
    $db = $database->getConnection();

    // Buscar dados da venda
    $query = "SELECT o.*, c.name as client_name
             FROM orders o
             LEFT JOIN clients c ON o.client_id = c.id
             WHERE o.id = :id";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $_GET['id'], PDO::PARAM_INT);
    $stmt->execute();

    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Venda não encontrada');
    }

    // Buscar itens da venda
    $query = "SELECT oi.*, p.name as product_name
             FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = :order_id";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':order_id', $_GET['id'], PDO::PARAM_INT);
    $stmt->execute();

    $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $order
    ]);

} catch(Exception $e) {
    error_log("Erro ao visualizar venda: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao visualizar venda: ' . $e->getMessage()
    ]);
}
