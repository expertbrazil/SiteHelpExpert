<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../includes/db_connection.php';

/**
 * Sistema de Migrações para HelpExpert
 * 
 * Este script gerencia migrações de banco de dados, permitindo:
 * - Criar novas migrações
 * - Executar migrações pendentes
 * - Reverter migrações (rollback)
 * - Listar status das migrações
 */

// Verificar se a tabela de migrações existe, se não, criá-la
function checkMigrationsTable() {
    global $conn;
    
    try {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT(11) NOT NULL,
            executed_at DATETIME NOT NULL
        )";
        
        $conn->exec($sql);
        return true;
    } catch(PDOException $e) {
        echo "Erro ao criar tabela de migrações: " . $e->getMessage() . "\n";
        return false;
    }
}

// Obter todas as migrações já executadas
function getExecutedMigrations() {
    global $conn;
    
    try {
        $stmt = $conn->query("SELECT migration FROM migrations ORDER BY id ASC");
        $executed = [];
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $executed[] = $row['migration'];
        }
        
        return $executed;
    } catch(PDOException $e) {
        echo "Erro ao obter migrações executadas: " . $e->getMessage() . "\n";
        return [];
    }
}

// Obter o número do último lote de migrações
function getLastBatch() {
    global $conn;
    
    try {
        $stmt = $conn->query("SELECT MAX(batch) as last_batch FROM migrations");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['last_batch'] ? $row['last_batch'] : 0;
    } catch(PDOException $e) {
        echo "Erro ao obter último lote: " . $e->getMessage() . "\n";
        return 0;
    }
}

// Obter todas as migrações disponíveis no diretório
function getAvailableMigrations() {
    global $migrations_dir;
    
    $files = scandir($migrations_dir);
    $migrations = [];
    
    foreach($files as $file) {
        if($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'php') {
            $migrations[] = $file;
        }
    }
    
    sort($migrations);
    return $migrations;
}

// Executar migrações pendentes
function runMigrations() {
    global $conn, $migrations_dir;
    
    $executed = getExecutedMigrations();
    $available = getAvailableMigrations();
    $batch = getLastBatch() + 1;
    $count = 0;
    
    foreach($available as $migration) {
        if(!in_array($migration, $executed)) {
            echo "Executando migração: $migration\n";
            
            // Incluir o arquivo de migração
            require_once $migrations_dir . '/' . $migration;
            
            // Extrair o nome da classe da migração do nome do arquivo
            $className = 'Migration_' . pathinfo($migration, PATHINFO_FILENAME);
            
            if(class_exists($className)) {
                $migrationObj = new $className();
                
                try {
                    // Executar o método up da migração
                    $migrationObj->up($conn);
                    
                    // Registrar a migração como executada
                    $stmt = $conn->prepare("INSERT INTO migrations (migration, batch, executed_at) VALUES (?, ?, NOW())");
                    $stmt->execute([$migration, $batch]);
                    
                    echo "Migração $migration executada com sucesso!\n";
                    $count++;
                } catch(Exception $e) {
                    echo "Erro ao executar migração $migration: " . $e->getMessage() . "\n";
                    break;
                }
            } else {
                echo "Erro: Classe $className não encontrada no arquivo $migration\n";
            }
        }
    }
    
    if($count == 0) {
        echo "Nenhuma migração pendente encontrada.\n";
    } else {
        echo "$count migrações executadas com sucesso!\n";
    }
}

// Reverter migrações do último lote
function rollbackMigrations() {
    global $conn, $migrations_dir;
    
    $lastBatch = getLastBatch();
    
    if($lastBatch == 0) {
        echo "Nenhuma migração para reverter.\n";
        return;
    }
    
    try {
        $stmt = $conn->prepare("SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC");
        $stmt->execute([$lastBatch]);
        $migrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if(count($migrations) == 0) {
            echo "Nenhuma migração para reverter no lote $lastBatch.\n";
            return;
        }
        
        $count = 0;
        
        foreach($migrations as $migration) {
            echo "Revertendo migração: " . $migration['migration'] . "\n";
            
            // Incluir o arquivo de migração
            require_once $migrations_dir . '/' . $migration['migration'];
            
            // Extrair o nome da classe da migração do nome do arquivo
            $className = 'Migration_' . pathinfo($migration['migration'], PATHINFO_FILENAME);
            
            if(class_exists($className)) {
                $migrationObj = new $className();
                
                try {
                    // Executar o método down da migração
                    $migrationObj->down($conn);
                    
                    // Remover o registro da migração
                    $stmt = $conn->prepare("DELETE FROM migrations WHERE migration = ?");
                    $stmt->execute([$migration['migration']]);
                    
                    echo "Migração " . $migration['migration'] . " revertida com sucesso!\n";
                    $count++;
                } catch(Exception $e) {
                    echo "Erro ao reverter migração " . $migration['migration'] . ": " . $e->getMessage() . "\n";
                    break;
                }
            } else {
                echo "Erro: Classe $className não encontrada no arquivo " . $migration['migration'] . "\n";
            }
        }
        
        echo "$count migrações revertidas com sucesso!\n";
    } catch(PDOException $e) {
        echo "Erro ao reverter migrações: " . $e->getMessage() . "\n";
    }
}

// Criar uma nova migração
function createMigration($name) {
    global $migrations_dir;
    
    $timestamp = date('YmdHis');
    $filename = $timestamp . '_' . $name . '.php';
    $path = $migrations_dir . '/' . $filename;
    
    $template = <<<EOT
<?php
/**
 * Migração: $name
 * Criada em: $timestamp
 */
class Migration_{$timestamp}_{$name} {
    /**
     * Executa a migração
     */
    public function up(\$conn) {
        // Coloque aqui o código SQL para aplicar a migração
        \$sql = "";
        \$conn->exec(\$sql);
    }
    
    /**
     * Reverte a migração
     */
    public function down(\$conn) {
        // Coloque aqui o código SQL para reverter a migração
        \$sql = "";
        \$conn->exec(\$sql);
    }
}
EOT;
    
    if(file_put_contents($path, $template)) {
        echo "Migração $filename criada com sucesso!\n";
        return true;
    } else {
        echo "Erro ao criar arquivo de migração.\n";
        return false;
    }
}

// Listar status das migrações
function listMigrations() {
    $executed = getExecutedMigrations();
    $available = getAvailableMigrations();
    
    echo "Status das migrações:\n";
    echo str_repeat('-', 80) . "\n";
    echo sprintf("%-50s %-15s %s\n", "Migração", "Status", "Batch");
    echo str_repeat('-', 80) . "\n";
    
    // Obter informações detalhadas das migrações executadas
    global $conn;
    $migrationDetails = [];
    
    try {
        $stmt = $conn->query("SELECT migration, batch, executed_at FROM migrations ORDER BY id ASC");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $migrationDetails[$row['migration']] = [
                'batch' => $row['batch'],
                'executed_at' => $row['executed_at']
            ];
        }
    } catch(PDOException $e) {
        echo "Erro ao obter detalhes das migrações: " . $e->getMessage() . "\n";
    }
    
    // Listar todas as migrações disponíveis
    foreach($available as $migration) {
        $status = in_array($migration, $executed) ? "Executada" : "Pendente";
        $batch = in_array($migration, $executed) ? $migrationDetails[$migration]['batch'] : "-";
        
        echo sprintf("%-50s %-15s %s\n", $migration, $status, $batch);
    }
    
    echo str_repeat('-', 80) . "\n";
}

// Função principal
function main($argv) {
    if(!checkMigrationsTable()) {
        exit(1);
    }
    
    if(count($argv) < 2) {
        showHelp();
        exit(0);
    }
    
    $command = $argv[1];
    
    switch($command) {
        case 'migrate':
            runMigrations();
            break;
        
        case 'rollback':
            rollbackMigrations();
            break;
        
        case 'create':
            if(count($argv) < 3) {
                echo "Erro: Nome da migração não especificado.\n";
                showHelp();
                exit(1);
            }
            
            createMigration($argv[2]);
            break;
        
        case 'status':
            listMigrations();
            break;
        
        default:
            echo "Comando desconhecido: $command\n";
            showHelp();
            exit(1);
    }
}

// Exibir ajuda
function showHelp() {
    echo "Sistema de Migrações HelpExpert\n";
    echo "Uso: php migrate.php [comando] [opções]\n\n";
    echo "Comandos disponíveis:\n";
    echo "  migrate              Executa todas as migrações pendentes\n";
    echo "  rollback             Reverte as migrações do último lote\n";
    echo "  create [nome]        Cria uma nova migração\n";
    echo "  status               Lista o status de todas as migrações\n";
}

// Executar o script
main($argv);
?>
