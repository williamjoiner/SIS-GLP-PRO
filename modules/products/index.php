<?php
require_once __DIR__ . '/../../auth/check_auth.php';
require_once __DIR__ . '/../../config/database.php';

// Criar conexão com o banco
$database = new Database();
$db = $database->getConnection();

// Buscar produtos
try {
    $query = "SELECT * FROM products ORDER BY name ASC";
    $stmt = $db->query($query);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Erro ao buscar produtos: " . $e->getMessage());
    $produtos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIS GLP PRO - Produtos</title>
    
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
                    <a href="/modules/products/index.php" class="nav-link active">
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

            <!-- Lista de Produtos -->
            <div class="card table-card">
                <div class="table-header">
                    <h2 class="table-title">Produtos</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class='bx bx-plus'></i>
                        <span>Novo Produto</span>
                    </button>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th>Preço</th>
                                <th>Estoque</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos as $produto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($produto['name']); ?></td>
                                <td><?php echo htmlspecialchars($produto['description']); ?></td>
                                <td>R$ <?php echo number_format($produto['price'], 2, ',', '.'); ?></td>
                                <td><?php echo $produto['stock']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="editProduct(<?php echo $produto['id']; ?>)">
                                        <i class='bx bx-edit-alt'></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?php echo $produto['id']; ?>)">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Produto -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addProductForm">
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descrição</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Preço</label>
                            <input type="number" class="form-control" name="price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estoque</label>
                            <input type="number" class="form-control" name="stock" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="saveProduct()">Salvar</button>
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

        // Funções do CRUD
        function saveProduct() {
            const form = document.getElementById('addProductForm');
            const formData = new FormData(form);

            fetch('/api/products/create.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao salvar produto: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao salvar produto');
            });
        }

        function editProduct(id) {
            // Implementar edição
        }

        function deleteProduct(id) {
            if (confirm('Tem certeza que deseja excluir este produto?')) {
                fetch('/api/products/delete.php', {
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
                        alert('Erro ao excluir produto: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao excluir produto');
                });
            }
        }
    </script>
</body>
</html>
