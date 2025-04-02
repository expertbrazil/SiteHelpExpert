<?php
// Incluir arquivo de configuração
require_once "../config.php";

// Selecionar o banco de dados
$conn->exec("USE helpexpert");

try {
    echo "<h2>Atualizando estrutura do banco de dados</h2>";
    
    // Verificar se a coluna status já existe
    $result = $conn->query("SHOW COLUMNS FROM leads LIKE 'status'");
    $exists = $result->rowCount() > 0;
    
    if (!$exists) {
        // Adicionar coluna status à tabela leads
        $conn->exec("ALTER TABLE leads ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'novo'");
        echo "<p>Coluna 'status' adicionada com sucesso à tabela 'leads'.</p>";
    } else {
        echo "<p>A coluna 'status' já existe na tabela 'leads'.</p>";
    }
    
    // Atualizar os leads existentes para ter o status 'novo'
    $stmt = $conn->prepare("UPDATE leads SET status = 'novo' WHERE status IS NULL OR status = ''");
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    echo "<p>{$rowCount} leads atualizados para o status 'novo'.</p>";
    
    echo "<p>Atualização do banco de dados concluída com sucesso!</p>";
    echo "<p><a href='index.php?tab=kanban' class='btn btn-primary'>Voltar para o Kanban</a></p>";
} catch(PDOException $e) {
    echo "<p>Erro ao atualizar o banco de dados: " . $e->getMessage() . "</p>";
}
?>
