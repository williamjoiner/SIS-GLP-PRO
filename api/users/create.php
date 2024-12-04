<?php
require_once __DIR__ . '/../../auth/check_auth.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verificar se o usuário atual é admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

// Validar dados
if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['role'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Verificar se o usuário já existe
    $query = "SELECT id FROM users WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->execute([':username' => $_POST['username']]);
    
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Nome de usuário já existe']);
        exit;
    }
    
    // Criar o usuário
    $query = "INSERT INTO users (username, password, role, created_at) 
              VALUES (:username, :password, :role, NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':username' => $_POST['username'],
        ':password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        ':role' => $_POST['role']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Usuário criado com sucesso'
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao criar usuário: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao criar usuário: ' . $e->getMessage()
    ]);
}
