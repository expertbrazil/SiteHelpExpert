<?php
// Configurações do banco de dados
$host = 'localhost';
$dbname = 'helpexpert';
$username = 'root';
$password = '';
$port = '3306'; // Porta padrão do MySQL

// Criar conexão diretamente com o banco de dados
try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    // Configurar o PDO para lançar exceções em caso de erro
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar se a coluna já existe
    $result = $conn->query("SHOW COLUMNS FROM leads LIKE 'notes'");
    $exists = $result->rowCount() > 0;
    
    if (!$exists) {
        // Adicionar a coluna notes à tabela leads
        $conn->exec("ALTER TABLE leads ADD COLUMN notes TEXT AFTER status");
        echo "Coluna 'notes' adicionada com sucesso à tabela leads.";
    } else {
        echo "A coluna 'notes' já existe na tabela leads.";
    }
} catch(PDOException $e) {
    echo "Erro ao adicionar coluna: " . $e->getMessage();
}
?>
