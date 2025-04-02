<?php
/**
 * Migração: create_leads_table
 * Criada em: 20250402154000
 */
class Migration_20250402154000_create_leads_table {
    /**
     * Executa a migração
     */
    public function up($conn) {
        $sql = "CREATE TABLE IF NOT EXISTS leads (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            company VARCHAR(100),
            employees VARCHAR(50),
            privacy TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL
        )";
        
        $conn->exec($sql);
    }
    
    /**
     * Reverte a migração
     */
    public function down($conn) {
        $sql = "DROP TABLE IF EXISTS leads";
        $conn->exec($sql);
    }
}
