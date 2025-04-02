<?php
// Configurações do SMTP para envio de emails
$smtp_config = [
    'host' => 'sandbox.smtp.mailtrap.io',      // Servidor SMTP para testes
    'username' => 'f9a3f9a3f9a3f9',            // Usuário temporário para testes
    'password' => 'e1e1e1e1e1e1e1',            // Senha temporária para testes
    'port' => 2525,                             // Porta SMTP para o mailtrap
    'encryption' => 'tls',                      // Tipo de criptografia
    'from_email' => 'no-reply@helpexpert.com.br', // Email que aparecerá como remetente
    'from_name' => 'HelpExpert - Excelência em atendimento',     // Nome que aparecerá como remetente
    'reply_to' => 'comercial@helpexpert.com.br', // Email para resposta
    'admin_emails' => ['ceo@expertbrazil.com.br', 'comercial@helpexpert.com.br'], // Emails dos administradores para receber notificações
];

// Nota: Estas são credenciais temporárias para testes.
// Para produção, você deve substituir por suas credenciais reais.
// Você pode obter credenciais de teste gratuitas em https://mailtrap.io
?>
