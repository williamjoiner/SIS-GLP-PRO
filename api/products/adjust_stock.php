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
    $data = $_POST;
    
    if(empty($data['id']) || !isset($data['quantity']) || empty($data['type'])) {
        throw new Exception('ID do produto, quantidade e tipo de ajuste são obrigatórios');
    }

    if($data['quantity'] <= 0) {
        throw new Exception('A quantidade deve ser maior que zero');
    }

    // Get current stock
    $query = "SELECT stock FROM products WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data['id']);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$product) {
        throw new Exception('Produto não encontrado');
    }

    // Calculate new stock
    $newStock = $data['type'] === 'add' ? 
        $product['stock'] + $data['quantity'] : 
        $product['stock'] - $data['quantity'];

    if($newStock < 0) {
        throw new Exception('Estoque insuficiente para realizar a saída');
    }

    // Update stock
    $query = "UPDATE products SET stock = :stock WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':stock', $newStock);
    $stmt->bindParam(':id', $data['id']);

    // Start transaction
    $db->beginTransaction();

    if($stmt->execute()) {
        // Log stock adjustment
        $query = "INSERT INTO stock_movements (product_id, quantity, type, reason, user_id) 
                  VALUES (:product_id, :quantity, :type, :reason, :user_id)";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':product_id', $data['id']);
        $stmt->bindParam(':quantity', $data['quantity']);
        $stmt->bindParam(':type', $data['type']);
        $stmt->bindParam(':reason', $data['reason']);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        
        if($stmt->execute()) {
            $db->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Estoque ajustado com sucesso'
            ]);
        } else {
            throw new Exception('Erro ao registrar movimentação de estoque');
        }
    } else {
        throw new Exception('Erro ao ajustar estoque');
    }
} catch(Exception $e) {
    if($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
