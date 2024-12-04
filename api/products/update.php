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
    
    if(empty($data['id']) || empty($data['name']) || !isset($data['price']) || !isset($data['stock'])) {
        throw new Exception('ID, nome, preço e estoque são obrigatórios');
    }

    if($data['price'] < 0) {
        throw new Exception('O preço não pode ser negativo');
    }

    if($data['stock'] < 0) {
        throw new Exception('O estoque não pode ser negativo');
    }

    $query = "UPDATE products 
              SET name = :name, 
                  description = :description, 
                  price = :price, 
                  stock = :stock 
              WHERE id = :id";
              
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':id', $data['id']);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':price', $data['price']);
    $stmt->bindParam(':stock', $data['stock']);

    if($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Produto atualizado com sucesso'
        ]);
    } else {
        throw new Exception('Erro ao atualizar produto');
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
