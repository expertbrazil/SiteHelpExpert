/**
 * Navegação Simples para o Kanban do HelpExpert
 * Implementa botões de navegação para rolar horizontalmente entre as colunas
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando navegação simples do Kanban');
    
    // Obter elementos
    const kanbanBoard = document.getElementById('kanbanBoard');
    const prevButton = document.getElementById('kanbanNavPrev');
    const nextButton = document.getElementById('kanbanNavNext');
    const navButtons = document.querySelector('.kanban-nav-buttons');
    
    if (!kanbanBoard || !prevButton || !nextButton) {
        console.error('Elementos de navegação não encontrados');
        return;
    }
    
    // Garantir que os botões estejam visíveis inicialmente
    if (navButtons) {
        navButtons.style.display = 'flex';
    }
    
    // Variáveis para controlar a navegação
    let currentPosition = 0;
    const columnWidth = 290; // Largura da coluna + margem
    
    // Função para calcular valores dinâmicos
    function calculateValues() {
        const columns = document.querySelectorAll('.kanban-column');
        const totalColumns = columns.length;
        const viewport = document.querySelector('.kanban-viewport');
        const visibleWidth = viewport ? viewport.clientWidth : 1200;
        const visibleColumns = Math.max(1, Math.floor(visibleWidth / columnWidth));
        const maxPosition = Math.max(0, totalColumns - visibleColumns);
        
        console.log(`Total de colunas: ${totalColumns}, Largura visível: ${visibleWidth}px`);
        console.log(`Colunas visíveis: ${visibleColumns}, Posição máxima: ${maxPosition}`);
        
        return { totalColumns, visibleColumns, maxPosition };
    }
    
    // Função para navegar para a esquerda
    function navigateLeft() {
        console.log("Botão anterior clicado");
        if (currentPosition > 0) {
            currentPosition--;
            updatePosition();
        }
    }
    
    // Função para navegar para a direita
    function navigateRight() {
        console.log("Botão próximo clicado");
        const { maxPosition } = calculateValues();
        if (currentPosition < maxPosition) {
            currentPosition++;
            updatePosition();
        }
    }
    
    // Função para atualizar a posição do kanban
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
        
        // Adicionar/remover classes visuais
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
        
        console.log(`Navegado para posição: ${currentPosition}/${maxPosition}`);
    }
    
    // Adicionar eventos de clique diretamente
    prevButton.addEventListener('click', function(e) {
        e.preventDefault();
        navigateLeft();
    });
    
    nextButton.addEventListener('click', function(e) {
        e.preventDefault();
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
        // Recalcular valores e atualizar posição
        updatePosition();
    });
    
    // Inicializar
    updatePosition();
    
    console.log('Navegação do Kanban inicializada com sucesso');
});
