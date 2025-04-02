<?php
// Função para obter todas as colunas do kanban
function getKanbanColumns($conn) {
    $stmt = $conn->prepare("SELECT * FROM kanban_columns ORDER BY position ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para inicializar a tabela de colunas do kanban se não existir
function initKanbanColumns($conn) {
    // Verificar se a tabela existe
    try {
        $stmt = $conn->prepare("SELECT 1 FROM kanban_columns LIMIT 1");
        $stmt->execute();
    } catch (PDOException $e) {
        // Criar a tabela se não existir
        $conn->exec("CREATE TABLE IF NOT EXISTS kanban_columns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            status_key VARCHAR(50) NOT NULL UNIQUE,
            color VARCHAR(20) DEFAULT '#007498',
            position INT NOT NULL
        )");
        
        // Inserir colunas padrão
        $defaultColumns = [
            ['name' => 'Novo', 'status_key' => 'novo', 'color' => '#007498', 'position' => 1],
            ['name' => 'Contato', 'status_key' => 'contato', 'color' => '#00aba5', 'position' => 2],
            ['name' => 'Negociação', 'status_key' => 'negociacao', 'color' => '#ffc107', 'position' => 3],
            ['name' => 'Fechado', 'status_key' => 'fechado', 'color' => '#28a745', 'position' => 4],
            ['name' => 'Perdido', 'status_key' => 'perdido', 'color' => '#dc3545', 'position' => 5]
        ];
        
        $stmt = $conn->prepare("INSERT INTO kanban_columns (name, status_key, color, position) VALUES (:name, :status_key, :color, :position)");
        
        foreach ($defaultColumns as $column) {
            $stmt->bindParam(':name', $column['name']);
            $stmt->bindParam(':status_key', $column['status_key']);
            $stmt->bindParam(':color', $column['color']);
            $stmt->bindParam(':position', $column['position']);
            $stmt->execute();
        }
    }
}
?>

<!-- Parte a ser inserida no index.php na seção do kanban -->
<div class="card-body p-0">
    <!-- Kanban Board -->
    <div class="kanban-container">
        <div class="kanban-board">
            <?php $kanbanColumns = getKanbanColumns($conn); ?>
            <?php foreach ($kanbanColumns as $column): ?>
            <div class="col-md-3">
                <div class="card kanban-column">
                    <div class="card-header kanban-column-header">
                        <h5 class="mb-0"><?php echo htmlspecialchars($column['name']); ?></h5>
                        <div class="kanban-column-actions">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-action="edit-column" data-column-id="<?php echo $column['id']; ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete-column" data-column-id="<?php echo $column['id']; ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body kanban-column-body" data-status="<?php echo htmlspecialchars($column['status_key']); ?>">
                        <?php if ($column['status_key'] === 'novo'): ?>
                        <?php foreach ($newLeads as $lead): ?>
                        <div class="kanban-card" id="card-<?php echo $lead['id']; ?>" data-lead-id="<?php echo $lead['id']; ?>">
                            <!-- Conteúdo do card -->
                        </div>
                        <?php endforeach; ?>
                        <?php elseif ($column['status_key'] === 'contato'): ?>
                        <?php foreach ($contactLeads as $lead): ?>
                        <div class="kanban-card" id="card-<?php echo $lead['id']; ?>" data-lead-id="<?php echo $lead['id']; ?>">
                            <!-- Conteúdo do card -->
                        </div>
                        <?php endforeach; ?>
                        <?php elseif ($column['status_key'] === 'negociacao'): ?>
                        <?php foreach ($negotiationLeads as $lead): ?>
                        <div class="kanban-card" id="card-<?php echo $lead['id']; ?>" data-lead-id="<?php echo $lead['id']; ?>">
                            <!-- Conteúdo do card -->
                        </div>
                        <?php endforeach; ?>
                        <?php elseif ($column['status_key'] === 'fechado'): ?>
                        <?php foreach ($closedLeads as $lead): ?>
                        <div class="kanban-card" id="card-<?php echo $lead['id']; ?>" data-lead-id="<?php echo $lead['id']; ?>">
                            <!-- Conteúdo do card -->
                        </div>
                        <?php endforeach; ?>
                        <?php elseif ($column['status_key'] === 'perdido'): ?>
                        <?php foreach ($lostLeads as $lead): ?>
                        <div class="kanban-card" id="card-<?php echo $lead['id']; ?>" data-lead-id="<?php echo $lead['id']; ?>">
                            <!-- Conteúdo do card -->
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- Adicionar nova coluna diretamente no quadro -->
            <div class="kanban-add-column" data-bs-toggle="modal" data-bs-target="#addColumnModal">
                <i class="bi bi-plus-circle"></i>
                <span>Adicionar Coluna</span>
            </div>
        </div>
    </div>
</div>

<!-- CSS a ser adicionado na seção de estilos -->
<style>
/* Estilos do Kanban */
.kanban-container {
    overflow-x: auto;
    padding-bottom: 1rem;
    width: 100%;
}

.kanban-board {
    display: flex;
    min-height: 600px;
    gap: 1rem;
    min-width: max-content;
    padding: 1rem;
}

.kanban-column {
    min-width: 300px;
    max-width: 300px;
    background-color: #FFFFFF;
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    display: flex;
    flex-direction: column;
    margin-bottom: 1rem;
    height: calc(100vh - 250px);
}
</style>

<!-- Script JavaScript para inicializar os eventos do kanban -->
<script>
// Dados das colunas do kanban para uso no JavaScript
var kanbanColumnsData = <?php echo json_encode($kanbanColumns); ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar o kanban
    setupDragAndDrop();
    setupColumnButtons();
    
    // Adicionar evento para o botão de adicionar coluna
    const addColumnButton = document.querySelector('.kanban-add-column');
    if (addColumnButton) {
        addColumnButton.addEventListener('click', function() {
            const addColumnModal = new bootstrap.Modal(document.getElementById('addColumnModal'));
            addColumnModal.show();
        });
    }
});
</script>
