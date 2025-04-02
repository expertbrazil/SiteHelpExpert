/**
 * Script para contar e exibir o número de colunas do Kanban
 */
document.addEventListener('DOMContentLoaded', function() {
    // Função para contar e exibir o número de colunas
    function countAndDisplayColumns() {
        // Contar colunas no DOM
        const columns = document.querySelectorAll('.kanban-column');
        const totalColumns = columns.length;
        
        // Exibir o contador
        const counterElement = document.getElementById('totalColumnsCount');
        if (counterElement) {
            counterElement.textContent = `Total de Colunas: ${totalColumns}`;
            
            // Adicionar cor com base no número de colunas
            if (totalColumns < 5) {
                counterElement.classList.remove('bg-info', 'bg-success');
                counterElement.classList.add('bg-warning');
            } else if (totalColumns === 5) {
                counterElement.classList.remove('bg-warning', 'bg-success');
                counterElement.classList.add('bg-info');
            } else {
                counterElement.classList.remove('bg-warning', 'bg-info');
                counterElement.classList.add('bg-success');
            }
        }
        
        console.log(`Total de colunas no Kanban: ${totalColumns}`);
        
        // Verificar se alguma coluna está faltando
        if (totalColumns < 5) {
            console.warn(`Atenção: Estão faltando ${5 - totalColumns} colunas no Kanban!`);
        }
    }
    
    // Executar a contagem inicial
    countAndDisplayColumns();
    
    // Adicionar um observador de mutação para atualizar a contagem quando o DOM mudar
    const kanbanBoard = document.getElementById('kanbanBoard');
    if (kanbanBoard) {
        const observer = new MutationObserver(function(mutations) {
            countAndDisplayColumns();
        });
        
        observer.observe(kanbanBoard, { 
            childList: true,
            subtree: true
        });
    }
    
    // Criar um link para a página de contagem de colunas
    const counterElement = document.getElementById('totalColumnsCount');
    if (counterElement) {
        counterElement.style.cursor = 'pointer';
        counterElement.addEventListener('click', function() {
            window.location.href = 'column_count.php';
        });
    }
});
