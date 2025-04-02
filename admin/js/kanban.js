/**
 * Kanban Board para HelpExpert
 * Implementa funcionalidades de drag and drop e gerenciamento de colunas
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar os dropdowns do Bootstrap manualmente
    var dropdownTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    dropdownTriggerList.forEach(function (dropdownTriggerEl) {
        new bootstrap.Dropdown(dropdownTriggerEl);
    });
    
    // Configurar drag and drop para o kanban
    setupDragAndDrop();
    
    // Configurar botões de edição e exclusão de colunas
    setupColumnButtons();
});

/**
 * Configura o drag and drop para os cards do kanban
 */
function setupDragAndDrop() {
    const kanbanCards = document.querySelectorAll('.kanban-card');
    const kanbanColumns = document.querySelectorAll('.kanban-column-body');
    
    console.log('Cards encontrados:', kanbanCards.length);
    console.log('Colunas encontradas:', kanbanColumns.length);
    
    // Adicionar eventos de drag para os cards
    kanbanCards.forEach(card => {
        card.setAttribute('draggable', true);
        
        card.addEventListener('dragstart', function(e) {
            console.log('Drag iniciado para o card:', card.id);
            e.dataTransfer.setData('text/plain', card.id);
            setTimeout(() => {
                card.classList.add('dragging');
            }, 0);
        });
        
        card.addEventListener('dragend', function() {
            console.log('Drag finalizado para o card:', card.id);
            card.classList.remove('dragging');
        });
    });
    
    // Adicionar eventos de drop para as colunas
    kanbanColumns.forEach(column => {
        column.addEventListener('dragover', function(e) {
            e.preventDefault();
            column.classList.add('drag-over');
        });
        
        column.addEventListener('dragleave', function() {
            column.classList.remove('drag-over');
        });
        
        column.addEventListener('drop', function(e) {
            e.preventDefault();
            column.classList.remove('drag-over');
            
            const cardId = e.dataTransfer.getData('text/plain');
            console.log('Card ID recebido no drop:', cardId);
            const card = document.getElementById(cardId);
            
            if (card) {
                console.log('Card encontrado:', card);
                // Obter o ID do lead e o novo status
                const leadId = card.getAttribute('data-lead-id');
                const newStatus = column.getAttribute('data-status');
                
                console.log('Lead ID:', leadId);
                console.log('Novo status:', newStatus);
                
                if (leadId && newStatus) {
                    // Enviar solicitação AJAX para atualizar o status
                    const formData = new FormData();
                    formData.append('lead_id', leadId);
                    formData.append('status', newStatus);
                    
                    fetch('update_lead_status.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Resposta do servidor:', data);
                        if (data.success) {
                            // Mover o card para a nova coluna
                            column.appendChild(card);
                            
                            // Atualizar a aparência do card com base no novo status
                            updateCardAppearance(card, newStatus);
                            
                            // Atualizar os contadores de leads
                            if (typeof updateCountersAfterDragDrop === 'function') {
                                updateCountersAfterDragDrop();
                            }
                            
                            // Disparar evento personalizado para notificar sobre a atualização
                            document.dispatchEvent(new CustomEvent('lead-status-updated', {
                                detail: {
                                    leadId: leadId,
                                    newStatus: newStatus
                                }
                            }));
                        } else {
                            console.error('Erro ao atualizar o status:', data.error);
                            alert('Erro ao atualizar o status: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao atualizar o status:', error);
                        alert('Erro ao processar a requisição. Tente novamente.');
                    });
                }
            } else {
                console.error('Card não encontrado com ID:', cardId);
            }
        });
    });
}

/**
 * Configura os botões de edição e exclusão de colunas
 */
function setupColumnButtons() {
    // Botões de edição de coluna
    const editButtons = document.querySelectorAll('[data-action="edit-column"]');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const columnId = this.getAttribute('data-column-id');
            const column = this.closest('.kanban-column');
            const columnName = column.querySelector('.kanban-column-header h5').textContent.trim().split(' ')[0]; // Obter apenas o nome, sem o contador
            const columnColor = getComputedStyle(column).getPropertyValue('--column-color') || '#007498';
            
            // Preencher o modal com os dados da coluna
            document.getElementById('editColumnId').value = columnId;
            document.getElementById('editColumnName').value = columnName;
            document.getElementById('editColumnColor').value = columnColor;
            
            // Abrir o modal
            new bootstrap.Modal(document.getElementById('editColumnModal')).show();
        });
    });
    
    // Botões de exclusão de coluna
    const deleteButtons = document.querySelectorAll('[data-action="delete-column"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const columnId = this.getAttribute('data-column-id');
            deleteColumn(columnId);
            
            // Abrir o modal
            new bootstrap.Modal(document.getElementById('deleteColumnModal')).show();
        });
    });
}

/**
 * Função para editar uma coluna
 */
function editColumn(id) {
    const columnId = parseInt(id);
    const columns = kanbanColumnsData; // Variável global definida no HTML
    
    const column = columns.find(col => parseInt(col.id) === columnId);
    
    if (column) {
        document.getElementById('editColumnId').value = columnId;
        document.getElementById('editColumnName').value = column.name;
        document.getElementById('editColumnColor').value = column.color;
    } else {
        console.error('Coluna não encontrada com ID:', columnId);
    }
}

/**
 * Função para excluir uma coluna
 */
function deleteColumn(id) {
    document.getElementById('deleteColumnId').value = id;
}

/**
 * Atualiza a aparência do card com base no novo status
 * @param {HTMLElement} card - O card do kanban
 * @param {string} status - O novo status do lead
 */
function updateCardAppearance(card, status) {
    // Encontrar a coluna de destino para obter a cor
    const targetColumn = document.querySelector(`.kanban-column-body[data-status="${status}"]`).closest('.kanban-column');
    const columnHeader = targetColumn.querySelector('.kanban-column-header');
    const columnName = columnHeader.querySelector('h5').textContent.trim().split(' ')[0]; // Obter apenas o nome, sem o contador
    const columnColor = getComputedStyle(targetColumn).getPropertyValue('--column-color') || '#007498';
    
    // Atualizar a borda do card com a cor da coluna
    card.style.borderLeft = `4px solid ${columnColor}`;
    
    // Atualizar o badge com a cor e o nome da coluna
    const badge = card.querySelector('.badge');
    if (badge) {
        badge.style.backgroundColor = columnColor;
        badge.textContent = columnName;
        
        // Ajustar a cor do texto com base na luminosidade da cor de fundo
        const isDarkColor = isColorDark(columnColor);
        badge.style.color = isDarkColor ? '#ffffff' : '#333333';
    }
}

/**
 * Verifica se uma cor é escura (para determinar a cor do texto)
 * @param {string} color - A cor em formato hexadecimal ou RGB
 * @returns {boolean} - Verdadeiro se a cor for escura
 */
function isColorDark(color) {
    // Converter para RGB se for hexadecimal
    let r, g, b;
    
    if (color.startsWith('#')) {
        // Cor hexadecimal
        const hex = color.substring(1);
        r = parseInt(hex.substr(0, 2), 16);
        g = parseInt(hex.substr(2, 2), 16);
        b = parseInt(hex.substr(4, 2), 16);
    } else if (color.startsWith('rgb')) {
        // Cor RGB
        const rgbValues = color.match(/\d+/g);
        if (rgbValues && rgbValues.length >= 3) {
            r = parseInt(rgbValues[0]);
            g = parseInt(rgbValues[1]);
            b = parseInt(rgbValues[2]);
        } else {
            return false;
        }
    } else {
        return false;
    }
    
    // Calcular a luminosidade (fórmula YIQ)
    const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
    return yiq < 128; // Se for menor que 128, é uma cor escura
}

/**
 * Atualiza os botões de ação do card com base no novo status
 * @param {HTMLElement} card - O card do kanban
 * @param {string} status - O novo status do lead
 */
function updateActionButtons(card, status) {
    // Esta função pode ser expandida para atualizar os botões de ação
    // com base no novo status, se necessário
    console.log('Atualizando botões de ação para o status:', status);
}
