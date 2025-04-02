<?php
// Habilitar exibição de erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Criar arquivo de log para depuração
$log_file = __DIR__ . '/debug_log.txt';
function debug_log($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

debug_log("Iniciando processamento do formulário de contato");

require_once 'config.php';
require_once 'includes/mailer.php';

// Verificar se o banco de dados existe e criar se necessário
try {
    debug_log("Verificando banco de dados");
    $conn->exec("CREATE DATABASE IF NOT EXISTS helpexpert");
    $conn->exec("USE helpexpert");
    debug_log("Banco de dados selecionado: helpexpert");
    
    // Verificar se a tabela contacts existe
    $tableExists = $conn->query("SHOW TABLES LIKE 'contacts'")->rowCount() > 0;
    debug_log("Tabela contacts existe: " . ($tableExists ? "Sim" : "Não"));
    
    if (!$tableExists) {
        // Criar tabela de contatos
        $sql = "CREATE TABLE IF NOT EXISTS contacts (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            subject VARCHAR(50),
            message TEXT NOT NULL,
            privacy TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL
        )";
        $conn->exec($sql);
        debug_log("Tabela contacts criada");
    }
} catch(PDOException $e) {
    // Registrar erro em log
    debug_log("Erro ao configurar banco de dados: " . $e->getMessage());
    error_log("Erro ao configurar banco de dados: " . $e->getMessage());
}

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    debug_log("Método POST detectado");
    
    // Verificar se é uma requisição AJAX ou um envio de formulário normal
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    debug_log("É requisição AJAX: " . ($isAjax ? "Sim" : "Não"));
    
    // Coletar dados do formulário
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    $privacy = isset($_POST['privacy']) ? 1 : 0;
    
    // Log dos dados recebidos para depuração
    debug_log("Dados do formulário contato: " . json_encode([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'subject' => $subject,
        'message' => substr($message, 0, 50) . (strlen($message) > 50 ? '...' : ''),
        'privacy' => $privacy
    ]));
    
    // Validar dados
    if (empty($name) || empty($email) || empty($message)) {
        debug_log("Validação falhou: campos obrigatórios não preenchidos");
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => 'Por favor, preencha todos os campos obrigatórios.']);
        } else {
            header('Location: index.html?error=campos_obrigatorios');
        }
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        debug_log("Validação falhou: email inválido");
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => 'Por favor, forneça um email válido.']);
        } else {
            header('Location: index.html?error=email_invalido');
        }
        exit;
    }
    
    if (!$privacy) {
        debug_log("Validação falhou: política de privacidade não aceita");
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => 'Você precisa concordar com a política de privacidade.']);
        } else {
            header('Location: index.html?error=privacidade');
        }
        exit;
    }
    
    try {
        debug_log("Iniciando inserção no banco de dados");
        // Preparar e executar a inserção no banco de dados
        $stmt = $conn->prepare("INSERT INTO contacts (name, email, phone, subject, message, privacy, created_at) VALUES (:name, :email, :phone, :subject, :message, :privacy, NOW())");
        
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':privacy', $privacy);
        
        $result = $stmt->execute();
        debug_log("Resultado da inserção: " . ($result ? "Sucesso" : "Falha"));
        
        $contactId = $conn->lastInsertId();
        debug_log("ID do contato inserido: " . $contactId);
        
        // Buscar o contato completo do banco de dados
        $stmt = $conn->prepare("SELECT * FROM contacts WHERE id = :id");
        $stmt->bindParam(':id', $contactId);
        $stmt->execute();
        $contact = $stmt->fetch(PDO::FETCH_ASSOC);
        debug_log("Contato recuperado do banco: " . ($contact ? "Sim" : "Não"));
        
        // Enviar notificação para o administrador
        debug_log("Tentando enviar notificação para o administrador");
        $adminNotification = sendContactNotification($contact);
        debug_log("Resultado do envio para admin: " . json_encode($adminNotification));
        
        // Enviar confirmação para o cliente
        debug_log("Tentando enviar confirmação para o cliente");
        $clientConfirmation = sendContactConfirmation($contact);
        debug_log("Resultado do envio para cliente: " . json_encode($clientConfirmation));
        
        // Verificar se houve erro no envio de emails
        $emailError = '';
        if (!$adminNotification['success']) {
            $emailError .= 'Aviso: ' . $adminNotification['message'] . ' ';
            debug_log("Erro ao enviar notificação admin: " . $adminNotification['message']);
        }
        if (!$clientConfirmation['success']) {
            $emailError .= 'Aviso: ' . $clientConfirmation['message'];
            debug_log("Erro ao enviar confirmação cliente: " . $clientConfirmation['message']);
        }
        
        if ($isAjax) {
            // Certifique-se de que nenhum conteúdo foi enviado antes
            if (ob_get_length()) ob_clean();
            
            // Definir cabeçalhos para evitar cache
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Content-Type: application/json');
            
            // Enviar resposta JSON
            $response = [
                'success' => true, 
                'message' => 'Mensagem enviada com sucesso! Entraremos em contato em breve.' . ($emailError ? ' ' . $emailError : ''),
                'redirect' => 'obrigado.html'
            ];
            debug_log("Enviando resposta AJAX: " . json_encode($response));
            echo json_encode($response);
        } else {
            // Certifique-se de que nenhum conteúdo foi enviado antes
            if (ob_get_length()) ob_clean();
            
            debug_log("Redirecionando para página de agradecimento");
            // Redirecionar para a página de agradecimento
            header('Location: obrigado.html');
            exit;
        }
    } catch(PDOException $e) {
        debug_log("Erro PDO: " . $e->getMessage());
        error_log("Erro PDO: " . $e->getMessage());
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => 'Erro ao enviar mensagem: ' . $e->getMessage()]);
        } else {
            header('Location: index.html?error=database&msg=' . urlencode($e->getMessage()));
        }
    } catch(Exception $e) {
        debug_log("Erro geral: " . $e->getMessage());
        error_log("Erro geral: " . $e->getMessage());
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => 'Erro ao processar mensagem: ' . $e->getMessage()]);
        } else {
            header('Location: index.html?error=geral&msg=' . urlencode($e->getMessage()));
        }
    }
} else {
    debug_log("Método não é POST, redirecionando para index.html");
    // Se não for uma requisição POST, redirecionar para a página inicial
    header('Location: index.html');
    exit;
}
?>
