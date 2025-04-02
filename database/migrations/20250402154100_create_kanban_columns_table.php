<?php
/**
 * Migração: create_kanban_columns_table
 * Criada em: 20250402154100
 */
class Migration_20250402154100_create_kanban_columns_table {
    /**
     * Executa a migração
     */
    public function up($conn) {
        $sql = "CREATE TABLE IF NOT EXISTS kanban_columns (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            status_key VARCHAR(50) NOT NULL,
            position INT(11) NOT NULL DEFAULT 0,
            is_default TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL
        )";
        
        $conn->exec($sql);
        
        // Inserir colunas padrão
        $stmt = $conn->prepare("INSERT INTO kanban_columns (name, status_key, position, is_default, created_at) VALUES (?, ?, ?, ?, NOW())");
        
        $defaultColumns = [
            ['Novo', 'novo', 0, 1],
            ['Contato', 'contato', 1, 1],
            ['Negociação', 'negociacao', 2, 1],
            ['Fechado', 'fechado', 3, 1],
            ['Perdido', 'perdido', 4, 1]
        ];
        
        foreach ($defaultColumns as $column) {
            $stmt->execute([$column[0], $column[1], $column[2], $column[3]]);
        }
    }
    
    /**
     * Reverte a migração
     */
    public function down($conn) {
        $sql = "DROP TABLE IF EXISTS kanban_columns";
        $conn->exec($sql);
    }
}
