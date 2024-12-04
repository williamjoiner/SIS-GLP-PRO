<?php
require_once __DIR__ . '/../../auth/check_auth.php';
require_once __DIR__ . '/../../config/database.php';

// Criar conexão com o banco
$database = new Database();
$db = $database->getConnection();

// Buscar clientes
try {
    $query = "SELECT * FROM clients ORDER BY name ASC";
    $stmt = $db->query($query);
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Erro ao buscar clientes: " . $e->getMessage());
    $clients = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIS GLP PRO - Clientes</title>
    
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
                    <a href="/modules/clients/index.php" class="nav-link active">
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

            <!-- Lista de Clientes -->
            <div class="card table-card">
                <div class="table-header">
                    <h2 class="table-title">Clientes</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clientModal" onclick="clearForm()">
                        <i class='bx bx-plus'></i>
                        <span>Novo Cliente</span>
                    </button>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>Endereço</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($client['name']); ?></td>
                                <td><?php echo htmlspecialchars($client['phone']); ?></td>
                                <td>
                                    <?php 
                                    $address = [];
                                    if (!empty($client['street'])) $address[] = $client['street'];
                                    if (!empty($client['number'])) $address[] = $client['number'];
                                    if (!empty($client['neighborhood'])) $address[] = $client['neighborhood'];
                                    if (!empty($client['city'])) $address[] = $client['city'];
                                    echo htmlspecialchars(implode(', ', $address));
                                    ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick='editClient(<?php echo json_encode($client); ?>)'>
                                        <i class='bx bx-edit'></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteClient(<?php echo $client['id']; ?>)">
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

    <!-- Modal Cliente -->
    <div class="modal fade" id="clientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Novo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="clientForm">
                        <input type="hidden" name="id" id="clientId">
                        
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label class="form-label">Nome</label>
                                <input type="text" class="form-control" name="name" id="clientName" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Telefone</label>
                                <input type="text" class="form-control" name="phone" id="clientPhone" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">CEP</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="zipcode" id="clientZipcode" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="searchZipcode()">
                                        <i class='bx bx-search'></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Rua</label>
                                <input type="text" class="form-control" name="street" id="clientStreet" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Número</label>
                                <input type="text" class="form-control" name="number" id="clientNumber" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Complemento</label>
                                <input type="text" class="form-control" name="complement" id="clientComplement">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Bairro</label>
                                <input type="text" class="form-control" name="neighborhood" id="clientNeighborhood" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cidade</label>
                                <input type="text" class="form-control" name="city" id="clientCity" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado</label>
                                <input type="text" class="form-control" name="state" id="clientState" required maxlength="2">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" name="notes" id="clientNotes" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="saveClient()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/inputmask.min.js"></script>
    <script>
        // Toggle Menu
        let toggle = document.querySelector('.toggle');
        let sidebar = document.querySelector('.sidebar');
        let mainContent = document.querySelector('.main-content');
        
        toggle.onclick = function() {
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('active');
        }

        // Máscaras de input
        document.addEventListener('DOMContentLoaded', function() {
            Inputmask({mask: "(99) 99999-9999"}).mask(document.getElementById("clientPhone"));
            Inputmask({mask: "99999-999"}).mask(document.getElementById("clientZipcode"));
        });

        // Funções do CRUD
        function clearForm() {
            document.getElementById('modalTitle').textContent = 'Novo Cliente';
            document.getElementById('clientForm').reset();
            document.getElementById('clientId').value = '';
        }

        function editClient(client) {
            document.getElementById('modalTitle').textContent = 'Editar Cliente';
            document.getElementById('clientId').value = client.id;
            document.getElementById('clientName').value = client.name;
            document.getElementById('clientPhone').value = client.phone;
            document.getElementById('clientZipcode').value = client.zipcode;
            document.getElementById('clientStreet').value = client.street;
            document.getElementById('clientNumber').value = client.number;
            document.getElementById('clientComplement').value = client.complement;
            document.getElementById('clientNeighborhood').value = client.neighborhood;
            document.getElementById('clientCity').value = client.city;
            document.getElementById('clientState').value = client.state;
            document.getElementById('clientNotes').value = client.notes;
            
            var modal = new bootstrap.Modal(document.getElementById('clientModal'));
            modal.show();
        }

        function searchZipcode() {
            const zipcode = document.getElementById('clientZipcode').value.replace(/\D/g, '');
            if (zipcode.length !== 8) {
                alert('CEP inválido');
                return;
            }

            fetch(`https://viacep.com.br/ws/${zipcode}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (data.erro) {
                        alert('CEP não encontrado');
                        return;
                    }

                    document.getElementById('clientStreet').value = data.logradouro;
                    document.getElementById('clientNeighborhood').value = data.bairro;
                    document.getElementById('clientCity').value = data.localidade;
                    document.getElementById('clientState').value = data.uf;

                    // Focar no campo número
                    document.getElementById('clientNumber').focus();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao buscar CEP');
                });
        }

        function saveClient() {
            const form = document.getElementById('clientForm');
            const formData = new FormData(form);

            const url = formData.get('id') ? '/api/clients/update.php' : '/api/clients/create.php';

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao salvar cliente: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao salvar cliente');
            });
        }

        function deleteClient(id) {
            if (confirm('Tem certeza que deseja excluir este cliente?')) {
                fetch('/api/clients/delete.php', {
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
                        alert('Erro ao excluir cliente: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao excluir cliente');
                });
            }
        }
    </script>
</body>
</html>
