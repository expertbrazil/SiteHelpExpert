<?php
session_start();

// Incluir arquivo de configuração
require_once "../includes/config/config.php";

// Incluir arquivo de conexão com o banco de dados
require_once "../includes/config/db_connection.php";

// Carregar o modelo de usuário
require_once "../app/models/User.php";

// Inicializar o modelo de usuário
$userModel = new User($conn);

// Verificar se o usuário já está logado
if (isset($_SESSION["admin_logged_in"]) && $_SESSION["admin_logged_in"] === true) {
    header("Location: index.php");
    exit;
}

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    
    // Verificar credenciais no banco de dados
    $user = $userModel->authenticate($username, $password);
    
    if ($user) {
        // Login bem-sucedido
        $_SESSION["admin_logged_in"] = true;
        $_SESSION["admin_username"] = $user['username'];
        $_SESSION["admin_name"] = $user['name'];
        $_SESSION["admin_id"] = $user['id'];
        $_SESSION["admin_role"] = $user['role'];
        
        // Redirecionar para o painel
        header("Location: index.php");
        exit;
    } else {
        // Login falhou
        $error_message = "Usuário ou senha incorretos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpExpert - Login Administrativo</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Favicons -->
    <link rel="icon" href="../assets/img/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/img/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="../assets/img/icone_512px.png">
    <link rel="icon" type="image/png" sizes="512x512" href="../assets/img/icone_512px.png">
    <meta name="theme-color" content="#007498">
    
    <style>
        :root {
            --primary: #007498;
            --secondary: #00aba5;
            --white: #FFFFFF;
            --light-gray: #F5F7F9;
            --medium-gray: #E0E5E9;
            --dark-gray: #3A4A5A;
            --soft-gray: #7A8A9A;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--light-gray);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            padding: 2rem;
            text-align: center;
            font-family: 'Archivo', sans-serif;
            font-weight: 600;
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        h1 {
            font-family: 'Archivo', sans-serif;
            color: var(--dark-gray);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .form-control {
            background-color: var(--light-gray);
            border: 1px solid var(--medium-gray);
            padding: 0.75rem 1rem;
            font-size: 1rem;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(0, 116, 152, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary);
            border: none;
            padding: 0.75rem 1rem;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
        }
        
        .btn-primary:hover {
            background-color: #006080;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <img src="../assets/img/logo.png" alt="HelpExpert Logo" height="50">
        </div>
        
        <div class="container p-4">
            <h1>Acesso Administrativo</h1>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuário</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Entrar</button>
            </form>
            
            <div class="mt-3 text-center">
                <small class="text-muted"> 2025 HelpExpert. Todos os direitos reservados.</small>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
