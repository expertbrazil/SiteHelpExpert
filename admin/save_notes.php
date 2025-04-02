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
if (!isset($_POST["lead_id"]) || !isset($_POST["notes"])) {
    $response = [
        'success' => false,
        'error' => 'Dados incompletos para atualização'
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$leadId = $_POST["lead_id"];
$notes = $_POST["notes"];

try {
    // Atualizar as anotações do lead
    $stmt = $conn->prepare("UPDATE leads SET notes = :notes WHERE id = :id");
    $stmt->bindParam(':notes', $notes);
    $stmt->bindParam(':id', $leadId);
    $stmt->execute();
    
    // Responder com JSON
    $response = [
        'success' => true,
        'message' => 'Anotações salvas com sucesso'
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
    
} catch(PDOException $e) {
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
