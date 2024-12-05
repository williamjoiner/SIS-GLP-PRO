<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

try {
    // Vendas de hoje
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total
        FROM sales 
        WHERE DATE(sale_date) = CURDATE()
        AND status != 'cancelled'
    ");
    $stmt->execute();
    $today = $stmt->get_result()->fetch_assoc();

    // Vendas do mês
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total
        FROM sales 
        WHERE MONTH(sale_date) = MONTH(CURRENT_DATE())
        AND YEAR(sale_date) = YEAR(CURRENT_DATE())
        AND status != 'cancelled'
    ");
    $stmt->execute();
    $month = $stmt->get_result()->fetch_assoc();

    echo json_encode([
        'success' => true,
        'today_sales' => $today['count'],
        'today_revenue' => number_format($today['total'], 2, ',', '.'),
        'month_sales' => $month['count'],
        'month_revenue' => number_format($month['total'], 2, ',', '.')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar dashboard: ' . $e->getMessage()
    ]);
}

$conn->close();
