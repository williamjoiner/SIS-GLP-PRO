<?php
session_start();
require_once '../../config/database.php';
require_once '../../helpers/auth_check.php';

$currentPage = 'orders';

// Inicializar conexão
$database = new Database();
$db = $database->getConnection();

// Buscar formas de pagamento
$paymentMethods = [
    'money' => 'Dinheiro',
    'credit_card' => 'Cartão de Crédito',
    'debit_card' => 'Cartão de Débito',
    'pix' => 'PIX',
    'bank_transfer' => 'Transferência'
];

// Buscar status disponíveis
$orderStatus = [
    'pending' => ['label' => 'Pendente', 'color' => 'warning'],
    'processing' => ['label' => 'Em Processamento', 'color' => 'info'],
    'ready' => ['label' => 'Pronto', 'color' => 'primary'],
    'delivered' => ['label' => 'Entregue', 'color' => 'success'],
    'cancelled' => ['label' => 'Cancelado', 'color' => 'danger']
];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendas - Sistema de Vendas</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    
    <style>
        .product-row { background: #f8f9fa; padding: 10px; margin-bottom: 10px; border-radius: 5px; }
        .total-box { background: #e9ecef; padding: 15px; border-radius: 5px; }
        .status-badge { font-size: 0.85rem; }
        .select2-container { width: 100% !important; }
    </style>
</head>
<body>

<div class="wrapper">
    <?php include '../../components/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Gerenciar Vendas</h1>
                    <small class="text-muted">Gerencie todas as vendas do sistema</small>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="window.location.href='reports.php'">
                        <i class='bx bx-bar-chart-alt-2 me-2'></i>Relatórios
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOrderModal">
                        <i class='bx bx-plus-circle me-2'></i>Nova Venda
                    </button>
                </div>
            </div>
            
            <!-- Filtros -->
            <div class="card mb-4 fade-in">
                <div class="card-body">
                    <form id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="filterStatus" name="status">
                                    <option value="">Todos</option>
                                    <?php foreach ($orderStatus as $key => $status): ?>
                                        <option value="<?php echo $key; ?>"><?php echo $status['label']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Data Inicial</label>
                                <input type="date" class="form-control" id="filterStartDate" name="start_date">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Data Final</label>
                                <input type="date" class="form-control" id="filterEndDate" name="end_date">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cliente</label>
                                <select class="form-select select2" id="filterCustomer" name="customer_id">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Resumo -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2">Total de Vendas</h6>
                            <h4 id="totalSales" class="card-title mb-0">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2">Vendas Concluídas</h6>
                            <h4 id="completedSales" class="card-title mb-0">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2">Vendas Pendentes</h6>
                            <h4 id="pendingSales" class="card-title mb-0">0</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2">Ticket Médio</h6>
                            <h4 id="averageTicket" class="card-title mb-0">R$ 0,00</h4>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabela de Vendas -->
            <div class="card fade-in">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="ordersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Data</th>
                                    <th>Total</th>
                                    <th>Pagamento</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dados serão carregados via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova Venda -->
<div class="modal fade" id="addOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Venda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="orderForm">
                    <!-- Informações Básicas -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Cliente</label>
                            <select class="form-select select2" name="customer_id" required>
                                <option value="">Selecione um cliente</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Forma de Pagamento</label>
                            <select class="form-select" name="payment_method" required>
                                <?php foreach ($paymentMethods as $key => $label): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Produtos -->
                    <h6 class="mb-3">Produtos</h6>
                    <div class="products-container mb-4">
                        <!-- Produtos serão adicionados aqui -->
                    </div>
                    
                    <div class="text-center mb-4">
                        <button type="button" class="btn btn-outline-primary" id="addProductRow">
                            <i class='bx bx-plus-circle me-2'></i>Adicionar Produto
                        </button>
                    </div>

                    <!-- Totais -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <div class="total-box">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span id="subtotal">R$ 0,00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Desconto:</span>
                                    <div class="input-group input-group-sm" style="width: 150px;">
                                        <input type="number" class="form-control" name="discount" min="0" step="0.01" value="0">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <strong>Total:</strong>
                                    <strong id="total">R$ 0,00</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveOrder">
                    <i class='bx bx-save me-2'></i>Salvar Venda
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Visualizar Venda -->
<div class="modal fade" id="viewOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes da Venda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Informações Básicas -->
                <div class="table-responsive mb-4">
                    <table class="table table-sm">
                        <tr>
                            <th width="30%">ID:</th>
                            <td id="view-id"></td>
                        </tr>
                        <tr>
                            <th>Cliente:</th>
                            <td id="view-customer"></td>
                        </tr>
                        <tr>
                            <th>Data:</th>
                            <td id="view-date"></td>
                        </tr>
                        <tr>
                            <th>Pagamento:</th>
                            <td id="view-payment"></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td id="view-status"></td>
                        </tr>
                    </table>
                </div>

                <!-- Produtos -->
                <h6 class="mb-3">Produtos</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-sm" id="view-products">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Qtd</th>
                                <th>Preço</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- Totais -->
                <div class="total-box mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span id="view-subtotal"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Desconto:</span>
                        <span id="view-discount"></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <strong id="view-total"></strong>
                    </div>
                </div>

                <!-- Observações -->
                <h6 class="mb-2">Observações</h6>
                <div id="view-notes" class="p-3 bg-light rounded"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <div class="dropdown d-inline-block">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Ações
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" id="printOrder"><i class='bx bx-printer me-2'></i>Imprimir</a></li>
                        <li><a class="dropdown-item" href="#" id="shareWhatsapp"><i class='bx bxl-whatsapp me-2'></i>Enviar WhatsApp</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" id="editOrder"><i class='bx bx-edit me-2'></i>Editar</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/pt-BR.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/assets/js/orders.js"></script>

</body>
</html>
