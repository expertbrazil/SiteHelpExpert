<?php
// Incluir arquivo de configuração
require_once __DIR__ . "/../includes/config/config.php";

// Selecionar o banco de dados
$conn->exec("USE helpexpert");

try {
    // Verificar se a tabela existe
    $tableExists = $conn->query("SHOW TABLES LIKE 'kanban_columns'")->rowCount() > 0;
    
    if ($tableExists) {
        // Verificar se a coluna is_default existe
        $columnExists = $conn->query("SHOW COLUMNS FROM kanban_columns LIKE 'is_default'")->rowCount() > 0;
        
        if (!$columnExists) {
            // Adicionar a coluna is_default
            $conn->exec("ALTER TABLE kanban_columns ADD COLUMN is_default TINYINT(1) NOT NULL DEFAULT 0 AFTER position");
            echo "Coluna is_default adicionada à tabela kanban_columns.<br>";
        }
        
        // Verificar se a coluna color existe
        $colorExists = $conn->query("SHOW COLUMNS FROM kanban_columns LIKE 'color'")->rowCount() > 0;
        
        if (!$colorExists) {
            // Adicionar a coluna color
            $conn->exec("ALTER TABLE kanban_columns ADD COLUMN color VARCHAR(20) NOT NULL DEFAULT '#007498' AFTER status_key");
            echo "Coluna color adicionada à tabela kanban_columns.<br>";
            
            // Atualizar as cores das colunas existentes
            $conn->exec("UPDATE kanban_columns SET color = '#007498' WHERE status_key = 'novo'");
            $conn->exec("UPDATE kanban_columns SET color = '#17a2b8' WHERE status_key = 'contato'");
            $conn->exec("UPDATE kanban_columns SET color = '#ffc107' WHERE status_key = 'negociacao'");
            $conn->exec("UPDATE kanban_columns SET color = '#28a745' WHERE status_key = 'fechado'");
            $conn->exec("UPDATE kanban_columns SET color = '#dc3545' WHERE status_key = 'perdido'");
            echo "Cores das colunas atualizadas.<br>";
        }
    } else {
        // Criar tabela para colunas do kanban se não existir
        $conn->exec("CREATE TABLE IF NOT EXISTS kanban_columns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            status_key VARCHAR(50) NOT NULL UNIQUE,
            color VARCHAR(20) NOT NULL,
            position INT NOT NULL,
            is_default TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "Tabela kanban_columns criada com sucesso.<br>";
    }
    
    // Verificar se já existem colunas padrão
    $stmt = $conn->prepare("SELECT COUNT(*) FROM kanban_columns");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    // Se não houver colunas, inserir as colunas padrão
    if ($count == 0) {
        $defaultColumns = [
            ['Novo', 'novo', '#007498', 1, 1], // Coluna "Novo" é a padrão
            ['Contato', 'contato', '#17a2b8', 2, 0],
            ['Negociação', 'negociacao', '#ffc107', 3, 0],
            ['Fechado', 'fechado', '#28a745', 4, 0],
            ['Perdido', 'perdido', '#dc3545', 5, 0]
        ];
        
        $stmt = $conn->prepare("INSERT INTO kanban_columns (name, status_key, color, position, is_default) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($defaultColumns as $column) {
            $stmt->execute($column);
        }
        
        echo "Colunas padrão do kanban criadas com sucesso!<br>";
    } else {
        echo "As colunas do kanban já existem.<br>";
    }
    
    // Verificar se há alguma coluna definida como padrão
    $stmt = $conn->prepare("SELECT COUNT(*) FROM kanban_columns WHERE is_default = 1");
    $stmt->execute();
    $hasDefault = $stmt->fetchColumn() > 0;
    
    if (!$hasDefault) {
        // Definir a coluna "Novo" como padrão
        $stmt = $conn->prepare("UPDATE kanban_columns SET is_default = 1 WHERE status_key = 'novo'");
        $stmt->execute();
        echo "Coluna 'Novo' definida como padrão.<br>";
    }
    
    echo "<br>Configuração das colunas do kanban concluída com sucesso!";
    
} catch(PDOException $e) {
    echo "Erro ao configurar as colunas do kanban: " . $e->getMessage();
}
?>
