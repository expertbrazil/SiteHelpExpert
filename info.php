<?php
// Informações sobre o ambiente PHP
echo "<h1>Informações do Servidor</h1>";
echo "<p>Versão do PHP: " . phpversion() . "</p>";
echo "<p>Servidor Web: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Documento Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>URI Solicitada: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Script Atual: " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p>Diretório Atual: " . __DIR__ . "</p>";

// Verificar se o banco de dados está acessível
echo "<h1>Teste de Conexão com o Banco de Dados</h1>";
try {
    $host = 'localhost';
    $dbname = 'helpexpert';
    $username = 'root';
    $password = '';
    $port = '3306';
    
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color:green'>Conexão com o banco de dados estabelecida com sucesso!</p>";
    
    // Listar tabelas
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>Tabelas encontradas: " . implode(", ", $tables) . "</p>";
    
} catch(PDOException $e) {
    echo "<p style='color:red'>Erro na conexão com o banco de dados: " . $e->getMessage() . "</p>";
}

// Verificar se os arquivos principais existem
echo "<h1>Verificação de Arquivos</h1>";
$files_to_check = [
    'index.html',
    'admin/index.php',
    'admin/login.php',
    'app/models/User.php',
    'includes/db_connection.php'
];

echo "<ul>";
foreach ($files_to_check as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        echo "<li style='color:green'>$file existe</li>";
    } else {
        echo "<li style='color:red'>$file não existe</li>";
    }
}
echo "</ul>";
?>
