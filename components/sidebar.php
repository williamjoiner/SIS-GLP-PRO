<nav class="sidebar">
    <div class="logo">
        <i class='bx bxs-gas-cylinder'></i>
        <span>SIS GLP PRO</span>
    </div>
    <ul class="nav">
        <li class="nav-item">
            <a href="/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class='bx bxs-dashboard'></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/modules/orders/index.php" class="nav-link <?php echo $_SERVER['PHP_SELF'] == '/modules/orders/index.php' ? 'active' : ''; ?>">
                <i class='bx bxs-cart'></i>
                <span>Vendas</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/modules/orders/reports.php" class="nav-link <?php echo $_SERVER['PHP_SELF'] == '/modules/orders/reports.php' ? 'active' : ''; ?>">
                <i class='bx bxs-report'></i>
                <span>Relatórios</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/modules/clients/index.php" class="nav-link <?php echo $_SERVER['PHP_SELF'] == '/modules/clients/index.php' ? 'active' : ''; ?>">
                <i class='bx bxs-user'></i>
                <span>Clientes</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/modules/products/index.php" class="nav-link <?php echo $_SERVER['PHP_SELF'] == '/modules/products/index.php' ? 'active' : ''; ?>">
                <i class='bx bxs-package'></i>
                <span>Produtos</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="/modules/deliverers/index.php" class="nav-link <?php echo $_SERVER['PHP_SELF'] == '/modules/deliverers/index.php' ? 'active' : ''; ?>">
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
