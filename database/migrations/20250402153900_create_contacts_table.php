<?php
/**
 * Migração: create_contacts_table
 * Criada em: 20250402153900
 */
class Migration_20250402153900_create_contacts_table {
    /**
     * Executa a migração
     */
    public function up($conn) {
        $sql = "CREATE TABLE IF NOT EXISTS contacts (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            subject VARCHAR(50),
            message TEXT NOT NULL,
            privacy TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL
        )";
        
        $conn->exec($sql);
    }
    
    /**
     * Reverte a migração
     */
    public function down($conn) {
        $sql = "DROP TABLE IF EXISTS contacts";
        $conn->exec($sql);
    }
}
