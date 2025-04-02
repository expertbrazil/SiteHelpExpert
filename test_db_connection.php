<?php
// Script para testar a conexão com o banco de dados

echo "Testando conexão com o banco de dados...\n";

// Configurações do banco de dados
$host = 'localhost';
$dbname = 'helpexpert';
$username = 'root';
$password = '';
$port = '3306';

try {
    // Tentar conectar sem especificar o banco de dados primeiro
    $conn = new PDO("mysql:host=$host;port=$port", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexão com o servidor MySQL estabelecida com sucesso!\n";
    
    // Verificar se o banco de dados existe
    $stmt = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    $dbExists = $stmt->fetchColumn();
    
    if ($dbExists) {
        echo "Banco de dados '$dbname' encontrado.\n";
        
        // Conectar ao banco de dados específico
        $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Conexão com o banco de dados '$dbname' estabelecida com sucesso!\n";
        
        // Listar tabelas
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "Tabelas encontradas no banco de dados:\n";
            foreach ($tables as $table) {
                echo "- $table\n";
            }
        } else {
            echo "Nenhuma tabela encontrada no banco de dados.\n";
        }
    } else {
        echo "Banco de dados '$dbname' não encontrado. Você precisa criá-lo ou executar as migrações.\n";
    }
    
} catch(PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage() . "\n";
}
?>
