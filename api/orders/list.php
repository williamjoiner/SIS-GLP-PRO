<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth_check.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();

    // Parâmetros de paginação e ordenação
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    
    // Parâmetros de filtro
    $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $startDate = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $endDate = isset($_POST['end_date']) ? $_POST['end_date'] : '';
    $customerId = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0;

    // Construir a query base
    $baseQuery = "FROM orders o
                  LEFT JOIN clients c ON o.client_id = c.id
                  WHERE 1=1";
    $params = [];

    // Adicionar filtros
    if (!empty($search)) {
        $baseQuery .= " AND (c.name LIKE :search OR o.id LIKE :search)";
        $params[':search'] = "%{$search}%";
    }

    if (!empty($status)) {
        $baseQuery .= " AND o.status = :status";
        $params[':status'] = $status;
    }

    if (!empty($startDate)) {
        $baseQuery .= " AND DATE(o.created_at) >= :start_date";
        $params[':start_date'] = $startDate;
    }

    if (!empty($endDate)) {
        $baseQuery .= " AND DATE(o.created_at) <= :end_date";
        $params[':end_date'] = $endDate;
    }

    if ($customerId > 0) {
        $baseQuery .= " AND o.client_id = :customer_id";
        $params[':customer_id'] = $customerId;
    }

    // Contar total de registros
    $countQuery = "SELECT COUNT(*) as total " . $baseQuery;
    $stmt = $db->prepare($countQuery);
    $stmt->execute($params);
    $totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $filteredRecords = $totalRecords;

    // Query principal com paginação
    $query = "SELECT o.*, 
                     c.name as client_name,
                     COALESCE((SELECT SUM(oi.quantity * oi.price)
                      FROM order_items oi
                      WHERE oi.order_id = o.id), 0) as subtotal
              " . $baseQuery . "
              ORDER BY o.created_at DESC
              LIMIT :start, :length";

    $stmt = $db->prepare($query);
    
    // Bind dos parâmetros de paginação
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    
    // Bind dos demais parâmetros
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatar os dados para o DataTables
    $formattedOrders = array_map(function($order) {
        $paymentMethods = [
            'money' => 'Dinheiro',
            'credit' => 'Cartão de Crédito',
            'debit' => 'Cartão de Débito',
            'pix' => 'PIX'
        ];

        return [
            'id' => $order['id'],
            'client_name' => $order['client_name'] ?? 'Cliente não encontrado',
            'created_at' => $order['created_at'],
            'subtotal' => $order['subtotal'],
            'discount' => $order['discount'],
            'total_amount' => $order['total_amount'],
            'payment_method' => $paymentMethods[$order['payment_method']] ?? $order['payment_method'],
            'status' => $order['status'] ?? 'pending',
            'notes' => $order['notes']
        ];
    }, $orders);

    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $formattedOrders
    ]);

} catch(PDOException $e) {
    error_log("Erro ao listar vendas: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao listar vendas: ' . $e->getMessage()
    ]);
}
