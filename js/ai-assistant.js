/**
 * Códice do Criador - Assistente de IA
 * 
 * Interface JavaScript para interação com o assistente de IA,
 * incluindo sugestões de conteúdo, geração de ideias e melhorias.
 * 
 * @author Manus AI
 * @version 1.0
 */

// ===== ASSISTENTE DE IA =====
const AIAssistant = {
    // Configurações
    config: {
        apiEndpoint: 'api/ai-assistant.php',
        maxRetries: 3,
        retryDelay: 1000
    },
    
    // Estado
    state: {
        isLoading: false,
        currentRequest: null,
        suggestions: [],
        isInitialized: false
    },
    
    // Cache de elementos
    elements: {},
    
    // Inicialização
    init() {
        this.cacheElements();
        this.bindEvents();
        this.createAIPanel();
        this.state.isInitialized = true;
        console.log('Assistente de IA inicializado');
    },
    
    // Cache de elementos DOM
    cacheElements() {
        this.elements = {
            aiButton: document.getElementById('ai-assistant-btn'),
            aiPanel: document.getElementById('ai-panel'),
            contentField: document.getElementById('article-content'),
            titleField: document.getElementById('article-title'),
            categorySelect: document.getElementById('article-category')
        };
    },
    
    // Vincular eventos
    bindEvents() {
        // Botão principal do assistente
        if (this.elements.aiButton) {
            this.elements.aiButton.addEventListener('click', () => this.togglePanel());
        }
        
        // Atalho de teclado (Ctrl+Shift+A)
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.shiftKey && e.key === 'A') {
                e.preventDefault();
                this.togglePanel();
            }
        });
    },
    
    // Criar painel do assistente de IA
    createAIPanel() {
        // Verificar se já existe
        if (document.getElementById('ai-panel')) return;
        
        const panel = document.createElement('div');
        panel.id = 'ai-panel';
        panel.className = 'ai-panel hidden';
        panel.innerHTML = `
            <div class="ai-panel-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-blue-500 rounded-lg flex items-center justify-center">
                        <span class="text-white text-sm">🤖</span>
                    </div>
                    <div>
                        <h3 class="font-semibold">Assistente de IA</h3>
                        <p class="text-xs text-muted">Seu parceiro criativo</p>
                    </div>
                </div>
                <button type="button" class="ai-panel-close btn btn-ghost btn-sm">
                    ✕
                </button>
            </div>
            
            <div class="ai-panel-content">
                <div class="ai-tabs">
                    <button type="button" class="ai-tab active" data-tab="suggestions">
                        💡 Sugestões
                    </button>
                    <button type="button" class="ai-tab" data-tab="ideas">
                        🌟 Ideias
                    </button>
                    <button type="button" class="ai-tab" data-tab="names">
                        📝 Nomes
                    </button>
                    <button type="button" class="ai-tab" data-tab="improve">
                        ✨ Melhorar
                    </button>
                </div>
                
                <div class="ai-tab-content">
                    <!-- Sugestões de Conteúdo -->
                    <div class="ai-tab-panel active" data-panel="suggestions">
                        <div class="mb-4">
                            <p class="text-sm text-muted mb-3">
                                Obtenha sugestões criativas para desenvolver seu artigo.
                            </p>
                            <button type="button" class="btn btn-primary btn-sm w-full" onclick="AIAssistant.generateContentSuggestions()">
                                🎯 Gerar Sugestões
                            </button>
                        </div>
                        <div id="ai-suggestions-result" class="ai-result-area"></div>
                    </div>
                    
                    <!-- Ideias de Artigos -->
                    <div class="ai-tab-panel" data-panel="ideas">
                        <div class="mb-4">
                            <p class="text-sm text-muted mb-3">
                                Descubra novas ideias de artigos para seu mundo.
                            </p>
                            <div class="form-group mb-3">
                                <select id="ai-ideas-category" class="form-select form-select-sm">
                                    <option value="">Todas as categorias</option>
                                    <option value="Personagens">Personagens</option>
                                    <option value="Locais">Locais</option>
                                    <option value="Itens">Itens</option>
                                    <option value="Criaturas">Criaturas</option>
                                    <option value="História">História</option>
                                    <option value="Facções">Facções</option>
                                    <option value="Eventos">Eventos</option>
                                    <option value="Conceitos">Conceitos</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm w-full" onclick="AIAssistant.generateArticleIdeas()">
                                💫 Gerar Ideias
                            </button>
                        </div>
                        <div id="ai-ideas-result" class="ai-result-area"></div>
                    </div>
                    
                    <!-- Gerador de Nomes -->
                    <div class="ai-tab-panel" data-panel="names">
                        <div class="mb-4">
                            <p class="text-sm text-muted mb-3">
                                Gere nomes únicos e criativos.
                            </p>
                            <div class="form-group mb-3">
                                <select id="ai-names-type" class="form-select form-select-sm">
                                    <option value="person">Personagens</option>
                                    <option value="place">Locais</option>
                                    <option value="item">Itens</option>
                                    <option value="creature">Criaturas</option>
                                    <option value="faction">Facções</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <input type="text" id="ai-names-context" class="form-input form-input-sm" 
                                       placeholder="Contexto adicional (opcional)">
                            </div>
                            <button type="button" class="btn btn-primary btn-sm w-full" onclick="AIAssistant.generateNames()">
                                🎭 Gerar Nomes
                            </button>
                        </div>
                        <div id="ai-names-result" class="ai-result-area"></div>
                    </div>
                    
                    <!-- Melhorar Texto -->
                    <div class="ai-tab-panel" data-panel="improve">
                        <div class="mb-4">
                            <p class="text-sm text-muted mb-3">
                                Analise e melhore seu conteúdo existente.
                            </p>
                            <button type="button" class="btn btn-primary btn-sm w-full" onclick="AIAssistant.analyzeContent()">
                                🔍 Analisar Conteúdo
                            </button>
                        </div>
                        <div id="ai-improve-result" class="ai-result-area"></div>
                    </div>
                </div>
            </div>
            
            <div class="ai-panel-footer">
                <div class="text-xs text-muted text-center">
                    Pressione <kbd>Ctrl+Shift+A</kbd> para abrir/fechar
                </div>
            </div>
        `;
        
        // Adicionar estilos
        const style = document.createElement('style');
        style.textContent = `
            .ai-panel {
                position: fixed;
                top: 50%;
                right: 20px;
                transform: translateY(-50%);
                width: 350px;
                max-height: 80vh;
                background: var(--color-surface-elevated);
                border: 1px solid var(--color-border);
                border-radius: var(--radius-lg);
                box-shadow: var(--shadow-2xl);
                z-index: 1000;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }
            
            .ai-panel.hidden {
                display: none;
            }
            
            .ai-panel-header {
                padding: var(--space-4);
                border-bottom: 1px solid var(--color-border);
                display: flex;
                align-items: center;
                justify-content: space-between;
                background: var(--color-surface);
            }
            
            .ai-panel-content {
                flex: 1;
                overflow: hidden;
                display: flex;
                flex-direction: column;
            }
            
            .ai-tabs {
                display: flex;
                border-bottom: 1px solid var(--color-border);
                background: var(--color-surface);
            }
            
            .ai-tab {
                flex: 1;
                padding: var(--space-2) var(--space-1);
                border: none;
                background: transparent;
                color: var(--color-text-secondary);
                font-size: 0.75rem;
                cursor: pointer;
                transition: all 0.2s;
            }
            
            .ai-tab:hover {
                background: var(--color-surface-elevated);
                color: var(--color-text);
            }
            
            .ai-tab.active {
                background: var(--color-primary);
                color: white;
            }
            
            .ai-tab-content {
                flex: 1;
                overflow-y: auto;
            }
            
            .ai-tab-panel {
                padding: var(--space-4);
                display: none;
            }
            
            .ai-tab-panel.active {
                display: block;
            }
            
            .ai-result-area {
                min-height: 100px;
                max-height: 300px;
                overflow-y: auto;
                border: 1px solid var(--color-border);
                border-radius: var(--radius-md);
                padding: var(--space-3);
                background: var(--color-surface);
                font-size: 0.875rem;
                line-height: 1.5;
            }
            
            .ai-result-area:empty::before {
                content: "Os resultados aparecerão aqui...";
                color: var(--color-text-muted);
                font-style: italic;
            }
            
            .ai-panel-footer {
                padding: var(--space-3);
                border-top: 1px solid var(--color-border);
                background: var(--color-surface);
            }
            
            .ai-suggestion-item {
                padding: var(--space-2);
                margin-bottom: var(--space-2);
                background: var(--color-background);
                border-radius: var(--radius-md);
                border-left: 3px solid var(--color-primary);
                cursor: pointer;
                transition: all 0.2s;
            }
            
            .ai-suggestion-item:hover {
                background: var(--color-surface-elevated);
                transform: translateX(2px);
            }
            
            .ai-loading {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: var(--space-6);
                color: var(--color-text-muted);
            }
            
            .ai-loading::before {
                content: "🤖";
                margin-right: var(--space-2);
                animation: pulse 1.5s infinite;
            }
            
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }
            
            kbd {
                background: var(--color-surface-elevated);
                border: 1px solid var(--color-border);
                border-radius: 3px;
                padding: 1px 4px;
                font-size: 0.75rem;
                font-family: monospace;
            }
        `;
        
        document.head.appendChild(style);
        document.body.appendChild(panel);
        
        // Vincular eventos do painel
        this.bindPanelEvents(panel);
        
        // Atualizar cache
        this.elements.aiPanel = panel;
    },
    
    // Vincular eventos do painel
    bindPanelEvents(panel) {
        // Fechar painel
        const closeBtn = panel.querySelector('.ai-panel-close');
        closeBtn.addEventListener('click', () => this.hidePanel());
        
        // Tabs
        const tabs = panel.querySelectorAll('.ai-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tabName = tab.dataset.tab;
                this.switchTab(tabName);
            });
        });
        
        // Fechar com ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !panel.classList.contains('hidden')) {
                this.hidePanel();
            }
        });
    },
    
    // Alternar visibilidade do painel
    togglePanel() {
        if (!this.elements.aiPanel) {
            this.createAIPanel();
        }
        
        if (this.elements.aiPanel.classList.contains('hidden')) {
            this.showPanel();
        } else {
            this.hidePanel();
        }
    },
    
    // Mostrar painel
    showPanel() {
        if (this.elements.aiPanel) {
            this.elements.aiPanel.classList.remove('hidden');
        }
    },
    
    // Esconder painel
    hidePanel() {
        if (this.elements.aiPanel) {
            this.elements.aiPanel.classList.add('hidden');
        }
    },
    
    // Trocar aba
    switchTab(tabName) {
        // Atualizar tabs
        const tabs = document.querySelectorAll('.ai-tab');
        tabs.forEach(tab => {
            if (tab.dataset.tab === tabName) {
                tab.classList.add('active');
            } else {
                tab.classList.remove('active');
            }
        });
        
        // Atualizar painéis
        const panels = document.querySelectorAll('.ai-tab-panel');
        panels.forEach(panel => {
            if (panel.dataset.panel === tabName) {
                panel.classList.add('active');
            } else {
                panel.classList.remove('active');
            }
        });
    },
    
    // Gerar sugestões de conteúdo
    async generateContentSuggestions() {
        const resultArea = document.getElementById('ai-suggestions-result');
        const title = this.elements.titleField?.value || '';
        const category = this.elements.categorySelect?.selectedOptions[0]?.text || '';
        const content = this.elements.contentField?.value || '';
        
        if (!title.trim()) {
            this.showError(resultArea, 'Por favor, insira um título para o artigo.');
            return;
        }
        
        this.showLoading(resultArea);
        
        try {
            const response = await this.makeRequest('generate_content_suggestions', {
                title,
                category,
                existing_content: content,
                world_id: this.getCurrentWorldId()
            });
            
            if (response.success) {
                this.displaySuggestions(resultArea, response.suggestions);
            } else {
                this.showError(resultArea, response.message);
            }
        } catch (error) {
            this.showError(resultArea, 'Erro ao gerar sugestões. Tente novamente.');
        }
    },
    
    // Gerar ideias de artigos
    async generateArticleIdeas() {
        const resultArea = document.getElementById('ai-ideas-result');
        const category = document.getElementById('ai-ideas-category').value;
        
        this.showLoading(resultArea);
        
        try {
            const response = await this.makeRequest('generate_article_ideas', {
                category,
                world_id: this.getCurrentWorldId()
            });
            
            if (response.success) {
                this.displayIdeas(resultArea, response.ideas);
            } else {
                this.showError(resultArea, response.message);
            }
        } catch (error) {
            this.showError(resultArea, 'Erro ao gerar ideias. Tente novamente.');
        }
    },
    
    // Gerar nomes
    async generateNames() {
        const resultArea = document.getElementById('ai-names-result');
        const type = document.getElementById('ai-names-type').value;
        const context = document.getElementById('ai-names-context').value;
        
        this.showLoading(resultArea);
        
        try {
            const response = await this.makeRequest('generate_names', {
                type,
                context,
                count: 10
            });
            
            if (response.success) {
                this.displayNames(resultArea, response.names);
            } else {
                this.showError(resultArea, response.message);
            }
        } catch (error) {
            this.showError(resultArea, 'Erro ao gerar nomes. Tente novamente.');
        }
    },
    
    // Analisar conteúdo
    async analyzeContent() {
        const resultArea = document.getElementById('ai-improve-result');
        const content = this.elements.contentField?.value || '';
        
        if (!content.trim()) {
            this.showError(resultArea, 'Por favor, escreva algum conteúdo para analisar.');
            return;
        }
        
        this.showLoading(resultArea);
        
        try {
            const response = await this.makeRequest('analyze_content', {
                content,
                type: 'article'
            });
            
            if (response.success) {
                this.displayAnalysis(resultArea, response.analysis);
            } else {
                this.showError(resultArea, response.message);
            }
        } catch (error) {
            this.showError(resultArea, 'Erro ao analisar conteúdo. Tente novamente.');
        }
    },
    
    // Fazer requisição para API
    async makeRequest(action, data) {
        const response = await fetch(this.config.apiEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action,
                ...data
            })
        });
        
        if (!response.ok) {
            throw new Error('Erro na requisição');
        }
        
        return await response.json();
    },
    
    // Exibir loading
    showLoading(container) {
        container.innerHTML = '<div class="ai-loading">Gerando...</div>';
    },
    
    // Exibir erro
    showError(container, message) {
        container.innerHTML = `
            <div class="text-error text-sm">
                <strong>Erro:</strong> ${message}
            </div>
        `;
    },
    
    // Exibir sugestões
    displaySuggestions(container, suggestions) {
        if (!suggestions || !suggestions.formatted_content) {
            this.showError(container, 'Nenhuma sugestão recebida.');
            return;
        }
        
        container.innerHTML = `
            <div class="space-y-3">
                <div class="ai-suggestion-item" onclick="AIAssistant.insertSuggestion('${suggestions.raw_content.replace(/'/g, "\\'")}')">
                    <div class="text-sm font-medium mb-1">💡 Sugestões Criativas</div>
                    <div class="text-sm">${suggestions.formatted_content}</div>
                    <div class="text-xs text-muted mt-2">Clique para inserir no editor</div>
                </div>
            </div>
        `;
    },
    
    // Exibir ideias
    displayIdeas(container, ideas) {
        if (!ideas || ideas.length === 0) {
            this.showError(container, 'Nenhuma ideia gerada.');
            return;
        }
        
        const ideasHtml = ideas.map(idea => `
            <div class="ai-suggestion-item" onclick="AIAssistant.createArticleFromIdea('${idea.title.replace(/'/g, "\\'")}')">
                <div class="text-sm font-medium">${idea.title}</div>
                ${idea.description ? `<div class="text-xs text-muted mt-1">${idea.description}</div>` : ''}
                <div class="text-xs text-primary mt-2">Clique para criar artigo</div>
            </div>
        `).join('');
        
        container.innerHTML = `<div class="space-y-2">${ideasHtml}</div>`;
    },
    
    // Exibir nomes
    displayNames(container, names) {
        if (!names || names.length === 0) {
            this.showError(container, 'Nenhum nome gerado.');
            return;
        }
        
        const namesHtml = names.map(name => `
            <div class="ai-suggestion-item" onclick="AIAssistant.insertName('${name.replace(/'/g, "\\'")}')">
                <div class="text-sm">${name}</div>
                <div class="text-xs text-muted">Clique para inserir</div>
            </div>
        `).join('');
        
        container.innerHTML = `<div class="space-y-1">${namesHtml}</div>`;
    },
    
    // Exibir análise
    displayAnalysis(container, analysis) {
        if (!analysis || !analysis.formatted_analysis) {
            this.showError(container, 'Nenhuma análise recebida.');
            return;
        }
        
        container.innerHTML = `
            <div class="space-y-3">
                <div class="text-sm">
                    <div class="font-medium mb-2">📊 Análise do Conteúdo</div>
                    <div>${analysis.formatted_analysis}</div>
                </div>
            </div>
        `;
    },
    
    // Inserir sugestão no editor
    insertSuggestion(content) {
        if (this.elements.contentField) {
            const currentContent = this.elements.contentField.value;
            const newContent = currentContent + (currentContent ? '\n\n' : '') + content;
            this.elements.contentField.value = newContent;
            this.elements.contentField.focus();
            
            // Trigger change event
            this.elements.contentField.dispatchEvent(new Event('input'));
            
            CodiceCriador.showNotification('Sugestão inserida no editor!', 'success');
        }
    },
    
    // Inserir nome
    insertName(name) {
        if (this.elements.contentField) {
            const textarea = this.elements.contentField;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const beforeText = textarea.value.substring(0, start);
            const afterText = textarea.value.substring(end);
            
            textarea.value = beforeText + name + afterText;
            
            const newPosition = start + name.length;
            textarea.setSelectionRange(newPosition, newPosition);
            textarea.focus();
            
            // Trigger change event
            textarea.dispatchEvent(new Event('input'));
            
            CodiceCriador.showNotification('Nome inserido!', 'success');
        }
    },
    
    // Criar artigo a partir de ideia
    createArticleFromIdea(title) {
        const worldId = this.getCurrentWorldId();
        if (worldId) {
            const url = `article-create.php?world_id=${worldId}&title=${encodeURIComponent(title)}`;
            window.open(url, '_blank');
        }
    },
    
    // Obter ID do mundo atual
    getCurrentWorldId() {
        return document.querySelector('[data-world-id]')?.dataset.worldId || null;
    }
};

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    // Verificar se estamos em uma página que precisa do assistente
    if (document.querySelector('.editor-container') || document.querySelector('[data-world-id]')) {
        AIAssistant.init();
        
        // Adicionar botão do assistente se não existir
        if (!document.getElementById('ai-assistant-btn')) {
            const button = document.createElement('button');
            button.id = 'ai-assistant-btn';
            button.type = 'button';
            button.className = 'btn btn-primary btn-sm';
            button.innerHTML = '🤖 IA';
            button.title = 'Assistente de IA (Ctrl+Shift+A)';
            button.style.cssText = 'position: fixed; bottom: 20px; right: 20px; z-index: 999;';
            
            document.body.appendChild(button);
            AIAssistant.elements.aiButton = button;
            AIAssistant.bindEvents();
        }
    }
});

// Exportar para uso global
window.AIAssistant = AIAssistant;

