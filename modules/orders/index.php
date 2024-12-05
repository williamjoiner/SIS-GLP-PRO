<?php
$page_title = "Vendas";
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800"><?php echo $page_title; ?></h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#saleModal" aria-label="Nova Venda">
                    <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Nova Venda
                </button>
            </div>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="row mb-4" role="region" aria-label="Resumo de Vendas">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Vendas (Hoje)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="today_sales">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cart3 fa-2x text-gray-300" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Faturamento (Hoje)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="today_revenue">R$ 0,00</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-currency-dollar fa-2x text-gray-300" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Vendas (Mês)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="month_sales">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-graph-up fa-2x text-gray-300" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Faturamento (Mês)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="month_revenue">R$ 0,00</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cash-stack fa-2x text-gray-300" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <button class="btn btn-link text-decoration-none p-0" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="true" aria-controls="filterCollapse">
                <i class="bi bi-funnel me-1" aria-hidden="true"></i>
                Filtros
            </button>
        </div>
        <div class="collapse show" id="filterCollapse" role="region" aria-label="Filtros de Vendas">
            <div class="card-body">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label" for="status_filter">Status</label>
                        <select class="form-select" id="status_filter">
                            <option value="">Todos</option>
                            <option value="pending">Pendente</option>
                            <option value="paid">Pago</option>
                            <option value="cancelled">Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="start_date">Data Inicial</label>
                        <input type="date" class="form-control" id="start_date">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="end_date">Data Final</label>
                        <input type="date" class="form-control" id="end_date">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="customer_filter">Cliente</label>
                        <select class="form-select" id="customer_filter"></select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1" aria-hidden="true"></i>Filtrar
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1" aria-hidden="true"></i>Limpar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabela de Vendas -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="salesTable">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Data</th>
                            <th scope="col" class="text-end">Subtotal</th>
                            <th scope="col" class="text-end">Desconto</th>
                            <th scope="col" class="text-end">Total</th>
                            <th scope="col">Pagamento</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-center">Ações</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Nova Venda -->
<div class="modal fade" id="saleModal" tabindex="-1" aria-labelledby="saleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saleModalLabel">Nova Venda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="saleForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="client_id">Cliente</label>
                            <select class="form-select" id="client_id" required></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="payment_method">Método de Pagamento</label>
                            <select class="form-select" id="payment_method" required>
                                <option value="">Selecione</option>
                                <option value="money">Dinheiro</option>
                                <option value="credit">Cartão de Crédito</option>
                                <option value="debit">Cartão de Débito</option>
                                <option value="pix">PIX</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Produtos</label>
                            <button type="button" class="btn btn-success btn-sm" id="addProduct" aria-label="Adicionar Produto">
                                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Adicionar Produto
                            </button>
                        </div>
                        <div id="productsList" role="region" aria-label="Lista de Produtos"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label" for="subtotal">Subtotal</label>
                            <input type="text" class="form-control currency" id="subtotal" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="discount">Desconto</label>
                            <input type="text" class="form-control currency" id="discount" value="0,00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="total">Total</label>
                            <input type="text" class="form-control currency" id="total" readonly>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveSale">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Visualização de Venda -->
<div class="modal fade" id="viewSaleModal" tabindex="-1" aria-labelledby="viewSaleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSaleModalLabel">Detalhes da Venda #<span id="viewSaleId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Cliente:</strong> <span id="viewClientName"></span></p>
                        <p><strong>Data:</strong> <span id="viewDate"></span></p>
                        <p><strong>Status:</strong> <span id="viewStatus"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Método de Pagamento:</strong> <span id="viewPaymentMethod"></span></p>
                        <p><strong>Subtotal:</strong> <span id="viewSubtotal"></span></p>
                        <p><strong>Desconto:</strong> <span id="viewDiscount"></span></p>
                        <p><strong>Total:</strong> <span id="viewTotal"></span></p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Produto</th>
                                <th scope="col" class="text-end">Quantidade</th>
                                <th scope="col" class="text-end">Preço</th>
                                <th scope="col" class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody id="viewProducts"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
