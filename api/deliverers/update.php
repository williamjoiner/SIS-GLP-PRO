<?php
header('Content-Type: application/json');
session_start();
require_once "../../config/database.php";

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

$database = new Database();
$db = $database->getConnection();

try {
    // Get and sanitize POST data
    $data = array_map(function($item) {
        return trim(htmlspecialchars(strip_tags($item)));
    }, $_POST);
    
    // Validate required fields
    $required_fields = ['id', 'name', 'phone', 'vehicle_type', 'license_plate', 'status'];
    $missing_fields = array_filter($required_fields, function($field) use ($data) {
        return empty($data[$field]);
    });
    
    if (!empty($missing_fields)) {
        throw new Exception('Campos obrigatórios faltando: ' . implode(', ', $missing_fields));
    }

    // Verify if deliverer exists
    $check_query = "SELECT id FROM deliverers WHERE id = :id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':id', $data['id']);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() === 0) {
        throw new Exception('Entregador não encontrado');
    }

    // Clean and validate phone number
    $phone = preg_replace('/\D/', '', $data['phone']);
    if (strlen($phone) !== 11) {
        throw new Exception('Número de telefone inválido. Use o formato (99) 99999-9999');
    }
    
    // Format phone number for storage
    $formatted_phone = sprintf('(%s) %s-%s',
        substr($phone, 0, 2),
        substr($phone, 2, 5),
        substr($phone, 7)
    );

    // Clean and validate license plate
    $license_plate = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $data['license_plate']));
    if (!preg_match('/^[A-Z]{3}[0-9]{4}$/', $license_plate)) {
        throw new Exception('Placa inválida. Use o formato AAA-9999');
    }
    $license_plate = substr($license_plate, 0, 3) . '-' . substr($license_plate, 3);

    // Validate vehicle type
    $valid_vehicle_types = ['Moto', 'Carro', 'Van', 'Caminhão'];
    if (!in_array($data['vehicle_type'], $valid_vehicle_types)) {
        throw new Exception('Tipo de veículo inválido');
    }

    // Validate status
    if (!in_array($data['status'], ['active', 'inactive'])) {
        throw new Exception('Status inválido');
    }

    // Check if license plate already exists (excluding current deliverer)
    $check_plate_query = "SELECT id FROM deliverers WHERE license_plate = :license_plate AND id != :id";
    $check_plate_stmt = $db->prepare($check_plate_query);
    $check_plate_stmt->bindParam(':license_plate', $license_plate);
    $check_plate_stmt->bindParam(':id', $data['id']);
    $check_plate_stmt->execute();
    
    if ($check_plate_stmt->rowCount() > 0) {
        throw new Exception('Esta placa já está cadastrada para outro entregador');
    }

    // Check if deliverer has active orders before setting as inactive
    if ($data['status'] === 'inactive') {
        $active_orders_query = "SELECT COUNT(*) as count FROM orders 
                              WHERE deliverer_id = :id 
                              AND status IN ('pending', 'processing')";
        $active_orders_stmt = $db->prepare($active_orders_query);
        $active_orders_stmt->bindParam(':id', $data['id']);
        $active_orders_stmt->execute();
        $result = $active_orders_stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            throw new Exception('Não é possível inativar entregador com entregas pendentes');
        }
    }

    // Prepare the update query
    $query = "UPDATE deliverers 
              SET name = :name, 
                  phone = :phone, 
                  vehicle_type = :vehicle_type, 
                  license_plate = :license_plate,
                  status = :status,
                  updated_at = CURRENT_TIMESTAMP
              WHERE id = :id";
              
    $stmt = $db->prepare($query);
    
    // Bind parameters
    $stmt->bindParam(':id', $data['id']);
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':phone', $formatted_phone);
    $stmt->bindParam(':vehicle_type', $data['vehicle_type']);
    $stmt->bindParam(':license_plate', $license_plate);
    $stmt->bindParam(':status', $data['status']);

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Entregador atualizado com sucesso',
            'data' => [
                'id' => $data['id'],
                'name' => $data['name'],
                'phone' => $formatted_phone,
                'vehicle_type' => $data['vehicle_type'],
                'license_plate' => $license_plate,
                'status' => $data['status']
            ]
        ]);
    } else {
        throw new Exception('Erro ao atualizar entregador no banco de dados');
    }

} catch (Exception $e) {
    error_log("Error in update.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
