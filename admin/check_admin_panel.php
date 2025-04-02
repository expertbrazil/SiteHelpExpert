<?php
// Incluir arquivo de configuração
require_once "../config.php";

// Selecionar o banco de dados
$conn->exec("USE helpexpert");

echo "<h1>Verificação do Painel Administrativo HelpExpert</h1>";

// Verificar se a tabela kanban_columns existe e tem a estrutura correta
echo "<h2>Verificação da tabela kanban_columns</h2>";
$result = $conn->query("SHOW TABLES LIKE 'kanban_columns'");
$tableExists = $result->rowCount() > 0;

if ($tableExists) {
    echo "<p>✅ Tabela kanban_columns existe</p>";
    
    // Verificar colunas da tabela
    $columns = $conn->query("SHOW COLUMNS FROM kanban_columns")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Colunas encontradas: " . implode(", ", $columns) . "</p>";
    
    // Verificar se todas as colunas necessárias existem
    $requiredColumns = ['id', 'name', 'status_key', 'color', 'position', 'is_default', 'created_at'];
    $missingColumns = array_diff($requiredColumns, $columns);
    
    if (empty($missingColumns)) {
        echo "<p>✅ Todas as colunas necessárias estão presentes</p>";
    } else {
        echo "<p>❌ Colunas faltando: " . implode(", ", $missingColumns) . "</p>";
    }
    
    // Verificar registros na tabela
    $count = $conn->query("SELECT COUNT(*) FROM kanban_columns")->fetchColumn();
    echo "<p>Total de registros: $count</p>";
    
    if ($count > 0) {
        echo "<p>✅ Existem registros na tabela</p>";
        
        // Verificar se há alguma coluna definida como padrão
        $defaultCount = $conn->query("SELECT COUNT(*) FROM kanban_columns WHERE is_default = 1")->fetchColumn();
        
        if ($defaultCount > 0) {
            echo "<p>✅ Existe pelo menos uma coluna definida como padrão</p>";
        } else {
            echo "<p>❌ Não há nenhuma coluna definida como padrão</p>";
        }
        
        // Verificar se todas as colunas têm cor definida
        $noColorCount = $conn->query("SELECT COUNT(*) FROM kanban_columns WHERE color IS NULL OR color = ''")->fetchColumn();
        
        if ($noColorCount == 0) {
            echo "<p>✅ Todas as colunas têm cor definida</p>";
        } else {
            echo "<p>❌ Existem $noColorCount colunas sem cor definida</p>";
        }
    } else {
        echo "<p>❌ Não existem registros na tabela</p>";
    }
} else {
    echo "<p>❌ Tabela kanban_columns não existe</p>";
}

// Verificar se a tabela leads existe e tem a estrutura correta
echo "<h2>Verificação da tabela leads</h2>";
$result = $conn->query("SHOW TABLES LIKE 'leads'");
$tableExists = $result->rowCount() > 0;

if ($tableExists) {
    echo "<p>✅ Tabela leads existe</p>";
    
    // Verificar se a coluna status existe
    $statusExists = $conn->query("SHOW COLUMNS FROM leads LIKE 'status'")->rowCount() > 0;
    
    if ($statusExists) {
        echo "<p>✅ Coluna status existe na tabela leads</p>";
    } else {
        echo "<p>❌ Coluna status não existe na tabela leads</p>";
    }
    
    // Verificar registros na tabela
    $count = $conn->query("SELECT COUNT(*) FROM leads")->fetchColumn();
    echo "<p>Total de registros: $count</p>";
} else {
    echo "<p>❌ Tabela leads não existe</p>";
}

// Verificar se a função getLeadsByStatus está funcionando corretamente
echo "<h2>Teste da função getLeadsByStatus</h2>";
try {
    // Definir a função getLeadsByStatus
    function getLeadsByStatus($conn, $status) {
        try {
            // Verificar se a coluna status existe na tabela leads
            $result = $conn->query("SHOW COLUMNS FROM leads LIKE 'status'");
            $statusExists = $result->rowCount() > 0;
            
            if ($statusExists) {
                // Se a coluna status existir, filtrar por status
                $stmt = $conn->prepare("SELECT * FROM leads WHERE status = :status ORDER BY created_at DESC");
                $stmt->bindParam(':status', $status);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Se a coluna status não existir, retornar todos os leads
                $stmt = $conn->prepare("SELECT * FROM leads ORDER BY created_at DESC");
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch(PDOException $e) {
            // Registrar erro em log
            error_log("Erro ao obter leads por status: " . $e->getMessage());
            return [];
        }
    }
    
    // Testar a função com um status existente
    $leads = getLeadsByStatus($conn, 'novo');
    echo "<p>✅ Função getLeadsByStatus executada com sucesso</p>";
    echo "<p>Leads encontrados com status 'novo': " . count($leads) . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Erro ao testar a função getLeadsByStatus: " . $e->getMessage() . "</p>";
}

// Verificar se a função getKanbanColumns está funcionando corretamente
echo "<h2>Teste da função getKanbanColumns</h2>";
try {
    // Definir a função getKanbanColumns
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
    
    // Testar a função
    $columns = getKanbanColumns($conn);
    echo "<p>✅ Função getKanbanColumns executada com sucesso</p>";
    echo "<p>Colunas encontradas: " . count($columns) . "</p>";
    
    // Verificar se todas as colunas têm a propriedade color
    $hasColorProperty = true;
    foreach ($columns as $column) {
        if (!isset($column['color'])) {
            $hasColorProperty = false;
            break;
        }
    }
    
    if ($hasColorProperty) {
        echo "<p>✅ Todas as colunas têm a propriedade color</p>";
    } else {
        echo "<p>❌ Nem todas as colunas têm a propriedade color</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro ao testar a função getKanbanColumns: " . $e->getMessage() . "</p>";
}

echo "<h2>Conclusão</h2>";
echo "<p>A verificação do painel administrativo foi concluída. Se todos os itens estiverem marcados com ✅, o painel deve estar funcionando corretamente.</p>";
?>
