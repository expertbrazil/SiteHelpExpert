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
$message = "";
$messageType = "";

// Processar o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_STRING);
    $employees = filter_input(INPUT_POST, 'employees', FILTER_SANITIZE_STRING);
    $privacy = isset($_POST['privacy']) ? 1 : 0;
    
    // Validar dados
    if (empty($name) || empty($email)) {
        $message = "Por favor, preencha todos os campos obrigatórios.";
        $messageType = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Por favor, forneça um email válido.";
        $messageType = "danger";
    } else {
        try {
            // Atualizar o lead no banco de dados
            $stmt = $conn->prepare("UPDATE leads SET name = :name, email = :email, phone = :phone, company = :company, employees = :employees, privacy = :privacy WHERE id = :id");
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':company', $company);
            $stmt->bindParam(':employees', $employees);
            $stmt->bindParam(':privacy', $privacy);
            $stmt->bindParam(':id', $id);
            
            $stmt->execute();
            
            $message = "Lead atualizado com sucesso!";
            $messageType = "success";
        } catch(PDOException $e) {
            $message = "Erro ao atualizar lead: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

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
    <title>Editar Lead - HelpExpert</title>
    
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
            <h1>Editar Lead</h1>
        </div>
        
        <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header py-3">
                <h5 class="mb-0">Formulário de Edição - Lead #<?php echo $lead['id']; ?></h5>
            </div>
            <div class="card-body p-4">
                <form method="post" action="edit_lead.php?id=<?php echo $lead['id']; ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nome *</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($lead['name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($lead['email']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Telefone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($lead['phone']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="company" class="form-label">Empresa</label>
                            <input type="text" class="form-control" id="company" name="company" value="<?php echo htmlspecialchars($lead['company']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="employees" class="form-label">Número de Funcionários</label>
                            <select class="form-select" id="employees" name="employees">
                                <option value="1-5" <?php if ($lead['employees'] == '1-5') echo 'selected'; ?>>1-5 funcionários</option>
                                <option value="6-10" <?php if ($lead['employees'] == '6-10') echo 'selected'; ?>>6-10 funcionários</option>
                                <option value="11-25" <?php if ($lead['employees'] == '11-25') echo 'selected'; ?>>11-25 funcionários</option>
                                <option value="26-50" <?php if ($lead['employees'] == '26-50') echo 'selected'; ?>>26-50 funcionários</option>
                                <option value="51-100" <?php if ($lead['employees'] == '51-100') echo 'selected'; ?>>51-100 funcionários</option>
                                <option value="100+" <?php if ($lead['employees'] == '100+') echo 'selected'; ?>>Mais de 100 funcionários</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data de Cadastro</label>
                            <input type="text" class="form-control" value="<?php echo date('d/m/Y H:i', strtotime($lead['created_at'])); ?>" readonly>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="privacy" name="privacy" <?php if ($lead['privacy']) echo 'checked'; ?>>
                                <label class="form-check-label" for="privacy">
                                    Aceitou a Política de Privacidade
                                </label>
                            </div>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Salvar Alterações
                            </button>
                            <a href="view_lead.php?id=<?php echo $lead['id']; ?>" class="btn btn-outline-secondary ms-2">
                                <i class="bi bi-x-circle me-1"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
