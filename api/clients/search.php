<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    $sql = "SELECT id, name, email, phone 
            FROM clients 
            WHERE name LIKE :search 
            OR email LIKE :search 
            OR phone LIKE :search 
            ORDER BY name 
            LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['search' => '%' . $search . '%']);
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $results = array_map(function($client) {
        return [
            'id' => $client['id'],
            'text' => $client['name'] . ' - ' . $client['phone']
        ];
    }, $clients);
    
    echo json_encode($results);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar clientes: ' . $e->getMessage()]);
}
