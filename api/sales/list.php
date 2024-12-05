<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

try {
    // Parâmetros do DataTables
    $draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
    $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
    $length = isset($_GET['length']) ? intval($_GET['length']) : 10;
    $search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
    
    // Filtros adicionais
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
    $client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

    // Construir a query base
    $query = "
        SELECT 
            s.id,
            c.name as client_name,
            s.sale_date,
            s.subtotal,
            s.discount,
            s.total,
            s.payment_method,
            s.status
        FROM sales s
        JOIN clients c ON s.client_id = c.id
        WHERE 1=1
    ";

    $countQuery = "SELECT COUNT(*) as total FROM sales s WHERE 1=1";
    $params = [];
    $types = "";

    // Adicionar condições de filtro
    if ($search) {
        $query .= " AND (c.name LIKE ? OR s.id LIKE ?)";
        $countQuery .= " AND (c.name LIKE ? OR s.id LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= "ss";
    }

    if ($status) {
        $query .= " AND s.status = ?";
        $countQuery .= " AND s.status = ?";
        $params[] = $status;
        $types .= "s";
    }

    if ($start_date) {
        $query .= " AND DATE(s.sale_date) >= ?";
        $countQuery .= " AND DATE(s.sale_date) >= ?";
        $params[] = $start_date;
        $types .= "s";
    }

    if ($end_date) {
        $query .= " AND DATE(s.sale_date) <= ?";
        $countQuery .= " AND DATE(s.sale_date) <= ?";
        $params[] = $end_date;
        $types .= "s";
    }

    if ($client_id) {
        $query .= " AND s.client_id = ?";
        $countQuery .= " AND s.client_id = ?";
        $params[] = $client_id;
        $types .= "i";
    }

    // Ordenação
    $query .= " ORDER BY s.sale_date DESC";

    // Paginação
    $query .= " LIMIT ?, ?";
    $params[] = $start;
    $params[] = $length;
    $types .= "ii";

    // Preparar e executar a query de contagem
    $stmtCount = $conn->prepare($countQuery);
    if ($types && $params) {
        $stmtCount->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2));
    }
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $totalRecords = $resultCount->fetch_assoc()['total'];

    // Preparar e executar a query principal
    $stmt = $conn->prepare($query);
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        // Formatar os valores monetários
        $row['subtotal'] = number_format($row['subtotal'], 2, ',', '.');
        $row['discount'] = number_format($row['discount'], 2, ',', '.');
        $row['total'] = number_format($row['total'], 2, ',', '.');
        
        // Formatar o método de pagamento
        switch ($row['payment_method']) {
            case 'money':
                $row['payment_method'] = 'Dinheiro';
                break;
            case 'credit':
                $row['payment_method'] = 'Cartão de Crédito';
                break;
            case 'debit':
                $row['payment_method'] = 'Cartão de Débito';
                break;
            case 'pix':
                $row['payment_method'] = 'PIX';
                break;
        }
        
        // Formatar o status
        switch ($row['status']) {
            case 'pending':
                $row['status'] = '<span class="badge bg-warning">Pendente</span>';
                break;
            case 'paid':
                $row['status'] = '<span class="badge bg-success">Pago</span>';
                break;
            case 'cancelled':
                $row['status'] = '<span class="badge bg-danger">Cancelado</span>';
                break;
        }
        
        // Adicionar botões de ação
        $row['actions'] = '
            <button type="button" class="btn btn-sm btn-info me-1" data-action="view" data-id="'.$row['id'].'" aria-label="Ver detalhes da venda #'.$row['id'].'">
                <i class="bi bi-eye" aria-hidden="true"></i>
            </button>
            <button type="button" class="btn btn-sm btn-success me-1" data-action="pay" data-id="'.$row['id'].'" aria-label="Marcar venda #'.$row['id'].' como paga">
                <i class="bi bi-check-lg" aria-hidden="true"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger" data-action="cancel" data-id="'.$row['id'].'" aria-label="Cancelar venda #'.$row['id'].'">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        ';
        
        $data[] = $row;
    }

    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalRecords,
        'data' => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao listar vendas: ' . $e->getMessage()
    ]);
}

$conn->close();
