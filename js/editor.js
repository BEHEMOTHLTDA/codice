/**
 * Códice do Criador - Editor de Artigos
 * 
 * Sistema de edição de artigos com funcionalidades avançadas,
 * incluindo sistema de interconexão wiki-style e editor WYSIWYG.
 * 
 * @author Manus AI
 * @version 1.0
 */

// ===== EDITOR DE ARTIGOS =====
const ArticleEditor = {
    // Configurações
    config: {
        autoSaveInterval: 30000, // 30 segundos
        linkPattern: /@\[([^\]]+)\]/g,
        maxTitleLength: 200,
        maxContentLength: 50000
    },
    
    // Estado do editor
    state: {
        currentArticleId: null,
        hasUnsavedChanges: false,
        autoSaveTimer: null,
        lastSavedContent: '',
        isInitialized: false
    },
    
    // Cache de elementos
    elements: {},
    
    // Inicialização
    init() {
        this.cacheElements();
        this.initEditor();
        this.bindEvents();
        this.startAutoSave();
        this.state.isInitialized = true;
        console.log('Editor de artigos inicializado');
    },
    
    // Cache de elementos DOM
    cacheElements() {
        this.elements = {
            titleField: document.getElementById('article-title'),
            contentField: document.getElementById('article-content'),
            categorySelect: document.getElementById('article-category'),
            tagsField: document.getElementById('article-tags'),
            saveButton: document.getElementById('save-article'),
            previewButton: document.getElementById('preview-article'),
            publishButton: document.getElementById('publish-article'),
            editorContainer: document.querySelector('.editor-container'),
            toolbar: document.querySelector('.editor-toolbar'),
            statusBar: document.querySelector('.editor-status'),
            linkSuggestions: document.getElementById('link-suggestions'),
            customFields: document.querySelectorAll('.custom-field')
        };
    },
    
    // Inicializar editor WYSIWYG
    initEditor() {
        if (!this.elements.contentField) return;
        
        // Configurar editor simples (pode ser substituído por TinyMCE ou CKEditor)
        this.elements.contentField.addEventListener('input', () => {
            this.markAsChanged();
            this.updateWordCount();
            this.processWikiLinks();
        });
        
        // Adicionar toolbar personalizada
        this.createToolbar();
        
        // Configurar área de preview
        this.createPreviewArea();
    },
    
    // Criar toolbar do editor
    createToolbar() {
        if (!this.elements.toolbar) return;
        
        const tools = [
            { name: 'bold', icon: '𝐁', title: 'Negrito', action: () => this.formatText('bold') },
            { name: 'italic', icon: '𝐼', title: 'Itálico', action: () => this.formatText('italic') },
            { name: 'underline', icon: '𝐔', title: 'Sublinhado', action: () => this.formatText('underline') },
            { name: 'separator', type: 'separator' },
            { name: 'h1', icon: 'H1', title: 'Título 1', action: () => this.formatText('h1') },
            { name: 'h2', icon: 'H2', title: 'Título 2', action: () => this.formatText('h2') },
            { name: 'h3', icon: 'H3', title: 'Título 3', action: () => this.formatText('h3') },
            { name: 'separator', type: 'separator' },
            { name: 'link', icon: '🔗', title: 'Link Wiki', action: () => this.insertWikiLink() },
            { name: 'image', icon: '🖼️', title: 'Inserir Imagem', action: () => this.insertImage() },
            { name: 'separator', type: 'separator' },
            { name: 'undo', icon: '↶', title: 'Desfazer', action: () => this.undo() },
            { name: 'redo', icon: '↷', title: 'Refazer', action: () => this.redo() }
        ];
        
        this.elements.toolbar.innerHTML = '';
        
        tools.forEach(tool => {
            if (tool.type === 'separator') {
                const separator = document.createElement('div');
                separator.className = 'toolbar-separator';
                separator.style.cssText = 'width: 1px; height: 24px; background: var(--color-border); margin: 0 var(--space-2);';
                this.elements.toolbar.appendChild(separator);
            } else {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn btn-ghost btn-sm toolbar-btn';
                button.innerHTML = tool.icon;
                button.title = tool.title;
                button.addEventListener('click', tool.action);
                this.elements.toolbar.appendChild(button);
            }
        });
    },
    
    // Criar área de preview
    createPreviewArea() {
        const previewContainer = document.createElement('div');
        previewContainer.id = 'preview-container';
        previewContainer.className = 'preview-container hidden';
        previewContainer.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--color-background);
            padding: var(--space-6);
            overflow-y: auto;
            z-index: 10;
        `;
        
        if (this.elements.editorContainer) {
            this.elements.editorContainer.style.position = 'relative';
            this.elements.editorContainer.appendChild(previewContainer);
        }
    },
    
    // Vincular eventos
    bindEvents() {
        // Eventos do título
        if (this.elements.titleField) {
            this.elements.titleField.addEventListener('input', () => {
                this.markAsChanged();
                this.updateSlug();
            });
        }
        
        // Eventos dos campos personalizados
        this.elements.customFields.forEach(field => {
            field.addEventListener('input', () => this.markAsChanged());
        });
        
        // Eventos dos botões
        if (this.elements.saveButton) {
            this.elements.saveButton.addEventListener('click', () => this.saveArticle());
        }
        
        if (this.elements.previewButton) {
            this.elements.previewButton.addEventListener('click', () => this.togglePreview());
        }
        
        if (this.elements.publishButton) {
            this.elements.publishButton.addEventListener('click', () => this.publishArticle());
        }
        
        // Atalhos de teclado
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 's':
                        e.preventDefault();
                        this.saveArticle();
                        break;
                    case 'b':
                        e.preventDefault();
                        this.formatText('bold');
                        break;
                    case 'i':
                        e.preventDefault();
                        this.formatText('italic');
                        break;
                    case 'k':
                        e.preventDefault();
                        this.insertWikiLink();
                        break;
                }
            }
        });
        
        // Detectar mudanças não salvas
        window.addEventListener('beforeunload', (e) => {
            if (this.state.hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = 'Você tem alterações não salvas. Deseja realmente sair?';
                return e.returnValue;
            }
        });
    },
    
    // Formatação de texto
    formatText(command) {
        const textarea = this.elements.contentField;
        if (!textarea) return;
        
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);
        const beforeText = textarea.value.substring(0, start);
        const afterText = textarea.value.substring(end);
        
        let formattedText = '';
        
        switch (command) {
            case 'bold':
                formattedText = `**${selectedText || 'texto em negrito'}**`;
                break;
            case 'italic':
                formattedText = `*${selectedText || 'texto em itálico'}*`;
                break;
            case 'underline':
                formattedText = `<u>${selectedText || 'texto sublinhado'}</u>`;
                break;
            case 'h1':
                formattedText = `# ${selectedText || 'Título 1'}`;
                break;
            case 'h2':
                formattedText = `## ${selectedText || 'Título 2'}`;
                break;
            case 'h3':
                formattedText = `### ${selectedText || 'Título 3'}`;
                break;
        }
        
        textarea.value = beforeText + formattedText + afterText;
        
        // Reposicionar cursor
        const newPosition = start + formattedText.length;
        textarea.setSelectionRange(newPosition, newPosition);
        textarea.focus();
        
        this.markAsChanged();
    },
    
    // Inserir link wiki
    insertWikiLink() {
        const textarea = this.elements.contentField;
        if (!textarea) return;
        
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);
        
        // Mostrar modal de seleção de artigo
        this.showLinkModal(selectedText, (linkText) => {
            const beforeText = textarea.value.substring(0, start);
            const afterText = textarea.value.substring(end);
            const wikiLink = `@[${linkText}]`;
            
            textarea.value = beforeText + wikiLink + afterText;
            
            const newPosition = start + wikiLink.length;
            textarea.setSelectionRange(newPosition, newPosition);
            textarea.focus();
            
            this.markAsChanged();
            this.processWikiLinks();
        });
    },
    
    // Mostrar modal de seleção de link
    showLinkModal(selectedText, callback) {
        // Criar modal simples
        const modal = document.createElement('div');
        modal.className = 'modal-backdrop show';
        modal.innerHTML = `
            <div class="modal show">
                <div class="modal-header">
                    <h3 class="modal-title">Inserir Link Wiki</h3>
                    <button type="button" class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nome do Artigo</label>
                        <input type="text" id="link-article-name" class="form-input" 
                               placeholder="Digite o nome do artigo..." 
                               value="${selectedText}">
                        <div class="form-help">
                            Digite o nome exato do artigo que você quer referenciar.
                        </div>
                    </div>
                    <div id="article-suggestions" class="mt-4"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancel-link">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="insert-link">Inserir Link</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        const input = modal.querySelector('#link-article-name');
        const suggestionsDiv = modal.querySelector('#article-suggestions');
        const insertBtn = modal.querySelector('#insert-link');
        const cancelBtn = modal.querySelector('#cancel-link');
        const closeBtn = modal.querySelector('.modal-close');
        
        // Focar no input
        input.focus();
        input.select();
        
        // Buscar sugestões
        const searchArticles = CodiceCriador.debounce((query) => {
            if (query.length >= 2) {
                // Simular busca de artigos (implementar com AJAX)
                const suggestions = [
                    'Lorde Aldric, o Protetor',
                    'Cidade de Pedraverde',
                    'A Grande Guerra dos Dragões',
                    'Reino de Eldoria'
                ].filter(article => 
                    article.toLowerCase().includes(query.toLowerCase())
                );
                
                if (suggestions.length > 0) {
                    suggestionsDiv.innerHTML = `
                        <div class="text-sm font-medium mb-2">Artigos encontrados:</div>
                        <div class="space-y-1">
                            ${suggestions.map(article => 
                                `<button type="button" class="btn btn-ghost btn-sm w-full text-left suggestion-item" 
                                         data-article="${article}">${article}</button>`
                            ).join('')}
                        </div>
                    `;
                    
                    // Adicionar eventos às sugestões
                    suggestionsDiv.querySelectorAll('.suggestion-item').forEach(btn => {
                        btn.addEventListener('click', () => {
                            input.value = btn.dataset.article;
                        });
                    });
                } else {
                    suggestionsDiv.innerHTML = '<div class="text-sm text-muted">Nenhum artigo encontrado.</div>';
                }
            } else {
                suggestionsDiv.innerHTML = '';
            }
        }, 300);
        
        input.addEventListener('input', (e) => searchArticles(e.target.value));
        
        // Eventos dos botões
        insertBtn.addEventListener('click', () => {
            const linkText = input.value.trim();
            if (linkText) {
                callback(linkText);
                modal.remove();
            }
        });
        
        const closeModal = () => modal.remove();
        cancelBtn.addEventListener('click', closeModal);
        closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
        
        // Enter para inserir
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                insertBtn.click();
            } else if (e.key === 'Escape') {
                closeModal();
            }
        });
    },
    
    // Processar links wiki no texto
    processWikiLinks() {
        if (!this.elements.contentField) return;
        
        const content = this.elements.contentField.value;
        const links = [];
        let match;
        
        while ((match = this.config.linkPattern.exec(content)) !== null) {
            links.push({
                fullMatch: match[0],
                articleName: match[1],
                index: match.index
            });
        }
        
        // Atualizar lista de links referenciados
        this.updateReferencedLinks(links);
    },
    
    // Atualizar lista de links referenciados
    updateReferencedLinks(links) {
        const linksContainer = document.getElementById('referenced-links');
        if (!linksContainer) return;
        
        if (links.length === 0) {
            linksContainer.innerHTML = '<div class="text-sm text-muted">Nenhum link referenciado.</div>';
            return;
        }
        
        const uniqueLinks = [...new Set(links.map(link => link.articleName))];
        
        linksContainer.innerHTML = `
            <div class="text-sm font-medium mb-2">Artigos Referenciados (${uniqueLinks.length}):</div>
            <div class="space-y-1">
                ${uniqueLinks.map(articleName => `
                    <div class="flex items-center justify-between p-2 bg-surface rounded">
                        <span class="text-sm">${articleName}</span>
                        <div class="flex gap-1">
                            <button type="button" class="btn btn-ghost btn-sm" 
                                    onclick="ArticleEditor.openArticle('${articleName}')" 
                                    title="Abrir artigo">
                                👁️
                            </button>
                            <button type="button" class="btn btn-ghost btn-sm" 
                                    onclick="ArticleEditor.createArticle('${articleName}')" 
                                    title="Criar artigo">
                                ➕
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    },
    
    // Inserir imagem
    insertImage() {
        // Implementar upload de imagem
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                this.uploadImage(file);
            }
        });
        input.click();
    },
    
    // Upload de imagem
    uploadImage(file) {
        // Implementar upload via AJAX
        const formData = new FormData();
        formData.append('image', file);
        formData.append('world_id', this.getCurrentWorldId());
        
        // Mostrar loading
        CodiceCriador.showNotification('Fazendo upload da imagem...', 'info', 2000);
        
        // Simular upload (implementar com fetch)
        setTimeout(() => {
            const imageUrl = URL.createObjectURL(file);
            const imageMarkdown = `![${file.name}](${imageUrl})`;
            this.insertTextAtCursor(imageMarkdown);
            CodiceCriador.showNotification('Imagem inserida com sucesso!', 'success');
        }, 1000);
    },
    
    // Inserir texto na posição do cursor
    insertTextAtCursor(text) {
        const textarea = this.elements.contentField;
        if (!textarea) return;
        
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const beforeText = textarea.value.substring(0, start);
        const afterText = textarea.value.substring(end);
        
        textarea.value = beforeText + text + afterText;
        
        const newPosition = start + text.length;
        textarea.setSelectionRange(newPosition, newPosition);
        textarea.focus();
        
        this.markAsChanged();
    },
    
    // Toggle preview
    togglePreview() {
        const previewContainer = document.getElementById('preview-container');
        const editorContent = document.querySelector('.editor-content');
        
        if (!previewContainer || !editorContent) return;
        
        if (previewContainer.classList.contains('hidden')) {
            // Mostrar preview
            const content = this.elements.contentField.value;
            const processedContent = this.processContentForPreview(content);
            
            previewContainer.innerHTML = `
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Preview do Artigo</h3>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="ArticleEditor.togglePreview()">
                        Voltar ao Editor
                    </button>
                </div>
                <div class="prose max-w-none">
                    ${processedContent}
                </div>
            `;
            
            previewContainer.classList.remove('hidden');
            editorContent.style.display = 'none';
            
            if (this.elements.previewButton) {
                this.elements.previewButton.textContent = 'Editar';
            }
        } else {
            // Voltar ao editor
            previewContainer.classList.add('hidden');
            editorContent.style.display = 'block';
            
            if (this.elements.previewButton) {
                this.elements.previewButton.textContent = 'Preview';
            }
        }
    },
    
    // Processar conteúdo para preview
    processContentForPreview(content) {
        // Converter markdown básico para HTML
        let html = content
            .replace(/### (.*)/g, '<h3>$1</h3>')
            .replace(/## (.*)/g, '<h2>$1</h2>')
            .replace(/# (.*)/g, '<h1>$1</h1>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n\n/g, '</p><p>')
            .replace(/\n/g, '<br>');
        
        // Processar links wiki
        html = html.replace(this.config.linkPattern, (match, articleName) => {
            return `<a href="#" class="wiki-link" data-article="${articleName}">${articleName}</a>`;
        });
        
        return `<p>${html}</p>`;
    },
    
    // Marcar como alterado
    markAsChanged() {
        this.state.hasUnsavedChanges = true;
        
        if (this.elements.saveButton) {
            this.elements.saveButton.classList.add('btn-warning');
            this.elements.saveButton.textContent = 'Salvar *';
        }
        
        this.updateStatus('Alterações não salvas');
    },
    
    // Marcar como salvo
    markAsSaved() {
        this.state.hasUnsavedChanges = false;
        
        if (this.elements.saveButton) {
            this.elements.saveButton.classList.remove('btn-warning');
            this.elements.saveButton.textContent = 'Salvar';
        }
        
        this.updateStatus('Salvo automaticamente');
    },
    
    // Atualizar status
    updateStatus(message) {
        if (this.elements.statusBar) {
            this.elements.statusBar.textContent = message;
        }
    },
    
    // Atualizar contagem de palavras
    updateWordCount() {
        if (!this.elements.contentField) return;
        
        const content = this.elements.contentField.value;
        const wordCount = content.trim() ? content.trim().split(/\s+/).length : 0;
        const charCount = content.length;
        
        const wordCountElement = document.getElementById('word-count');
        if (wordCountElement) {
            wordCountElement.textContent = `${wordCount} palavras, ${charCount} caracteres`;
        }
    },
    
    // Atualizar slug do artigo
    updateSlug() {
        const titleField = this.elements.titleField;
        const slugField = document.getElementById('article-slug');
        
        if (titleField && slugField) {
            const slug = CodiceCriador.slugify(titleField.value);
            slugField.value = slug;
        }
    },
    
    // Salvar artigo
    saveArticle() {
        if (!this.validateArticle()) return;
        
        const formData = this.getFormData();
        
        // Mostrar loading
        CodiceCriador.setButtonLoading(this.elements.saveButton, true);
        
        // Simular salvamento (implementar com fetch)
        setTimeout(() => {
            this.markAsSaved();
            CodiceCriador.setButtonLoading(this.elements.saveButton, false);
            CodiceCriador.showNotification('Artigo salvo com sucesso!', 'success');
            
            this.state.lastSavedContent = this.elements.contentField.value;
        }, 1000);
    },
    
    // Publicar artigo
    publishArticle() {
        if (!this.validateArticle()) return;
        
        // Confirmar publicação
        if (!confirm('Tem certeza que deseja publicar este artigo? Ele ficará visível para todos os colaboradores.')) {
            return;
        }
        
        const formData = this.getFormData();
        formData.is_published = true;
        
        // Implementar publicação
        CodiceCriador.showNotification('Artigo publicado com sucesso!', 'success');
    },
    
    // Validar artigo
    validateArticle() {
        const title = this.elements.titleField?.value.trim();
        const content = this.elements.contentField?.value.trim();
        
        if (!title) {
            CodiceCriador.showNotification('O título é obrigatório.', 'error');
            this.elements.titleField?.focus();
            return false;
        }
        
        if (title.length > this.config.maxTitleLength) {
            CodiceCriador.showNotification(`O título não pode ter mais de ${this.config.maxTitleLength} caracteres.`, 'error');
            this.elements.titleField?.focus();
            return false;
        }
        
        if (!content) {
            CodiceCriador.showNotification('O conteúdo é obrigatório.', 'error');
            this.elements.contentField?.focus();
            return false;
        }
        
        if (content.length > this.config.maxContentLength) {
            CodiceCriador.showNotification(`O conteúdo não pode ter mais de ${this.config.maxContentLength} caracteres.`, 'error');
            this.elements.contentField?.focus();
            return false;
        }
        
        return true;
    },
    
    // Obter dados do formulário
    getFormData() {
        const data = {
            title: this.elements.titleField?.value.trim(),
            content: this.elements.contentField?.value.trim(),
            category_id: this.elements.categorySelect?.value,
            tags: this.elements.tagsField?.value.trim()
        };
        
        // Adicionar campos personalizados
        this.elements.customFields.forEach(field => {
            data[field.name] = field.value;
        });
        
        return data;
    },
    
    // Auto-save
    startAutoSave() {
        this.state.autoSaveTimer = setInterval(() => {
            if (this.state.hasUnsavedChanges && this.elements.contentField) {
                const currentContent = this.elements.contentField.value;
                if (currentContent !== this.state.lastSavedContent) {
                    this.autoSave();
                }
            }
        }, this.config.autoSaveInterval);
    },
    
    // Auto-save silencioso
    autoSave() {
        if (!this.validateArticle()) return;
        
        const formData = this.getFormData();
        formData.auto_save = true;
        
        // Implementar auto-save via AJAX
        // Por enquanto, apenas simular
        this.state.lastSavedContent = this.elements.contentField.value;
        this.updateStatus('Salvo automaticamente às ' + new Date().toLocaleTimeString());
    },
    
    // Utilitários
    getCurrentWorldId() {
        return document.querySelector('[data-world-id]')?.dataset.worldId || null;
    },
    
    openArticle(articleName) {
        // Implementar abertura de artigo
        console.log('Abrindo artigo:', articleName);
    },
    
    createArticle(articleName) {
        // Implementar criação de artigo
        console.log('Criando artigo:', articleName);
    },
    
    undo() {
        document.execCommand('undo');
    },
    
    redo() {
        document.execCommand('redo');
    }
};

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.editor-container')) {
        ArticleEditor.init();
    }
});

// Exportar para uso global
window.ArticleEditor = ArticleEditor;

