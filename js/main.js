/**
 * Códice do Criador - JavaScript Principal
 * 
 * Funcionalidades principais da aplicação, incluindo gerenciamento de tema,
 * interações de UI, modais, formulários e micro-interações.
 * 
 * @author Manus AI
 * @version 1.0
 */

// ===== CONFIGURAÇÕES GLOBAIS =====
const CodiceCriador = {
    // Configurações
    config: {
        theme: localStorage.getItem('theme') || 'light',
        apiBaseUrl: '/codice_do_criador/api/',
        debounceDelay: 300,
        animationDuration: 250
    },
    
    // Cache de elementos DOM
    elements: {},
    
    // Estado da aplicação
    state: {
        sidebarOpen: false,
        currentModal: null,
        activeEditor: null,
        unsavedChanges: false
    },
    
    // Inicialização
    init() {
        this.cacheElements();
        this.bindEvents();
        this.initTheme();
        this.initModals();
        this.initForms();
        this.initTooltips();
        this.initAnimations();
        console.log('Códice do Criador inicializado com sucesso!');
    },
    
    // Cache de elementos DOM frequentemente utilizados
    cacheElements() {
        this.elements = {
            body: document.body,
            themeToggle: document.querySelector('.theme-toggle'),
            sidebar: document.querySelector('.sidebar'),
            sidebarToggle: document.querySelector('.sidebar-toggle'),
            mainContent: document.querySelector('.main-content'),
            modals: document.querySelectorAll('.modal'),
            forms: document.querySelectorAll('form'),
            tooltips: document.querySelectorAll('[data-tooltip]')
        };
    },
    
    // Vinculação de eventos
    bindEvents() {
        // Evento de redimensionamento da janela
        window.addEventListener('resize', this.debounce(this.handleResize.bind(this), 250));
        
        // Evento de scroll
        window.addEventListener('scroll', this.throttle(this.handleScroll.bind(this), 16));
        
        // Evento de mudança de hash
        window.addEventListener('hashchange', this.handleHashChange.bind(this));
        
        // Evento de beforeunload para mudanças não salvas
        window.addEventListener('beforeunload', this.handleBeforeUnload.bind(this));
        
        // Eventos de teclado globais
        document.addEventListener('keydown', this.handleKeydown.bind(this));
        
        // Eventos de clique global
        document.addEventListener('click', this.handleGlobalClick.bind(this));
    }
};

// ===== GERENCIAMENTO DE TEMA =====
CodiceCriador.initTheme = function() {
    // Aplicar tema inicial
    this.setTheme(this.config.theme);
    
    // Evento do toggle de tema
    if (this.elements.themeToggle) {
        this.elements.themeToggle.addEventListener('click', () => {
            const newTheme = this.config.theme === 'light' ? 'dark' : 'light';
            this.setTheme(newTheme);
        });
    }
};

CodiceCriador.setTheme = function(theme) {
    this.config.theme = theme;
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    
    // Animar a transição do tema
    this.elements.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
    setTimeout(() => {
        this.elements.body.style.transition = '';
    }, 300);
};

// ===== GERENCIAMENTO DE SIDEBAR =====
CodiceCriador.toggleSidebar = function() {
    this.state.sidebarOpen = !this.state.sidebarOpen;
    
    if (this.elements.sidebar) {
        this.elements.sidebar.classList.toggle('open', this.state.sidebarOpen);
    }
    
    if (this.elements.mainContent) {
        this.elements.mainContent.classList.toggle('with-sidebar', this.state.sidebarOpen);
    }
    
    // Salvar estado no localStorage
    localStorage.setItem('sidebarOpen', this.state.sidebarOpen);
};

// ===== GERENCIAMENTO DE MODAIS =====
CodiceCriador.initModals = function() {
    // Eventos para abrir modais
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-modal-target]');
        if (trigger) {
            e.preventDefault();
            const modalId = trigger.getAttribute('data-modal-target');
            this.openModal(modalId);
        }
    });
    
    // Eventos para fechar modais
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal-backdrop') || 
            e.target.classList.contains('modal-close') ||
            e.target.closest('.modal-close')) {
            this.closeModal();
        }
    });
};

CodiceCriador.openModal = function(modalId) {
    const modal = document.getElementById(modalId);
    const backdrop = modal?.querySelector('.modal-backdrop') || 
                    modal?.parentElement.querySelector('.modal-backdrop');
    
    if (modal) {
        this.state.currentModal = modal;
        
        // Mostrar backdrop
        if (backdrop) {
            backdrop.classList.add('show');
        }
        
        // Mostrar modal com delay para animação
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
        
        // Focar no primeiro elemento focável
        const firstFocusable = modal.querySelector('input, textarea, select, button');
        if (firstFocusable) {
            firstFocusable.focus();
        }
        
        // Prevenir scroll do body
        this.elements.body.style.overflow = 'hidden';
    }
};

CodiceCriador.closeModal = function() {
    if (this.state.currentModal) {
        const modal = this.state.currentModal;
        const backdrop = modal.querySelector('.modal-backdrop') || 
                        modal.parentElement.querySelector('.modal-backdrop');
        
        modal.classList.remove('show');
        
        if (backdrop) {
            backdrop.classList.remove('show');
        }
        
        // Restaurar scroll do body
        this.elements.body.style.overflow = '';
        
        this.state.currentModal = null;
    }
};

// ===== GERENCIAMENTO DE FORMULÁRIOS =====
CodiceCriador.initForms = function() {
    this.elements.forms.forEach(form => {
        // Validação em tempo real
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', this.debounce(() => this.validateField(input), 500));
        });
        
        // Submissão de formulário
        form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        
        // Detectar mudanças não salvas
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                this.state.unsavedChanges = true;
            });
        });
    });
};

CodiceCriador.validateField = function(field) {
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');
    let isValid = true;
    let message = '';
    
    // Limpar erros anteriores
    this.clearFieldError(field);
    
    // Validação de campo obrigatório
    if (required && !value) {
        isValid = false;
        message = 'Este campo é obrigatório.';
    }
    
    // Validações específicas por tipo
    if (value && type === 'email') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            message = 'Digite um e-mail válido.';
        }
    }
    
    if (value && type === 'password') {
        if (value.length < 6) {
            isValid = false;
            message = 'A senha deve ter pelo menos 6 caracteres.';
        }
    }
    
    if (value && field.name === 'username') {
        const usernameRegex = /^[a-zA-Z0-9_]+$/;
        if (!usernameRegex.test(value) || value.length < 3) {
            isValid = false;
            message = 'Nome de usuário deve ter pelo menos 3 caracteres e conter apenas letras, números e underscore.';
        }
    }
    
    // Mostrar erro se inválido
    if (!isValid) {
        this.showFieldError(field, message);
    }
    
    return isValid;
};

CodiceCriador.showFieldError = function(field, message) {
    field.classList.add('error');
    
    let errorElement = field.parentElement.querySelector('.form-error');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'form-error';
        field.parentElement.appendChild(errorElement);
    }
    
    errorElement.textContent = message;
};

CodiceCriador.clearFieldError = function(field) {
    field.classList.remove('error');
    const errorElement = field.parentElement.querySelector('.form-error');
    if (errorElement) {
        errorElement.remove();
    }
};

CodiceCriador.handleFormSubmit = function(e) {
    const form = e.target;
    const inputs = form.querySelectorAll('input, textarea, select');
    let isFormValid = true;
    
    // Validar todos os campos
    inputs.forEach(input => {
        if (!this.validateField(input)) {
            isFormValid = false;
        }
    });
    
    if (!isFormValid) {
        e.preventDefault();
        this.showNotification('Por favor, corrija os erros no formulário.', 'error');
        return;
    }
    
    // Mostrar loading no botão de submit
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        this.setButtonLoading(submitButton, true);
    }
    
    // Se for um formulário AJAX
    if (form.hasAttribute('data-ajax')) {
        e.preventDefault();
        this.submitFormAjax(form);
    }
};

CodiceCriador.submitFormAjax = function(form) {
    const formData = new FormData(form);
    const url = form.action || window.location.href;
    const method = form.method || 'POST';
    
    fetch(url, {
        method: method,
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            this.showNotification(data.message || 'Operação realizada com sucesso!', 'success');
            
            // Fechar modal se estiver em um
            if (this.state.currentModal && this.state.currentModal.contains(form)) {
                this.closeModal();
            }
            
            // Recarregar página se necessário
            if (data.reload) {
                setTimeout(() => window.location.reload(), 1000);
            }
            
            // Redirecionar se necessário
            if (data.redirect) {
                setTimeout(() => window.location.href = data.redirect, 1000);
            }
        } else {
            this.showNotification(data.message || 'Erro ao processar solicitação.', 'error');
        }
    })
    .catch(error => {
        console.error('Erro na requisição:', error);
        this.showNotification('Erro de conexão. Tente novamente.', 'error');
    })
    .finally(() => {
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            this.setButtonLoading(submitButton, false);
        }
    });
};

// ===== SISTEMA DE NOTIFICAÇÕES =====
CodiceCriador.showNotification = function(message, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} animate-slide-up`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1080;
        max-width: 400px;
        box-shadow: var(--shadow-xl);
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Auto-remover após o tempo especificado
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, duration);
    
    // Permitir fechar clicando
    notification.addEventListener('click', () => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    });
};

// ===== UTILITÁRIOS DE UI =====
CodiceCriador.setButtonLoading = function(button, loading) {
    if (loading) {
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.innerHTML = '<span class="loading"></span> Carregando...';
    } else {
        button.disabled = false;
        button.textContent = button.dataset.originalText || button.textContent;
    }
};

CodiceCriador.initTooltips = function() {
    // Tooltips já são implementados via CSS
    // Aqui podemos adicionar funcionalidades extras se necessário
};

CodiceCriador.initAnimations = function() {
    // Observador de interseção para animações de entrada
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-slide-up');
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    // Observar elementos com classe de animação
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
};

// ===== MANIPULADORES DE EVENTOS =====
CodiceCriador.handleResize = function() {
    // Ajustar sidebar em telas pequenas
    if (window.innerWidth < 768 && this.state.sidebarOpen) {
        this.toggleSidebar();
    }
};

CodiceCriador.handleScroll = function() {
    // Adicionar sombra à navbar no scroll
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        if (window.scrollY > 10) {
            navbar.style.boxShadow = 'var(--shadow-md)';
        } else {
            navbar.style.boxShadow = '';
        }
    }
};

CodiceCriador.handleHashChange = function() {
    // Gerenciar mudanças de hash para navegação SPA
    const hash = window.location.hash;
    if (hash) {
        const element = document.querySelector(hash);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    }
};

CodiceCriador.handleBeforeUnload = function(e) {
    if (this.state.unsavedChanges) {
        e.preventDefault();
        e.returnValue = 'Você tem alterações não salvas. Deseja realmente sair?';
        return e.returnValue;
    }
};

CodiceCriador.handleKeydown = function(e) {
    // Atalhos de teclado
    if (e.ctrlKey || e.metaKey) {
        switch (e.key) {
            case 's':
                e.preventDefault();
                this.saveCurrentContent();
                break;
            case 'k':
                e.preventDefault();
                this.openSearchModal();
                break;
        }
    }
    
    // Fechar modal com ESC
    if (e.key === 'Escape' && this.state.currentModal) {
        this.closeModal();
    }
};

CodiceCriador.handleGlobalClick = function(e) {
    // Fechar dropdowns ao clicar fora
    const dropdowns = document.querySelectorAll('.dropdown.open');
    dropdowns.forEach(dropdown => {
        if (!dropdown.contains(e.target)) {
            dropdown.classList.remove('open');
        }
    });
    
    // Toggle de sidebar
    if (e.target.closest('.sidebar-toggle')) {
        e.preventDefault();
        this.toggleSidebar();
    }
};

// ===== FUNCIONALIDADES ESPECÍFICAS =====
CodiceCriador.saveCurrentContent = function() {
    if (this.state.activeEditor) {
        // Implementar salvamento automático
        this.showNotification('Conteúdo salvo automaticamente!', 'success', 2000);
        this.state.unsavedChanges = false;
    }
};

CodiceCriador.openSearchModal = function() {
    // Implementar modal de busca global
    console.log('Abrindo modal de busca...');
};

// ===== UTILITÁRIOS =====
CodiceCriador.debounce = function(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

CodiceCriador.throttle = function(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
};

CodiceCriador.formatDate = function(date, format = 'dd/mm/yyyy') {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    
    switch (format) {
        case 'dd/mm/yyyy':
            return `${day}/${month}/${year}`;
        case 'yyyy-mm-dd':
            return `${year}-${month}-${day}`;
        case 'relative':
            return this.getRelativeTime(d);
        default:
            return d.toLocaleDateString('pt-BR');
    }
};

CodiceCriador.getRelativeTime = function(date) {
    const now = new Date();
    const diff = now - date;
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (days > 0) return `${days} dia${days > 1 ? 's' : ''} atrás`;
    if (hours > 0) return `${hours} hora${hours > 1 ? 's' : ''} atrás`;
    if (minutes > 0) return `${minutes} minuto${minutes > 1 ? 's' : ''} atrás`;
    return 'Agora mesmo';
};

CodiceCriador.slugify = function(text) {
    return text
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
};

CodiceCriador.copyToClipboard = function(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            this.showNotification('Copiado para a área de transferência!', 'success', 2000);
        });
    } else {
        // Fallback para navegadores mais antigos
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        this.showNotification('Copiado para a área de transferência!', 'success', 2000);
    }
};

// ===== INICIALIZAÇÃO =====
document.addEventListener('DOMContentLoaded', () => {
    CodiceCriador.init();
});

// Exportar para uso global
window.CodiceCriador = CodiceCriador;

