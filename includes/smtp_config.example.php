<?php
// Configurações do SMTP para envio de emails
$smtp_config = [
    'host' => 'smtp.gmail.com',      // Servidor SMTP (exemplo: smtp.gmail.com, smtp.office365.com)
    'username' => 'seu-email@gmail.com', // Seu email
    'password' => 'sua-senha-de-app', // Sua senha ou senha de aplicativo
    'port' => 587,                   // Porta SMTP (geralmente 587 para TLS ou 465 para SSL)
    'encryption' => 'tls',           // Tipo de criptografia (tls ou ssl)
    'from_email' => 'seu-email@gmail.com', // Email que aparecerá como remetente
    'from_name' => 'HelpExpert - Excelência em atendimento, soluções inteligentes.',     // Nome que aparecerá como remetente
    'reply_to' => 'seu-email@gmail.com', // Email para resposta
    'admin_emails' => ['seu-email@gmail.com'], // Emails dos administradores para receber notificações
];

// Instruções para configurar o SMTP:
// 1. Substitua 'seu-email@gmail.com' pelo seu email real
// 2. Substitua 'sua-senha-de-app' pela sua senha ou senha de aplicativo
// 3. Se estiver usando Gmail, você precisa criar uma senha de aplicativo:
//    - Acesse https://myaccount.google.com/security
//    - Ative a verificação em duas etapas
//    - Acesse "Senhas de app" e crie uma nova senha para o aplicativo
// 4. Ajuste as outras configurações conforme necessário
?>
