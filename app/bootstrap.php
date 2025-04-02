<?php
/**
 * Bootstrap da Aplicação
 * 
 * Arquivo de inicialização que carrega configurações e dependências
 */

// Definir constantes do sistema
define('APP_ROOT', dirname(__DIR__));
define('APP_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/LP_HelpExpert');

// Iniciar sessão
session_start();

// Carregar configuração do banco de dados
$db_config = require_once APP_ROOT . '/app/config/database.php';

// Estabelecer conexão com o banco de dados
try {
    $conn = new PDO(
        "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['dbname']};charset={$db_config['charset']}",
        $db_config['username'],
        $db_config['password']
    );
    
    // Configurar o PDO para lançar exceções em caso de erro
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configurar o PDO para retornar resultados como arrays associativos por padrão
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Configurar o PDO para emular prepared statements apenas se necessário
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
} catch(PDOException $e) {
    // Em caso de erro, registrar em log e exibir mensagem amigável
    error_log("Erro na conexão com o banco de dados: " . $e->getMessage());
    
    // Verificar se a requisição é AJAX
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Erro na conexão com o banco de dados']);
        exit;
    } else {
        echo "Ocorreu um erro na conexão com o banco de dados. Por favor, tente novamente mais tarde.";
        exit;
    }
}

// Carregar helpers
require_once APP_ROOT . '/app/helpers/auth_helper.php';

// Carregar modelos
require_once APP_ROOT . '/app/models/User.php';

// Inicializar modelos
$userModel = new User($conn);

// Função para exibir mensagens de alerta
function set_alert($type, $message) {
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Função para obter e limpar mensagens de alerta
function get_alert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}
?>
