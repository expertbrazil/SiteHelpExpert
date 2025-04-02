<?php
/**
 * Migração: create_users_table
 * Criada em: 20250402195853
 */
class Migration_20250402195853_create_users_table {
    /**
     * Executa a migração
     */
    public function up($conn) {
        // Criar tabela de usuários
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            role VARCHAR(20) NOT NULL DEFAULT 'admin',
            last_login DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        )";
        
        $conn->exec($sql);
        
        // Criar usuário padrão (admin/helpexpert2025)
        $username = 'admin';
        $password = password_hash('helpexpert2025', PASSWORD_DEFAULT);
        $name = 'Administrador';
        $email = 'admin@helpexpert.com';
        $now = date('Y-m-d H:i:s');
        
        $stmt = $conn->prepare("INSERT INTO users (username, password, name, email, role, created_at, updated_at) VALUES (?, ?, ?, ?, 'admin', ?, ?)");
        $stmt->execute([$username, $password, $name, $email, $now, $now]);
    }
    
    /**
     * Reverte a migração
     */
    public function down($conn) {
        // Remover a tabela de usuários
        $sql = "DROP TABLE IF EXISTS users";
        $conn->exec($sql);
    }
}