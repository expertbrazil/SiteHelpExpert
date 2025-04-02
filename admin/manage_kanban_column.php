<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: login.php");
    exit;
}

// Incluir arquivo de configuração
require_once __DIR__ . "/../includes/config/config.php";

// Selecionar o banco de dados
$conn->exec("USE helpexpert");

// Processar solicitações
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = isset($_POST["action"]) ? $_POST["action"] : "";
    
    // Adicionar nova coluna
    if ($action === "add") {
        $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
        $statusKey = isset($_POST["status_key"]) ? trim($_POST["status_key"]) : "";
        $color = isset($_POST["color"]) ? trim($_POST["color"]) : "#007498";
        
        if (empty($name) || empty($statusKey)) {
            header("Location: index.php?tab=kanban&error=Nome+e+chave+são+obrigatórios");
            exit;
        }
        
        // Verificar se a chave já existe
        $stmt = $conn->prepare("SELECT COUNT(*) FROM kanban_columns WHERE status_key = :status_key");
        $stmt->bindParam(':status_key', $statusKey);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            header("Location: index.php?tab=kanban&error=Chave+já+existe");
            exit;
        }
        
        // Obter a próxima posição
        $stmt = $conn->prepare("SELECT MAX(position) FROM kanban_columns");
        $stmt->execute();
        $maxPosition = $stmt->fetchColumn();
        $position = $maxPosition ? $maxPosition + 1 : 1;
        
        // Inserir nova coluna
        $stmt = $conn->prepare("INSERT INTO kanban_columns (name, status_key, color, position) VALUES (:name, :status_key, :color, :position)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':status_key', $statusKey);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':position', $position);
        $stmt->execute();
        
        header("Location: index.php?tab=kanban&success=Coluna+adicionada+com+sucesso");
        exit;
    }
    
    // Editar coluna existente
    if ($action === "edit") {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
        $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
        $color = isset($_POST["color"]) ? trim($_POST["color"]) : "#007498";
        
        if ($id <= 0 || empty($name)) {
            header("Location: index.php?tab=kanban&error=ID+e+nome+são+obrigatórios");
            exit;
        }
        
        // Atualizar coluna
        $stmt = $conn->prepare("UPDATE kanban_columns SET name = :name, color = :color WHERE id = :id");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        header("Location: index.php?tab=kanban&success=Coluna+atualizada+com+sucesso");
        exit;
    }
    
    // Excluir coluna
    if ($action === "delete") {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
        
        if ($id <= 0) {
            header("Location: index.php?tab=kanban&error=ID+inválido");
            exit;
        }
        
        // Obter a chave de status da coluna
        $stmt = $conn->prepare("SELECT status_key FROM kanban_columns WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $statusKey = $stmt->fetchColumn();
        
        // Verificar se existem leads com este status
        $stmt = $conn->prepare("SELECT COUNT(*) FROM leads WHERE status = :status");
        $stmt->bindParam(':status', $statusKey);
        $stmt->execute();
        $leadCount = $stmt->fetchColumn();
        
        if ($leadCount > 0) {
            header("Location: index.php?tab=kanban&error=Não+é+possível+excluir+coluna+com+leads");
            exit;
        }
        
        // Excluir coluna
        $stmt = $conn->prepare("DELETE FROM kanban_columns WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        header("Location: index.php?tab=kanban&success=Coluna+excluída+com+sucesso");
        exit;
    }
    
    // Reordenar colunas
    if ($action === "reorder") {
        $columnIds = isset($_POST["column_ids"]) ? $_POST["column_ids"] : [];
        
        if (!empty($columnIds)) {
            $position = 1;
            
            foreach ($columnIds as $id) {
                $stmt = $conn->prepare("UPDATE kanban_columns SET position = :position WHERE id = :id");
                $stmt->bindParam(':position', $position);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                $position++;
            }
            
            header("Location: index.php?tab=kanban&success=Colunas+reordenadas+com+sucesso");
            exit;
        }
    }
}

// Redirecionar de volta para o kanban
header("Location: index.php?tab=kanban");
exit;
?>
