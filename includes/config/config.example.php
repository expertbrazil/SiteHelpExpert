<?php
// Configurações de conexão com o banco de dados
$db_config = [
    'host' => 'localhost',      // Servidor do banco de dados
    'dbname' => 'helpexpert',   // Nome do banco de dados
    'username' => 'root',       // Usuário do banco de dados
    'password' => '',           // Senha do banco de dados
    'charset' => 'utf8mb4',     // Charset
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];

// Configurações do site
$site_config = [
    'name' => 'HelpExpert',
    'url' => 'https://helpexpert.com.br',
    'email' => 'contato@helpexpert.com.br',
    'phone' => '(11) 99999-9999',
    'address' => 'Av. Paulista, 1000 - São Paulo/SP',
    'debug' => false // Definir como true para exibir erros detalhados (apenas em ambiente de desenvolvimento)
];

// Configurações de logs
$log_config = [
    'enabled' => true,
    'path' => __DIR__ . '/logs/',
    'level' => 'debug' // debug, info, warning, error
];

// Não altere o código abaixo
try {
    // Criar conexão PDO
    $conn = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}",
        $db_config['username'],
        $db_config['password'],
        $db_config['options']
    );
} catch(PDOException $e) {
    // Em ambiente de produção, não exibir erros detalhados
    if ($site_config['debug']) {
        echo "Erro de conexão: " . $e->getMessage();
    } else {
        echo "Erro ao conectar ao banco de dados. Por favor, tente novamente mais tarde.";
    }
    
    // Registrar erro em log
    if ($log_config['enabled']) {
        if (!file_exists($log_config['path'])) {
            mkdir($log_config['path'], 0755, true);
        }
        error_log(date('Y-m-d H:i:s') . " - Erro de conexão: " . $e->getMessage() . "\n", 3, $log_config['path'] . 'db_errors.log');
    }
    
    die();
}

// Função para registrar logs
function logMessage($message, $level = 'info') {
    global $log_config;
    
    if ($log_config['enabled']) {
        $levels = ['debug', 'info', 'warning', 'error'];
        $log_level_index = array_search($log_config['level'], $levels);
        $message_level_index = array_search($level, $levels);
        
        // Só registra se o nível da mensagem for maior ou igual ao nível configurado
        if ($message_level_index >= $log_level_index) {
            if (!file_exists($log_config['path'])) {
                mkdir($log_config['path'], 0755, true);
            }
            
            $log_file = $log_config['path'] . 'app.log';
            $log_entry = date('Y-m-d H:i:s') . " - [$level] - $message\n";
            
            error_log($log_entry, 3, $log_file);
        }
    }
}
?>
