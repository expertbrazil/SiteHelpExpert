<?php
// Script para inicializar o banco de dados antes de executar as migrações

// Configurações do banco de dados
$host = 'localhost';
$dbname = 'helpexpert';
$username = 'root';
$password = '';
$port = '3306';

try {
    // Conectar sem especificar o banco de dados
    $conn = new PDO("mysql:host=$host;port=$port", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar o banco de dados se não existir
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    echo "Banco de dados '$dbname' criado ou verificado com sucesso!\n";
    
    // Selecionar o banco de dados
    $conn->exec("USE $dbname");
    echo "Banco de dados '$dbname' selecionado com sucesso!\n";
    
    echo "Inicialização do banco de dados concluída.\n";
    
} catch(PDOException $e) {
    echo "Erro na inicialização do banco de dados: " . $e->getMessage() . "\n";
    exit(1);
}
?>
