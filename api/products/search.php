<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    $sql = "SELECT id, name, price, stock 
            FROM products 
            WHERE name LIKE :search 
            OR description LIKE :search 
            OR code LIKE :search 
            ORDER BY name 
            LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['search' => '%' . $search . '%']);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $results = array_map(function($product) {
        return [
            'id' => $product['id'],
            'text' => $product['name'] . ' - R$ ' . number_format($product['price'], 2, ',', '.'),
            'price' => $product['price'],
            'stock' => $product['stock']
        ];
    }, $products);
    
    echo json_encode($results);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar produtos: ' . $e->getMessage()]);
}
