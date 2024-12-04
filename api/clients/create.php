<?php
require_once __DIR__ . '/../../auth/check_auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/validation.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $db->beginTransaction();
    
    // Validar dados do cliente
    $name = trim($_POST['name']);
    $document = preg_replace('/[^0-9]/', '', $_POST['document']);
    $type = $_POST['type'];
    
    if (empty($name)) {
        throw new Exception('Nome é obrigatório');
    }
    
    if (empty($document)) {
        throw new Exception('CPF/CNPJ é obrigatório');
    }
    
    // Validar CPF/CNPJ
    if ($type === 'pf' && !validateCPF($document)) {
        throw new Exception('CPF inválido');
    } else if ($type === 'pj' && !validateCNPJ($document)) {
        throw new Exception('CNPJ inválido');
    }
    
    // Verificar se já existe cliente com este documento
    $stmt = $db->prepare("SELECT id FROM clients WHERE document = ?");
    $stmt->execute([$document]);
    if ($stmt->fetch()) {
        throw new Exception('Já existe um cliente com este CPF/CNPJ');
    }
    
    // Inserir cliente
    $query = "INSERT INTO clients (name, document, type) VALUES (:name, :document, :type)";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':name' => $name,
        ':document' => $document,
        ':type' => $type
    ]);
    
    $client_id = $db->lastInsertId();
    
    // Inserir contatos
    $phones = $_POST['phones'];
    $emails = $_POST['emails'];
    
    $query = "INSERT INTO client_contacts (client_id, type, value, is_default) VALUES (:client_id, :type, :value, :is_default)";
    $stmt = $db->prepare($query);
    
    foreach ($phones as $i => $phone) {
        if (!empty($phone)) {
            $stmt->execute([
                ':client_id' => $client_id,
                ':type' => 'phone',
                ':value' => preg_replace('/[^0-9]/', '', $phone),
                ':is_default' => $i === 0
            ]);
        }
    }
    
    foreach ($emails as $i => $email) {
        if (!empty($email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido: ' . $email);
            }
            $stmt->execute([
                ':client_id' => $client_id,
                ':type' => 'email',
                ':value' => $email,
                ':is_default' => $i === 0
            ]);
        }
    }
    
    // Inserir endereços
    $addresses = json_decode($_POST['addresses'], true);
    
    $query = "INSERT INTO client_addresses (
                client_id, street, number, complement, neighborhood, 
                city, state, zip_code, is_default
              ) VALUES (
                :client_id, :street, :number, :complement, :neighborhood,
                :city, :state, :zip_code, :is_default
              )";
    $stmt = $db->prepare($query);
    
    foreach ($addresses as $i => $address) {
        $stmt->execute([
            ':client_id' => $client_id,
            ':street' => $address['street'],
            ':number' => $address['number'],
            ':complement' => $address['complement'] ?? null,
            ':neighborhood' => $address['neighborhood'],
            ':city' => $address['city'],
            ':state' => $address['state'],
            ':zip_code' => preg_replace('/[^0-9]/', '', $address['zip_code']),
            ':is_default' => $i === 0
        ]);
    }
    
    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Cliente cadastrado com sucesso']);
    
} catch(Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao cadastrar cliente: ' . $e->getMessage()
    ]);
}
