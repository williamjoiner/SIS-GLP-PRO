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

// Validar ID
if (empty($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do cliente não informado']);
    exit;
}

// Validar dados obrigatórios
$required_fields = ['name', 'phone', 'zipcode', 'street', 'number', 'neighborhood', 'city', 'state'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Campo obrigatório não preenchido: ' . $field]);
        exit;
    }
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Atualizar o cliente
    $query = "UPDATE clients SET 
                name = :name,
                phone = :phone,
                zipcode = :zipcode,
                street = :street,
                number = :number,
                complement = :complement,
                neighborhood = :neighborhood,
                city = :city,
                state = :state,
                notes = :notes
              WHERE id = :id";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':id' => $_POST['id'],
        ':name' => $_POST['name'],
        ':phone' => $_POST['phone'],
        ':zipcode' => $_POST['zipcode'],
        ':street' => $_POST['street'],
        ':number' => $_POST['number'],
        ':complement' => $_POST['complement'] ?? null,
        ':neighborhood' => $_POST['neighborhood'],
        ':city' => $_POST['city'],
        ':state' => $_POST['state'],
        ':notes' => $_POST['notes'] ?? null
    ]);
    
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Cliente não encontrado']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Cliente atualizado com sucesso'
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao atualizar cliente: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar cliente: ' . $e->getMessage()
    ]);
}
