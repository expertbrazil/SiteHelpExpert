<?php
require_once 'config.php';

try {
    // Criar o banco de dados se não existir
    $conn->exec("CREATE DATABASE IF NOT EXISTS helpexpert");
    echo "Banco de dados criado com sucesso!<br>";
    
    // Selecionar o banco de dados
    $conn->exec("USE helpexpert");
    
    // Criar tabela de contatos
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
    echo "Tabela de contatos criada com sucesso!<br>";
    
    // Criar tabela de leads (para pessoas que solicitam demonstração)
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
    echo "Tabela de leads criada com sucesso!<br>";
    
    echo "<br>Configuração do banco de dados concluída com sucesso!";
} catch(PDOException $e) {
    echo "Erro ao criar banco de dados ou tabelas: " . $e->getMessage();
}
?>
