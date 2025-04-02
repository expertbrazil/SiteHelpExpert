<?php
/**
 * Dashboard do Painel Administrativo
 */

// Carregar o bootstrap da aplicação
require_once '../../app/bootstrap.php';

// Verificar se o usuário está autenticado
require_authentication();

// Obter dados do usuário autenticado
$user = get_authenticated_user();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | HelpExpert Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #007498;
            --secondary-color: #00aba5;
            --dark-color: #3A4A5A;
            --light-color: #F5F7F9;
            --gray-color: #E0E5E9;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--light-color);
        }
        
        .sidebar {
            background-color: var(--dark-color);
            min-height: 100vh;
            color: white;
        }
        
        .sidebar .logo {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .logo h3 {
            color: white;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            margin: 0;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        
        .content {
            padding: 2rem;
        }
        
        .header {
            background-color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            border-radius: 5px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
        }
        
        .card-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="logo">
                    <h3>HelpExpert</h3>
                </div>
                <ul class="nav flex-column mt-4">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-users"></i> Leads
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-envelope"></i> Contatos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-columns"></i> Kanban
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-cog"></i> Configurações
                        </a>
                    </li>
                    <li class="nav-item mt-5">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Sair
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="header d-flex justify-content-between align-items-center">
                    <h4>Dashboard</h4>
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle text-decoration-none" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($user['name']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Perfil</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Configurações</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="content">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    Leads
                                </div>
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h2 class="mb-0">0</h2>
                                        <p class="text-muted mb-0">Total de leads</p>
                                    </div>
                                    <div class="card-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    Contatos
                                </div>
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h2 class="mb-0">0</h2>
                                        <p class="text-muted mb-0">Total de contatos</p>
                                    </div>
                                    <div class="card-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    Colunas Kanban
                                </div>
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h2 class="mb-0">5</h2>
                                        <p class="text-muted mb-0">Total de colunas</p>
                                    </div>
                                    <div class="card-icon">
                                        <i class="fas fa-columns"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    Bem-vindo ao Painel Administrativo
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">Olá, <?php echo htmlspecialchars($user['name']); ?>!</h5>
                                    <p class="card-text">Bem-vindo ao painel administrativo do HelpExpert. Aqui você pode gerenciar leads, contatos e configurar o sistema Kanban.</p>
                                    <p>Usuário autenticado com sucesso. Suas informações:</p>
                                    <ul>
                                        <li><strong>Nome:</strong> <?php echo htmlspecialchars($user['name']); ?></li>
                                        <li><strong>Usuário:</strong> <?php echo htmlspecialchars($user['username']); ?></li>
                                        <li><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></li>
                                        <li><strong>Função:</strong> <?php echo htmlspecialchars($user['role']); ?></li>
                                        <li><strong>Último login:</strong> <?php echo $user['last_login'] ? date('d/m/Y H:i:s', strtotime($user['last_login'])) : 'Primeiro login'; ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
