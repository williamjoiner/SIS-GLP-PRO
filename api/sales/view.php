<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID da venda não informado');
    }

    $sale_id = intval($_GET['id']);

    // Buscar dados da venda
    $stmt = $conn->prepare("
        SELECT 
            s.*,
            c.name as client_name,
            c.document as client_document
        FROM sales s
        JOIN clients c ON s.client_id = c.id
        WHERE s.id = ?
    ");

    $stmt->bind_param("i", $sale_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sale = $result->fetch_assoc();

    if (!$sale) {
        throw new Exception('Venda não encontrada');
    }

    // Buscar itens da venda
    $stmt = $conn->prepare("
        SELECT 
            si.*,
            p.name as product_name,
            p.code as product_code
        FROM sale_items si
        JOIN products p ON si.product_id = p.id
        WHERE si.sale_id = ?
    ");

    $stmt->bind_param("i", $sale_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'product_name' => $row['product_name'],
            'product_code' => $row['product_code'],
            'quantity' => $row['quantity'],
            'price' => number_format($row['price'], 2, ',', '.'),
            'total' => number_format($row['total'], 2, ',', '.')
        ];
    }

    // Formatar dados da venda
    $sale['subtotal'] = number_format($sale['subtotal'], 2, ',', '.');
    $sale['discount'] = number_format($sale['discount'], 2, ',', '.');
    $sale['total'] = number_format($sale['total'], 2, ',', '.');
    $sale['sale_date'] = date('d/m/Y H:i', strtotime($sale['sale_date']));

    switch ($sale['payment_method']) {
        case 'money':
            $sale['payment_method'] = 'Dinheiro';
            break;
        case 'credit':
            $sale['payment_method'] = 'Cartão de Crédito';
            break;
        case 'debit':
            $sale['payment_method'] = 'Cartão de Débito';
            break;
        case 'pix':
            $sale['payment_method'] = 'PIX';
            break;
    }

    switch ($sale['status']) {
        case 'pending':
            $sale['status'] = 'Pendente';
            break;
        case 'paid':
            $sale['status'] = 'Pago';
            break;
        case 'cancelled':
            $sale['status'] = 'Cancelado';
            break;
    }

    echo json_encode([
        'success' => true,
        'sale' => $sale,
        'items' => $items
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar venda: ' . $e->getMessage()
    ]);
}

$conn->close();
