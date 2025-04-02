<?php
// Migração para adicionar a coluna status à tabela leads
class Migration_20250402171900_add_status_to_leads {
    public function up($conn) {
        try {
            // Verificar se a coluna já existe
            $result = $conn->query("SHOW COLUMNS FROM leads LIKE 'status'");
            $exists = $result->rowCount() > 0;
            
            if (!$exists) {
                // Adicionar a coluna status com valor padrão 'novo'
                $conn->exec("ALTER TABLE leads ADD COLUMN status VARCHAR(20) DEFAULT 'novo' AFTER privacy");
                return true;
            }
            
            return false; // A coluna já existe
        } catch(PDOException $e) {
            echo "Erro na migração: " . $e->getMessage();
            return false;
        }
    }
    
    public function down($conn) {
        try {
            // Verificar se a coluna existe
            $result = $conn->query("SHOW COLUMNS FROM leads LIKE 'status'");
            $exists = $result->rowCount() > 0;
            
            if ($exists) {
                // Remover a coluna status
                $conn->exec("ALTER TABLE leads DROP COLUMN status");
                return true;
            }
            
            return false; // A coluna não existe
        } catch(PDOException $e) {
            echo "Erro na migração: " . $e->getMessage();
            return false;
        }
    }
}
?>
