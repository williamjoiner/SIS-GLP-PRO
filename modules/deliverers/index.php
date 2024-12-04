<?php
require_once __DIR__ . '/../../auth/check_auth.php';
require_once __DIR__ . '/../../config/database.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIS GLP PRO - Entregadores</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="/assets/style.css" rel="stylesheet">
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
                    <a href="/modules/orders/index.php" class="nav-link">
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
                    <a href="/modules/deliverers/index.php" class="nav-link active">
                        <i class='bx bxs-truck'></i>
                        <span>Entregadores</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/modules/users/index.php" class="nav-link">
                        <i class='bx bxs-user-account'></i>
                        <span>Usuários</span>
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

            <!-- Lista de Entregadores -->
            <div class="card table-card">
                <div class="table-header">
                    <h2 class="table-title">Entregadores</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDelivererModal" id="addDelivererBtn">
                        <i class='bx bx-plus'></i>
                        <span>Novo Entregador</span>
                    </button>
                </div>
                <div class="table-wrapper">
                    <table id="deliverersTable" class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>Veículo</th>
                                <th>Placa</th>
                                <th>Status</th>
                                <th>Entregas</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Será preenchido pelo DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar/Editar Entregador -->
    <div class="modal fade" id="addDelivererModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Entregador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addDelivererForm">
                        <input type="hidden" id="delivererId" name="id">
                        
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" class="form-control" name="name" id="delivererName" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="tel" class="form-control" name="phone" id="delivererPhone" required>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Veículo</label>
                                <select class="form-select" name="vehicle_type" id="delivererVehicleType" required>
                                    <option value="">Selecione...</option>
                                    <option value="Moto">Moto</option>
                                    <option value="Carro">Carro</option>
                                    <option value="Van">Van</option>
                                    <option value="Caminhão">Caminhão</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Placa</label>
                                <input type="text" class="form-control" name="license_plate" id="delivererLicensePlate" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="delivererStatus" required>
                                <option value="active">Ativo</option>
                                <option value="inactive">Inativo</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="saveDeliverer()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Histórico de Entregas -->
    <div class="modal fade" id="deliveriesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Histórico de Entregas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="deliveriesTable" class="table">
                            <thead>
                                <tr>
                                    <th>Pedido</th>
                                    <th>Cliente</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Será preenchido via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="/assets/js/deliverers.js"></script>
</body>
</html>
