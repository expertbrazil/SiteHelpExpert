/**
 * Navegação do Kanban com botões fixos
 * Implementa botões de navegação para rolar horizontalmente entre as colunas
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando navegação do Kanban com botões fixos');
    
    // Elementos do Kanban
    const kanbanBoard = document.getElementById('kanbanBoard');
    const prevButton = document.getElementById('kanbanNavPrev');
    const nextButton = document.getElementById('kanbanNavNext');
    
    if (!kanbanBoard || !prevButton || !nextButton) {
        console.error('Elementos de navegação do Kanban não encontrados');
        return;
    }
    
    // Variáveis de controle
    let currentPosition = 0;
    const columnWidth = 295; // Largura da coluna + margem
    
    // Função para calcular valores dinâmicos
    function calculateValues() {
        const columns = document.querySelectorAll('.kanban-column');
        const totalColumns = columns.length;
        const container = document.querySelector('.kanban-container');
        const containerWidth = container ? container.clientWidth : 1200;
        const visibleColumns = Math.floor(containerWidth / columnWidth);
        const maxPosition = Math.max(0, totalColumns - visibleColumns);
        
        console.log(`Total de colunas: ${totalColumns}, Largura do container: ${containerWidth}px`);
        console.log(`Colunas visíveis: ${visibleColumns}, Posição máxima: ${maxPosition}`);
        
        return { totalColumns, visibleColumns, maxPosition };
    }
    
    // Função para navegar para a esquerda
    function navigateLeft() {
        console.log('Navegando para a esquerda');
        if (currentPosition > 0) {
            currentPosition--;
            updatePosition();
        }
    }
    
    // Função para navegar para a direita
    function navigateRight() {
        console.log('Navegando para a direita');
        const { maxPosition } = calculateValues();
        if (currentPosition < maxPosition) {
            currentPosition++;
            updatePosition();
        }
    }
    
    // Função para atualizar a posição do Kanban
    function updatePosition() {
        const { maxPosition } = calculateValues();
        
        // Garantir que a posição atual esteja dentro dos limites
        currentPosition = Math.max(0, Math.min(currentPosition, maxPosition));
        
        // Aplicar a transformação
        const translateX = currentPosition * columnWidth;
        kanbanBoard.style.transform = `translateX(-${translateX}px)`;
        
        // Atualizar estado dos botões
        prevButton.disabled = currentPosition <= 0;
        nextButton.disabled = currentPosition >= maxPosition;
        
        // Adicionar/remover classes para feedback visual
        if (currentPosition <= 0) {
            prevButton.classList.add('disabled');
        } else {
            prevButton.classList.remove('disabled');
        }
        
        if (currentPosition >= maxPosition) {
            nextButton.classList.add('disabled');
        } else {
            nextButton.classList.remove('disabled');
        }
        
        console.log(`Posição atual: ${currentPosition}/${maxPosition}`);
    }
    
    // Adicionar eventos de clique
    prevButton.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        navigateLeft();
    });
    
    nextButton.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        navigateRight();
    });
    
    // Adicionar eventos de teclado
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            navigateLeft();
        } else if (e.key === 'ArrowRight') {
            navigateRight();
        }
    });
    
    // Adicionar evento de redimensionamento da janela
    window.addEventListener('resize', function() {
        updatePosition();
    });
    
    // Inicializar
    updatePosition();
    
    console.log('Navegação do Kanban inicializada com sucesso');
});
