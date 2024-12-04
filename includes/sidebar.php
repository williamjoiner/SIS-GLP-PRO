<?php
if(!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}
?>
<div class="container-fluid">
    <div class="row">
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../../dashboard.php">
                            <i class='bx bxs-dashboard'></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../../modules/clients/">
                            <i class='bx bxs-user'></i>
                            Clientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../../modules/products/">
                            <i class='bx bxs-box'></i>
                            Produtos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../../modules/deliverers/">
                            <i class='bx bxs-truck'></i>
                            Entregadores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../../modules/orders/">
                            <i class='bx bxs-cart'></i>
                            Pedidos
                        </a>
                    </li>
                    <?php if($_SESSION['role'] == 'master' || $_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../../modules/users/">
                            <i class='bx bxs-user-account'></i>
                            Usuários
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item mt-3">
                        <a class="nav-link text-white" href="../../auth/logout.php">
                            <i class='bx bxs-log-out'></i>
                            Sair
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>
