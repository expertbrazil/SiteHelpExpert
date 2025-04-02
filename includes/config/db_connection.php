<?php
// Configurações do banco de dados
$host = 'localhost';
$dbname = 'helpexpert';
$username = 'root';
$password = '';
$port = '3306'; // Porta padrão do MySQL

// Criar conexão diretamente com o banco de dados
try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    // Configurar o PDO para lançar exceções em caso de erro
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Configurar o charset para utf8
    $conn->exec("SET NAMES utf8");
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
?>
