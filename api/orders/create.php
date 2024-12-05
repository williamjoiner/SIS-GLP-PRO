<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth_check.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    $database = new Database();
    $db = $database->getConnection();
    
    // Receber dados do pedido
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['customer_id']) || empty($data['items'])) {
        throw new Exception('Dados incompletos do pedido');
    }

    // Validar cliente
    $stmt = $db->prepare("SELECT id FROM clients WHERE id = ?");
    $stmt->execute([$data['customer_id']]);
    if (!$stmt->fetch()) {
        throw new Exception('Cliente não encontrado');
    }

    // Validar produtos e calcular total
    $totalAmount = 0;
    $validatedItems = [];
    
    foreach ($data['items'] as $item) {
        if (!isset($item['product_id']) || !isset($item['quantity']) || $item['quantity'] <= 0) {
            throw new Exception('Dados de item inválidos');
        }

        $stmt = $db->prepare("SELECT id, price, stock FROM products WHERE id = ?");
        $stmt->execute([$item['product_id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception('Produto não encontrado: ' . $item['product_id']);
        }

        if ($product['stock'] < $item['quantity']) {
            throw new Exception('Estoque insuficiente para o produto: ' . $item['product_id']);
        }

        $validatedItems[] = [
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'price' => $product['price']
        ];

        $totalAmount += $product['price'] * $item['quantity'];
    }

    // Validar método de pagamento
    $validPaymentMethods = ['money', 'credit', 'debit', 'pix'];
    if (!isset($data['payment_method']) || !in_array($data['payment_method'], $validPaymentMethods)) {
        throw new Exception('Método de pagamento inválido');
    }

    // Validar desconto
    $discount = isset($data['discount']) ? floatval($data['discount']) : 0;
    if ($discount < 0 || $discount > $totalAmount) {
        throw new Exception('Valor de desconto inválido');
    }

    // Calcular total final
    $finalAmount = $totalAmount - $discount;

    // Iniciar transação
    $db->beginTransaction();

    try {
        // Criar pedido
        $stmt = $db->prepare("
            INSERT INTO orders (
                client_id, 
                total_amount, 
                discount,
                payment_method,
                notes,
                status, 
                created_at
            ) VALUES (
                :client_id, 
                :total_amount, 
                :discount,
                :payment_method,
                :notes,
                :status, 
                NOW()
            )
        ");

        $stmt->execute([
            ':client_id' => $data['customer_id'],
            ':total_amount' => $finalAmount,
            ':discount' => $discount,
            ':payment_method' => $data['payment_method'],
            ':notes' => $data['notes'] ?? '',
            ':status' => 'pending'
        ]);

        $orderId = $db->lastInsertId();

        // Inserir itens do pedido
        $stmt = $db->prepare("
            INSERT INTO order_items (
                order_id, 
                product_id, 
                quantity, 
                price
            ) VALUES (
                :order_id, 
                :product_id, 
                :quantity, 
                :price
            )
        ");

        foreach ($validatedItems as $item) {
            $stmt->execute([
                ':order_id' => $orderId,
                ':product_id' => $item['product_id'],
                ':quantity' => $item['quantity'],
                ':price' => $item['price']
            ]);

            // Atualizar estoque
            $updateStmt = $db->prepare("
                UPDATE products 
                SET stock = stock - :quantity 
                WHERE id = :product_id
            ");
            
            $updateStmt->execute([
                ':quantity' => $item['quantity'],
                ':product_id' => $item['product_id']
            ]);
        }

        // Commit da transação
        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Venda criada com sucesso',
            'order_id' => $orderId
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch(Exception $e) {
    error_log("Erro ao criar venda: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
