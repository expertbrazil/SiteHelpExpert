<?php
// Incluir configuração SMTP
require_once 'smtp_config.php';

// Função para enviar email usando PHPMailer
function sendEmail($to, $subject, $message, $attachments = []) {
    global $smtp_config;
    
    // Verificar se a biblioteca PHPMailer está instalada
    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        // Se não estiver instalada, retornar erro
        return [
            'success' => false,
            'message' => 'A biblioteca PHPMailer não está instalada. Execute "composer require phpmailer/phpmailer" na raiz do projeto.'
        ];
    }
    
    // Incluir o autoloader do Composer
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Criar instância do PHPMailer
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Configurações do servidor
        $mail->SMTPDebug = 0; // Desativar debug (0 = desativado, 1 = mensagens cliente, 2 = mensagens cliente e servidor)
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
        if (is_array($to)) {
            foreach ($to as $email) {
                $mail->addAddress($email);
            }
        } else {
            $mail->addAddress($to);
        }
        
        // Anexos
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $mail->addAttachment($attachment);
            }
        }
        
        // Conteúdo
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $message));
        
        // Registrar informações de envio para depuração
        error_log("Tentando enviar e-mail para: " . (is_array($to) ? implode(', ', $to) : $to));
        error_log("Assunto: " . $subject);
        
        // Enviar email
        $result = $mail->send();
        
        if ($result) {
            error_log("E-mail enviado com sucesso para: " . (is_array($to) ? implode(', ', $to) : $to));
            return [
                'success' => true,
                'message' => 'Email enviado com sucesso!'
            ];
        } else {
            error_log("Falha no envio do e-mail: " . $mail->ErrorInfo);
            return [
                'success' => false,
                'message' => 'Erro ao enviar email: ' . $mail->ErrorInfo
            ];
        }
    } catch (Exception $e) {
        error_log("Exceção ao enviar e-mail: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erro ao enviar email: ' . $e->getMessage()
        ];
    }
}

// Função para enviar notificação de novo contato
function sendContactNotification($contact) {
    global $smtp_config;
    
    $subject = 'Novo Contato - HelpExpert';
    
    $message = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #007498; color: white; padding: 15px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .footer { text-align: center; padding: 10px; font-size: 12px; color: #777; }
            h1 { color: #007498; }
            table { width: 100%; border-collapse: collapse; }
            table td { padding: 8px; border-bottom: 1px solid #ddd; }
            table th { text-align: left; padding: 8px; border-bottom: 2px solid #ddd; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>Novo Contato Recebido</h2>
            </div>
            <div class="content">
                <p>Olá,</p>
                <p>Um novo contato foi recebido através do formulário do site HelpExpert.</p>
                
                <h3>Detalhes do Contato:</h3>
                <table>
                    <tr>
                        <th>Nome:</th>
                        <td>' . htmlspecialchars($contact['name']) . '</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>' . htmlspecialchars($contact['email']) . '</td>
                    </tr>
                    <tr>
                        <th>Telefone:</th>
                        <td>' . htmlspecialchars($contact['phone']) . '</td>
                    </tr>
                    <tr>
                        <th>Assunto:</th>
                        <td>' . htmlspecialchars($contact['subject']) . '</td>
                    </tr>
                    <tr>
                        <th>Mensagem:</th>
                        <td>' . nl2br(htmlspecialchars($contact['message'])) . '</td>
                    </tr>
                    <tr>
                        <th>Data:</th>
                        <td>' . date('d/m/Y H:i', strtotime($contact['created_at'])) . '</td>
                    </tr>
                </table>
                
                <p>Acesse o painel administrativo para mais detalhes.</p>
                <p><a href="http://seusite.com.br/admin" style="background-color: #007498; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;">Acessar Painel</a></p>
            </div>
            <div class="footer">
                <p> HelpExpert. Todos os direitos reservados.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    return sendEmail($smtp_config['admin_emails'], $subject, $message);
}

// Função para enviar notificação de novo lead
function sendLeadNotification($lead) {
    global $smtp_config;
    
    // Verificar se os emails dos administradores estão definidos
    if (empty($smtp_config['admin_emails']) || !is_array($smtp_config['admin_emails'])) {
        error_log("Emails dos administradores não configurados corretamente em smtp_config.php");
        return [
            'success' => false,
            'message' => 'Emails dos administradores não configurados corretamente'
        ];
    }
    
    $subject = 'Novo Lead - HelpExpert';
    
    $message = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #007498; color: white; padding: 15px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .footer { text-align: center; padding: 10px; font-size: 12px; color: #777; }
            h1 { color: #007498; }
            table { width: 100%; border-collapse: collapse; }
            table td { padding: 8px; border-bottom: 1px solid #ddd; }
            table th { text-align: left; padding: 8px; border-bottom: 2px solid #ddd; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>Novo Lead Recebido</h2>
            </div>
            <div class="content">
                <p>Olá,</p>
                <p>Um novo lead foi recebido através do formulário de demonstração do site HelpExpert.</p>
                
                <h3>Detalhes do Lead:</h3>
                <table>
                    <tr>
                        <th>Nome:</th>
                        <td>' . htmlspecialchars($lead['name']) . '</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>' . htmlspecialchars($lead['email']) . '</td>
                    </tr>
                    <tr>
                        <th>Telefone:</th>
                        <td>' . htmlspecialchars($lead['phone']) . '</td>
                    </tr>
                    <tr>
                        <th>Empresa:</th>
                        <td>' . htmlspecialchars($lead['company']) . '</td>
                    </tr>
                    <tr>
                        <th>Funcionários:</th>
                        <td>' . htmlspecialchars($lead['employees']) . '</td>
                    </tr>
                    <tr>
                        <th>Data:</th>
                        <td>' . date('d/m/Y H:i', strtotime($lead['created_at'])) . '</td>
                    </tr>
                </table>
                
                <p>Acesse o painel administrativo para mais detalhes.</p>
                <p><a href="http://seusite.com.br/admin" style="background-color: #007498; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;">Acessar Painel</a></p>
            </div>
            <div class="footer">
                <p> HelpExpert. Todos os direitos reservados.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // Registrar tentativa de envio
    error_log("Tentando enviar notificação de lead para administradores: " . implode(', ', $smtp_config['admin_emails']));
    
    // Enviar email para todos os administradores
    $result = sendEmail($smtp_config['admin_emails'], $subject, $message);
    
    // Registrar resultado
    error_log("Resultado do envio para administradores: " . ($result['success'] ? 'Sucesso' : 'Falha: ' . $result['message']));
    
    return $result;
}

// Função para enviar confirmação ao cliente que enviou contato
function sendContactConfirmation($contact) {
    $subject = 'Contato Recebido - HelpExpert';
    
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Contato Recebido - HelpExpert</title>
        <style>
            @import url("https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&family=Nunito:wght@300;400;600;700&display=swap");
            
            body { 
                font-family: "Nunito", Arial, sans-serif; 
                line-height: 1.6; 
                color: #3A4A5A; 
                margin: 0;
                padding: 0;
                background-color: #F5F7F9;
            }
            
            .email-container { 
                max-width: 600px; 
                margin: 20px auto; 
                padding: 0;
                background-color: #FFFFFF;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }
            
            .email-header { 
                background: linear-gradient(135deg, #007498, #00aba5);
                color: white; 
                padding: 30px;
                text-align: center; 
                font-family: "Archivo", Arial, sans-serif;
                font-weight: 600;
            }
            
            .content { 
                padding: 30px; 
                background-color: #FFFFFF; 
            }
            
            .info-box {
                background-color: #F5F7F9;
                border-radius: 8px;
                padding: 20px;
                margin: 20px 0;
                border-left: 4px solid #007498;
            }
            
            .info-item {
                margin-bottom: 10px;
            }
            
            .info-label {
                font-weight: 600;
                color: #3A4A5A;
            }
            
            .info-value {
                color: #7A8A9A;
            }
            
            .btn {
                display: inline-block;
                background-color: #007498;
                color: white;
                text-decoration: none;
                padding: 12px 24px;
                border-radius: 6px;
                font-family: "Archivo", Arial, sans-serif;
                font-weight: 600;
                margin: 15px 0;
                text-align: center;
                transition: background-color 0.3s ease;
            }
            
            .btn:hover {
                background-color: #006080;
            }
            
            .footer { 
                text-align: center; 
                padding: 20px; 
                font-size: 13px; 
                color: #7A8A9A; 
                background-color: #F5F7F9;
                border-top: 1px solid #E0E5E9;
            }
            
            .social-links {
                margin: 15px 0;
            }
            
            .social-link {
                display: inline-block;
                margin: 0 5px;
            }
            
            p {
                margin: 0 0 15px;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h2>Contato Recebido</h2>
            </div>
            <div class="content">
                <p>Olá ' . htmlspecialchars($contact['name']) . ',</p>
                <p>Obrigado por entrar em contato com a equipe HelpExpert. Recebemos sua mensagem e retornaremos o mais breve possível.</p>
                
                <div class="info-box">
                    <h3 style="margin-top: 0; font-family: Archivo, Arial, sans-serif; color: #007498;">Resumo do seu contato:</h3>
                    <div class="info-item">
                        <span class="info-label">Assunto:</span> 
                        <span class="info-value">' . htmlspecialchars($contact['subject']) . '</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Data:</span> 
                        <span class="info-value">' . date('d/m/Y H:i', strtotime($contact['created_at'])) . '</span>
                    </div>
                </div>
                
                <p>Enquanto isso, você pode conhecer mais sobre o HelpExpert visitando nosso site e blog.</p>
                
                <a href="https://helpexpert.com.br" class="btn">Visitar o Site</a>
                
                <p>Atenciosamente,<br>Equipe HelpExpert</p>
            </div>
            <div class="footer">
                <div class="social-links">
                    <a href="#" class="social-link">
                        <img src="https://cdn-icons-png.flaticon.com/128/733/733547.png" alt="Facebook" width="24" height="24">
                    </a>
                    <a href="#" class="social-link">
                        <img src="https://cdn-icons-png.flaticon.com/128/2111/2111463.png" alt="Instagram" width="24" height="24">
                    </a>
                    <a href="#" class="social-link">
                        <img src="https://cdn-icons-png.flaticon.com/128/3536/3536505.png" alt="LinkedIn" width="24" height="24">
                    </a>
                </div>
                <p> HelpExpert. Todos os direitos reservados.</p>
                <p>Este é um email automático, por favor não responda.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    return sendEmail($contact['email'], $subject, $message);
}

// Função para enviar confirmação ao cliente que solicitou demonstração
function sendLeadConfirmation($lead) {
    $subject = 'Solicitação de Demonstração - HelpExpert';
    
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Solicitação de Demonstração - HelpExpert</title>
        <style>
            @import url("https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&family=Nunito:wght@300;400;600;700&display=swap");
            
            body { 
                font-family: "Nunito", Arial, sans-serif; 
                line-height: 1.6; 
                color: #3A4A5A; 
                margin: 0;
                padding: 0;
                background-color: #F5F7F9;
            }
            
            .email-container { 
                max-width: 600px; 
                margin: 20px auto; 
                padding: 0;
                background-color: #FFFFFF;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }
            
            .email-header { 
                background: linear-gradient(135deg, #007498, #00aba5);
                color: white; 
                padding: 30px;
                text-align: center; 
                font-family: "Archivo", Arial, sans-serif;
                font-weight: 600;
            }
            
            .content { 
                padding: 30px; 
                background-color: #FFFFFF; 
            }
            
            .info-box {
                background-color: #F5F7F9;
                border-radius: 8px;
                padding: 20px;
                margin: 20px 0;
                border-left: 4px solid #007498;
            }
            
            .info-item {
                margin-bottom: 10px;
            }
            
            .info-label {
                font-weight: 600;
                color: #3A4A5A;
            }
            
            .info-value {
                color: #7A8A9A;
            }
            
            .btn {
                display: inline-block;
                background-color: #007498;
                color: white;
                text-decoration: none;
                padding: 12px 24px;
                border-radius: 6px;
                font-family: "Archivo", Arial, sans-serif;
                font-weight: 600;
                margin: 15px 0;
                text-align: center;
                transition: background-color 0.3s ease;
            }
            
            .btn:hover {
                background-color: #006080;
            }
            
            .footer { 
                text-align: center; 
                padding: 20px; 
                font-size: 13px; 
                color: #7A8A9A; 
                background-color: #F5F7F9;
                border-top: 1px solid #E0E5E9;
            }
            
            .social-links {
                margin: 15px 0;
            }
            
            .social-link {
                display: inline-block;
                margin: 0 5px;
            }
            
            p {
                margin: 0 0 15px;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h2>Solicitação de Demonstração</h2>
            </div>
            <div class="content">
                <p>Olá ' . htmlspecialchars($lead['name']) . ',</p>
                <p>Obrigado por solicitar uma demonstração do HelpExpert. Nossa equipe entrará em contato em breve para agendar a melhor data e horário para a apresentação.</p>
                
                <div class="info-box">
                    <h3 style="margin-top: 0; font-family: Archivo, Arial, sans-serif; color: #007498;">Resumo da sua solicitação:</h3>
                    <div class="info-item">
                        <span class="info-label">Empresa:</span> 
                        <span class="info-value">' . htmlspecialchars($lead['company']) . '</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Porte da empresa:</span> 
                        <span class="info-value">' . htmlspecialchars($lead['employees']) . ' funcionários</span>
                    </div>
                </div>
                
                <p>Enquanto isso, você pode conhecer mais sobre o HelpExpert visitando nosso site e blog.</p>
                
                <a href="https://helpexpert.com.br" class="btn">Visitar o Site</a>
                
                <p>Atenciosamente,<br>Equipe HelpExpert</p>
            </div>
            <div class="footer">
                <div class="social-links">
                    <a href="#" class="social-link">
                        <img src="https://cdn-icons-png.flaticon.com/128/733/733547.png" alt="Facebook" width="24" height="24">
                    </a>
                    <a href="#" class="social-link">
                        <img src="https://cdn-icons-png.flaticon.com/128/2111/2111463.png" alt="Instagram" width="24" height="24">
                    </a>
                    <a href="#" class="social-link">
                        <img src="https://cdn-icons-png.flaticon.com/128/3536/3536505.png" alt="LinkedIn" width="24" height="24">
                    </a>
                </div>
                <p> HelpExpert. Todos os direitos reservados.</p>
                <p>Este é um email automático, por favor não responda.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    return sendEmail($lead['email'], $subject, $message);
}
?>
