<?php
require_once __DIR__ . '/../../auth/check_auth.php';
require_once __DIR__ . '/../../config/database.php';

// Buscar entregadores para o filtro
$database = new Database();
$db = $database->getConnection();

try {
    $query = "SELECT id, name FROM deliverers ORDER BY name ASC";
    $stmt = $db->query($query);
    $entregadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $entregadores = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIS GLP PRO - Relatórios de Vendas</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
    <link href="/assets/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include '../../components/sidebar.php'; ?>
        
        <div class="main-content">
            <!-- Top Bar -->
            <?php include '../../components/topbar.php'; ?>
            
            <!-- Content -->
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <h4>Relatórios de Vendas</h4>
                    </div>
                </div>
                
                <!-- Filtros -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Período</label>
                        <input type="text" id="dateRange" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select id="statusFilter" class="form-select">
                            <option value="">Todos</option>
                            <option value="pending">Pendente</option>
                            <option value="processing">Em Processamento</option>
                            <option value="out_for_delivery">Saiu para Entrega</option>
                            <option value="delivered">Entregue</option>
                            <option value="cancelled">Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Entregador</label>
                        <select id="delivererFilter" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($entregadores as $entregador): ?>
                                <option value="<?php echo $entregador['id']; ?>">
                                    <?php echo htmlspecialchars($entregador['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary w-100" onclick="loadReport()">
                            Atualizar
                        </button>
                    </div>
                </div>
                
                <!-- Resumo -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Total de Vendas</h6>
                                <h4 id="totalOrders" class="card-title">0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Valor Total</h6>
                                <h4 id="totalAmount" class="card-title">R$ 0,00</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Ticket Médio</h6>
                                <h4 id="averageOrderValue" class="card-title">R$ 0,00</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Taxa de Entrega</h6>
                                <h4 id="deliveryRate" class="card-title">0%</h4>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Gráficos -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Vendas por Dia</h5>
                                <canvas id="dailySalesChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Top 5 Produtos</h5>
                                <canvas id="topProductsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script src="/assets/js/orders_report.js"></script>
</body>
</html>
