<?php
// Script para verificar as tabelas criadas no banco de dados

require_once 'includes/db_connection.php';

echo "Verificando tabelas no banco de dados...\n\n";

try {
    // Listar todas as tabelas
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "Tabelas encontradas no banco de dados:\n";
        echo str_repeat('-', 50) . "\n";
        
        foreach ($tables as $table) {
            echo "- $table\n";
            
            // Mostrar estrutura da tabela
            $stmt = $conn->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "  Colunas:\n";
            foreach ($columns as $column) {
                echo "    - {$column['Field']} ({$column['Type']})" . 
                     ($column['Key'] == 'PRI' ? ' PRIMARY KEY' : '') . 
                     ($column['Null'] == 'NO' ? ' NOT NULL' : '') . "\n";
            }
            
            // Contar registros
            $stmt = $conn->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "  Total de registros: $count\n\n";
        }
    } else {
        echo "Nenhuma tabela encontrada no banco de dados.\n";
    }
} catch(PDOException $e) {
    echo "Erro ao verificar tabelas: " . $e->getMessage() . "\n";
}
?>
