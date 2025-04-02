<?php
/**
 * Processamento do formulário de lead - Com salvamento no banco de dados
 */

// Habilitar exibição de erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Criar arquivo de log para depuração
$log_file = __DIR__ . '/lead_debug.log';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Início do processamento\n", FILE_APPEND);

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Método POST detectado\n", FILE_APPEND);
    
    // Registrar todos os dados recebidos
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Dados do formulário: " . print_r($_POST, true) . "\n", FILE_APPEND);
    
    // Verificar se é uma requisição AJAX
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    try {
        // Incluir arquivo de configuração
        require_once 'includes/config/config.php';
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Arquivo de configuração incluído\n", FILE_APPEND);
        
        // Verificar conexão com o banco de dados
        if (!isset($conn) || !$conn) {
            throw new Exception("Conexão com o banco de dados não estabelecida");
        }
        
        // Selecionar o banco de dados
        $conn->exec("CREATE DATABASE IF NOT EXISTS helpexpert");
        $conn->exec("USE helpexpert");
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Banco de dados selecionado\n", FILE_APPEND);
        
        // Verificar se a tabela leads existe
        $tableExists = $conn->query("SHOW TABLES LIKE 'leads'")->rowCount() > 0;
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Tabela leads existe: " . ($tableExists ? "Sim" : "Não") . "\n", FILE_APPEND);
        
        if (!$tableExists) {
            // Criar tabela de leads
            $sql = "CREATE TABLE IF NOT EXISTS leads (
                id INT(11) AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                phone VARCHAR(20),
                company VARCHAR(100),
                employees VARCHAR(20),
                privacy TINYINT(1) NOT NULL,
                status VARCHAR(20) DEFAULT 'novo',
                created_at DATETIME NOT NULL
            )";
            $conn->exec($sql);
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - Tabela leads criada\n", FILE_APPEND);
        }
        
        // Coletar dados do formulário
        $name = isset($_POST['lead_name']) ? $_POST['lead_name'] : '';
        $email = isset($_POST['lead_email']) ? $_POST['lead_email'] : '';
        $phone = isset($_POST['lead_phone']) ? $_POST['lead_phone'] : '';
        $company = isset($_POST['lead_company']) ? $_POST['lead_company'] : '';
        $employees = isset($_POST['lead_employees']) ? $_POST['lead_employees'] : '';
        $privacy = isset($_POST['lead_privacy']) ? 1 : 0;
        
        // Inserir dados no banco de dados
        $stmt = $conn->prepare("INSERT INTO leads (name, email, phone, company, employees, privacy, status, created_at) VALUES (:name, :email, :phone, :company, :employees, :privacy, 'novo', NOW())");
        
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':company', $company);
        $stmt->bindParam(':employees', $employees);
        $stmt->bindParam(':privacy', $privacy);
        
        $result = $stmt->execute();
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Inserção no banco de dados: " . ($result ? "Sucesso" : "Falha") . "\n", FILE_APPEND);
        
        if ($result) {
            $leadId = $conn->lastInsertId();
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - ID do lead inserido: " . $leadId . "\n", FILE_APPEND);
        }
    } catch (Exception $e) {
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Erro: " . $e->getMessage() . "\n", FILE_APPEND);
    }
    
    // Responder com sucesso (mesmo se houver erro no banco de dados, para não afetar a experiência do usuário)
    if ($isAjax) {
        // Resposta JSON para requisições AJAX
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Solicitação enviada com sucesso! Entraremos em contato em breve.',
            'redirect' => 'obrigado.html'
        ]);
    } else {
        // Redirecionamento para requisições normais
        header('Location: obrigado.html');
        exit;
    }
} else {
    // Se não for uma requisição POST, redirecionar para a página inicial
    header('Location: index.html');
    exit;
}
?>
