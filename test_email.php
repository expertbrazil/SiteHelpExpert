<?php
// Arquivo para testar o envio de emails

// Incluir configurações SMTP
require_once 'includes/smtp_config.php';

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coletar dados do formulário
    $to = filter_input(INPUT_POST, 'to', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    
    // Validar email
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor, forneça um email válido.";
    } else {
        // Tentar enviar o email
        try {
            // Verificar se a biblioteca PHPMailer está instalada
            if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
                throw new Exception('A biblioteca PHPMailer não está instalada. Execute "composer require phpmailer/phpmailer" na raiz do projeto.');
            }
            
            // Incluir o autoloader do Composer
            require_once __DIR__ . '/vendor/autoload.php';
            
            // Criar instância do PHPMailer
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configurações do servidor
            $mail->SMTPDebug = 2; // Habilitar debug (0 = desativado, 1 = mensagens cliente, 2 = cliente e servidor)
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
            ob_start(); // Capturar saída para exibir no log
            $mail->send();
            $log = ob_get_clean();
            
            $success = "Email enviado com sucesso para $to!";
        } catch (Exception $e) {
            $error = "Erro ao enviar email: " . $mail->ErrorInfo;
            $log = ob_get_clean();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpExpert - Teste de Email</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    
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
            padding: 2rem 0;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
            color: var(--dark-gray);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .card-header {
            background-color: var(--primary);
            color: var(--white);
            border-radius: 10px 10px 0 0 !important;
            padding: 1.25rem 1.5rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background-color: #006080;
        }
        
        .form-control, .form-select {
            padding: 0.75rem 1rem;
            border: 1px solid var(--medium-gray);
            border-radius: 8px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(0, 116, 152, 0.25);
        }
        
        .log-container {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            max-height: 300px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 0.85rem;
            white-space: pre-wrap;
        }
        
        .config-info {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h2 class="mb-0">Teste de Envio de Email</h2>
                    </div>
                    <div class="card-body">
                        <div class="config-info">
                            <h5>Configurações SMTP Atuais:</h5>
                            <ul>
                                <li><strong>Servidor:</strong> <?php echo htmlspecialchars($smtp_config['host']); ?></li>
                                <li><strong>Usuário:</strong> <?php echo htmlspecialchars($smtp_config['username']); ?></li>
                                <li><strong>Porta:</strong> <?php echo htmlspecialchars($smtp_config['port']); ?></li>
                                <li><strong>Criptografia:</strong> <?php echo htmlspecialchars($smtp_config['encryption']); ?></li>
                                <li><strong>Email de Envio:</strong> <?php echo htmlspecialchars($smtp_config['from_email']); ?></li>
                                <li><strong>Nome de Envio:</strong> <?php echo htmlspecialchars($smtp_config['from_name']); ?></li>
                            </ul>
                            <p class="mb-0">Para alterar estas configurações, edite o arquivo <code>includes/smtp_config.php</code>.</p>
                        </div>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="mb-3">
                                <label for="to" class="form-label">Destinatário</label>
                                <input type="email" class="form-control" id="to" name="to" placeholder="Email para teste" required>
                                <div class="form-text">Email que receberá a mensagem de teste</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Assunto</label>
                                <input type="text" class="form-control" id="subject" name="subject" value="Teste de Email - HelpExpert" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Mensagem</label>
                                <textarea class="form-control" id="message" name="message" rows="4" required>Este é um email de teste enviado pelo sistema HelpExpert.

Se você está recebendo esta mensagem, a configuração SMTP está funcionando corretamente.

Atenciosamente,
Equipe HelpExpert</textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Enviar Email de Teste</button>
                        </form>
                        
                        <?php if (isset($log)): ?>
                            <div class="mt-4">
                                <h5>Log de Envio:</h5>
                                <div class="log-container">
                                    <?php echo $log; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Solução de Problemas</h3>
                    </div>
                    <div class="card-body">
                        <h5>Problemas Comuns:</h5>
                        <ul>
                            <li><strong>Erro de autenticação:</strong> Verifique se o usuário e senha estão corretos.</li>
                            <li><strong>Conexão recusada:</strong> Verifique se o servidor SMTP e a porta estão corretos.</li>
                            <li><strong>Gmail rejeitando login:</strong> Para Gmail, você precisa:
                                <ul>
                                    <li>Habilitar a verificação em duas etapas na sua conta Google</li>
                                    <li>Criar uma "Senha de App" específica para o aplicativo</li>
                                    <li>Usar essa senha no arquivo de configuração</li>
                                </ul>
                            </li>
                            <li><strong>Erro de SSL/TLS:</strong> Verifique se o tipo de criptografia está correto (tls ou ssl).</li>
                        </ul>
                        
                        <h5>Passos para Configuração:</h5>
                        <ol>
                            <li>Edite o arquivo <code>includes/smtp_config.php</code> com suas credenciais</li>
                            <li>Use este formulário para testar o envio</li>
                            <li>Verifique o log para identificar possíveis erros</li>
                            <li>Corrija as configurações conforme necessário</li>
                        </ol>
                        
                        <div class="mt-3">
                            <a href="index.html" class="btn btn-outline-primary">Voltar para o Site</a>
                            <a href="admin/login.php" class="btn btn-outline-secondary ms-2">Painel Administrativo</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
