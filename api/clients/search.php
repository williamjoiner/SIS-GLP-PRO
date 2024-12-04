<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $term = isset($_GET['term']) ? $_GET['term'] : '';
    
    $sql = "SELECT c.*, 
            GROUP_CONCAT(DISTINCT cc.phone) as phones,
            GROUP_CONCAT(DISTINCT ca.address) as addresses
            FROM clients c
            LEFT JOIN client_contacts cc ON c.id = cc.client_id
            LEFT JOIN client_addresses ca ON c.id = ca.client_id
            WHERE c.name LIKE :term 
            OR cc.phone LIKE :term 
            OR ca.address LIKE :term
            GROUP BY c.id
            LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['term' => '%' . $term . '%']);
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $clients]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar clientes: ' . $e->getMessage()]);
}
