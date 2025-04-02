<?php
// Habilitar exibição de erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir configurações
require_once 'config.php';
require_once 'includes/mailer.php';

// Verificar conexão com o banco de dados
echo "<h3>Verificando conexão com o banco de dados:</h3>";
try {
    // Tentar selecionar o banco de dados
    $conn->exec("USE helpexpert");
    echo "<p style='color: green;'>✓ Conexão com o banco de dados estabelecida com sucesso.</p>";
    
    // Verificar se as tabelas existem
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tabelas encontradas: " . implode(", ", $tables) . "</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Erro na conexão com o banco de dados: " . $e->getMessage() . "</p>";
}

// Verificar configuração SMTP
echo "<h3>Verificando configuração SMTP:</h3>";
echo "<pre>";
print_r($smtp_config);
echo "</pre>";

// Verificar se o PHPMailer está instalado
echo "<h3>Verificando instalação do PHPMailer:</h3>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "<p style='color: green;'>✓ Composer autoload encontrado.</p>";
    
    // Incluir o autoloader do Composer
    require_once __DIR__ . '/vendor/autoload.php';
    
    // Verificar se a classe PHPMailer existe
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "<p style='color: green;'>✓ Classe PHPMailer encontrada.</p>";
    } else {
        echo "<p style='color: red;'>✗ Classe PHPMailer não encontrada. Execute 'composer require phpmailer/phpmailer' na raiz do projeto.</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Composer autoload não encontrado. Execute 'composer require phpmailer/phpmailer' na raiz do projeto.</p>";
}

// Testar envio de email
echo "<h3>Teste de envio de email:</h3>";
?>

<form method="post" action="debug_send_email.php">
    <div style="margin-bottom: 15px;">
        <label for="to" style="display: block; margin-bottom: 5px;">Email de destino:</label>
        <input type="email" id="to" name="to" style="width: 300px; padding: 8px;" required>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="subject" style="display: block; margin-bottom: 5px;">Assunto:</label>
        <input type="text" id="subject" name="subject" value="Teste de Email - Debug" style="width: 300px; padding: 8px;" required>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="message" style="display: block; margin-bottom: 5px;">Mensagem:</label>
        <textarea id="message" name="message" style="width: 300px; height: 100px; padding: 8px;" required>Este é um email de teste para depuração.</textarea>
    </div>
    
    <button type="submit" style="padding: 10px 15px; background-color: #007498; color: white; border: none; cursor: pointer;">Enviar Email de Teste</button>
</form>

<h3>Formulário de teste (Lead):</h3>
<form method="post" action="debug_form_submit.php">
    <input type="hidden" name="form_type" value="lead">
    
    <div style="margin-bottom: 15px;">
        <label for="lead_name" style="display: block; margin-bottom: 5px;">Nome:</label>
        <input type="text" id="lead_name" name="lead_name" style="width: 300px; padding: 8px;" required>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="lead_email" style="display: block; margin-bottom: 5px;">Email:</label>
        <input type="email" id="lead_email" name="lead_email" style="width: 300px; padding: 8px;" required>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="lead_phone" style="display: block; margin-bottom: 5px;">Telefone:</label>
        <input type="tel" id="lead_phone" name="lead_phone" style="width: 300px; padding: 8px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="lead_company" style="display: block; margin-bottom: 5px;">Empresa:</label>
        <input type="text" id="lead_company" name="lead_company" style="width: 300px; padding: 8px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="lead_employees" style="display: block; margin-bottom: 5px;">Número de funcionários:</label>
        <select id="lead_employees" name="lead_employees" style="width: 300px; padding: 8px;">
            <option value="1-5">1-5</option>
            <option value="6-20">6-20</option>
            <option value="21-50">21-50</option>
            <option value="51-100">51-100</option>
            <option value="101+">Mais de 100</option>
        </select>
    </div>
    
    <div style="margin-bottom: 15px;">
        <label style="display: flex; align-items: center;">
            <input type="checkbox" name="lead_privacy" value="1" required style="margin-right: 8px;">
            Concordo com a Política de Privacidade
        </label>
    </div>
    
    <button type="submit" style="padding: 10px 15px; background-color: #007498; color: white; border: none; cursor: pointer;">Enviar Lead de Teste</button>
</form>
