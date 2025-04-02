<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: login.php");
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php?tab=kanban");
    exit;
}

// Incluir arquivo de conexão com o banco de dados
require_once "../includes/db_connection.php";

// Verificar se os dados necessários foram fornecidos
if (!isset($_POST["lead_id"]) || !isset($_POST["status"])) {
    // Log para depuração
    error_log("Dados incompletos: lead_id=" . (isset($_POST["lead_id"]) ? $_POST["lead_id"] : "não definido") . 
              ", status=" . (isset($_POST["status"]) ? $_POST["status"] : "não definido"));
    
    $response = [
        'success' => false,
        'error' => 'Dados incompletos para atualização'
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$leadId = $_POST["lead_id"];
$status = $_POST["status"];

// Log para depuração
error_log("Atualizando lead ID: $leadId para status: $status");

// Obter todas as colunas do kanban para validar o status
try {
    $stmt = $conn->query("SELECT status_key FROM kanban_columns");
    $validStatuses = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Adicionar os status padrão caso a tabela não exista ou esteja vazia
    if (empty($validStatuses)) {
        $validStatuses = ['novo', 'contato', 'negociacao', 'fechado', 'perdido'];
    }
    
    error_log("Status válidos: " . implode(", ", $validStatuses));
} catch(PDOException $e) {
    // Se houver erro ao consultar a tabela, usar os status padrão
    $validStatuses = ['novo', 'contato', 'negociacao', 'fechado', 'perdido'];
    error_log("Erro ao obter status válidos: " . $e->getMessage() . ". Usando status padrão.");
}

// Validar o status
if (!in_array($status, $validStatuses)) {
    $response = [
        'success' => false,
        'error' => 'Status inválido: ' . $status . '. Status válidos: ' . implode(", ", $validStatuses)
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    // Atualizar o status do lead
    $stmt = $conn->prepare("UPDATE leads SET status = :status WHERE id = :id");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $leadId);
    $stmt->execute();
    
    // Log para depuração
    error_log("Lead atualizado com sucesso: $leadId para $status");
    
    // Verificar se a requisição é AJAX
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    // Responder com JSON independentemente do tipo de requisição
    $response = ['success' => true];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
    
} catch(PDOException $e) {
    // Log para depuração
    error_log("Erro ao atualizar lead: " . $e->getMessage());
    
    // Responder com JSON
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
