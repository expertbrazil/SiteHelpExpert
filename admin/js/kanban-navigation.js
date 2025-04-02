/**
 * Navegação Horizontal para o Kanban do HelpExpert
 * Implementa botões de navegação para rolar horizontalmente entre as colunas
 */

// Garantir que o script seja executado após o carregamento do DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado, inicializando navegação do Kanban');
    // Inicializar a navegação do kanban
    setupKanbanNavigation();
});

/**
 * Configura a navegação horizontal do kanban
 */
function setupKanbanNavigation() {
    const kanbanContainer = document.querySelector('.kanban-container');
    const kanbanViewport = document.querySelector('.kanban-viewport');
    const kanbanBoard = document.getElementById('kanbanBoard');
    const prevButton = document.getElementById('kanbanNavPrev');
    const nextButton = document.getElementById('kanbanNavNext');
    
    console.log('Elementos de navegação:', {
        kanbanContainer: kanbanContainer,
        kanbanViewport: kanbanViewport,
        kanbanBoard: kanbanBoard,
        prevButton: prevButton,
        nextButton: nextButton
    });
    
    if (!kanbanBoard || !prevButton || !nextButton || !kanbanViewport) {
        console.error('Elementos de navegação do Kanban não encontrados');
        return;
    }
    
    // Variáveis para controlar a navegação
    let currentPosition = 0;
    const columnWidth = 290; // Largura da coluna + margem
    const columns = document.querySelectorAll('.kanban-column');
    const totalColumns = columns.length;
    
    console.log(`Total de colunas: ${totalColumns}, Largura da coluna: ${columnWidth}px`);
    
    // Calcular quantas colunas são visíveis na tela
    const calculateVisibleColumns = () => {
        const visibleWidth = kanbanViewport.clientWidth;
        console.log(`Largura visível do viewport: ${visibleWidth}px`);
        return Math.floor(visibleWidth / columnWidth);
    };
    
    let visibleColumns = calculateVisibleColumns();
    let maxPosition = Math.max(0, totalColumns - visibleColumns);
    
    console.log(`Colunas visíveis: ${visibleColumns}, Posição máxima: ${maxPosition}`);
    
    // Função para atualizar o estado dos botões
    const updateButtonState = () => {
        // Desativar/ativar botão de voltar
        if (currentPosition <= 0) {
            prevButton.classList.add('disabled');
            prevButton.setAttribute('disabled', 'disabled');
        } else {
            prevButton.classList.remove('disabled');
            prevButton.removeAttribute('disabled');
        }
        
        // Desativar/ativar botão de avançar
        if (currentPosition >= maxPosition) {
            nextButton.classList.add('disabled');
            nextButton.setAttribute('disabled', 'disabled');
        } else {
            nextButton.classList.remove('disabled');
            nextButton.removeAttribute('disabled');
        }
        
        console.log(`Estado dos botões atualizado: posição atual = ${currentPosition}`);
    };
    
    // Função para navegar para a posição especificada
    const navigateTo = (position) => {
        // Limitar a posição dentro dos limites válidos
        position = Math.max(0, Math.min(position, maxPosition));
        currentPosition = position;
        
        // Aplicar transformação para mover o kanban
        const translateX = position * columnWidth;
        console.log(`Navegando para posição ${position}, translateX: -${translateX}px`);
        kanbanBoard.style.transform = `translateX(-${translateX}px)`;
        
        // Atualizar estado dos botões
        updateButtonState();
    };
    
    // Adicionar eventos de clique aos botões
    prevButton.addEventListener('click', function(e) {
        console.log('Botão anterior clicado');
        navigateTo(currentPosition - 1);
    });
    
    nextButton.addEventListener('click', function(e) {
        console.log('Botão próximo clicado');
        navigateTo(currentPosition + 1);
    });
    
    // Adicionar evento de redimensionamento da janela
    window.addEventListener('resize', () => {
        // Recalcular variáveis ao redimensionar
        visibleColumns = calculateVisibleColumns();
        maxPosition = Math.max(0, totalColumns - visibleColumns);
        
        console.log(`Janela redimensionada: colunas visíveis = ${visibleColumns}, posição máxima = ${maxPosition}`);
        
        // Ajustar posição atual se necessário
        if (currentPosition > maxPosition) {
            navigateTo(maxPosition);
        } else {
            updateButtonState();
        }
    });
    
    // Inicializar estado dos botões
    updateButtonState();
    
    // Adicionar navegação por teclado
    document.addEventListener('keydown', (e) => {
        // Só navegar se o foco estiver no kanban ou em seus elementos
        const activeElement = document.activeElement;
        const isKanbanFocused = kanbanContainer.contains(activeElement) || 
                               activeElement === document.body;
        
        if (isKanbanFocused) {
            if (e.key === 'ArrowLeft' && currentPosition > 0) {
                console.log('Tecla seta esquerda pressionada');
                navigateTo(currentPosition - 1);
                e.preventDefault();
            } else if (e.key === 'ArrowRight' && currentPosition < maxPosition) {
                console.log('Tecla seta direita pressionada');
                navigateTo(currentPosition + 1);
                e.preventDefault();
            }
        }
    });
    
    console.log('Navegação do Kanban inicializada com sucesso');
}
