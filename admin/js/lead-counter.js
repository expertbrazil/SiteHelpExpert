/**
 * Contador de Leads para o Kanban do HelpExpert
 * Implementa a contagem de leads em cada coluna e atualização automática
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar os contadores de leads
    updateAllLeadCounters();
    
    // Configurar observador para mudanças nas colunas
    setupLeadCounterObserver();
});

/**
 * Atualiza os contadores de leads em todas as colunas
 */
function updateAllLeadCounters() {
    const columns = document.querySelectorAll('.kanban-column');
    
    columns.forEach(column => {
        updateLeadCounter(column);
    });
}

/**
 * Atualiza o contador de leads em uma coluna específica
 * @param {HTMLElement} column - A coluna do kanban
 */
function updateLeadCounter(column) {
    const columnBody = column.querySelector('.kanban-column-body');
    const leadCounter = column.querySelector('.lead-counter');
    const columnColor = getComputedStyle(column).getPropertyValue('--column-color') || '#007498';
    
    if (columnBody && leadCounter) {
        const leadCount = columnBody.querySelectorAll('.kanban-card').length;
        leadCounter.textContent = leadCount;
        
        // Usar a cor da coluna para o contador
        leadCounter.style.backgroundColor = columnColor;
        
        // Ajustar a cor do texto com base na luminosidade da cor de fundo
        const isDarkColor = isColorDark(columnColor);
        leadCounter.style.color = isDarkColor ? '#ffffff' : '#333333';
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
 * Configura um observador para detectar mudanças nas colunas do kanban
 */
function setupLeadCounterObserver() {
    const kanbanBoard = document.querySelector('.kanban-board');
    
    if (!kanbanBoard) return;
    
    // Criar um observador para detectar mudanças no DOM
    const observer = new MutationObserver(function(mutations) {
        let shouldUpdate = false;
        
        mutations.forEach(function(mutation) {
            // Verificar se houve adição ou remoção de nós
            if (mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0) {
                shouldUpdate = true;
            }
        });
        
        // Atualizar contadores se necessário
        if (shouldUpdate) {
            updateAllLeadCounters();
        }
    });
    
    // Configurar o observador para monitorar todas as colunas
    const columnBodies = document.querySelectorAll('.kanban-column-body');
    columnBodies.forEach(body => {
        observer.observe(body, { 
            childList: true,  // Observar adições/remoções de filhos
            subtree: false    // Não observar mudanças nos descendentes
        });
    });
    
    // Adicionar listener para o evento de atualização de status
    document.addEventListener('lead-status-updated', function() {
        updateAllLeadCounters();
    });
}

/**
 * Atualiza o contador de leads após uma operação de arrastar e soltar
 * Esta função será chamada pelo script de drag and drop
 */
function updateCountersAfterDragDrop() {
    updateAllLeadCounters();
}
