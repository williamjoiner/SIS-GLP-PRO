<?php
session_start();
require_once '../../config/database.php';
require_once '../../helpers/auth_check.php';

$currentPage = 'orders';
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/style.css" rel="stylesheet">
</head>
<body>

<div class="wrapper">
    <?php include '../../components/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Gerenciar Vendas</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOrderModal">
                    <i class='bx bx-plus-circle me-2'></i>Nova Venda
                </button>
            </div>
            
            <!-- Filtros -->
            <div class="card mb-4 fade-in">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="filterStatus">
                                <option value="">Todos</option>
                                <option value="pending">Pendente</option>
                                <option value="processing">Em Processamento</option>
                                <option value="completed">Concluído</option>
                                <option value="cancelled">Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Data Inicial</label>
                            <input type="date" class="form-control" id="filterStartDate">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Data Final</label>
                            <input type="date" class="form-control" id="filterEndDate">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cliente</label>
                            <select class="form-select select2" id="filterCustomer">
                                <option value="">Todos</option>
                            </select>
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

<!-- Modal Adicionar Venda -->
<div class="modal fade" id="addOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Venda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addOrderForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Cliente</label>
                            <select class="form-select select2" name="customer_id" required>
                                <option value="">Selecione um cliente</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Data</label>
                            <input type="date" class="form-control" name="order_date" required>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="products-container">
                        <div class="row g-3 product-row mb-3">
                            <div class="col-md-5">
                                <label class="form-label">Produto</label>
                                <select class="form-select select2-products" name="products[]" required>
                                    <option value="">Selecione um produto</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Quantidade</label>
                                <input type="number" class="form-control" name="quantities[]" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Preço Unit.</label>
                                <input type="number" class="form-control" name="prices[]" step="0.01" required>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label d-block">&nbsp;</label>
                                <button type="button" class="btn btn-outline-danger remove-product">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-outline-primary" id="addProduct">
                            <i class='bx bx-plus-circle me-2'></i>Adicionar Produto
                        </button>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
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
                <div class="table-responsive">
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
                            <th>Status:</th>
                            <td id="view-status"></td>
                        </tr>
                        <tr>
                            <th>Total:</th>
                            <td id="view-total"></td>
                        </tr>
                    </table>
                </div>
                
                <h6 class="mt-4 mb-3">Produtos</h6>
                <div class="table-responsive">
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
                
                <h6 class="mt-4 mb-2">Observações</h6>
                <div id="view-notes" class="p-3 bg-light rounded"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Venda -->
<div class="modal fade" id="editOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Venda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editOrderForm">
                    <input type="hidden" name="order_id" id="edit-order-id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Cliente</label>
                            <select class="form-select select2" name="customer_id" id="edit-customer" required>
                                <option value="">Selecione um cliente</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="edit-status" required>
                                <option value="pending">Pendente</option>
                                <option value="processing">Em Processamento</option>
                                <option value="completed">Concluído</option>
                                <option value="cancelled">Cancelado</option>
                            </select>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="edit-products-container">
                        <!-- Produtos serão carregados aqui -->
                    </div>
                    
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-outline-primary" id="addEditProduct">
                            <i class='bx bx-plus-circle me-2'></i>Adicionar Produto
                        </button>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" name="notes" id="edit-notes" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="updateOrder">
                    <i class='bx bx-save me-2'></i>Atualizar Venda
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script src="/assets/js/orders.js"></script>

</body>
</html>
