<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir o caminho base
$baseUrl = "/";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Vendas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #5b1d99 0%, #5271ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 1.5rem;
            box-shadow: 0 15px 30px rgba(91, 29, 153, 0.2);
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            animation: fadeIn 0.5s ease-out;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: #5b1d99;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #718096;
            font-size: 0.875rem;
        }

        .form-floating {
            margin-bottom: 1rem;
        }

        .form-floating .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1rem;
            height: calc(3.5rem + 2px);
            font-size: 0.875rem;
        }

        .form-floating .form-control:focus {
            border-color: #5b1d99;
            box-shadow: 0 0 0 3px rgba(91, 29, 153, 0.1);
        }

        .form-floating label {
            padding: 1rem;
            color: #718096;
        }

        .btn-login {
            background: linear-gradient(135deg, #5b1d99 0%, #5271ff 100%);
            border: none;
            border-radius: 1rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(91, 29, 153, 0.2);
        }

        .alert {
            border-radius: 1rem;
            font-size: 0.875rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: none;
        }

        .alert-danger {
            background-color: rgba(255, 49, 49, 0.1);
            color: #ff3131;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 576px) {
            .login-container {
                margin: 1rem;
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Sistema de Vendas</h1>
            <p>Faça login para acessar o sistema</p>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php 
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo $baseUrl; ?>auth/login.php" method="POST">
            <div class="form-floating">
                <input type="text" class="form-control" id="username" name="username" placeholder="Usuário" required>
                <label for="username">Usuário</label>
            </div>
            <div class="form-floating">
                <input type="password" class="form-control" id="password" name="password" placeholder="Senha" required>
                <label for="password">Senha</label>
            </div>
            <button type="submit" class="btn btn-primary btn-login">
                Entrar
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
