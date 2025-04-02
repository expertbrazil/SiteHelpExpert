<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    echo "Você precisa estar logado para executar este script.";
    exit;
}

// Incluir arquivo de configuração
require_once "../config.php";

// Selecionar o banco de dados
$conn->exec("USE helpexpert");

try {
    // Verificar se a coluna status já existe na tabela leads
    $result = $conn->query("SHOW COLUMNS FROM leads LIKE 'status'");
    $exists = $result->rowCount() > 0;
    
    if (!$exists) {
        // Adicionar coluna status à tabela leads
        $conn->exec("ALTER TABLE leads ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'novo'");
        echo "Coluna 'status' adicionada com sucesso à tabela 'leads'.<br>";
    } else {
        echo "A coluna 'status' já existe na tabela 'leads'.<br>";
    }
    
    // Atualizar os leads existentes para ter o status 'novo'
    $conn->exec("UPDATE leads SET status = 'novo' WHERE status IS NULL OR status = ''");
    echo "Leads existentes atualizados para o status 'novo'.<br>";
    
    echo "Atualização do banco de dados concluída com sucesso!";
} catch(PDOException $e) {
    echo "Erro ao atualizar o banco de dados: " . $e->getMessage();
}
?>
