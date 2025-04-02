<?php
// Habilitar exibição de erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir configurações
require_once 'config.php';
require_once 'includes/mailer.php';

echo "<h1>Teste de Envio de Email</h1>";

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coletar dados do formulário
    $to = filter_input(INPUT_POST, 'to', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    
    echo "<h3>Dados recebidos:</h3>";
    echo "<p><strong>Para:</strong> $to</p>";
    echo "<p><strong>Assunto:</strong> $subject</p>";
    echo "<p><strong>Mensagem:</strong> " . nl2br($message) . "</p>";
    
    // Verificar se a biblioteca PHPMailer está instalada
    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        echo "<p style='color: red;'>✗ A biblioteca PHPMailer não está instalada. Execute 'composer require phpmailer/phpmailer' na raiz do projeto.</p>";
        exit;
    }
    
    // Incluir o autoloader do Composer
    require_once __DIR__ . '/vendor/autoload.php';
    
    // Verificar se a classe PHPMailer existe
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "<p style='color: red;'>✗ Classe PHPMailer não encontrada. Execute 'composer require phpmailer/phpmailer' na raiz do projeto.</p>";
        exit;
    }
    
    // Criar instância do PHPMailer com debug habilitado
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->SMTPDebug = 2; // Habilitar debug (0 = desativado, 1 = mensagens cliente, 2 = cliente e servidor)
    
    try {
        echo "<h3>Tentando enviar email:</h3>";
        echo "<pre>";
        
        // Configurações do servidor
        $mail->isSMTP();
        $mail->Host = $smtp_config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_config['username'];
        $mail->Password = $smtp_config['password'];
        $mail->SMTPSecure = $smtp_config['encryption'];
        $mail->Port = $smtp_config['port'];
        $mail->CharSet = 'UTF-8';
        
        // Remetente
        $mail->setFrom($smtp_config['from_email'], $smtp_config['from_name']);
        $mail->addReplyTo($smtp_config['reply_to'], $smtp_config['from_name']);
        
        // Destinatário
        $mail->addAddress($to);
        
        // Conteúdo
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = nl2br($message);
        $mail->AltBody = strip_tags($message);
        
        // Enviar email
        $mail->send();
        echo "</pre>";
        
        echo "<p style='color: green;'>✓ Email enviado com sucesso para $to!</p>";
    } catch (Exception $e) {
        echo "</pre>";
        echo "<p style='color: red;'>✗ Erro ao enviar email: " . $mail->ErrorInfo . "</p>";
    }
} else {
    echo "<p>Nenhum dado de formulário recebido.</p>";
}

echo "<p><a href='debug_form.php' style='display: inline-block; padding: 10px 15px; background-color: #007498; color: white; text-decoration: none;'>Voltar para o formulário de depuração</a></p>";
?>
