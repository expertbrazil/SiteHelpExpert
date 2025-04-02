<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: login.php");
    exit;
}

// Incluir arquivo de configuração
require_once "../includes/config/config.php";

// Selecionar o banco de dados
$conn->exec("USE helpexpert");

// Obter informações do usuário logado
$user_id = $_SESSION["admin_id"] ?? 0;
$user_name = $_SESSION["admin_name"] ?? 'Administrador';
$user_role = $_SESSION["admin_role"] ?? 'admin';

// Função para obter todos os leads
function getLeads($conn) {
    $stmt = $conn->prepare("SELECT * FROM leads ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter leads por status
function getLeadsByStatus($conn, $status) {
    try {
        // Verificar se a tabela leads existe
        $result = $conn->query("SHOW TABLES LIKE 'leads'");
        $tableExists = $result->rowCount() > 0;
        
        if (!$tableExists) {
            return [];
        }
        
        // Verificar se a coluna status existe
        $result = $conn->query("SHOW COLUMNS FROM leads LIKE 'status'");
        $exists = $result->rowCount() > 0;
        
        if ($exists) {
            $stmt = $conn->prepare("SELECT * FROM leads WHERE status = :status ORDER BY created_at DESC");
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Se a coluna não existir, retornar um array vazio
            return [];
        }
    } catch(PDOException $e) {
        // Registrar erro em log
        error_log("Erro ao obter leads por status: " . $e->getMessage());
        // Em caso de erro, retornar um array vazio
        return [];
    }
}

// Função para obter todos os contatos
function getContacts($conn) {
    $stmt = $conn->prepare("SELECT * FROM contacts ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter todas as colunas do kanban
function getKanbanColumns($conn) {
    try {
        // Verificar se a tabela existe
        $result = $conn->query("SHOW TABLES LIKE 'kanban_columns'");
        $tableExists = $result->rowCount() > 0;
        
        if (!$tableExists) {
            // Se a tabela não existir, retornar colunas padrão
            return [
                ['id' => 1, 'name' => 'Novo', 'status_key' => 'novo', 'color' => '#007498', 'position' => 1, 'is_default' => 1],
                ['id' => 2, 'name' => 'Contato', 'status_key' => 'contato', 'color' => '#17a2b8', 'position' => 2, 'is_default' => 0],
                ['id' => 3, 'name' => 'Negociação', 'status_key' => 'negociacao', 'color' => '#ffc107', 'position' => 3, 'is_default' => 0],
                ['id' => 4, 'name' => 'Fechado', 'status_key' => 'fechado', 'color' => '#28a745', 'position' => 4, 'is_default' => 0],
                ['id' => 5, 'name' => 'Perdido', 'status_key' => 'perdido', 'color' => '#dc3545', 'position' => 5, 'is_default' => 0]
            ];
        }
        
        // Obter todas as colunas ordenadas por posição
        $stmt = $conn->prepare("SELECT * FROM kanban_columns ORDER BY position ASC");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Verificar se há colunas retornadas
        if (empty($columns)) {
            // Se não houver colunas, retornar colunas padrão
            return [
                ['id' => 1, 'name' => 'Novo', 'status_key' => 'novo', 'color' => '#007498', 'position' => 1, 'is_default' => 1],
                ['id' => 2, 'name' => 'Contato', 'status_key' => 'contato', 'color' => '#17a2b8', 'position' => 2, 'is_default' => 0],
                ['id' => 3, 'name' => 'Negociação', 'status_key' => 'negociacao', 'color' => '#ffc107', 'position' => 3, 'is_default' => 0],
                ['id' => 4, 'name' => 'Fechado', 'status_key' => 'fechado', 'color' => '#28a745', 'position' => 4, 'is_default' => 0],
                ['id' => 5, 'name' => 'Perdido', 'status_key' => 'perdido', 'color' => '#dc3545', 'position' => 5, 'is_default' => 0]
            ];
        }
        
        return $columns;
    } catch(PDOException $e) {
        // Registrar erro em log
        error_log("Erro ao obter colunas do kanban: " . $e->getMessage());
        // Em caso de erro, retornar colunas padrão
        return [
            ['id' => 1, 'name' => 'Novo', 'status_key' => 'novo', 'color' => '#007498', 'position' => 1, 'is_default' => 1],
            ['id' => 2, 'name' => 'Contato', 'status_key' => 'contato', 'color' => '#17a2b8', 'position' => 2, 'is_default' => 0],
            ['id' => 3, 'name' => 'Negociação', 'status_key' => 'negociacao', 'color' => '#ffc107', 'position' => 3, 'is_default' => 0],
            ['id' => 4, 'name' => 'Fechado', 'status_key' => 'fechado', 'color' => '#28a745', 'position' => 4, 'is_default' => 0],
            ['id' => 5, 'name' => 'Perdido', 'status_key' => 'perdido', 'color' => '#dc3545', 'position' => 5, 'is_default' => 0]
        ];
    }
}

// Obter dados
$leads = getLeads($conn);
$contacts = getContacts($conn);

// Verificar se a coluna status existe
$result = $conn->query("SHOW COLUMNS FROM leads LIKE 'status'");
$statusColumnExists = $result->rowCount() > 0;

// Se a coluna não existir, redirecionar para o script de correção
if (!$statusColumnExists) {
    header("Location: fix_database.php");
    exit;
}

// Obter leads por status para o kanban
$newLeads = getLeadsByStatus($conn, 'novo');
$contactLeads = getLeadsByStatus($conn, 'contato');
$negotiationLeads = getLeadsByStatus($conn, 'negociacao');
$closedLeads = getLeadsByStatus($conn, 'fechado');
$lostLeads = getLeadsByStatus($conn, 'perdido');

// Para leads que ainda não têm status definido, definir como 'novo'
$stmt = $conn->prepare("UPDATE leads SET status = 'novo' WHERE status IS NULL OR status = ''");
$stmt->execute();

// Processar logout
if (isset($_GET["logout"])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Definir a aba ativa
$activeTab = 'dashboard';
if (isset($_GET['tab']) && in_array($_GET['tab'], ['dashboard', 'leads', 'contacts', 'kanban'])) {
    $activeTab = $_GET['tab'];
}

// Mensagens de feedback
$successMessage = '';
$errorMessage = '';

if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $successMessage = 'Lead excluído com sucesso!';
}

if (isset($_GET['error'])) {
    $errorMessage = 'Erro: ' . htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpExpert - Painel Administrativo</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    
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
            --dark-gray: #3A4A5A;
            --soft-gray: #7A8A9A;
            --light-gray: #F5F7F9;
            --white: #FFFFFF;
            --medium-gray: #E0E5E9;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--light-gray);
            min-height: 100vh;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Archivo', sans-serif;
        }
        
        .navbar-brand {
            font-family: 'Archivo', sans-serif;
            font-weight: 700;
        }
        
        /* Cores personalizadas */
        .bg-primary-helpexpert {
            background-color: #007498 !important;
        }
        
        .text-primary-helpexpert {
            color: #007498 !important;
        }
        
        .bg-secondary-helpexpert {
            background-color: #00aba5 !important;
        }
        
        .text-secondary-helpexpert {
            color: #00aba5 !important;
        }
        
        /* Estilos do Kanban */
        .kanban-container {
            position: relative;
            padding: 1rem;
            overflow: hidden;
            width: 100%;
            height: 100%;
        }
        
        .kanban-board {
            display: flex;
            min-height: 600px;
            width: max-content;
            transition: transform 0.3s ease;
        }
        
        .kanban-column {
            min-width: 280px;
            width: 280px;
            margin-right: 15px;
            background-color: #F5F7F9;
            border-radius: 5px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .kanban-column-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 3px solid var(--column-color, #007498);
            background-color: #fff;
            border-radius: 5px 5px 0 0;
        }
        
        .kanban-column-body {
            padding: 10px;
            flex-grow: 1;
            overflow-y: auto;
            max-height: 600px;
        }
        
        .kanban-card {
            background-color: #fff;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 10px;
            margin-bottom: 10px;
            border-left: 4px solid var(--column-color, #007498);
        }
        
        .kanban-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .kanban-card.dragging {
            opacity: 0.5;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            transform: scale(0.95);
        }
        
        .kanban-column-body.drag-over {
            background-color: rgba(0, 171, 165, 0.1);
            border: 2px dashed #00aba5;
        }
        
        .kanban-card-title {
            font-weight: 600;
            margin-bottom: 0.3rem;
            color: #3A4A5A;
        }
        
        .kanban-card-info {
            font-size: 0.875rem;
            color: #7A8A9A;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }
        
        .kanban-card-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.5rem;
        }
        
        .kanban-badge-novo {
            background-color: #007498;
        }
        
        .kanban-badge-contato {
            background-color: #00aba5;
        }
        
        .kanban-badge-negociacao {
            background-color: #ffc107;
            color: #3A4A5A;
        }
        
        .kanban-badge-fechado {
            background-color: #28a745;
        }
        
        .kanban-badge-perdido {
            background-color: #dc3545;
        }
        
        .kanban-status-novo {
            border-left-color: var(--primary);
        }
        
        .kanban-status-contato {
            border-left-color: var(--info);
        }
        
        .kanban-status-negociacao {
            border-left-color: var(--warning);
        }
        
        .kanban-status-fechado {
            border-left-color: var(--success);
        }
        
        .kanban-status-perdido {
            border-left-color: var(--danger);
        }
        
        .kanban-add-column {
            min-width: 300px;
            border: 2px dashed var(--medium-gray);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            background-color: var(--white);
        }
        
        .kanban-add-column:hover {
            border-color: var(--primary);
            background-color: var(--light-gray);
        }
        
        .kanban-add-column i {
            font-size: 2rem;
            color: var(--soft-gray);
            margin-bottom: 0.5rem;
        }
        
        .kanban-column-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .kanban-column-actions button {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
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
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .header h1 {
            font-family: 'Archivo', sans-serif;
            color: var(--dark-gray);
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        
        .card-header {
            background-color: var(--white);
            border-bottom: 1px solid var(--medium-gray);
            padding: 1.25rem 1.5rem;
            font-family: 'Archivo', sans-serif;
            font-weight: 600;
            color: var(--dark-gray);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .table {
            color: var(--dark-gray);
        }
        
        .table th {
            font-weight: 600;
            color: var(--dark-gray);
            border-bottom-width: 1px;
        }
        
        .badge {
            font-weight: 500;
            padding: 0.5em 0.75em;
        }
        
        .tab-content {
            padding-top: 1.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #006080;
        }
        
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary);
            color: var(--white);
        }
        
        .user-dropdown {
            display: flex;
            align-items: center;
        }
        
        .user-dropdown img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 0.75rem;
        }
        
        .user-dropdown .dropdown-toggle {
            background: none;
            border: none;
            color: var(--dark-gray);
            font-weight: 600;
        }
        
        .user-dropdown .dropdown-toggle::after {
            margin-left: 0.5rem;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        
        .dropdown-item {
            padding: 0.5rem 1rem;
            font-weight: 500;
        }
        
        .dropdown-item:hover {
            background-color: var(--light-gray);
            color: var(--primary);
        }
        
        .dropdown-item i {
            margin-right: 0.5rem;
        }
        
        /* Responsividade */
        @media (max-width: 992px) {
            .kanban-container {
                overflow-x: auto;
            }
            
            .kanban-board {
                min-width: 1200px;
            }
        }
        
        /* Estilos do contador de leads */
        .lead-counter {
            background-color: #007498;
            color: white !important;
            font-weight: normal;
            padding: 4px 8px;
            border-radius: 4px;
            margin-left: 8px;
            font-size: 0.85rem;
            display: inline-block;
        }
        
        /* Ajuste para o dropdown de ações */
        .kanban-card .dropdown-menu {
            min-width: 200px;
            padding: 8px 0;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1050;
            position: absolute;
            left: auto !important;
            right: 0 !important;
            transform: none !important;
        }
        
        .kanban-card .dropdown-item {
            padding: 8px 16px;
            color: #3A4A5A;
            font-size: 0.9rem;
            white-space: nowrap;
        }
        
        .kanban-card .dropdown-item:hover {
            background-color: #F5F7F9;
            color: #007498;
        }
        
        .kanban-card .dropdown-item i {
            margin-right: 8px;
            color: #7A8A9A;
        }
        
        .kanban-card .dropdown-divider {
            margin: 4px 0;
            border-color: #E0E5E9;
        }
        
        /* Botões de navegação do Kanban */
        .kanban-nav-container {
            position: relative;
        }
        
        .kanban-nav-buttons {
            display: flex;
            justify-content: flex-end;
            padding: 10px 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e0e5e9;
        }
        
        .kanban-nav-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #007498;
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            margin-left: 10px;
        }
        
        .kanban-nav-btn:hover {
            background-color: #00aba5;
        }
        
        .kanban-nav-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        
        /* Estilo para o link do WhatsApp */
        .whatsapp-link {
            display: inline-block;
            margin-left: 8px;
            text-decoration: none;
            transition: transform 0.2s ease;
        }
        
        .whatsapp-link i {
            font-size: 1.1rem;
        }
        
        .whatsapp-link:hover {
            transform: scale(1.2);
        }
        
        /* Estilo para o indicador de anotações */
        .notes-indicator {
            display: inline-block;
            margin-left: 5px;
            position: relative;
        }
        
        .notes-indicator i {
            color: #00aba5;
            font-size: 0.6rem;
            position: relative;
            top: -1px;
        }
        
        /* Estilo para o ícone de anotações no card */
        .kanban-card-notes {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #007498;
            cursor: pointer;
            font-size: 1rem;
            opacity: 0.7;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        
        .kanban-card-notes:hover {
            opacity: 1;
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="../assets/img/logo.png" alt="HelpExpert Logo" height="40">
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link <?php echo $activeTab === 'dashboard' ? 'active' : ''; ?>" href="#dashboard" data-bs-toggle="tab">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link <?php echo $activeTab === 'leads' ? 'active' : ''; ?>" href="#leads" data-bs-toggle="tab">
                <i class="bi bi-people-fill"></i> Leads
            </a>
            <a class="nav-link <?php echo $activeTab === 'contacts' ? 'active' : ''; ?>" href="#contacts" data-bs-toggle="tab">
                <i class="bi bi-chat-dots-fill"></i> Contatos
            </a>
            <a class="nav-link <?php echo $activeTab === 'kanban' ? 'active' : ''; ?>" href="#kanban" data-bs-toggle="tab">
                <i class="bi bi-kanban"></i> Kanban
            </a>
            <a class="nav-link" href="?logout=1">
                <i class="bi bi-box-arrow-right"></i> Sair
            </a>
        </nav>
    </div>
    
    <!-- Content -->
    <div class="content">
        <div class="header">
            <h1>Painel Administrativo</h1>
            
            <div class="user-dropdown dropdown">
                <button class="dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle fs-4 me-2"></i>
                    <?php echo htmlspecialchars($_SESSION["admin_username"]); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="?logout=1"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
                </ul>
            </div>
        </div>
        
        <?php if ($successMessage): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($errorMessage): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <div class="tab-content">
            <!-- Dashboard Tab -->
            <div class="tab-pane fade show <?php echo $activeTab === 'dashboard' ? 'active' : ''; ?>" id="dashboard">
                <div class="row">
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-muted mb-2">Total de Leads</h5>
                                <h2 class="mb-0"><?php echo count($leads); ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-muted mb-2">Total de Contatos</h5>
                                <h2 class="mb-0"><?php echo count($contacts); ?></h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-muted mb-2">Leads Hoje</h5>
                                <h2 class="mb-0">
                                    <?php 
                                    $today = date('Y-m-d');
                                    $todayLeads = 0;
                                    foreach ($leads as $lead) {
                                        if (substr($lead['created_at'], 0, 10) === $today) {
                                            $todayLeads++;
                                        }
                                    }
                                    echo $todayLeads;
                                    ?>
                                </h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-muted mb-2">Contatos Hoje</h5>
                                <h2 class="mb-0">
                                    <?php 
                                    $todayContacts = 0;
                                    foreach ($contacts as $contact) {
                                        if (substr($contact['created_at'], 0, 10) === $today) {
                                            $todayContacts++;
                                        }
                                    }
                                    echo $todayContacts;
                                    ?>
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Leads Recentes</span>
                                <a href="#leads" class="btn btn-sm btn-outline-primary" data-bs-toggle="tab">Ver Todos</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>Email</th>
                                                <th>Data</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $recentLeads = array_slice($leads, 0, 5);
                                            foreach ($recentLeads as $lead): 
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($lead['name']); ?></td>
                                                <td><?php echo htmlspecialchars($lead['email']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($lead['created_at'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            
                                            <?php if (empty($recentLeads)): ?>
                                            <tr>
                                                <td colspan="3" class="text-center">Nenhum lead encontrado.</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Contatos Recentes</span>
                                <a href="#contacts" class="btn btn-sm btn-outline-primary" data-bs-toggle="tab">Ver Todos</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>Assunto</th>
                                                <th>Data</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $recentContacts = array_slice($contacts, 0, 5);
                                            foreach ($recentContacts as $contact): 
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                                <td><?php echo htmlspecialchars($contact['subject']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            
                                            <?php if (empty($recentContacts)): ?>
                                            <tr>
                                                <td colspan="3" class="text-center">Nenhum contato encontrado.</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Leads por Período</span>
                            </div>
                            <div class="card-body">
                                <canvas id="leadsChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Distribuição por Tamanho de Empresa</span>
                            </div>
                            <div class="card-body">
                                <canvas id="companyChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Leads Recentes</span>
                                <a href="#leads" class="btn btn-sm btn-outline-primary" data-bs-toggle="tab">Ver Todos</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>Email</th>
                                                <th>Data</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $recentLeads = array_slice($leads, 0, 5);
                                            foreach ($recentLeads as $lead): 
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($lead['name']); ?></td>
                                                <td><?php echo htmlspecialchars($lead['email']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($lead['created_at'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            
                                            <?php if (empty($recentLeads)): ?>
                                            <tr>
                                                <td colspan="3" class="text-center">Nenhum lead encontrado.</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Contatos Recentes</span>
                                <a href="#contacts" class="btn btn-sm btn-outline-primary" data-bs-toggle="tab">Ver Todos</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>Assunto</th>
                                                <th>Data</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $recentContacts = array_slice($contacts, 0, 5);
                                            foreach ($recentContacts as $contact): 
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                                <td><?php echo htmlspecialchars($contact['subject']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            
                                            <?php if (empty($recentContacts)): ?>
                                            <tr>
                                                <td colspan="3" class="text-center">Nenhum contato encontrado.</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Leads Tab -->
            <div class="tab-pane fade <?php echo $activeTab === 'leads' ? 'show active' : ''; ?>" id="leads">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Todos os Leads</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nome</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Empresa</th>
                                        <th scope="col">Funcionários</th>
                                        <th scope="col">Data</th>
                                        <th scope="col">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($leads) > 0): ?>
                                        <?php foreach ($leads as $lead): ?>
                                            <tr>
                                                <th scope="row"><?php echo $lead['id']; ?></th>
                                                <td><?php echo htmlspecialchars($lead['name']); ?></td>
                                                <td><?php echo htmlspecialchars($lead['email']); ?></td>
                                                <td><?php echo htmlspecialchars($lead['company']); ?></td>
                                                <td><?php echo htmlspecialchars($lead['employees']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($lead['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="view_lead.php?id=<?php echo $lead['id']; ?>" class="btn btn-outline-primary" title="Visualizar">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="edit_lead.php?id=<?php echo $lead['id']; ?>" class="btn btn-outline-primary" title="Editar">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-outline-danger" title="Excluir" data-bs-toggle="modal" data-bs-target="#deleteLeadModal<?php echo $lead['id']; ?>">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                    
                                                    <!-- Modal de Confirmação de Exclusão -->
                                                    <div class="modal fade" id="deleteLeadModal<?php echo $lead['id']; ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Confirmar Exclusão</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Tem certeza que deseja excluir o lead de <strong><?php echo htmlspecialchars($lead['name']); ?></strong>? Esta ação não pode ser desfeita.</p>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                    <a href="delete_lead.php?id=<?php echo $lead['id']; ?>" class="btn btn-danger">Sim, Excluir</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="bi bi-inbox display-6 d-block mb-3 text-muted"></i>
                                                <p class="text-muted">Nenhum lead encontrado.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contacts Tab -->
            <div class="tab-pane fade <?php echo $activeTab === 'contacts' ? 'show active' : ''; ?>" id="contacts">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Todos os Contatos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="contactsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Telefone</th>
                                        <th>Assunto</th>
                                        <th>Mensagem</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($contacts as $contact): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($contact['id']); ?></td>
                                        <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                        <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                        <td><?php echo htmlspecialchars($contact['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($contact['subject']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($contact['message'], 0, 50)) . (strlen($contact['message']) > 50 ? '...' : ''); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($contacts)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Nenhum contato encontrado.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Kanban Tab -->
            <div class="tab-pane fade <?php echo $activeTab === 'kanban' ? 'show active' : ''; ?>" id="kanban">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Kanban de Leads</h5>
                        <div>
                            <span class="badge bg-info me-2" id="totalColumnsCount"></span>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addColumnModal">
                                <i class="bi bi-plus-circle me-1"></i>Nova Coluna
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <!-- Kanban Board com navegação -->
                        <div class="kanban-nav-container">
                            <div class="kanban-nav-buttons">
                                <button type="button" class="kanban-nav-btn" id="kanbanNavPrev">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button type="button" class="kanban-nav-btn" id="kanbanNavNext">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                            <div class="kanban-container">
                                <div class="kanban-board" id="kanbanBoard">
                                    <?php $kanbanColumns = getKanbanColumns($conn); ?>
                                    <?php foreach ($kanbanColumns as $column): ?>
                                    <div class="kanban-column" style="--column-color: <?php echo htmlspecialchars($column['color']); ?>">
                                        <div class="kanban-column-header" style="border-bottom: 3px solid <?php echo htmlspecialchars($column['color']); ?>">
                                            <h5 class="mb-0"><?php echo htmlspecialchars($column['name']); ?> <span class="lead-counter">0</span></h5>
                                            <div class="kanban-column-actions">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" data-action="edit-column" data-column-id="<?php echo $column['id']; ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete-column" data-column-id="<?php echo $column['id']; ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="kanban-column-body" data-status="<?php echo htmlspecialchars($column['status_key']); ?>">
                                            <?php 
                                            // Obter leads para esta coluna
                                            $columnLeads = getLeadsByStatus($conn, $column['status_key']);
                                            foreach ($columnLeads as $lead): 
                                            ?>
                                            <div class="kanban-card" id="card-<?php echo $lead['id']; ?>" data-lead-id="<?php echo $lead['id']; ?>" style="border-left: 4px solid <?php echo htmlspecialchars($column['color']); ?>">
                                                <div class="kanban-card-title"><?php echo htmlspecialchars($lead['name']); ?></div>
                                                <div class="kanban-card-info">
                                                    <i class="bi bi-building"></i> <?php echo htmlspecialchars($lead['company']); ?><br>
                                                    <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($lead['email']); ?><br>
                                                    <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($lead['phone']); ?>
                                                    <?php if (!empty($lead['phone'])): ?>
                                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $lead['phone']); ?>" target="_blank" class="whatsapp-link" title="Iniciar conversa no WhatsApp">
                                                        <i class="bi bi-whatsapp" style="color: #25D366;"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="kanban-card-actions">
                                                    <span class="badge" style="background-color: <?php echo htmlspecialchars($column['color']); ?>"><?php echo htmlspecialchars($column['name']); ?></span>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton-<?php echo $lead['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                            Ações
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-<?php echo $lead['id']; ?>">
                                                            <li>
                                                                <a class="dropdown-item" href="view_lead.php?id=<?php echo $lead['id']; ?>">
                                                                    <i class="bi bi-eye"></i> Ver Detalhes
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="edit_lead.php?id=<?php echo $lead['id']; ?>">
                                                                    <i class="bi bi-pencil"></i> Editar
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item notes-btn" href="#" data-lead-id="<?php echo $lead['id']; ?>" data-lead-name="<?php echo htmlspecialchars($lead['name']); ?>" data-notes="<?php echo htmlspecialchars($lead['notes'] ?? ''); ?>">
                                                                    <i class="bi bi-journal-text"></i> Anotações
                                                                    <?php if (!empty($lead['notes'])): ?>
                                                                    <span class="notes-indicator"><i class="bi bi-circle-fill"></i></span>
                                                                    <?php endif; ?>
                                                                </a>
                                                            </li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <?php if ($column['status_key'] === 'novo'): ?>
                                                            <li>
                                                                <form action="update_lead_status.php" method="post" style="display: inline;">
                                                                    <input type="hidden" name="lead_id" value="<?php echo $lead['id']; ?>">
                                                                    <input type="hidden" name="status" value="contato">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="bi bi-arrow-right"></i> Mover para Contato
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <?php elseif ($column['status_key'] === 'contato'): ?>
                                                            <li>
                                                                <form action="update_lead_status.php" method="post" style="display: inline;">
                                                                    <input type="hidden" name="lead_id" value="<?php echo $lead['id']; ?>">
                                                                    <input type="hidden" name="status" value="novo">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="bi bi-arrow-left"></i> Mover para Novo
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form action="update_lead_status.php" method="post" style="display: inline;">
                                                                    <input type="hidden" name="lead_id" value="<?php echo $lead['id']; ?>">
                                                                    <input type="hidden" name="status" value="negociacao">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="bi bi-arrow-right"></i> Mover para Negociação
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <?php elseif ($column['status_key'] === 'negociacao'): ?>
                                                            <li>
                                                                <form action="update_lead_status.php" method="post" style="display: inline;">
                                                                    <input type="hidden" name="lead_id" value="<?php echo $lead['id']; ?>">
                                                                    <input type="hidden" name="status" value="contato">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="bi bi-arrow-left"></i> Mover para Contato
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form action="update_lead_status.php" method="post" style="display: inline;">
                                                                    <input type="hidden" name="lead_id" value="<?php echo $lead['id']; ?>">
                                                                    <input type="hidden" name="status" value="fechado">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="bi bi-arrow-right"></i> Mover para Fechado
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form action="update_lead_status.php" method="post" style="display: inline;">
                                                                    <input type="hidden" name="lead_id" value="<?php echo $lead['id']; ?>">
                                                                    <input type="hidden" name="status" value="perdido">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="bi bi-x-circle"></i> Mover para Perdido
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <?php elseif ($column['status_key'] === 'fechado'): ?>
                                                            <li>
                                                                <form action="update_lead_status.php" method="post" style="display: inline;">
                                                                    <input type="hidden" name="lead_id" value="<?php echo $lead['id']; ?>">
                                                                    <input type="hidden" name="status" value="negociacao">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="bi bi-arrow-left"></i> Mover para Negociação
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <?php elseif ($column['status_key'] === 'perdido'): ?>
                                                            <li>
                                                                <form action="update_lead_status.php" method="post" style="display: inline;">
                                                                    <input type="hidden" name="lead_id" value="<?php echo $lead['id']; ?>">
                                                                    <input type="hidden" name="status" value="negociacao">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="bi bi-arrow-left"></i> Mover para Negociação
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para adicionar coluna -->
    <div class="modal fade" id="addColumnModal" tabindex="-1" aria-labelledby="addColumnModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addColumnModalLabel">Nova Coluna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <form action="manage_kanban_column.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="columnName" class="form-label">Nome da Coluna</label>
                            <input type="text" class="form-control" id="columnName" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="columnKey" class="form-label">Chave (identificador único, sem espaços)</label>
                            <input type="text" class="form-control" id="columnKey" name="status_key" pattern="[a-z0-9_]+" required>
                            <small class="form-text text-muted">Use apenas letras minúsculas, números e underscores.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="columnColor" class="form-label">Cor</label>
                            <input type="color" class="form-control" id="columnColor" name="color" value="#007498">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal para editar coluna -->
    <div class="modal fade" id="editColumnModal" tabindex="-1" aria-labelledby="editColumnModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editColumnModalLabel">Editar Coluna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <form action="manage_kanban_column.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="editColumnId">
                        
                        <div class="mb-3">
                            <label for="editColumnName" class="form-label">Nome da Coluna</label>
                            <input type="text" class="form-control" id="editColumnName" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editColumnColor" class="form-label">Cor</label>
                            <input type="color" class="form-control" id="editColumnColor" name="color">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal para excluir coluna -->
    <div class="modal fade" id="deleteColumnModal" tabindex="-1" aria-labelledby="deleteColumnModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteColumnModalLabel">Excluir Coluna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <form action="manage_kanban_column.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteColumnId">
                        
                        <p>Tem certeza que deseja excluir esta coluna?</p>
                        <p class="text-danger">Atenção: Só é possível excluir colunas que não possuem leads associados.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal para editar anotações -->
    <div class="modal fade" id="editNotesModal" tabindex="-1" aria-labelledby="editNotesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editNotesModalLabel">Anotações do Lead</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form id="notesForm">
                        <input type="hidden" id="notesLeadId" name="lead_id">
                        <div class="mb-3">
                            <label for="leadNotes" class="form-label">Anotações</label>
                            <textarea class="form-control" id="leadNotes" name="notes" rows="5" placeholder="Adicione suas anotações sobre este lead aqui..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveNotesBtn">Salvar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Dados para o Kanban -->
    <script>
        // Dados das colunas do kanban para uso no JavaScript
        var kanbanColumnsData = <?php echo json_encode($kanbanColumns); ?>;
    </script>
    
    <!-- Script do Kanban -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar os dropdowns do Bootstrap manualmente
            var dropdownTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
            var dropdownList = dropdownTriggerList.map(function (dropdownTriggerEl) {
                return new bootstrap.Dropdown(dropdownTriggerEl);
            });
            
            // Configurar drag and drop para o kanban
            const kanbanCards = document.querySelectorAll('.kanban-card');
            const kanbanColumns = document.querySelectorAll('.kanban-column-body');
            
            console.log('Cards encontrados:', kanbanCards.length);
            console.log('Colunas encontradas:', kanbanColumns.length);
            
            // Adicionar eventos de drag para os cards
            kanbanCards.forEach(card => {
                card.setAttribute('draggable', true);
                
                card.addEventListener('dragstart', function(e) {
                    console.log('Drag iniciado para o card:', card.id);
                    e.dataTransfer.setData('text/plain', card.id);
                    setTimeout(() => {
                        card.classList.add('dragging');
                    }, 0);
                });
                
                card.addEventListener('dragend', function() {
                    console.log('Drag finalizado para o card:', card.id);
                    card.classList.remove('dragging');
                });
            });
            
            // Adicionar eventos de drop para as colunas
            kanbanColumns.forEach(column => {
                column.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    column.classList.add('drag-over');
                });
                
                column.addEventListener('dragleave', function() {
                    column.classList.remove('drag-over');
                });
                
                column.addEventListener('drop', function(e) {
                    e.preventDefault();
                    column.classList.remove('drag-over');
                    
                    const cardId = e.dataTransfer.getData('text/plain');
                    console.log('Card ID recebido no drop:', cardId);
                    const card = document.getElementById(cardId);
                    
                    if (card) {
                        console.log('Card encontrado:', card);
                        // Obter o ID do lead e o novo status
                        const leadId = card.getAttribute('data-lead-id');
                        const newStatus = column.getAttribute('data-status');
                        
                        console.log('Lead ID:', leadId);
                        console.log('Novo status:', newStatus);
                        
                        if (leadId && newStatus) {
                            // Enviar solicitação AJAX para atualizar o status
                            const formData = new FormData();
                            formData.append('lead_id', leadId);
                            formData.append('status', newStatus);
                            
                            fetch('update_lead_status.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Resposta do servidor:', data);
                                if (data.success) {
                                    // Mover o card para a nova coluna
                                    column.appendChild(card);
                                } else {
                                    console.error('Erro ao atualizar o status:', data.error);
                                    alert('Erro ao atualizar o status: ' + data.error);
                                }
                            })
                            .catch(error => {
                                console.error('Erro ao atualizar o status:', error);
                                alert('Erro ao processar a requisição. Tente novamente.');
                            });
                        }
                    } else {
                        console.error('Card não encontrado com ID:', cardId);
                    }
                });
            });
            
            // Configurar botões de edição e exclusão de colunas
            const editButtons = document.querySelectorAll('[data-action="edit-column"]');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const columnId = this.getAttribute('data-column-id');
                    editColumn(parseInt(columnId));
                    
                    // Abrir o modal de edição
                    var editModal = new bootstrap.Modal(document.getElementById('editColumnModal'));
                    editModal.show();
                });
            });
            
            const deleteButtons = document.querySelectorAll('[data-action="delete-column"]');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const columnId = this.getAttribute('data-column-id');
                    deleteColumn(columnId);
                    
                    // Abrir o modal de exclusão
                    var deleteModal = new bootstrap.Modal(document.getElementById('deleteColumnModal'));
                    deleteModal.show();
                });
            });
        });
        
        // Funções para gerenciar colunas
        function editColumn(id) {
            const columns = <?php echo json_encode($kanbanColumns); ?>;
            const column = columns.find(col => parseInt(col.id) === id);
            
            if (column) {
                document.getElementById('editColumnId').value = id;
                document.getElementById('editColumnName').value = column.name;
                document.getElementById('editColumnColor').value = column.color;
            }
        }
        
        function deleteColumn(id) {
            document.getElementById('deleteColumnId').value = id;
        }
    </script>
    
    <!-- Script para gerenciar anotações -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Adicionar ícone de anotações aos cards que já têm anotações
            const cards = document.querySelectorAll('.kanban-card');
            cards.forEach(card => {
                const leadId = card.getAttribute('data-lead-id');
                const notesBtn = document.querySelector(`.notes-btn[data-lead-id="${leadId}"]`);
                if (notesBtn && notesBtn.querySelector('.notes-indicator')) {
                    const notesIcon = document.createElement('i');
                    notesIcon.className = 'bi bi-journal-text kanban-card-notes';
                    notesIcon.setAttribute('data-lead-id', leadId);
                    notesIcon.setAttribute('title', 'Ver anotações');
                    card.appendChild(notesIcon);
                    
                    notesIcon.addEventListener('click', function() {
                        const notesBtn = document.querySelector(`.notes-btn[data-lead-id="${leadId}"]`);
                        notesBtn.click();
                    });
                }
            });
            
            // Configurar botões de anotações
            const notesBtns = document.querySelectorAll('.notes-btn');
            notesBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const leadId = this.getAttribute('data-lead-id');
                    const leadName = this.getAttribute('data-lead-name');
                    const notes = this.getAttribute('data-notes');
                    
                    // Atualizar o modal
                    document.getElementById('editNotesModalLabel').textContent = `Anotações: ${leadName}`;
                    document.getElementById('notesLeadId').value = leadId;
                    document.getElementById('leadNotes').value = notes;
                    
                    // Abrir o modal
                    const modal = new bootstrap.Modal(document.getElementById('editNotesModal'));
                    modal.show();
                });
            });
            
            // Configurar botão de salvar anotações
            document.getElementById('saveNotesBtn').addEventListener('click', function() {
                const leadId = document.getElementById('notesLeadId').value;
                const notes = document.getElementById('leadNotes').value;
                
                // Enviar dados para o servidor
                const formData = new FormData();
                formData.append('lead_id', leadId);
                formData.append('notes', notes);
                
                fetch('save_notes.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Atualizar o atributo data-notes do botão
                        const notesBtn = document.querySelector(`.notes-btn[data-lead-id="${leadId}"]`);
                        notesBtn.setAttribute('data-notes', notes);
                        
                        // Adicionar ou remover o indicador de anotações
                        let indicator = notesBtn.querySelector('.notes-indicator');
                        if (notes.trim() !== '') {
                            if (!indicator) {
                                indicator = document.createElement('span');
                                indicator.className = 'notes-indicator';
                                indicator.innerHTML = '<i class="bi bi-circle-fill"></i>';
                                notesBtn.appendChild(indicator);
                                
                                // Adicionar ícone ao card se não existir
                                const card = document.querySelector(`.kanban-card[data-lead-id="${leadId}"]`);
                                if (card && !card.querySelector('.kanban-card-notes')) {
                                    const notesIcon = document.createElement('i');
                                    notesIcon.className = 'bi bi-journal-text kanban-card-notes';
                                    notesIcon.setAttribute('data-lead-id', leadId);
                                    notesIcon.setAttribute('title', 'Ver anotações');
                                    card.appendChild(notesIcon);
                                    
                                    notesIcon.addEventListener('click', function() {
                                        notesBtn.click();
                                    });
                                }
                            }
                        } else if (indicator) {
                            indicator.remove();
                            
                            // Remover ícone do card
                            const notesIcon = document.querySelector(`.kanban-card-notes[data-lead-id="${leadId}"]`);
                            if (notesIcon) {
                                notesIcon.remove();
                            }
                        }
                        
                        // Fechar o modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editNotesModal'));
                        modal.hide();
                        
                        // Mostrar mensagem de sucesso
                        alert('Anotações salvas com sucesso!');
                    } else {
                        alert('Erro ao salvar anotações: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao processar a requisição. Tente novamente.');
                });
            });
        });
    </script>
    
    <script src="js/kanban.js"></script>
    
    <script src="js/simple-kanban-nav.js"></script>
    
    <script src="js/lead-counter.js"></script>
    
    <script src="js/column-counter.js"></script>
    
    <script src="js/kanban-nav-fixed.js"></script>
    
    <script>
        $(document).ready(function() {
            // Inicializar DataTables
            $('#leadsTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
                },
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]]
            });
            
            $('#contactsTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
                },
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]]
            });
            
            // Gráfico de Leads por Período
            const leadsCtx = document.getElementById('leadsChart');
            if (leadsCtx) {
                const ctx = leadsCtx.getContext('2d');
                
                // Preparar dados para o gráfico de leads
                <?php
                // Obter dados dos últimos 7 dias
                $dates = [];
                $leadCounts = [];
                $contactCounts = [];
                
                for ($i = 6; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-$i days"));
                    $dates[] = date('d/m', strtotime("-$i days"));
                    
                    $leadCount = 0;
                    foreach ($leads as $lead) {
                        if (substr($lead['created_at'], 0, 10) === $date) {
                            $leadCount++;
                        }
                    }
                    $leadCounts[] = $leadCount;
                    
                    $contactCount = 0;
                    foreach ($contacts as $contact) {
                        if (substr($contact['created_at'], 0, 10) === $date) {
                            $contactCount++;
                        }
                    }
                    $contactCounts[] = $contactCount;
                }
                ?>
                
                const leadsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($dates); ?>,
                        datasets: [
                            {
                                label: 'Leads',
                                data: <?php echo json_encode($leadCounts); ?>,
                                backgroundColor: 'rgba(0, 116, 152, 0.2)',
                                borderColor: 'rgba(0, 116, 152, 1)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: true
                            },
                            {
                                label: 'Contatos',
                                data: <?php echo json_encode($contactCounts); ?>,
                                backgroundColor: 'rgba(0, 171, 165, 0.2)',
                                borderColor: 'rgba(0, 171, 165, 1)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Leads e Contatos nos Últimos 7 Dias'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
            
            // Gráfico de Distribuição por Tamanho de Empresa
            const companyCtx = document.getElementById('companyChart');
            if (companyCtx) {
                const ctx = companyCtx.getContext('2d');
                
                // Preparar dados para o gráfico de tamanho de empresa
                <?php
                $employeeCounts = [
                    '1-5' => 0,
                    '6-10' => 0,
                    '11-25' => 0,
                    '26-50' => 0,
                    '51-100' => 0,
                    '100+' => 0
                ];
                
                foreach ($leads as $lead) {
                    if (isset($lead['employees']) && isset($employeeCounts[$lead['employees']])) {
                        $employeeCounts[$lead['employees']]++;
                    }
                }
                ?>
                
                const companyChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(<?php echo json_encode($employeeCounts); ?>),
                        datasets: [{
                            data: Object.values(<?php echo json_encode($employeeCounts); ?>),
                            backgroundColor: [
                                'rgba(0, 116, 152, 0.8)',
                                'rgba(0, 171, 165, 0.8)',
                                'rgba(58, 74, 90, 0.8)',
                                'rgba(122, 138, 154, 0.8)',
                                'rgba(224, 229, 233, 0.8)',
                                'rgba(245, 247, 249, 0.8)'
                            ],
                            borderColor: [
                                'rgba(0, 116, 152, 1)',
                                'rgba(0, 171, 165, 1)',
                                'rgba(58, 74, 90, 1)',
                                'rgba(122, 138, 154, 1)',
                                'rgba(224, 229, 233, 1)',
                                'rgba(245, 247, 249, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'right',
                            },
                            title: {
                                display: true,
                                text: 'Distribuição por Número de Funcionários'
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
