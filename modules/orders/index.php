<?php
require_once __DIR__ . '/../../auth/check_auth.php';
require_once __DIR__ . '/../../config/database.php';

// Criar conexão com o banco
$database = new Database();
$db = $database->getConnection();

// Buscar vendas
try {
    $query = "SELECT o.*, c.name as client_name, d.name as deliverer_name 
              FROM orders o 
              LEFT JOIN clients c ON o.client_id = c.id 
              LEFT JOIN deliverers d ON o.deliverer_id = d.id 
              ORDER BY o.created_at DESC";
    $stmt = $db->query($query);
    $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar clientes para o select
    $query = "SELECT id, name FROM clients ORDER BY name ASC";
    $stmt = $db->query($query);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar produtos para o select
    $query = "SELECT id, name, price FROM products ORDER BY name ASC";
    $stmt = $db->query($query);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar entregadores para o select
    $query = "SELECT id, name FROM deliverers ORDER BY name ASC";
    $stmt = $db->query($query);
    $entregadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Erro ao buscar dados: " . $e->getMessage());
    $vendas = [];
    $clientes = [];
    $produtos = [];
    $entregadores = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIS GLP PRO - Vendas</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="logo">
                <i class='bx bxs-gas-cylinder'></i>
                <span>SIS GLP PRO</span>
            </div>
            <ul class="nav">
                <li class="nav-item">
                    <a href="/dashboard.php" class="nav-link">
                        <i class='bx bxs-dashboard'></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/modules/orders/index.php" class="nav-link active">
                        <i class='bx bxs-cart'></i>
                        <span>Vendas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/modules/clients/index.php" class="nav-link">
                        <i class='bx bxs-user'></i>
                        <span>Clientes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/modules/products/index.php" class="nav-link">
                        <i class='bx bxs-package'></i>
                        <span>Produtos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/modules/deliverers/index.php" class="nav-link">
                        <i class='bx bxs-truck'></i>
                        <span>Entregadores</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/auth/logout.php" class="nav-link text-danger">
                        <i class='bx bxs-log-out'></i>
                        <span>Sair</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="topbar">
                <div class="toggle">
                    <i class='bx bx-menu'></i>
                </div>
                <div class="user">
                    <div class="user-info">
                        <div class="user-name">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </div>
                        <div class="user-role">Administrador</div>
                    </div>
                    <img src="/assets/img/user.png" alt="User" class="user-img">
                </div>
            </div>

            <!-- Lista de Vendas -->
            <div class="container-fluid">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">Vendas</h4>
                        <p class="text-muted mb-0">Gerencie todas as suas vendas</p>
                    </div>
                    <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addOrderModal">
                        <i class='bx bx-plus'></i> Nova Venda
                    </button>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="statusFilter">
                                    <option value="">Todos</option>
                                    <option value="pending">Pendente</option>
                                    <option value="processing">Em Processamento</option>
                                    <option value="completed">Concluído</option>
                                    <option value="cancelled">Cancelado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Data Inicial</label>
                                <input type="date" class="form-control" id="startDate">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Data Final</label>
                                <input type="date" class="form-control" id="endDate">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cliente</label>
                                <select class="form-select select2" id="clientFilter">
                                    <option value="">Todos</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?php echo $cliente['id']; ?>"><?php echo htmlspecialchars($cliente['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Vendas -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="ordersTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Entregador</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                        <th width="180">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nova/Editar Venda -->
    <div class="modal fade" id="addOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nova Venda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addOrderForm">
                        <input type="hidden" name="order_id" id="order_id">
                        
                        <!-- Informações Principais -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Cliente <span class="text-danger">*</span></label>
                                <select class="form-select select2" name="client_id" required>
                                    <option value="">Selecione o cliente</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?php echo $cliente['id']; ?>"><?php echo htmlspecialchars($cliente['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Entregador <span class="text-danger">*</span></label>
                                <select class="form-select select2" name="deliverer_id" required>
                                    <option value="">Selecione o entregador</option>
                                    <?php foreach ($entregadores as $entregador): ?>
                                        <option value="<?php echo $entregador['id']; ?>"><?php echo htmlspecialchars($entregador['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Produtos -->
                        <div class="card mb-4">
                            <div class="card-header py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Produtos</h6>
                                    <button type="button" class="btn btn-sm btn-primary" id="add-product">
                                        <i class='bx bx-plus'></i> Adicionar Produto
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="products-container">
                                    <div class="product-row row g-2 mb-2">
                                        <div class="col-md-6">
                                            <select class="form-select select2 product-select" name="products[]" required>
                                                <option value="">Selecione o produto</option>
                                                <?php foreach ($produtos as $produto): ?>
                                                    <option value="<?php echo $produto['id']; ?>" 
                                                            data-price="<?php echo $produto['price']; ?>">
                                                        <?php echo htmlspecialchars($produto['name']); ?> - 
                                                        R$ <?php echo number_format($produto['price'], 2, ',', '.'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="input-group">
                                                <input type="number" class="form-control product-quantity" 
                                                       name="quantities[]" value="1" min="1" required>
                                                <span class="input-group-text">un</span>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control product-subtotal" 
                                                   readonly placeholder="Subtotal">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-outline-danger remove-product">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row justify-content-end mt-3">
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <span class="input-group-text">Total:</span>
                                            <input type="text" class="form-control" id="total" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informações Adicionais -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Forma de Pagamento <span class="text-danger">*</span></label>
                                <select class="form-select" name="payment_method" required>
                                    <option value="">Selecione</option>
                                    <option value="money">Dinheiro</option>
                                    <option value="credit_card">Cartão de Crédito</option>
                                    <option value="debit_card">Cartão de Débito</option>
                                    <option value="pix">PIX</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="pending">Pendente</option>
                                    <option value="processing">Em Processamento</option>
                                    <option value="completed">Concluído</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observações</label>
                                <textarea class="form-control" name="notes" rows="2"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveOrderBtn">
                        <i class='bx bx-save'></i> Salvar Venda
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Visualizar Venda -->
    <div class="modal fade" id="viewOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes da Venda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informações Gerais</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="120">Cliente:</th>
                                    <td id="view-client"></td>
                                </tr>
                                <tr>
                                    <th>Entregador:</th>
                                    <td id="view-deliverer"></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td id="view-status"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Informações de Pagamento</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="120">Total:</th>
                                    <td id="view-total"></td>
                                </tr>
                                <tr>
                                    <th>Pagamento:</th>
                                    <td id="view-payment"></td>
                                </tr>
                                <tr>
                                    <th>Data:</th>
                                    <td id="view-date"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="products-list mb-4">
                        <h6>Produtos</h6>
                        <div class="table-responsive">
                            <table class="table table-sm" id="view-products">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Quantidade</th>
                                        <th>Preço Un.</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="notes">
                        <h6>Observações</h6>
                        <p id="view-notes" class="mb-0"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="/assets/js/orders.js"></script>
    <script>
        // Toggle Menu
        let toggle = document.querySelector('.toggle');
        let sidebar = document.querySelector('.sidebar');
        let mainContent = document.querySelector('.main-content');
        
        toggle.onclick = function() {
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('active');
        }

        // Funções do CRUD
        function addProduct() {
            const productsList = document.getElementById('productsList');
            const newProduct = productsList.children[0].cloneNode(true);
            
            // Limpar valores
            newProduct.querySelectorAll('select, input').forEach(input => {
                input.value = '';
            });
            
            // Adicionar botão de remover
            const col = document.createElement('div');
            col.className = 'col-md-1 d-flex align-items-center';
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-danger btn-sm';
            removeBtn.innerHTML = '<i class="bx bx-trash"></i>';
            removeBtn.onclick = function() {
                this.closest('.product-item').remove();
                calculateTotal();
            };
            col.appendChild(removeBtn);
            newProduct.appendChild(col);
            
            productsList.appendChild(newProduct);
        }

        function updatePrice(select) {
            const price = select.options[select.selectedIndex].dataset.price;
            const row = select.closest('.product-item');
            const quantity = row.querySelector('input[name="quantities[]"]').value;
            const total = price * quantity;
            row.querySelector('.product-total').value = total.toFixed(2);
            calculateTotal();
        }

        function updateTotal(input) {
            const row = input.closest('.product-item');
            const select = row.querySelector('select[name="products[]"]');
            const price = select.options[select.selectedIndex].dataset.price;
            const quantity = input.value;
            const total = price * quantity;
            row.querySelector('.product-total').value = total.toFixed(2);
            calculateTotal();
        }

        function calculateTotal() {
            const totals = document.querySelectorAll('.product-total');
            let orderTotal = 0;
            totals.forEach(input => {
                if (input.value) {
                    orderTotal += parseFloat(input.value);
                }
            });
            document.getElementById('orderTotal').value = orderTotal.toFixed(2);
        }

        function saveOrder() {
            const form = document.getElementById('addOrderForm');
            const formData = new FormData(form);

            fetch('/api/orders/create.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao salvar venda: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao salvar venda');
            });
        }

        function viewOrder(id) {
            // Implementar visualização
        }

        function cancelOrder(id) {
            if (confirm('Tem certeza que deseja cancelar esta venda?')) {
                fetch('/api/orders/cancel.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erro ao cancelar venda: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao cancelar venda');
                });
            }
        }

        function sendToWhatsApp(id) {
            // Implementar envio para WhatsApp
        }
    </script>
</body>
</html>
