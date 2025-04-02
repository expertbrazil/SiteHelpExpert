<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: login.php");
    exit;
}

// Incluir arquivo de configuração
require_once "../config.php";

// Selecionar o banco de dados
$conn->exec("USE helpexpert");

// Verificar se o ID foi fornecido
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$id = $_GET["id"];

// Obter detalhes do lead
$stmt = $conn->prepare("SELECT * FROM leads WHERE id = :id");
$stmt->bindParam(":id", $id);
$stmt->execute();
$lead = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se o lead existe
if (!$lead) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Lead - HelpExpert</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    
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
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Archivo', sans-serif;
            font-weight: 600;
        }
        
        /* Estilos do layout principal */
        .sidebar {
            background-color: var(--white);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            height: 100vh;
            position: fixed;
            width: 250px;
            padding: 1.5rem;
        }
        
        .sidebar-logo {
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .nav-link {
            color: var(--dark-gray);
            padding: 0.75rem 1rem;
            border-radius: 5px;
            margin-bottom: 0.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .nav-link i {
            margin-right: 0.75rem;
            font-size: 1.2rem;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: var(--light-gray);
            color: var(--primary);
        }
        
        .content {
            margin-left: 250px;
            padding: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            border-radius: 10px 10px 0 0 !important;
            font-family: 'Archivo', sans-serif;
            font-weight: 600;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(0, 116, 152, 0.25);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-gray);
        }
        
        .lead-info {
            background-color: var(--light-gray);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .lead-label {
            font-weight: 600;
            color: var(--dark-gray);
        }
        
        .lead-value {
            color: var(--soft-gray);
        }
        
        .status-badge {
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 30px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="../img/logo.png" alt="HelpExpert Logo" height="40">
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link" href="index.php?tab=dashboard">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link active" href="index.php?tab=leads">
                <i class="bi bi-people-fill"></i> Leads
            </a>
            <a class="nav-link" href="index.php?tab=contacts">
                <i class="bi bi-chat-dots-fill"></i> Contatos
            </a>
            <a class="nav-link" href="index.php?tab=kanban">
                <i class="bi bi-kanban"></i> Kanban
            </a>
            <a class="nav-link" href="?logout=1">
                <i class="bi bi-box-arrow-right"></i> Sair
            </a>
        </nav>
    </div>
    
    <!-- Conteúdo Principal -->
    <div class="content">
        <div class="header mb-4">
            <h1>Detalhes do Lead</h1>
        </div>
        
        <div class="card">
            <div class="card-header py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informações do Lead #<?php echo $lead['id']; ?></h5>
                    <span class="badge bg-success status-badge">
                        <i class="bi bi-check-circle me-1"></i>Ativo
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="lead-info">
                            <p class="lead-label">Nome</p>
                            <p class="lead-value"><?php echo htmlspecialchars($lead['name']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="lead-info">
                            <p class="lead-label">Email</p>
                            <p class="lead-value"><?php echo htmlspecialchars($lead['email']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="lead-info">
                            <p class="lead-label">Telefone</p>
                            <p class="lead-value"><?php echo htmlspecialchars($lead['phone']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="lead-info">
                            <p class="lead-label">Empresa</p>
                            <p class="lead-value"><?php echo htmlspecialchars($lead['company']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="lead-info">
                            <p class="lead-label">Número de Funcionários</p>
                            <p class="lead-value"><?php echo htmlspecialchars($lead['employees']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="lead-info">
                            <p class="lead-label">Data de Cadastro</p>
                            <p class="lead-value"><?php echo date('d/m/Y H:i', strtotime($lead['created_at'])); ?></p>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="lead-info">
                            <p class="lead-label">Aceitou Política de Privacidade</p>
                            <p class="lead-value">
                                <?php if ($lead['privacy']): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Sim</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="bi bi-x-lg me-1"></i>Não</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="row">
                    <div class="col-12">
                        <h5 class="mb-3">Ações</h5>
                        <a href="mailto:<?php echo htmlspecialchars($lead['email']); ?>" class="btn btn-outline-primary me-2">
                            <i class="bi bi-envelope me-1"></i>Enviar Email
                        </a>
                        <a href="tel:<?php echo htmlspecialchars($lead['phone']); ?>" class="btn btn-outline-primary me-2">
                            <i class="bi bi-telephone me-1"></i>Ligar
                        </a>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bi bi-trash me-1"></i>Excluir Lead
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir este lead? Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="delete_lead.php?id=<?php echo $lead['id']; ?>" class="btn btn-danger">Sim, Excluir</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
