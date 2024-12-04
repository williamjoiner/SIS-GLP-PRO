<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_POST['id']) || !isset($_POST['status'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID do pedido ou status não fornecido'
    ]);
    exit;
}

$validStatus = ['pending', 'processing', 'delivered', 'cancelled'];
if (!in_array($_POST['status'], $validStatus)) {
    echo json_encode([
        'success' => false,
        'message' => 'Status inválido'
    ]);
    exit;
}

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->beginTransaction();
    
    // Atualizar status da venda
    $query = "UPDATE orders 
              SET status = :status,
                  updated_at = NOW()
              WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':status' => $_POST['status'],
        ':id' => $_POST['id']
    ]);

    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Pedido não encontrado'
        ]);
        exit;
    }

    // Registrar no histórico
    $query = "INSERT INTO order_status_history (order_id, status, comments) VALUES (:order_id, :status, :comments)";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':order_id' => $_POST['id'],
        ':status' => $_POST['status'],
        ':comments' => $_POST['comments'] ?? null
    ]);

    $db->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Status do pedido atualizado com sucesso'
    ]);
} catch(PDOException $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar status: ' . $e->getMessage()
    ]);
}
?>
