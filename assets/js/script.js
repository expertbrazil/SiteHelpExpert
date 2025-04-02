// HelpExpert - Scripts Personalizados

document.addEventListener('DOMContentLoaded', function() {
    // Animação de scroll suave para links de âncora
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Validação e envio do formulário de contato
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Preparar para envio do formulário
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...';
            
            // Coletar dados do formulário
            const formData = new FormData(this);
            
            // Enviar dados via AJAX
            fetch('process_contact.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                const formElements = this.elements;
                for (let i = 0; i < formElements.length; i++) {
                    formElements[i].disabled = true;
                }
                
                // Exibir mensagem de sucesso ou erro
                const messageDiv = document.createElement('div');
                messageDiv.className = data.success ? 'alert alert-success mt-3' : 'alert alert-danger mt-3';
                messageDiv.innerHTML = data.success ? 
                    '<i class="bi bi-check-circle-fill me-2"></i> ' + data.message : 
                    '<i class="bi bi-exclamation-triangle-fill me-2"></i> ' + data.message;
                
                this.appendChild(messageDiv);
                
                // Redirecionar para página de agradecimento se for sucesso
                if (data.success) {
                    console.log('Redirecionando para: ' + (data.redirect || 'obrigado.html'));
                    setTimeout(() => {
                        window.location.href = data.redirect || 'obrigado.html';
                    }, 2000);
                } else {
                    // Se for erro, apenas habilitar o formulário após 3 segundos
                    setTimeout(() => {
                        messageDiv.remove();
                        
                        for (let i = 0; i < formElements.length; i++) {
                            formElements[i].disabled = false;
                        }
                    }, 3000);
                }
                
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            })
            .catch(error => {
                console.error('Erro:', error);
                
                // Exibir mensagem de erro
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mt-3';
                errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i> Ocorreu um erro ao enviar o formulário. Por favor, tente novamente.';
                
                this.appendChild(errorDiv);
                
                // Remover mensagem após 3 segundos
                setTimeout(() => {
                    errorDiv.remove();
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }, 3000);
            });
        });
    }

    // Animação para os cards na seção de benefícios
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.card, .pricing-card, .testimonial-card, .step-item');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementPosition < windowHeight - 100) {
                element.classList.add('animated');
            }
        });
    };

    // Executar animação no carregamento e no scroll
    animateOnScroll();
    window.addEventListener('scroll', animateOnScroll);
    
    // Botão Voltar ao Topo
    const backToTopButton = document.querySelector('.back-to-top');
    
    if (backToTopButton) {
        // Mostrar/esconder botão conforme scroll
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('active');
            } else {
                backToTopButton.classList.remove('active');
            }
        });
        
        // Scroll suave ao topo quando clicado
        backToTopButton.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Efeito de scroll na navegação
    const navbar = document.querySelector('.navbar');
    
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }
    
    // Destacar link ativo na navegação
    const sections = document.querySelectorAll('section[id]');
    
    window.addEventListener('scroll', () => {
        let current = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100;
            const sectionHeight = section.offsetHeight;
            
            if (window.pageYOffset >= sectionTop && window.pageYOffset < sectionTop + sectionHeight) {
                current = section.getAttribute('id');
            }
        });
        
        document.querySelectorAll('.navbar .nav-link').forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });
    
    // Novas animações personalizadas
    const animateElements = function() {
        // Selecionar todos os elementos com classes de animação
        const fadeInElements = document.querySelectorAll('.fade-in:not(.visible)');
        const scaleInElements = document.querySelectorAll('.scale-in:not(.visible)');
        const slideInLeftElements = document.querySelectorAll('.slide-in-left:not(.visible)');
        const slideInRightElements = document.querySelectorAll('.slide-in-right:not(.visible)');
        const rotateInElements = document.querySelectorAll('.rotate-in:not(.visible)');
        
        // Função para verificar se um elemento está visível na viewport
        const isElementInViewport = function(el) {
            const rect = el.getBoundingClientRect();
            return (
                rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.8
            );
        };
        
        // Aplicar animações quando elementos estiverem visíveis
        fadeInElements.forEach(element => {
            if (isElementInViewport(element)) {
                element.classList.add('visible');
            }
        });
        
        scaleInElements.forEach(element => {
            if (isElementInViewport(element)) {
                element.classList.add('visible');
            }
        });
        
        slideInLeftElements.forEach(element => {
            if (isElementInViewport(element)) {
                element.classList.add('visible');
            }
        });
        
        slideInRightElements.forEach(element => {
            if (isElementInViewport(element)) {
                element.classList.add('visible');
            }
        });
        
        rotateInElements.forEach(element => {
            if (isElementInViewport(element)) {
                element.classList.add('visible');
            }
        });
    };
    
    // Executar animações no carregamento e no scroll
    animateElements();
    window.addEventListener('scroll', animateElements);
    
    // Efeito parallax para fundos
    const parallaxBackgrounds = document.querySelectorAll('.parallax-bg');
    
    window.addEventListener('scroll', () => {
        const scrollPosition = window.pageYOffset;
        
        parallaxBackgrounds.forEach(background => {
            const parent = background.parentElement;
            const speed = 0.5; // Velocidade do efeito parallax
            
            // Calcular a posição do fundo com base no scroll
            const yPos = -(scrollPosition - parent.offsetTop) * speed;
            
            // Aplicar a transformação
            background.style.transform = `translateY(${yPos}px)`;
        });
    });
    
    // Contador para números
    const startCounters = function() {
        const counters = document.querySelectorAll('.counter');
        
        counters.forEach(counter => {
            // Verificar se o contador já foi iniciado
            if (counter.classList.contains('counted')) return;
            
            const rect = counter.getBoundingClientRect();
            
            // Verificar se o contador está visível na viewport
            if (rect.top <= window.innerHeight && rect.bottom >= 0) {
                counter.classList.add('counted');
                
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2000; // Duração da animação em ms
                const step = Math.ceil(target / (duration / 16)); // 60fps
                
                let current = 0;
                const updateCounter = () => {
                    current += step;
                    
                    if (current >= target) {
                        counter.textContent = target.toLocaleString();
                    } else {
                        counter.textContent = current.toLocaleString();
                        requestAnimationFrame(updateCounter);
                    }
                };
                
                updateCounter();
            }
        });
    };
    
    // Iniciar contadores no scroll
    window.addEventListener('scroll', startCounters);
    startCounters(); // Verificar contadores visíveis no carregamento
    
    // Efeito de digitação para títulos
    const typingElements = document.querySelectorAll('.typing-effect');
    
    typingElements.forEach(element => {
        const text = element.textContent;
        element.textContent = '';
        
        const isElementVisible = function(el) {
            const rect = el.getBoundingClientRect();
            return rect.top <= window.innerHeight && rect.bottom >= 0;
        };
        
        const typeText = function() {
            if (!element.classList.contains('typing-started') && isElementVisible(element)) {
                element.classList.add('typing-started');
                
                let i = 0;
                const speed = 50; // Velocidade de digitação em ms
                
                const type = function() {
                    if (i < text.length) {
                        element.textContent += text.charAt(i);
                        i++;
                        setTimeout(type, speed);
                    }
                };
                
                type();
            }
        };
        
        // Iniciar efeito de digitação quando o elemento estiver visível
        window.addEventListener('scroll', typeText);
        typeText(); // Verificar elementos visíveis no carregamento
    });
    
    // Processamento do formulário de leads (demonstração)
    const leadForm = document.getElementById('leadForm');
    if (leadForm) {
        leadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Preparar para envio do formulário
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...';
            
            // Coletar dados do formulário
            const formData = new FormData(this);
            
            // Enviar dados via AJAX
            fetch('process_lead.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                const formElements = this.elements;
                for (let i = 0; i < formElements.length; i++) {
                    formElements[i].disabled = true;
                }
                
                // Exibir mensagem de sucesso ou erro
                const messageDiv = document.createElement('div');
                messageDiv.className = data.success ? 'alert alert-success mt-3' : 'alert alert-danger mt-3';
                messageDiv.innerHTML = data.success ? 
                    '<i class="bi bi-check-circle-fill me-2"></i> ' + data.message : 
                    '<i class="bi bi-exclamation-triangle-fill me-2"></i> ' + data.message;
                
                this.appendChild(messageDiv);
                
                // Redirecionar para página de agradecimento se for sucesso
                if (data.success) {
                    console.log('Redirecionando para: ' + (data.redirect || 'obrigado.html'));
                    setTimeout(() => {
                        window.location.href = data.redirect || 'obrigado.html';
                    }, 2000);
                } else {
                    // Se for erro, apenas habilitar o formulário após 3 segundos
                    setTimeout(() => {
                        messageDiv.remove();
                        
                        for (let i = 0; i < formElements.length; i++) {
                            formElements[i].disabled = false;
                        }
                    }, 3000);
                }
                
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            })
            .catch(error => {
                console.error('Erro:', error);
                
                // Exibir mensagem de erro
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mt-3';
                errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i> Ocorreu um erro ao enviar o formulário. Por favor, tente novamente.';
                
                this.appendChild(errorDiv);
                
                // Remover mensagem após 3 segundos
                setTimeout(() => {
                    errorDiv.remove();
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }, 3000);
            });
        });
    }
});
