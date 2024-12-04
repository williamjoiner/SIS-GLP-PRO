<?php
require_once __DIR__ . '/auth/check_auth.php';
require_once __DIR__ . '/config/database.php';

// Criar conexão com o banco
$database = new Database();
$db = $database->getConnection();

// Buscar estatísticas
try {
    // Total de vendas hoje
    $query = "SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()";
    $stmt = $db->query($query);
    $vendasHoje = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de clientes
    $query = "SELECT COUNT(*) as total FROM clients";
    $stmt = $db->query($query);
    $totalClientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de produtos
    $query = "SELECT COUNT(*) as total FROM products";
    $stmt = $db->query($query);
    $totalProdutos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Pedidos recentes
    $query = "SELECT o.*, c.name as client_name FROM orders o 
              LEFT JOIN clients c ON o.client_id = c.id 
              ORDER BY o.created_at DESC LIMIT 5";
    $stmt = $db->query($query);
    $pedidosRecentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Erro ao buscar estatísticas: " . $e->getMessage());
    // Definir valores padrão em caso de erro
    $vendasHoje = 0;
    $totalClientes = 0;
    $totalProdutos = 0;
    $pedidosRecentes = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIS GLP PRO - Dashboard</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                    <a href="/dashboard.php" class="nav-link active">
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

            <!-- Cards -->
            <div class="cardBox">
                <div class="card">
                    <div class="stat-card">
                        <div>
                            <div class="numbers"><?php echo $vendasHoje; ?></div>
                            <div class="cardName">Vendas Hoje</div>
                        </div>
                        <div class="iconBx">
                            <i class='bx bxs-cart-add'></i>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="stat-card">
                        <div>
                            <div class="numbers"><?php echo $totalClientes; ?></div>
                            <div class="cardName">Total de Clientes</div>
                        </div>
                        <div class="iconBx">
                            <i class='bx bxs-group'></i>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="stat-card">
                        <div>
                            <div class="numbers"><?php echo $totalProdutos; ?></div>
                            <div class="cardName">Produtos Cadastrados</div>
                        </div>
                        <div class="iconBx">
                            <i class='bx bxs-package'></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pedidos Recentes -->
            <div class="card table-card">
                <div class="table-header">
                    <h2 class="table-title">Pedidos Recentes</h2>
                    <a href="/modules/orders/index.php" class="btn btn-primary">
                        <i class='bx bx-plus'></i>
                        <span>Novo Pedido</span>
                    </a>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidosRecentes as $pedido): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pedido['client_name']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($pedido['created_at'])); ?></td>
                                <td>
                                    <span class="status <?php echo $pedido['status']; ?>">
                                        <?php echo ucfirst($pedido['status']); ?>
                                    </span>
                                </td>
                                <td>R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Menu
        let toggle = document.querySelector('.toggle');
        let sidebar = document.querySelector('.sidebar');
        let mainContent = document.querySelector('.main-content');
        
        toggle.onclick = function() {
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('active');
        }
    </script>
</body>
</html>
