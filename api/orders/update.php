<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth_check.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    $database = new Database();
    $db = $database->getConnection();
    
    // Receber dados do pedido
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['order_id'])) {
        throw new Exception('ID do pedido não fornecido');
    }

    // Verificar se o pedido existe
    $stmt = $db->prepare("SELECT id, status FROM orders WHERE id = ?");
    $stmt->execute([$data['order_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Pedido não encontrado');
    }

    // Iniciar transação
    $db->beginTransaction();

    try {
        // Atualizar status do pedido
        if (isset($data['status'])) {
            $validStatus = ['pending', 'processing', 'completed', 'cancelled'];
            if (!in_array($data['status'], $validStatus)) {
                throw new Exception('Status inválido');
            }

            // Se estiver cancelando o pedido, devolver os itens ao estoque
            if ($data['status'] === 'cancelled' && $order['status'] !== 'cancelled') {
                $stmt = $db->prepare("
                    SELECT product_id, quantity 
                    FROM order_items 
                    WHERE order_id = ?
                ");
                $stmt->execute([$data['order_id']]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($items as $item) {
                    $updateStmt = $db->prepare("
                        UPDATE products 
                        SET stock = stock + :quantity 
                        WHERE id = :product_id
                    ");
                    $updateStmt->execute([
                        ':quantity' => $item['quantity'],
                        ':product_id' => $item['product_id']
                    ]);
                }
            }

            $stmt = $db->prepare("
                UPDATE orders 
                SET status = :status, 
                    updated_at = NOW() 
                WHERE id = :order_id
            ");
            $stmt->execute([
                ':status' => $data['status'],
                ':order_id' => $data['order_id']
            ]);
        }

        // Se houver itens para atualizar
        if (isset($data['items'])) {
            // Primeiro, recuperar os itens atuais para devolver ao estoque
            $stmt = $db->prepare("
                SELECT product_id, quantity 
                FROM order_items 
                WHERE order_id = ?
            ");
            $stmt->execute([$data['order_id']]);
            $currentItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Devolver itens ao estoque
            foreach ($currentItems as $item) {
                $updateStmt = $db->prepare("
                    UPDATE products 
                    SET stock = stock + :quantity 
                    WHERE id = :product_id
                ");
                $updateStmt->execute([
                    ':quantity' => $item['quantity'],
                    ':product_id' => $item['product_id']
                ]);
            }

            // Remover itens antigos
            $stmt = $db->prepare("DELETE FROM order_items WHERE order_id = ?");
            $stmt->execute([$data['order_id']]);

            // Validar e inserir novos itens
            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                if (!isset($item['product_id']) || !isset($item['quantity']) || $item['quantity'] <= 0) {
                    throw new Exception('Dados de item inválidos');
                }

                // Verificar produto e estoque
                $stmt = $db->prepare("SELECT id, price, stock FROM products WHERE id = ?");
                $stmt->execute([$item['product_id']]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    throw new Exception('Produto não encontrado: ' . $item['product_id']);
                }

                if ($product['stock'] < $item['quantity']) {
                    throw new Exception('Estoque insuficiente para o produto: ' . $item['product_id']);
                }

                // Inserir novo item
                $stmt = $db->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price)
                    VALUES (:order_id, :product_id, :quantity, :price)
                ");
                $stmt->execute([
                    ':order_id' => $data['order_id'],
                    ':product_id' => $item['product_id'],
                    ':quantity' => $item['quantity'],
                    ':price' => $product['price']
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

                $totalAmount += $product['price'] * $item['quantity'];
            }

            // Atualizar total do pedido
            $stmt = $db->prepare("
                UPDATE orders 
                SET total_amount = :total_amount,
                    updated_at = NOW()
                WHERE id = :order_id
            ");
            $stmt->execute([
                ':total_amount' => $totalAmount,
                ':order_id' => $data['order_id']
            ]);
        }

        // Commit da transação
        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Pedido atualizado com sucesso'
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch(Exception $e) {
    error_log("Erro ao atualizar pedido: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar pedido: ' . $e->getMessage()
    ]);
}
