<?php
// Incluir arquivo de configuração
require_once "../config.php";

// Selecionar o banco de dados
$conn->exec("USE helpexpert");

// Função para obter todas as colunas do kanban
function getKanbanColumns($conn) {
    try {
        // Verificar se a tabela existe
        $result = $conn->query("SHOW TABLES LIKE 'kanban_columns'");
        $tableExists = $result->rowCount() > 0;
        
        if (!$tableExists) {
            // Se a tabela não existir, retornar colunas padrão
            return [
                ['id' => 1, 'name' => 'Novo', 'status_key' => 'novo', 'color' => '#007498', 'position' => 1, 'is_default' => 1],
                ['id' => 2, 'name' => 'Contato', 'status_key' => 'contato', 'color' => '#17a2b8', 'position' => 2, 'is_default' => 0],
                ['id' => 3, 'name' => 'Negociação', 'status_key' => 'negociacao', 'color' => '#ffc107', 'position' => 3, 'is_default' => 0],
                ['id' => 4, 'name' => 'Fechado', 'status_key' => 'fechado', 'color' => '#28a745', 'position' => 4, 'is_default' => 0],
                ['id' => 5, 'name' => 'Perdido', 'status_key' => 'perdido', 'color' => '#dc3545', 'position' => 5, 'is_default' => 0]
            ];
        }
        
        // Obter todas as colunas ordenadas por posição
        $stmt = $conn->prepare("SELECT * FROM kanban_columns ORDER BY position ASC");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Verificar se há colunas retornadas
        if (empty($columns)) {
            // Se não houver colunas, retornar colunas padrão
            return [
                ['id' => 1, 'name' => 'Novo', 'status_key' => 'novo', 'color' => '#007498', 'position' => 1, 'is_default' => 1],
                ['id' => 2, 'name' => 'Contato', 'status_key' => 'contato', 'color' => '#17a2b8', 'position' => 2, 'is_default' => 0],
                ['id' => 3, 'name' => 'Negociação', 'status_key' => 'negociacao', 'color' => '#ffc107', 'position' => 3, 'is_default' => 0],
                ['id' => 4, 'name' => 'Fechado', 'status_key' => 'fechado', 'color' => '#28a745', 'position' => 4, 'is_default' => 0],
                ['id' => 5, 'name' => 'Perdido', 'status_key' => 'perdido', 'color' => '#dc3545', 'position' => 5, 'is_default' => 0]
            ];
        }
        
        return $columns;
    } catch(PDOException $e) {
        // Registrar erro em log
        error_log("Erro ao obter colunas do kanban: " . $e->getMessage());
        // Em caso de erro, retornar colunas padrão
        return [
            ['id' => 1, 'name' => 'Novo', 'status_key' => 'novo', 'color' => '#007498', 'position' => 1, 'is_default' => 1],
            ['id' => 2, 'name' => 'Contato', 'status_key' => 'contato', 'color' => '#17a2b8', 'position' => 2, 'is_default' => 0],
            ['id' => 3, 'name' => 'Negociação', 'status_key' => 'negociacao', 'color' => '#ffc107', 'position' => 3, 'is_default' => 0],
            ['id' => 4, 'name' => 'Fechado', 'status_key' => 'fechado', 'color' => '#28a745', 'position' => 4, 'is_default' => 0],
            ['id' => 5, 'name' => 'Perdido', 'status_key' => 'perdido', 'color' => '#dc3545', 'position' => 5, 'is_default' => 0]
        ];
    }
}

// Obter as colunas do kanban
$kanbanColumns = getKanbanColumns($conn);

// Exibir as colunas em formato legível
echo "<h2>Colunas do Kanban</h2>";
echo "<pre>";
print_r($kanbanColumns);
echo "</pre>";

// Verificar se há algum problema com as colunas
$hasError = false;
$errorMessages = [];

foreach ($kanbanColumns as $index => $column) {
    // Verificar se todas as propriedades necessárias estão presentes
    $requiredProperties = ['id', 'name', 'status_key', 'color', 'position', 'is_default'];
    foreach ($requiredProperties as $prop) {
        if (!isset($column[$prop])) {
            $hasError = true;
            $errorMessages[] = "Coluna $index: Propriedade '$prop' não encontrada";
        }
    }
}

// Exibir mensagens de erro, se houver
if ($hasError) {
    echo "<h2>Erros encontrados:</h2>";
    echo "<ul>";
    foreach ($errorMessages as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Nenhum erro encontrado nas colunas do Kanban.</p>";
}

// Verificar se há leads no banco de dados
$stmt = $conn->prepare("SELECT COUNT(*) FROM leads");
$stmt->execute();
$leadCount = $stmt->fetchColumn();

echo "<h2>Leads no banco de dados</h2>";
echo "<p>Total de leads: $leadCount</p>";

// Se houver leads, exibir alguns detalhes
if ($leadCount > 0) {
    $stmt = $conn->prepare("SELECT * FROM leads LIMIT 5");
    $stmt->execute();
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Amostra de leads:</h3>";
    echo "<pre>";
    print_r($leads);
    echo "</pre>";
}

// Verificar se a coluna status existe na tabela leads
$stmt = $conn->query("SHOW COLUMNS FROM leads LIKE 'status'");
$statusExists = $stmt->rowCount() > 0;

echo "<h2>Coluna 'status' na tabela leads</h2>";
if ($statusExists) {
    echo "<p>A coluna 'status' existe na tabela leads.</p>";
} else {
    echo "<p>A coluna 'status' NÃO existe na tabela leads.</p>";
}
?>
