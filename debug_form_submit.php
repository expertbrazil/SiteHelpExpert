<?php
// Habilitar exibição de erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir configurações
require_once 'config.php';
require_once 'includes/mailer.php';

echo "<h1>Teste de Envio de Formulário</h1>";

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_type = $_POST['form_type'] ?? '';
    
    if ($form_type == 'lead') {
        // Coletar dados do formulário de lead
        $name = filter_input(INPUT_POST, 'lead_name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'lead_email', FILTER_SANITIZE_EMAIL);
        $phone = filter_input(INPUT_POST, 'lead_phone', FILTER_SANITIZE_STRING);
        $company = filter_input(INPUT_POST, 'lead_company', FILTER_SANITIZE_STRING);
        $employees = filter_input(INPUT_POST, 'lead_employees', FILTER_SANITIZE_STRING);
        $privacy = isset($_POST['lead_privacy']) ? 1 : 0;
        
        echo "<h3>Dados do lead recebidos:</h3>";
        echo "<p><strong>Nome:</strong> $name</p>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>Telefone:</strong> $phone</p>";
        echo "<p><strong>Empresa:</strong> $company</p>";
        echo "<p><strong>Funcionários:</strong> $employees</p>";
        echo "<p><strong>Privacidade:</strong> " . ($privacy ? 'Aceito' : 'Não aceito') . "</p>";
        
        try {
            echo "<h3>Tentando inserir no banco de dados:</h3>";
            
            // Selecionar o banco de dados
            $conn->exec("USE helpexpert");
            
            // Verificar se a tabela leads existe
            $tableExists = $conn->query("SHOW TABLES LIKE 'leads'")->rowCount() > 0;
            if (!$tableExists) {
                echo "<p style='color: red;'>✗ A tabela 'leads' não existe. Execute o script db_setup.php primeiro.</p>";
                exit;
            }
            
            // Preparar e executar a inserção no banco de dados
            $stmt = $conn->prepare("INSERT INTO leads (name, email, phone, company, employees, privacy, created_at) VALUES (:name, :email, :phone, :company, :employees, :privacy, NOW())");
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':company', $company);
            $stmt->bindParam(':employees', $employees);
            $stmt->bindParam(':privacy', $privacy);
            
            $stmt->execute();
            $leadId = $conn->lastInsertId();
            
            echo "<p style='color: green;'>✓ Lead inserido com sucesso no banco de dados. ID: $leadId</p>";
            
            // Buscar o lead completo do banco de dados
            $stmt = $conn->prepare("SELECT * FROM leads WHERE id = :id");
            $stmt->bindParam(':id', $leadId);
            $stmt->execute();
            $lead = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<h3>Tentando enviar emails:</h3>";
            
            // Enviar notificação para o administrador
            echo "<h4>Email para o administrador:</h4>";
            try {
                $adminNotification = sendLeadNotification($lead);
                if ($adminNotification['success']) {
                    echo "<p style='color: green;'>✓ Notificação enviada para o administrador com sucesso.</p>";
                } else {
                    echo "<p style='color: red;'>✗ Erro ao enviar notificação para o administrador: " . $adminNotification['message'] . "</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>✗ Exceção ao enviar notificação para o administrador: " . $e->getMessage() . "</p>";
            }
            
            // Enviar confirmação para o cliente
            echo "<h4>Email para o cliente:</h4>";
            try {
                $clientConfirmation = sendLeadConfirmation($lead);
                if ($clientConfirmation['success']) {
                    echo "<p style='color: green;'>✓ Confirmação enviada para o cliente com sucesso.</p>";
                } else {
                    echo "<p style='color: red;'>✗ Erro ao enviar confirmação para o cliente: " . $clientConfirmation['message'] . "</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>✗ Exceção ao enviar confirmação para o cliente: " . $e->getMessage() . "</p>";
            }
            
            echo "<p style='color: green;'>✓ Processo de lead concluído com sucesso!</p>";
            
            // Link para a página de agradecimento
            echo "<p><a href='obrigado.html' style='display: inline-block; padding: 10px 15px; background-color: #007498; color: white; text-decoration: none;'>Ir para página de agradecimento</a></p>";
            
        } catch(PDOException $e) {
            echo "<p style='color: red;'>✗ Erro no banco de dados: " . $e->getMessage() . "</p>";
        } catch(Exception $e) {
            echo "<p style='color: red;'>✗ Erro geral: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Tipo de formulário desconhecido.</p>";
    }
} else {
    echo "<p>Nenhum dado de formulário recebido.</p>";
}

echo "<p><a href='debug_form.php' style='display: inline-block; padding: 10px 15px; background-color: #007498; color: white; text-decoration: none;'>Voltar para o formulário de depuração</a></p>";
?>
