<?php
/**
 * Processamento do formulário de lead - Versão simplificada
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
    
    // Responder com sucesso
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
