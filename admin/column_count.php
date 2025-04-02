<?php
// Incluir arquivo de conexão
require_once '../includes/db_connection.php';

// Função para obter colunas do Kanban (cópia da função em index.php)
function getKanbanColumns($conn) {
    try {
        // Verificar se a tabela existe
        $result = $conn->query("SHOW TABLES LIKE 'kanban_columns'");
        $tableExists = $result->rowCount() > 0;
        
        if (!$tableExists) {
            // Se a tabela não existir, retornar colunas padrão
            return [
                ['id' => 1, 'name' => 'Novo', 'status_key' => 'novo', 'color' => '#007498', 'position' => 1],
                ['id' => 2, 'name' => 'Contato', 'status_key' => 'contato', 'color' => '#17a2b8', 'position' => 2],
                ['id' => 3, 'name' => 'Negociação', 'status_key' => 'negociacao', 'color' => '#ffc107', 'position' => 3],
                ['id' => 4, 'name' => 'Fechado', 'status_key' => 'fechado', 'color' => '#28a745', 'position' => 4],
                ['id' => 5, 'name' => 'Perdido', 'status_key' => 'perdido', 'color' => '#dc3545', 'position' => 5]
            ];
        }
        
        // Obter todas as colunas ordenadas por posição
        $stmt = $conn->prepare("SELECT * FROM kanban_columns ORDER BY position ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        // Em caso de erro, retornar colunas padrão
        return [
            ['id' => 1, 'name' => 'Novo', 'status_key' => 'novo', 'color' => '#007498', 'position' => 1],
            ['id' => 2, 'name' => 'Contato', 'status_key' => 'contato', 'color' => '#17a2b8', 'position' => 2],
            ['id' => 3, 'name' => 'Negociação', 'status_key' => 'negociacao', 'color' => '#ffc107', 'position' => 3],
            ['id' => 4, 'name' => 'Fechado', 'status_key' => 'fechado', 'color' => '#28a745', 'position' => 4],
            ['id' => 5, 'name' => 'Perdido', 'status_key' => 'perdido', 'color' => '#dc3545', 'position' => 5]
        ];
    }
}

// Obter as colunas do Kanban
$columns = getKanbanColumns($conn);
$columnCount = count($columns);

// Verificar se há colunas no banco de dados
$dbColumnsQuery = "SELECT COUNT(*) as count FROM kanban_columns";
$dbColumnsResult = $conn->query($dbColumnsQuery);
$dbColumnsCount = 0;

if ($dbColumnsResult) {
    $row = $dbColumnsResult->fetch(PDO::FETCH_ASSOC);
    $dbColumnsCount = $row['count'];
}

// Verificar se a tabela existe
$tableExistsQuery = "SHOW TABLES LIKE 'kanban_columns'";
$tableExistsResult = $conn->query($tableExistsQuery);
$tableExists = $tableExistsResult->rowCount() > 0;

// Obter detalhes completos das colunas
$columnsDetails = [];
if ($tableExists) {
    $detailsQuery = "SELECT * FROM kanban_columns ORDER BY position ASC";
    $detailsResult = $conn->query($detailsQuery);
    $columnsDetails = $detailsResult->fetchAll(PDO::FETCH_ASSOC);
}

// Verificar se há colunas no HTML
$htmlColumnsCount = 0;
$htmlQuery = "SELECT COUNT(*) as count FROM (
    SELECT DISTINCT status_key FROM leads
) as distinct_statuses";
$htmlResult = $conn->query($htmlQuery);
if ($htmlResult) {
    $row = $htmlResult->fetch(PDO::FETCH_ASSOC);
    $htmlColumnsCount = $row['count'];
}

// Incluir o cabeçalho
include 'includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Contagem de Colunas do Kanban</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h4>Informações sobre as Colunas do Kanban</h4>
                        <p><strong>Número total de colunas retornadas pela função:</strong> <?php echo $columnCount; ?></p>
                        <p><strong>Número de colunas no banco de dados:</strong> <?php echo $dbColumnsCount; ?></p>
                        <p><strong>A tabela kanban_columns existe?</strong> <?php echo $tableExists ? 'Sim' : 'Não'; ?></p>
                        <p><strong>Status distintos nos leads:</strong> <?php echo $htmlColumnsCount; ?></p>
                    </div>
                    
                    <?php if (!empty($columnsDetails)): ?>
                    <h5 class="mt-4">Detalhes das Colunas no Banco de Dados</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Chave de Status</th>
                                    <th>Cor</th>
                                    <th>Posição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($columnsDetails as $column): ?>
                                <tr>
                                    <td><?php echo $column['id']; ?></td>
                                    <td><?php echo $column['name']; ?></td>
                                    <td><?php echo $column['status_key']; ?></td>
                                    <td>
                                        <span class="color-preview" style="display: inline-block; width: 20px; height: 20px; background-color: <?php echo $column['color']; ?>; border-radius: 3px;"></span>
                                        <?php echo $column['color']; ?>
                                    </td>
                                    <td><?php echo $column['position']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="index.php" class="btn btn-primary">Voltar para o Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir o rodapé
include 'includes/footer.php';
?>
