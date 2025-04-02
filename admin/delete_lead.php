<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: login.php");
    exit;
}

// Incluir arquivo de configuração
require_once "../includes/config/config.php";

// Selecionar o banco de dados
$conn->exec("USE helpexpert");

// Verificar se o ID foi fornecido
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: index.php?tab=leads");
    exit;
}

$id = $_GET["id"];

try {
    // Excluir o lead do banco de dados
    $stmt = $conn->prepare("DELETE FROM leads WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    // Redirecionar para a página de leads com mensagem de sucesso
    header("Location: index.php?tab=leads&deleted=1");
    exit;
} catch(PDOException $e) {
    // Redirecionar para a página de leads com mensagem de erro
    header("Location: index.php?tab=leads&error=" . urlencode($e->getMessage()));
    exit;
}
?>
