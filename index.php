<?php
session_start();

// Debug - Remova após resolver o problema
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir o caminho base
$baseUrl = "/";  // Agora usando caminho absoluto do root

// Debug - Remova após resolver o problema
echo "<!-- Base URL: " . htmlspecialchars($baseUrl) . " -->";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIS GLP PRO - Login</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/style.css" rel="stylesheet">
    
    <!-- Debug - Remova após resolver o problema -->
    <script>
    window.addEventListener('load', function() {
        console.log('Verificando carregamento do CSS...');
        const styleSheets = document.styleSheets;
        for (let i = 0; i < styleSheets.length; i++) {
            console.log('StyleSheet ' + i + ':', styleSheets[i].href);
        }
    });
    </script>
</head>
<body>
    <div class="login-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="login-card fade-in">
                        <div class="card-header text-center">
                            <i class='bx bxs-gas-cylinder text-primary' style='font-size: 3rem;'></i>
                            <h3>SIS GLP PRO</h3>
                            <p class="text-muted">Sistema de Gestão de Gás</p>
                        </div>
                        <div class="card-body">
                            <?php if(isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php 
                                        echo htmlspecialchars($_SESSION['error']);
                                        unset($_SESSION['error']);
                                    ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form class="login-form" action="/auth/login.php" method="POST">
                                <div class="mb-4">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class='bx bxs-user'></i>
                                        </span>
                                        <input type="text" name="username" class="form-control" placeholder="Usuário" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class='bx bxs-lock-alt'></i>
                                        </span>
                                        <input type="password" name="password" class="form-control" placeholder="Senha" required>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class='bx bx-log-in-circle me-2'></i>Entrar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
