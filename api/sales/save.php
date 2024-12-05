<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Dados inválidos');
    }

    $conn->begin_transaction();

    // Inserir a venda
    $stmt = $conn->prepare("
        INSERT INTO sales (client_id, sale_date, subtotal, discount, total, payment_method, status)
        VALUES (?, NOW(), ?, ?, ?, ?, 'pending')
    ");

    $stmt->bind_param(
        "iddds",
        $data['client_id'],
        $data['subtotal'],
        $data['discount'],
        $data['total'],
        $data['payment_method']
    );

    $stmt->execute();
    $sale_id = $conn->insert_id;

    // Inserir os itens da venda
    $stmt = $conn->prepare("
        INSERT INTO sale_items (sale_id, product_id, quantity, price, total)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($data['products'] as $product) {
        $stmt->bind_param(
            "iiddd",
            $sale_id,
            $product['product_id'],
            $product['quantity'],
            $product['price'],
            $product['total']
        );
        $stmt->execute();
    }

    // Atualizar o estoque dos produtos
    $stmt = $conn->prepare("
        UPDATE products 
        SET stock = stock - ? 
        WHERE id = ?
    ");

    foreach ($data['products'] as $product) {
        $stmt->bind_param(
            "di",
            $product['quantity'],
            $product['product_id']
        );
        $stmt->execute();
    }

    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Venda registrada com sucesso',
        'sale_id' => $sale_id
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao registrar venda: ' . $e->getMessage()
    ]);
}

$conn->close();
