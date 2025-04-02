# Sistema de Migrações do HelpExpert

Este sistema permite gerenciar as migrações de banco de dados do projeto HelpExpert de forma organizada e controlada.

## Estrutura

- `database/config.php` - Configurações do banco de dados
- `database/migrate.php` - Script principal do sistema de migrações
- `database/migrations/` - Diretório onde as migrações são armazenadas

## Comandos Disponíveis

Execute os comandos a partir da raiz do projeto:

```bash
# Executar todas as migrações pendentes
php migrate.php migrate

# Reverter as migrações do último lote
php migrate.php rollback

# Criar uma nova migração
php migrate.php create nome_da_migracao

# Verificar o status das migrações
php migrate.php status
```

## Como Criar uma Nova Migração

1. Execute o comando:
   ```bash
   php migrate.php create nome_da_migracao
   ```

2. Edite o arquivo gerado em `database/migrations/` e implemente os métodos `up()` e `down()`:
   - `up()` - Contém o SQL para aplicar a migração
   - `down()` - Contém o SQL para reverter a migração

## Exemplo de Migração

```php
<?php
class Migration_20250402154500_add_column_to_table {
    public function up($conn) {
        $sql = "ALTER TABLE tabela ADD COLUMN nova_coluna VARCHAR(100)";
        $conn->exec($sql);
    }
    
    public function down($conn) {
        $sql = "ALTER TABLE tabela DROP COLUMN nova_coluna";
        $conn->exec($sql);
    }
}
```

## Tabela de Controle

O sistema cria automaticamente uma tabela `migrations` no banco de dados para controlar quais migrações foram executadas:

- `id` - ID único da migração executada
- `migration` - Nome do arquivo de migração
- `batch` - Número do lote de execução
- `executed_at` - Data e hora da execução
